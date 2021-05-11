<?php
/**
 * Created by PhpStorm.
 * User: Willem Turkstra
 * Date: 5/11/2021
 * Time: 9:39 PM
 */
namespace LydicGroup\RapidApiCrudBundle\Factory;

use LydicGroup\Filtering\ExpressionParser;
use LydicGroup\RapidApiCrudBundle\Builder\BasicQueryBuilder;
use LydicGroup\RapidApiCrudBundle\Dto\ControllerConfig;
use LydicGroup\RapidApiCrudBundle\Enum\FilterMode;
use LydicGroup\RapidApiCrudBundle\QueryBuilder\BasicRapidApiCriteria;
use LydicGroup\RapidApiCrudBundle\QueryBuilder\ExtendedRapidApiCriteria;
use LydicGroup\RapidApiCrudBundle\QueryBuilder\RapidApiCriteriaInterface;
use Symfony\Component\HttpFoundation\Request;

class CriteriaFactory
{
    private ExpressionParser $parser;

    /**
     * CriteriaFactory constructor.
     * @param ExpressionParser $parser
     */
    public function __construct(ExpressionParser $parser)
    {
        $this->parser = $parser;
    }

    public function create(ControllerConfig $config, Request $request): RapidApiCriteriaInterface
    {
        switch ($config->getFilterMode()) {
            case FilterMode::BASIC:
                $builder = new BasicRapidApiCriteria();
                $builder->setRequest($request);
                $builder->setConfig($config);

                return $builder;
            case FilterMode::EXTENDED:
                $builder = new ExtendedRapidApiCriteria($this->parser);
                $builder->setConfig($config);
                $builder->setRequest($request);

                return $builder;
        }
    }
}
