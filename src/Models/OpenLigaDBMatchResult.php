<?php

namespace Rockschtar\Soccr\Models;

use JsonSerializable;

class OpenLigaDBMatchResult implements JsonSerializable
{
    private int $id;

    private string $name;

    private int $pointsTeam1;

    private int $pointsTeam2;

    private int $orderId;

    private int $typeId;

    private ?string $description;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): OpenLigaDBMatchResult
    {
        $this->id = $id;
        return $this;
    }


    public function getName(): string
    {
        return $this->name;
    }


    public function setName(string $name): OpenLigaDBMatchResult
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return int
     */
    public function getPointsTeam1(): int
    {
        return $this->pointsTeam1;
    }

    public function setPointsTeam1(int $pointsTeam1): OpenLigaDBMatchResult
    {
        $this->pointsTeam1 = $pointsTeam1;
        return $this;
    }

    public function getPointsTeam2(): int
    {
        return $this->pointsTeam2;
    }

    public function setPointsTeam2(int $pointsTeam2): OpenLigaDBMatchResult
    {
        $this->pointsTeam2 = $pointsTeam2;
        return $this;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function setOrderId(int $orderId): OpenLigaDBMatchResult
    {
        $this->orderId = $orderId;
        return $this;
    }

    public function getTypeId(): int
    {
        return $this->typeId;
    }

    public function setTypeId(int $typeId): OpenLigaDBMatchResult
    {
        $this->typeId = $typeId;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): OpenLigaDBMatchResult
    {
        $this->description = $description;
        return $this;
    }

    public function __toString(): string
    {
        return $this->getPointsTeam1() . ':' . $this->getPointsTeam2();
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'pointsTeam1' => $this->getPointsTeam1(),
            'pointsTeam2' => $this->getPointsTeam2(),
            'orderId' => $this->getOrderId(),
            'typeId' => $this->getTypeId(),
            'description' => $this->getDescription(),
        ];
    }
}
