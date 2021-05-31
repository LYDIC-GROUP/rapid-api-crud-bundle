<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\Command;

class FindAssociationCommand
{
    public string $className;
    public string $id;
    public string $associationName;

    public function __construct(string $className, string $id, string $associationName)
    {
        $this->className = $className;
        $this->id = $id;
        $this->associationName = $associationName;
    }
}