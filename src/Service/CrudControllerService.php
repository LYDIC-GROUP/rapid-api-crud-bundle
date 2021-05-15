<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\Service;

use LydicGroup\RapidApiCrudBundle\Dto\ControllerConfig;
use LydicGroup\RapidApiCrudBundle\Exception\NotFoundException;
use LydicGroup\RapidApiCrudBundle\Exception\ValidationException;
use LydicGroup\RapidApiCrudBundle\Factory\CriteriaFactory;
use LydicGroup\RapidApiCrudBundle\Factory\SortFactory;
use LydicGroup\RapidApiCrudBundle\Serializer\Serializer;
use LydicGroup\RapidApiCrudBundle\Context\RapidApiContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;

class CrudControllerService
{
    protected CrudService $crudService;
    protected CriteriaFactory $criteriaFactory;
    protected SortFactory $sortFactory;
    protected MessageBusInterface $messageBus;
    protected SerializerInterface $serializer;

    public function __construct(CrudService $crudService, CriteriaFactory $criteriaFactory, SortFactory $sortFactory, MessageBusInterface $messageBus, SerializerInterface $serializer)
    {
        $this->crudService = $crudService;
        $this->criteriaFactory = $criteriaFactory;
        $this->sortFactory = $sortFactory;
        $this->messageBus = $messageBus;
        $this->serializer = $serializer;
    }

    /**
     * Returns a JsonResponse that uses the serializer component if enabled, or json_encode.
     */
    protected function json($data, int $status = 200, array $headers = [], array $context = []): JsonResponse
    {
        $json = $this->serializer->serialize($data, 'json', array_merge([
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
        ], $context));

        return new JsonResponse($json, $status, $headers, true);
    }

    public function list(RapidApiContext $context): JsonResponse
    {
        if (!$context->getConfig()->isListActionEnabled()) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $page = (int) $context->getRequest()->query->get('page', 1);
        $limit = (int) $context->getRequest()->query->get('limit', 10);

        try {
            $criteria = $this->criteriaFactory->create($context);
            $sorter = $this->sortFactory->create($context);

            $paginator = $this->crudService->list($context->getConfig()->getEntityClassName(), $page, $limit, $criteria, $sorter);
            return $this->json(
                $paginator->getIterator(),
                Response::HTTP_OK,
                [
                    'Paging-rows' => $paginator->count(),
                    'Paging-page' => $page,
                    'Paging-limit' => $paginator->getQuery()->getMaxResults()
                ],
                [
                    'groups' => 'list'
                ]
            );
        } catch (\Throwable $throwable) {
            throw $throwable;
            return $this->badResponse($throwable);
        }
    }

    public function find(RapidApiContext $context, string $id): JsonResponse
    {
        if (!$context->getConfig()->isFindActionEnabled()) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $entity = $this->crudService->find($context->getConfig()->getEntityClassName(), $id);
            return $this->json($entity, Response::HTTP_OK, [], ['groups' => 'detail']);
        } catch (\Throwable $throwable) {
            return $this->badResponse($throwable);
        }
    }

    public function create(RapidApiContext $context): JsonResponse
    {
        if (!$context->getConfig()->isCreateActionEnabled()) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $entity = $this->crudService->create($context->getConfig()->getEntityClassName(), $context->getRequest()->toArray());
            return $this->json( $entity, Response::HTTP_OK, [],  ['groups' => 'detail']);
        } catch (\Throwable $throwable) {
            return $this->badResponse($throwable);
        }
    }

    public function update(RapidApiContext $context, string $id): JsonResponse
    {
        if (!$context->getConfig()->isUpdateActionEnabled()) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $entity = $this->crudService->update($context->getConfig()->getEntityClassName(), $id, $context->getRequest()->toArray());
            return $this->json($entity, Response::HTTP_OK, [], ['groups' => 'detail']);
        } catch (\Throwable $throwable) {
            return $this->badResponse($throwable);
        }
    }

    public function delete(RapidApiContext $context, string $id): Response
    {
        if (!$context->getConfig()->isDeleteActionEnabled()) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $this->crudService->delete($context->getConfig()->getEntityClassName(), $id);
            return new Response(null, Response::HTTP_NO_CONTENT);
        } catch (\Throwable $throwable) {
            return $this->badResponse($throwable);
        }
    }

    private function badResponse(\Throwable $throwable = null): JsonResponse
    {
        if ($throwable instanceof HandlerFailedException) {
            //Handle the actual exception from command handler when it fails
            $throwable = $throwable->getPrevious();
        }

        if ($throwable instanceof NotFoundException) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        if ($throwable instanceof ValidationException) {
            return new JsonResponse(['message' => $throwable->getPrevious()->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
    }
}