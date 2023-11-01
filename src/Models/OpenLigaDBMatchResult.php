<?php

namespace Rockschtar\WordPress\Soccr\Models;

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

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return OpenLigaDBMatchResult
     */
    public function setId(int $id): OpenLigaDBMatchResult
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return OpenLigaDBMatchResult
     */
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

    /**
     * @param int $pointsTeam1
     * @return OpenLigaDBMatchResult
     */
    public function setPointsTeam1(int $pointsTeam1): OpenLigaDBMatchResult
    {
        $this->pointsTeam1 = $pointsTeam1;
        return $this;
    }

    /**
     * @return int
     */
    public function getPointsTeam2(): int
    {
        return $this->pointsTeam2;
    }

    /**
     * @param int $pointsTeam2
     * @return OpenLigaDBMatchResult
     */
    public function setPointsTeam2(int $pointsTeam2): OpenLigaDBMatchResult
    {
        $this->pointsTeam2 = $pointsTeam2;
        return $this;
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * @param int $orderId
     * @return OpenLigaDBMatchResult
     */
    public function setOrderId(int $orderId): OpenLigaDBMatchResult
    {
        $this->orderId = $orderId;
        return $this;
    }

    /**
     * @return int
     */
    public function getTypeId(): int
    {
        return $this->typeId;
    }

    /**
     * @param int $typeId
     * @return OpenLigaDBMatchResult
     */
    public function setTypeId(int $typeId): OpenLigaDBMatchResult
    {
        $this->typeId = $typeId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return OpenLigaDBMatchResult
     */
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
