<?php

namespace Rockschtar\WordPress\Soccr\Models;

use JsonSerializable;

class OpenLigaDBGroup implements JsonSerializable
{
    private string $groupName;

    private int $groupOrderId;

    private string $groupId;

    /**
     * @param string $groupName
     * @param string $groupOrderId
     * @param string $groupId
     */
    public function __construct(
        string $groupName,
        string $groupOrderId,
        string $groupId
    ) {
        $this->groupName = $groupName;
        $this->groupOrderId = $groupOrderId;
        $this->groupId = $groupId;
    }

    /**
     * @return string
     */
    public function getGroupName(): string
    {
        return $this->groupName;
    }

    /**
     * @param string $groupName
     * @return OpenLigaDBGroup
     */
    public function setGroupName(string $groupName): OpenLigaDBGroup
    {
        $this->groupName = $groupName;
        return $this;
    }

    /**
     * @return int
     */
    public function getGroupOrderId(): int
    {
        return $this->groupOrderId;
    }

    /**
     * @param string $groupOrderId
     * @return OpenLigaDBGroup
     */
    public function setGroupOrderId(string $groupOrderId): OpenLigaDBGroup
    {
        $this->groupOrderId = $groupOrderId;
        return $this;
    }

    /**
     * @return string
     */
    public function getGroupId(): string
    {
        return $this->groupId;
    }

    /**
     * @param string $groupId
     * @return OpenLigaDBGroup
     */
    public function setGroupId(string $groupId): OpenLigaDBGroup
    {
        $this->groupId = $groupId;
        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'groupName' => $this->getGroupName(),
            'groupOrderId' => $this->getGroupOrderId(),
            'groupId' => $this->getGroupId(),
        ];
    }
}
