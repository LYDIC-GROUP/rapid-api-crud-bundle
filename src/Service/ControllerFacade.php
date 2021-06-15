<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\Service;

use Doctrine\ORM\Tools\Pagination\Paginator;
use LydicGroup\RapidApiCrudBundle\Enum\SerializerGroups;
use LydicGroup\RapidApiCrudBundle\Exception\NotFoundException;
use LydicGroup\RapidApiCrudBundle\Exception\ValidationException;
use LydicGroup\RapidApiCrudBundle\Factory\CriteriaFactory;
use LydicGroup\RapidApiCrudBundle\Factory\SortFactory;
use LydicGroup\RapidApiCrudBundle\Context\RapidApiContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;
use function Symfony\Component\String\u;

/**
 * Class ControllerFacade
 * @package LydicGroup\RapidApiCrudBundle\Service
 *
 * This class acts as a gateway and it should contain everything regarding:
 * - Requests (e.g. reading/manipulating input data)
 * - Responses (e.g. serializing output data, defining the correct response/HTTP status code)
 */
class ControllerFacade
{
    protected CrudService $crudService;
    protected CriteriaFactory $criteriaFactory;
    protected SortFactory $sortFactory;
    protected MessageBusInterface $messageBus;
    protected SerializerInterface $serializer;

    public function __construct(
        CrudService $crudService,
        CriteriaFactory $criteriaFactory,
        SortFactory $sortFactory,
        MessageBusInterface $messageBus,
        SerializerInterface $serializer
    )
    {
        $this->crudService = $crudService;
        $this->criteriaFactory = $criteriaFactory;
        $this->sortFactory = $sortFactory;
        $this->messageBus = $messageBus;
        $this->serializer = $serializer;
    }

    private function paramToAssocName(string $assocName): string
    {
        return u($assocName)->camel()->toString();
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

        $page = (int)$context->getRequest()->query->get('page', 1);
        $limit = (int)$context->getRequest()->query->get('limit', 10);

        try {
            $criteria = $this->criteriaFactory->create($context);
            $sorter = $this->sortFactory->create($context);

            $paginator = $this->crudService->list($context->getEntityClassName(), $page, $limit, $criteria, $sorter);
            return $this->json(
                $paginator->getIterator(),
                Response::HTTP_OK,
                [
                    'Paging-rows' => $paginator->count(),
                    'Paging-page' => $page,
                    'Paging-limit' => $paginator->getQuery()->getMaxResults()
                ],
                [
                    'groups' => SerializerGroups::LIST,
                    'include' => $context->getRequest()->get('include')
                ]
            );
        } catch (\Throwable $throwable) {
            return $this->badResponse($throwable);
        }
    }

    public function find(RapidApiContext $context, string $id): JsonResponse
    {
        if (!$context->getConfig()->isFindActionEnabled()) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $entity = $this->crudService->find($context->getEntityClassName(), $id);
            return $this->json($entity, Response::HTTP_OK, [], [
                'groups' => SerializerGroups::DETAIL,
                'include' => $context->getRequest()->get('include')
            ]);
        } catch (\Throwable $throwable) {
            return $this->badResponse($throwable);
        }
    }

    public function findAssoc(RapidApiContext $context, string $id, string $assocName): JsonResponse
    {
        if (!$context->getConfig()->isFindActionEnabled()) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $assocName = $this->paramToAssocName($assocName);
            $associationData = $this->crudService->findAssoc($context->getEntityClassName(), $id, $assocName);

            //TODO: This is a duplicate line of code which is used to set the page header.
            $page = (int)$context->getRequest()->query->get('page', 1);

            if ($associationData instanceof Paginator) {
                return $this->json(
                    $associationData->getIterator(),
                    Response::HTTP_OK,
                    [
                        'Paging-rows' => $associationData->count(),
                        'Paging-page' => $page,
                        'Paging-limit' => $associationData->getQuery()->getMaxResults()
                    ],
                    [
                        'groups' => SerializerGroups::LIST,
                        'include' => $context->getRequest()->get('include')
                    ]
                );
            } else {
                return $this->json($associationData, Response::HTTP_OK, [], [
                    'groups' => SerializerGroups::DETAIL,
                    'include' => $context->getRequest()->get('include')
                ]);
            }
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
            $entity = $this->crudService->create($context->getEntityClassName(), $context->getRequest()->toArray());
            return $this->json($entity, Response::HTTP_CREATED, [], ['groups' => SerializerGroups::DETAIL]);
        } catch (\Throwable $throwable) {
            return $this->badResponse($throwable);
        }
    }

    public function createAssoc(RapidApiContext $context, string $id, string $assocName, $assocId): JsonResponse
    {
        if (!$context->getConfig()->isCreateActionEnabled()) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $assocName = $this->paramToAssocName($assocName);
            $entity = $this->crudService->createAssoc($context->getEntityClassName(), $id, $assocName, $assocId);
            return $this->json($entity, Response::HTTP_CREATED, [], ['groups' => SerializerGroups::DETAIL]);
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
            $entity = $this->crudService->update($context->getEntityClassName(), $id, $context->getRequest()->toArray());
            return $this->json($entity, Response::HTTP_OK, [], ['groups' => SerializerGroups::DETAIL]);
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
            $this->crudService->delete($context->getEntityClassName(), $id);
            return new Response(null, Response::HTTP_NO_CONTENT);
        } catch (\Throwable $throwable) {
            return $this->badResponse($throwable);
        }
    }

    public function deleteAssoc(RapidApiContext $context, string $id, string $assocName, $assocId): Response
    {
        if (!$context->getConfig()->isDeleteActionEnabled()) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $assocName = $this->paramToAssocName($assocName);
            $this->crudService->deleteAssoc($context->getEntityClassName(), $id, $assocName, $assocId);
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

        if ($_ENV['APP_ENV'] == 'dev') {
            throw $throwable;
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
