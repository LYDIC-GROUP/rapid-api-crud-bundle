<?php
/**
 * Created by PhpStorm.
 * User: Willem
 * Date: 2/15/2021
 * Time: 11:57 PM
 */
namespace LydicGroup\RapidApiCrudBundle\Event;

class AfterEntityDeletedEvent extends AbstractEntityEvent
{
    public const NAME = 'entity.after.delete';
}
