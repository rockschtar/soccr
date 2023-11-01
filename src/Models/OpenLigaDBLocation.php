<?php

namespace ClubfansUnited\Models;

class OpenLigaDBLocation
{
    private int $id;

    private string $city;

    private string $name;

    /**
     * @param int $id
     * @param string $city
     * @param string $name
     */
    public function __construct(int $id, string $city, string $name)
    {
        $this->id = $id;
        $this->city = $city;
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return OpenLigaDBLocation
     */
    public function setId(int $id): OpenLigaDBLocation
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param string $city
     * @return OpenLigaDBLocation
     */
    public function setCity(string $city): OpenLigaDBLocation
    {
        $this->city = $city;
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
     * @return OpenLigaDBLocation
     */
    public function setName(string $name): OpenLigaDBLocation
    {
        $this->name = $name;
        return $this;
    }
}
