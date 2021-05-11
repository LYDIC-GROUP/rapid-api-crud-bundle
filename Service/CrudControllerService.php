<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\Service;

use LydicGroup\RapidApiCrudBundle\Dto\ControllerConfig;
use LydicGroup\RapidApiCrudBundle\Exception\NotFoundException;
use LydicGroup\RapidApiCrudBundle\Exception\ValidationException;
use LydicGroup\RapidApiCrudBundle\Factory\CriteriaFactory;
use LydicGroup\RapidApiCrudBundle\Factory\SortFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;

class CrudControllerService
{
    protected CrudService $crudService;
    protected CriteriaFactory $criteriaFactory;
    protected SortFactory $sortFactory;
    protected MessageBusInterface $messageBus;

    public function __construct(CrudService $crudService, CriteriaFactory $criteriaFactory, SortFactory $sortFactory, MessageBusInterface $messageBus)
    {
        $this->crudService = $crudService;
        $this->criteriaFactory = $criteriaFactory;
        $this->sortFactory = $sortFactory;
        $this->messageBus = $messageBus;
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

            $data = $this->crudService->list($config->getEntityClassName(), $page, $limit, $criteria, $sorter);
            return new JsonResponse($data, Response::HTTP_OK);
        } catch (\Throwable $throwable) {
            throw $throwable;

//            return $this->badResponse($throwable);
        }
    }

    public function find(ControllerConfig $config, string $id): JsonResponse
    {
        if (!$config->isFindActionEnabled()) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $data = $this->crudService->find($config->getEntityClassName(), $id);
            return new JsonResponse($data, Response::HTTP_OK);
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
            $this->crudService->create($config->getEntityClassName(), $request->toArray());
            return new JsonResponse( null, Response::HTTP_CREATED);
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
            $this->crudService->update($config->getEntityClassName(), $id, $request->toArray());
            return new JsonResponse(null, Response::HTTP_OK);
        } catch (\Throwable $throwable) {
            return $this->badResponse($throwable);
        }
    }

    public function delete(ControllerConfig $config, string $id): JsonResponse
    {
        if (!$config->isDeleteActionEnabled()) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $this->crudService->delete($config->getEntityClassName(), $id);
            return new JsonResponse(null, Response::HTTP_OK);
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