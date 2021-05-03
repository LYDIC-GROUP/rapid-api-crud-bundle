<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\Controller;

use LydicGroup\RapidApiCrudBundle\Command\CreateEntityCommand;
use LydicGroup\RapidApiCrudBundle\Command\DeleteEntityCommand;
use LydicGroup\RapidApiCrudBundle\Command\UpdateEntityCommand;
use LydicGroup\RapidApiCrudBundle\Dto\ControllerConfig;
use LydicGroup\RapidApiCrudBundle\Service\CrudService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class RapidApiCrudController extends AbstractController
{
    private CrudService $crudService;

    public function __construct(CrudService $crudService)
    {
        $this->crudService = $crudService;
    }

    protected abstract function controllerConfig(): ControllerConfig;

    /**
     * @Route("", name="list", methods={"GET"})
     */
    public function list(Request $request): JsonResponse
    {
        if (!$this->controllerConfig()->listActionEnabled) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $data = $this->crudService->list($this->controllerConfig(), $request);
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
        if (!$this->controllerConfig()->findActionEnabled) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $data = $this->crudService->find($this->controllerConfig(), $id);
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
        if (!$this->controllerConfig()->createActionEnabled) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $command = new CreateEntityCommand($this->controllerConfig()->entityClassName, $request->toArray());
            $this->dispatchMessage($command);

            return new JsonResponse([
                'message' => $this->crudService->createdMessage($this->controllerConfig()->entityClassName)
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
        if (!$this->controllerConfig()->updateActionEnabled) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $command = new UpdateEntityCommand($this->controllerConfig()->entityClassName, $id, $request->toArray());
            $this->dispatchMessage($command);

            return new JsonResponse([
                'message' => $this->crudService->updatedMessage($this->controllerConfig()->entityClassName)
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
        if (!$this->controllerConfig()->deleteActionEnabled) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $command = new DeleteEntityCommand($this->controllerConfig()->entityClassName, $id);
            $this->dispatchMessage($command);

            return new JsonResponse([
                'message' => $this->crudService->deletedMessage($this->controllerConfig()->entityClassName)
            ], Response::HTTP_OK);
        } catch (\Throwable $exception) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }
    }
}