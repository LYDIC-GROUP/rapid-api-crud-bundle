<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\Command;

class CreateEntityCommand
{
    public string $className;
    public array $data;

    public function __construct(string $className, array $data)
    {
        $this->className = $className;
        $this->data = $data;
    }
}