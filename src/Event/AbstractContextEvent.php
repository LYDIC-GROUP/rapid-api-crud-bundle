<?php
/**
 * Created by PhpStorm.
 * User: Willem
 * Date: 2/15/2021
 * Time: 11:55 PM
 */
namespace LydicGroup\RapidApiCrudBundle\Event;

use LydicGroup\RapidApiCrudBundle\Context\RapidApiContext;
use LydicGroup\RapidApiCrudBundle\Entity\RapidApiCrudEntity;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractContextEvent extends Event
{
    private RapidApiContext $context;

    /**
     * AbstractEntityEvent constructor.
     * @param $entity
     */
    public function __construct(RapidApiContext $context)
    {
        $this->context = $context;
    }

    public function getContext(): RapidApiContext
    {
        return $this->context;
    }
}
