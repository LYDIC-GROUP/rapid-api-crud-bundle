<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\Service;

use LydicGroup\RapidApiCrudBundle\Dto\ControllerConfig;
use LydicGroup\RapidApiCrudBundle\Exception\NotFoundException;
use LydicGroup\RapidApiCrudBundle\Exception\ValidationException;
use LydicGroup\RapidApiCrudBundle\Factory\CriteriaFactory;
use LydicGroup\RapidApiCrudBundle\Factory\SortFactory;
use LydicGroup\RapidApiCrudBundle\Serializer\Serializer;
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

    public function list(ControllerConfig $config, Request $request): JsonResponse
    {
        if (!$config->isListActionEnabled()) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 10);

        try {
            $criteria = $this->criteriaFactory->create($config, $request);
            $sorter = $this->sortFactory->create($config, $request);

            $paginator = $this->crudService->list($config->getEntityClassName(), $page, $limit, $criteria, $sorter);
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
            return $this->badResponse($throwable);
        }
    }

    public function find(ControllerConfig $config, string $id): JsonResponse
    {
        if (!$config->isFindActionEnabled()) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $entity = $this->crudService->find($config->getEntityClassName(), $id);
            return $this->json($entity, Response::HTTP_OK, [], ['groups' => 'detail']);
        } catch (\Throwable $throwable) {
            return $this->badResponse($throwable);
        }
    }

    public function create(ControllerConfig $config, Request $request): JsonResponse
    {
        if (!$config->isCreateActionEnabled()) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $entity = $this->crudService->create($config->getEntityClassName(), $request->toArray());
            return $this->json( $entity, Response::HTTP_OK, [],  ['groups' => 'detail']);
        } catch (\Throwable $throwable) {
            return $this->badResponse($throwable);
        }
    }

    public function update(ControllerConfig $config, string $id, Request $request): JsonResponse
    {
        if (!$config->isUpdateActionEnabled()) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $entity = $this->crudService->update($config->getEntityClassName(), $id, $request->toArray());
            return $this->json($entity, Response::HTTP_OK, [], ['groups' => 'detail']);
        } catch (\Throwable $throwable) {
            return $this->badResponse($throwable);
        }
    }

    public function delete(ControllerConfig $config, string $id): Response
    {
        if (!$config->isDeleteActionEnabled()) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $this->crudService->delete($config->getEntityClassName(), $id);
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