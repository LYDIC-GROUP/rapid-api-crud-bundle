<?php
/**
 * Created by PhpStorm.
 * User: Willem Turkstra
 * Date: 5/15/2021
 * Time: 10:49 PM
 */

namespace LydicGroup\RapidApiCrudBundle\EventSubscriber;

use LydicGroup\RapidApiCrudBundle\Controller\RapidApiCrudController;
use LydicGroup\RapidApiCrudBundle\Dto\ControllerConfig;
use LydicGroup\RapidApiCrudBundle\RapidApiCrudBundle;
use LydicGroup\RapidApiCrudBundle\Context\RapidApiContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class RequestEventSubscriber
 * @package src\EventSubscriber
 */
class RequestEventSubscriber implements EventSubscriberInterface
{
    private ControllerConfig $defaultControllerConfig;

    /**
     * RequestEventSubscriber constructor.
     * @param ControllerConfig $defaultControllerConfig
     */
    public function __construct(ControllerConfig $defaultControllerConfig)
    {
        $this->defaultControllerConfig = $defaultControllerConfig;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',

        ];
    }

    public function onKernelController(ControllerEvent $event)
    {
        $controller = $event->getController();
        $request = $event->getRequest();

        if (is_array($controller)) {
            $controller = $controller[0];
        }

        if ($controller instanceof RapidApiCrudController) {
            $context = new RapidApiContext($controller->entityClassName(), $event->getRequest(), $controller->controllerConfig($this->defaultControllerConfig));
            $request->attributes->set(RapidApiCrudBundle::CONTEXT_ATTRIBUTE_NAME, $context);
        }
    }
}
