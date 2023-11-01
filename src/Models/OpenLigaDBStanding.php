<?php

namespace ClubfansUnited\Models;

class OpenLigaDBStanding
{
    private OpenLigaDBTeam $team;

    private int $points = 0;

    private int $goalsScored = 0;

    private int $goalsConceded = 0;

    private int $matches = 0;

    private int $wins = 0;

    private int $looses = 0;

    private int $draws = 0;

    private int $goalDifference = 0;

    /**
     * @return OpenLigaDBTeam
     */
    public function getTeam(): OpenLigaDBTeam
    {
        return $this->team;
    }

    /**
     * @param OpenLigaDBTeam $team
     * @return OpenLigaDBStanding
     */
    public function setTeam(OpenLigaDBTeam $team): OpenLigaDBStanding
    {
        $this->team = $team;
        return $this;
    }

    /**
     * @return int
     */
    public function getPoints(): int
    {
        return $this->points;
    }

    /**
     * @param int $points
     * @return OpenLigaDBStanding
     */
    public function setPoints(int $points): OpenLigaDBStanding
    {
        $this->points = $points;
        return $this;
    }

    /**
     * @return int
     */
    public function getGoalsScored(): int
    {
        return $this->goalsScored;
    }

    /**
     * @param int $goalsScored
     * @return OpenLigaDBStanding
     */
    public function setGoalsScored(int $goalsScored): OpenLigaDBStanding
    {
        $this->goalsScored = $goalsScored;
        return $this;
    }

    /**
     * @return int
     */
    public function getGoalsConceded(): int
    {
        return $this->goalsConceded;
    }

    /**
     * @param int $goalsConceded
     * @return OpenLigaDBStanding
     */
    public function setGoalsConceded(int $goalsConceded): OpenLigaDBStanding
    {
        $this->goalsConceded = $goalsConceded;
        return $this;
    }

    /**
     * @return int
     */
    public function getMatches(): int
    {
        return $this->matches;
    }

    /**
     * @param int $matches
     * @return OpenLigaDBStanding
     */
    public function setMatches(int $matches): OpenLigaDBStanding
    {
        $this->matches = $matches;
        return $this;
    }

    /**
     * @return int
     */
    public function getWins(): int
    {
        return $this->wins;
    }

    /**
     * @param int $wins
     * @return OpenLigaDBStanding
     */
    public function setWins(int $wins): OpenLigaDBStanding
    {
        $this->wins = $wins;
        return $this;
    }

    /**
     * @return int
     */
    public function getLooses(): int
    {
        return $this->looses;
    }

    /**
     * @param int $looses
     * @return OpenLigaDBStanding
     */
    public function setLooses(int $looses): OpenLigaDBStanding
    {
        $this->looses = $looses;
        return $this;
    }

    /**
     * @return int
     */
    public function getDraws(): int
    {
        return $this->draws;
    }

    /**
     * @param int $draws
     * @return OpenLigaDBStanding
     */
    public function setDraws(int $draws): OpenLigaDBStanding
    {
        $this->draws = $draws;
        return $this;
    }

    /**
     * @return int
     */
    public function getGoalDifference(): int
    {
        return $this->goalDifference;
    }

    /**
     * @param int $goalDifference
     * @return OpenLigaDBStanding
     */
    public function setGoalDifference(int $goalDifference): OpenLigaDBStanding
    {
        $this->goalDifference = $goalDifference;
        return $this;
    }
}
