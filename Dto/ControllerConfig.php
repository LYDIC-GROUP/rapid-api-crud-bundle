<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\Dto;

use LydicGroup\RapidApiCrudBundle\Enum\FilterMode;

class ControllerConfig
{
    private string $entityClassName;

    private bool $listActionEnabled;
    private bool $findActionEnabled;
    private bool $createActionEnabled;
    private bool $updateActionEnabled;
    private bool $deleteActionEnabled;

    private int $filterMode;

    public function __construct(string $entityClassName)
    {
        $this->entityClassName = $entityClassName;

        $this->listActionEnabled = true;
        $this->findActionEnabled = true;
        $this->createActionEnabled = true;
        $this->updateActionEnabled = true;
        $this->deleteActionEnabled = true;

        $this->filterMode = FilterMode::BASIC;

        return $this;
    }

    /**
     * @return string
     */
    public function getEntityClassName(): string
    {
        return $this->entityClassName;
    }

    /**
     * @return bool
     */
    public function isListActionEnabled(): bool
    {
        return $this->listActionEnabled;
    }

    /**
     * @param bool $listActionEnabled
     * @return ControllerConfig
     */
    public function setListActionEnabled(bool $listActionEnabled): ControllerConfig
    {
        $this->listActionEnabled = $listActionEnabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function isFindActionEnabled(): bool
    {
        return $this->findActionEnabled;
    }

    /**
     * @param bool $findActionEnabled
     * @return ControllerConfig
     */
    public function setFindActionEnabled(bool $findActionEnabled): ControllerConfig
    {
        $this->findActionEnabled = $findActionEnabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCreateActionEnabled(): bool
    {
        return $this->createActionEnabled;
    }

    /**
     * @param bool $createActionEnabled
     * @return ControllerConfig
     */
    public function setCreateActionEnabled(bool $createActionEnabled): ControllerConfig
    {
        $this->createActionEnabled = $createActionEnabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function isUpdateActionEnabled(): bool
    {
        return $this->updateActionEnabled;
    }

    /**
     * @param bool $updateActionEnabled
     * @return ControllerConfig
     */
    public function setUpdateActionEnabled(bool $updateActionEnabled): ControllerConfig
    {
        $this->updateActionEnabled = $updateActionEnabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDeleteActionEnabled(): bool
    {
        return $this->deleteActionEnabled;
    }

    /**
     * @param bool $deleteActionEnabled
     * @return ControllerConfig
     */
    public function setDeleteActionEnabled(bool $deleteActionEnabled): ControllerConfig
    {
        $this->deleteActionEnabled = $deleteActionEnabled;
        return $this;
    }

    /**
     * @return int
     */
    public function getFilterMode(): int
    {
        return $this->filterMode;
    }

    /**
     * @param int $filterMode
     * @return ControllerConfig
     */
    public function setFilterMode(int $filterMode): ControllerConfig
    {
        $this->filterMode = $filterMode;
        return $this;
    }
}
