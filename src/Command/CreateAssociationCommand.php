<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\Command;

class CreateAssociationCommand
{
    public string $className;
    public string $id;
    public string $assocName;
    public string $assocId;

    public function __construct(string $className, string $id, string $assocName, string $assocId)
    {
        $this->className = $className;
        $this->id = $id;
        $this->assocName = $assocName;
        $this->assocId = $assocId;
    }
}