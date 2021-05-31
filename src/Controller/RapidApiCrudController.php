<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\Controller;

use LydicGroup\RapidApiCrudBundle\Dto\ControllerConfig;
use LydicGroup\RapidApiCrudBundle\Provider\RapidApiContextProvider;
use LydicGroup\RapidApiCrudBundle\Service\ControllerFacade;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class RapidApiCrudController extends AbstractController
{
    private ControllerFacade $controllerFacade;
    private RapidApiContextProvider $contextProvider;

    public function __construct(ControllerFacade $crudControllerService, RapidApiContextProvider $contextProvider)
    {
        $this->controllerFacade = $crudControllerService;
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
        return $this->controllerFacade->list($this->contextProvider->getContext());
    }

    /**
     * @Route("/{id}", name="find", methods={"GET"})
     */
    public function find(string $id): JsonResponse
    {
        return $this->controllerFacade->find($this->contextProvider->getContext(), $id);
    }

    /**
     * @Route("/{id}/{assocName}", name="find_assoc", methods={"GET"})
     */
    public function findAssoc(string $id, string $assocName): JsonResponse
    {
        return $this->controllerFacade->findAssoc($this->contextProvider->getContext(), $id, $assocName);
    }

    /**
     * @Route("", name="create", methods={"POST"})
     */
    public function create(): JsonResponse
    {
        return $this->controllerFacade->create($this->contextProvider->getContext());
    }

    /**
     * @Route("/{id}/{assocName}/{assocId}", name="create_assoc", methods={"POST"})
     */
    public function createAssoc(string $id, string $assocName, string $assocId): JsonResponse
    {
        return $this->controllerFacade->createAssoc($this->contextProvider->getContext(), $id, $assocName, $assocId);
    }

    /**
     * @Route("/{id}", name="update", methods={"PUT"})
     */
    public function update(string $id): JsonResponse
    {
        return $this->controllerFacade->update($this->contextProvider->getContext(), $id);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     */
    public function delete(string $id): Response
    {
        return $this->controllerFacade->delete($this->contextProvider->getContext(), $id);
    }

    /**
     * @Route("/{id}/{assocName}/{assocId}", name="delete_assoc", methods={"DELETE"})
     */
    public function deleteAssoc(string $id, string $assocName, string $assocId): Response
    {
        return $this->controllerFacade->deleteAssoc($this->contextProvider->getContext(), $id, $assocName, $assocId);
    }
}