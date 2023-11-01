<?php

namespace Rockschtar\WordPress\Soccr\Models;
class OpenLigaDBStandings
{
    private OpenligaDBLeague $league;

    private array $standings;

    /**
     * @return OpenligaDBLeague
     */
    public function getLeague(): OpenligaDBLeague
    {
        return $this->league;
    }

    /**
     * @param OpenligaDBLeague $league
     * @return OpenLigaDBStandings
     */
    public function setLeague(OpenligaDBLeague $league): OpenLigaDBStandings
    {
        $this->league = $league;
        return $this;
    }

    /**
     * @return OpenLigaDBStanding[]
     */
    public function getStandings(): array
    {
        return $this->standings;
    }

    /**
     * @param array $standings
     * @return OpenLigaDBStandings
     */
    public function setStandings(array $standings): OpenLigaDBStandings
    {
        $this->standings = $standings;
        return $this;
    }

    public function addStanding(
        OpenLigaDBStanding $standing
    ): OpenLigaDBStandings {
        $this->standings[] = $standing;
        return $this;
    }
}
