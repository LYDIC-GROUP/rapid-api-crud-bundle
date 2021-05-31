<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\Command;

class FindEntityCommand
{
    public string $className;
    public string $id;

    public function __construct(string $className, string $id)
    {
        $this->className = $className;
        $this->id = $id;
    }
}