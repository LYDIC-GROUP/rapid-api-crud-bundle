<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\Command;

class UpdateEntityCommand
{
    public string $className;
    public string $id;
    public array $data;

    public function __construct(string $className, string $id, array $data)
    {
        $this->className = $className;
        $this->id = $id;
        $this->data = $data;
    }
}