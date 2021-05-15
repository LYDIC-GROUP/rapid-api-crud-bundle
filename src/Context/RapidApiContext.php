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
    private Request $request;
    private ControllerConfig $config;

    /**
     * RapidApiContext constructor.
     * @param Request $request
     * @param ControllerConfig $config
     */
    public function __construct(Request $request, ControllerConfig $config)
    {
        $this->request = $request;
        $this->config = $config;
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