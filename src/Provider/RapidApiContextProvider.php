<?php

/**
 * Created by PhpStorm.
 * User: Willem Turkstra
 * Date: 5/16/2021
 * Time: 12:05 AM
 */

namespace LydicGroup\RapidApiCrudBundle\Provider;

use LydicGroup\RapidApiCrudBundle\RapidApiCrudBundle;
use LydicGroup\RapidApiCrudBundle\Context\RapidApiContext;
use Symfony\Component\HttpFoundation\RequestStack;

final class RapidApiContextProvider
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getContext(): ?RapidApiContext
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        return null !== $currentRequest ? $currentRequest->get(RapidApiCrudBundle::CONTEXT_ATTRIBUTE_NAME) : null;
    }
}