<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\Controller;

use LydicGroup\RapidApiCrudBundle\Command\CreateEntityCommand;
use LydicGroup\RapidApiCrudBundle\Command\DeleteEntityCommand;
use LydicGroup\RapidApiCrudBundle\Command\UpdateEntityCommand;
use LydicGroup\RapidApiCrudBundle\Service\CrudService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class RapidApiCrudController extends AbstractController
{
    protected bool $listEnabled = true;
    protected bool $findEnabled = true;
    protected bool $createEnabled = true;
    protected bool $updateEnabled = true;
    protected bool $deleteEnabled = true;

    private CrudService $crudService;

    public function __construct(CrudService $crudService)
    {
        $this->crudService = $crudService;
    }

    protected abstract function entityClassName(): string;

    /**
     * @Route("", name="list", methods={"GET"})
     */
    public function list(Request $request): JsonResponse
    {
        if (!$this->listEnabled) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $data = $this->crudService->list($this->entityClassName(), $request);
            return new JsonResponse($data, Response::HTTP_OK);
        } catch (\Throwable $throwable) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/{id}", name="find", methods={"GET"})
     */
    public function find(string $id): JsonResponse
    {
        try {
            $data = $this->crudService->find($this->entityClassName(), $id);
            return new JsonResponse($data, Response::HTTP_OK);
        } catch (\Throwable $throwable) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("", name="create", methods={"POST"})
     */
    public function create(Request $request): JsonResponse
    {
        if (!$this->createEnabled) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $command = new CreateEntityCommand($this->entityClassName(), $request->toArray());
            $this->dispatchMessage($command);

            return new JsonResponse([
                'message' => $this->crudService->createdMessage($this->entityClassName())
            ], Response::HTTP_CREATED);
        } catch (\Throwable $exception) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/{id}", name="update", methods={"PUT"})
     */
    public function update(string $id, Request $request): JsonResponse
    {
        if (!$this->updateEnabled) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $command = new UpdateEntityCommand($this->entityClassName(), $id, $request->toArray());
            $this->dispatchMessage($command);

            return new JsonResponse([
                'message' => $this->crudService->updatedMessage($this->entityClassName())
            ], Response::HTTP_OK);
        } catch (\Throwable $exception) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     */
    public function delete(string $id): JsonResponse
    {
        if (!$this->deleteEnabled) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $command = new DeleteEntityCommand($this->entityClassName(), $id);
            $this->dispatchMessage($command);

            return new JsonResponse([
                'message' => $this->crudService->deletedMessage($this->entityClassName())
            ], Response::HTTP_OK);
        } catch (\Throwable $exception) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }
    }
}