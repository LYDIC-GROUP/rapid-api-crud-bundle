<?php
/**
 * Created by PhpStorm.
 * User: Willem
 * Date: 2/15/2021
 * Time: 11:56 PM
 */
namespace LydicGroup\RapidApiCrudBundle\Event;

use LydicGroup\RapidApiCrudBundle\Context\RapidApiContext;
use LydicGroup\RapidApiCrudBundle\Entity\RapidApiCrudEntity;

class AfterEntityUpdatedEvent extends AbstractEntityEvent
{
    public const NAME = 'entity.after.update';

    private RapidApiCrudEntity $oldEntity;

    public function __construct(RapidApiContext $context, RapidApiCrudEntity $entity, RapidApiCrudEntity $oldEntity)
    {
        parent::__construct($context, $entity);

        $this->oldEntity = $oldEntity;
    }

    /**
     * @return RapidApiCrudEntity
     */
    public function getOldEntity(): RapidApiCrudEntity
    {
        return $this->oldEntity;
    }
}
