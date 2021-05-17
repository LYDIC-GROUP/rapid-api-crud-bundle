<?php

/**
 * Created by PhpStorm.
 * User: Willem Turkstra
 * Date: 5/15/2021
 * Time: 8:13 PM
 */

namespace LydicGroup\RapidApiCrudBundle\Context;

use LydicGroup\RapidApiCrudBundle\Dto\ControllerConfig;
use Symfony\Component\HttpFoundation\Request;

class RapidApiContext
{
    private string $entityClassName;

    private Request $request;
    private ControllerConfig $config;

    /**
     * RapidApiContext constructor.
     * @param Request $request
     * @param ControllerConfig $config
     */
    public function __construct(string $entityClassName, Request $request, ControllerConfig $config)
    {
        $this->entityClassName = $entityClassName;
        $this->request = $request;
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getEntityClassName(): string
    {
        return $this->entityClassName;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @return ControllerConfig
     */
    public function getConfig(): ControllerConfig
    {
        return $this->config;
    }
}