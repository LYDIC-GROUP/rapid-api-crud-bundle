<?php
/**
 * Created by PhpStorm.
 * User: Willem
 * Date: 2/15/2021
 * Time: 11:54 PM
 */
namespace LydicGroup\RapidApiCrudBundle\Event;

class BeforeEntityCreatedEvent extends AbstractDataEvent
{
    public const NAME = 'entity.before.create';
}
