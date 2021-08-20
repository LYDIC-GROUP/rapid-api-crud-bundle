<?php
/**
 * Created by PhpStorm.
 * User: Willem
 * Date: 2/15/2021
 * Time: 11:54 PM
 */
namespace LydicGroup\RapidApiCrudBundle\Event;

use Doctrine\ORM\Tools\Pagination\Paginator;
use LydicGroup\RapidApiCrudBundle\Context\RapidApiContext;
use LydicGroup\RapidApiCrudBundle\Entity\RapidApiCrudEntity;

class PostListEntitiesEvent extends AbstractContextEvent
{
    public const NAME = 'entities.post.list';

    private Paginator $paginator;

    public function __construct(RapidApiContext $context, Paginator $paginator)
    {
        parent::__construct($context);

        $this->paginator = $paginator;
    }

    /**
     * @return Paginator
     */
    public function getPaginator(): Paginator
    {
        return $this->paginator;
    }
}
