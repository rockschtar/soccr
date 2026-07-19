<?php

namespace Rockschtar\Soccr\Models;

use JsonSerializable;

class OpenLigaDBGroup implements JsonSerializable
{
    private string $groupName;

    private int $groupOrderId;

    private string $groupId;

    public function __construct(
        string $groupName,
        string $groupOrderId,
        string $groupId,
    ) {
        $this->groupName = $groupName;
        $this->groupOrderId = $groupOrderId;
        $this->groupId = $groupId;
    }

    public function getGroupName(): string
    {
        return $this->groupName;
    }

    public function setGroupName(string $groupName): OpenLigaDBGroup
    {
        $this->groupName = $groupName;
        return $this;
    }

    public function getGroupOrderId(): int
    {
        return $this->groupOrderId;
    }

    public function setGroupOrderId(string $groupOrderId): OpenLigaDBGroup
    {
        $this->groupOrderId = $groupOrderId;
        return $this;
    }

    public function getGroupId(): string
    {
        return $this->groupId;
    }

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
