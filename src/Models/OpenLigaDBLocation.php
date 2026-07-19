<?php

namespace Rockschtar\Soccr\Models;

class OpenLigaDBLocation
{
    private int $id;

    private string $city;

    private string $name;

    public function __construct(int $id, string $city, string $name)
    {
        $this->id = $id;
        $this->city = $city;
        $this->name = $name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): OpenLigaDBLocation
    {
        $this->id = $id;
        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }


    public function setCity(string $city): OpenLigaDBLocation
    {
        $this->city = $city;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): OpenLigaDBLocation
    {
        $this->name = $name;
        return $this;
    }
}
