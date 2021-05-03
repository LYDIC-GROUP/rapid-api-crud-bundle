<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\Dto;

class ControllerConfig
{
    public string $entityClassName;

    public bool $listActionEnabled = true;
    public bool $findActionEnabled = true;
    public bool $createActionEnabled = true;
    public bool $updateActionEnabled = true;
    public bool $deleteActionEnabled = true;

    public function __construct(string $entityClassName)
    {
        $this->entityClassName = $entityClassName;
    }
}