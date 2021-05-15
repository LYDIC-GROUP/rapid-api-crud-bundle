<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\Controller;

use LydicGroup\RapidApiCrudBundle\Dto\ControllerConfig;
use LydicGroup\RapidApiCrudBundle\Service\CrudControllerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class RapidApiCrudController extends AbstractController
{
    private CrudControllerService $crudControllerService;

    public function __construct(CrudControllerService $crudControllerService)
    {
        $this->crudControllerService = $crudControllerService;
    }

    protected abstract function controllerConfig(): ControllerConfig;

    /**
     * @Route("", name="list", methods={"GET"})
     */
    public function list(Request $request): JsonResponse
    {
        return $this->crudControllerService->list($this->controllerConfig(), $request);
    }

    /**
     * @Route("/{id}", name="find", methods={"GET"})
     */
    public function find(string $id): JsonResponse
    {
        return $this->crudControllerService->find($this->controllerConfig(), $id);
    }

    /**
     * @Route("", name="create", methods={"POST"})
     */
    public function create(Request $request): JsonResponse
    {
        return $this->crudControllerService->create($this->controllerConfig(), $request);
    }

    /**
     * @Route("/{id}", name="update", methods={"PUT"})
     */
    public function update(string $id, Request $request): JsonResponse
    {
        return $this->crudControllerService->update($this->controllerConfig(), $id, $request);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     */
    public function delete(string $id): Response
    {
        return $this->crudControllerService->delete($this->controllerConfig(), $id);
    }
}