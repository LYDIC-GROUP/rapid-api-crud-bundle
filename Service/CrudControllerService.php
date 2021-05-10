<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\Service;

use LydicGroup\RapidApiCrudBundle\Command\CreateEntityCommand;
use LydicGroup\RapidApiCrudBundle\Command\DeleteEntityCommand;
use LydicGroup\RapidApiCrudBundle\Command\UpdateEntityCommand;
use LydicGroup\RapidApiCrudBundle\Dto\ControllerConfig;
use LydicGroup\RapidApiCrudBundle\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;

class CrudControllerService
{
    protected CrudService $crudService;
    protected MessageBusInterface $messageBus;

    public function __construct(CrudService $crudService, MessageBusInterface $messageBus)
    {
        $this->crudService = $crudService;
        $this->messageBus = $messageBus;
    }

    public function list(ControllerConfig $config, Request $request): JsonResponse
    {
        if (!$config->listActionEnabled) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $criteria = $this->crudService->criteria($config->entityClassName, $request);
            $data = $this->crudService->list($config->entityClassName, $criteria);
            return new JsonResponse($data, Response::HTTP_OK);
        } catch (\Throwable $throwable) {
            return $this->badResponse($throwable);
        }
    }

    public function find(ControllerConfig $config, string $id): JsonResponse
    {
        if (!$config->findActionEnabled) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $data = $this->crudService->find($config->entityClassName, $id);
            return new JsonResponse($data, Response::HTTP_OK);
        } catch (\Throwable $throwable) {
            return $this->badResponse($throwable);
        }
    }

    public function create(ControllerConfig $config, Request $request): JsonResponse
    {
        if (!$config->createActionEnabled) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $this->crudService->create($config->entityClassName, $request->toArray());
            return new JsonResponse( null, Response::HTTP_CREATED);
        } catch (\Throwable $throwable) {
            return $this->badResponse($throwable);
        }
    }

    public function update(ControllerConfig $config, string $id, Request $request): JsonResponse
    {
        if (!$config->updateActionEnabled) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $this->crudService->update($config->entityClassName, $id, $request->toArray());
            return new JsonResponse(null, Response::HTTP_OK);
        } catch (\Throwable $throwable) {
            return $this->badResponse($throwable);
        }
    }

    public function delete(ControllerConfig $config, string $id): JsonResponse
    {
        if (!$config->deleteActionEnabled) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $this->crudService->delete($config->entityClassName, $id);
            return new JsonResponse(null, Response::HTTP_OK);
        } catch (\Throwable $throwable) {
            return $this->badResponse($throwable);
        }
    }

    private function badResponse(\Throwable $throwable = null): JsonResponse
    {
        if ($throwable instanceof HandlerFailedException) {
            //Handle the actual exception from command handler when it fails
            if ($throwable->getPrevious() instanceof ValidationException) {
                return new JsonResponse(['message' => $throwable->getPrevious()->getMessage()], Response::HTTP_BAD_REQUEST);
            }
        }

        return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
    }
}