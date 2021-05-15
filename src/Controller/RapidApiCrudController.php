<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\Controller;

use LydicGroup\RapidApiCrudBundle\Dto\ControllerConfig;
use LydicGroup\RapidApiCrudBundle\Provider\RapidApiContextProvider;
use LydicGroup\RapidApiCrudBundle\Service\CrudControllerService;
use LydicGroup\RapidApiCrudBundle\Context\RapidApiContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class RapidApiCrudController extends AbstractController
{
    private CrudControllerService $crudControllerService;
    private RapidApiContextProvider $contextProvider;

    public function __construct(CrudControllerService $crudControllerService, RapidApiContextProvider $contextProvider)
    {
        $this->crudControllerService = $crudControllerService;
        $this->contextProvider = $contextProvider;
    }

    public abstract function controllerConfig(): ControllerConfig;

    /**
     * @Route("", name="list", methods={"GET"})
     */
    public function list(Request $request): JsonResponse
    {
        return $this->crudControllerService->list($this->contextProvider->getContext());
    }

    /**
     * @Route("/{id}", name="find", methods={"GET"})
     */
    public function find(string $id): JsonResponse
    {
        return $this->crudControllerService->find($this->contextProvider->getContext(), $id);
    }

    /**
     * @Route("", name="create", methods={"POST"})
     */
    public function create(Request $request): JsonResponse
    {
        return $this->crudControllerService->create($this->contextProvider->getContext());
    }

    /**
     * @Route("/{id}", name="update", methods={"PUT"})
     */
    public function update(string $id, Request $request): JsonResponse
    {
        return $this->crudControllerService->update($this->contextProvider->getContext(), $id);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     */
    public function delete(string $id): Response
    {
        return $this->crudControllerService->delete($this->contextProvider->getContext(), $id);
    }
}