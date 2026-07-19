<?php

namespace Rockschtar\Soccr\Models;

class OpenLigaDBStandings
{
    private OpenligaDBLeague $league;

    private array $standings = [];

    public function getLeague(): OpenligaDBLeague
    {
        return $this->league;
    }

    public function setLeague(OpenligaDBLeague $league): OpenLigaDBStandings
    {
        $this->league = $league;
        return $this;
    }

    public function getStandings(): array
    {
        return $this->standings;
    }

    public function setStandings(array $standings): OpenLigaDBStandings
    {
        $this->standings = $standings;
        return $this;
    }

    public function addStanding(
        OpenLigaDBStanding $standing,
    ): OpenLigaDBStandings {
        $this->standings[] = $standing;
        return $this;
    }
}
