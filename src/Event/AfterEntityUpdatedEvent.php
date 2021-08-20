<?php
/**
 * Created by PhpStorm.
 * User: Willem
 * Date: 2/15/2021
 * Time: 11:56 PM
 */
namespace LydicGroup\RapidApiCrudBundle\Event;

class AfterEntityUpdatedEvent extends BeforeEntityUpdatedEvent
{
    public const NAME = 'entity.after.update';
}
