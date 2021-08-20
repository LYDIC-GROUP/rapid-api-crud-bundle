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

abstract class AbstractDataEvent extends AbstractContextEvent
{
    private array $data;

    /**
     * AbstractEntityEvent constructor.
     * @param $entity
     */
    public function __construct(RapidApiContext $context, array $data)
    {
        parent::__construct($context);

        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
