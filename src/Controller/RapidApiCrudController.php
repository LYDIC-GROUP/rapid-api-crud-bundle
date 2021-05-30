<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\Controller;

use LydicGroup\RapidApiCrudBundle\Dto\ControllerConfig;
use LydicGroup\RapidApiCrudBundle\Provider\RapidApiContextProvider;
use LydicGroup\RapidApiCrudBundle\Service\ControllerFacade;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class RapidApiCrudController extends AbstractController
{
    private ControllerFacade $crudFacade;
    private RapidApiContextProvider $contextProvider;

    public function __construct(ControllerFacade $crudControllerService, RapidApiContextProvider $contextProvider)
    {
        $this->crudFacade = $crudControllerService;
        $this->contextProvider = $contextProvider;
    }

    public abstract function entityClassName(): string;

    public function controllerConfig(ControllerConfig $config): ControllerConfig
    {
        return $config;
    }

    /**
     * @Route("", name="list", methods={"GET"})
     */
    public function list(): JsonResponse
    {
        return $this->crudFacade->list($this->contextProvider->getContext());
    }

    /**
     * @Route("/{id}", name="find", methods={"GET"})
     */
    public function find(string $id): JsonResponse
    {
        return $this->crudFacade->find($this->contextProvider->getContext(), $id);
    }

    /**
     * @Route("/{id}/{assocName}", name="find_assoc", methods={"GET"})
     */
    public function findAssoc(string $id, string $assocName): JsonResponse
    {
        return $this->crudFacade->findAssoc($this->contextProvider->getContext(), $id, $assocName);
    }

    /**
     * @Route("", name="create", methods={"POST"})
     */
    public function create(): JsonResponse
    {
        return $this->crudFacade->create($this->contextProvider->getContext());
    }

    /**
     * @Route("/{id}/{assocName}/{assocId}", name="create_assoc", methods={"POST"})
     */
    public function createAssoc(string $id, string $assocName, string $assocId): JsonResponse
    {
        return $this->crudFacade->createAssoc($this->contextProvider->getContext(), $id, $assocName, $assocId);
    }

    /**
     * @Route("/{id}", name="update", methods={"PUT"})
     */
    public function update(string $id): JsonResponse
    {
        return $this->crudFacade->update($this->contextProvider->getContext(), $id);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     */
    public function delete(string $id): Response
    {
        return $this->crudFacade->delete($this->contextProvider->getContext(), $id);
    }

    /**
     * @Route("/{id}/{assocName}/{assocId}", name="delete_assoc", methods={"DELETE"})
     */
    public function deleteAssoc(string $id, string $assocName, string $assocId): Response
    {
        return $this->crudFacade->deleteAssoc($this->contextProvider->getContext(), $id, $assocName, $assocId);
    }
}