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

class BeforeEntityUpdatedEvent extends AbstractDataEvent
{
    public const NAME = 'entity.before.update';

    private $oldEntity;

    /**
     * BeforeEntityUpdatedEvent constructor.
     *
     * @param RapidApiContext $context
     * @param RapidApiCrudEntity $entity
     * @param RapidApiCrudEntity $oldEntity
     */
    public function __construct(RapidApiContext $context, array $data, RapidApiCrudEntity $oldEntity)
    {
        parent::__construct($context, $data);

        $this->oldEntity = $oldEntity;
    }

    /**
     * @return mixed
     */
    public function getOldEntity()
    {
        return $this->oldEntity;
    }
}
