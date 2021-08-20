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

abstract class AbstractEntityEvent extends AbstractContextEvent
{
    private RapidApiCrudEntity $entity;

    /**
     * AbstractEntityEvent constructor.
     * @param $entity
     */
    public function __construct(RapidApiContext $context, RapidApiCrudEntity $entity)
    {
        parent::__construct($context);

        $this->entity = $entity;
    }

    public function getEntity(): RapidApiCrudEntity
    {
        return $this->entity;
    }
}
