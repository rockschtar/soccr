<?php

namespace Rockschtar\Soccr\Models;

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

    public function getTeam(): OpenLigaDBTeam
    {
        return $this->team;
    }

    public function setTeam(OpenLigaDBTeam $team): OpenLigaDBStanding
    {
        $this->team = $team;
        return $this;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function setPoints(int $points): OpenLigaDBStanding
    {
        $this->points = $points;
        return $this;
    }

    public function getGoalsScored(): int
    {
        return $this->goalsScored;
    }

    public function setGoalsScored(int $goalsScored): OpenLigaDBStanding
    {
        $this->goalsScored = $goalsScored;
        return $this;
    }

    public function getGoalsConceded(): int
    {
        return $this->goalsConceded;
    }

    public function setGoalsConceded(int $goalsConceded): OpenLigaDBStanding
    {
        $this->goalsConceded = $goalsConceded;
        return $this;
    }

    public function getMatches(): int
    {
        return $this->matches;
    }

    public function setMatches(int $matches): OpenLigaDBStanding
    {
        $this->matches = $matches;
        return $this;
    }

    public function getWins(): int
    {
        return $this->wins;
    }

    public function setWins(int $wins): OpenLigaDBStanding
    {
        $this->wins = $wins;
        return $this;
    }

    public function getLooses(): int
    {
        return $this->looses;
    }

    public function setLooses(int $looses): OpenLigaDBStanding
    {
        $this->looses = $looses;
        return $this;
    }

    public function getDraws(): int
    {
        return $this->draws;
    }

    public function setDraws(int $draws): OpenLigaDBStanding
    {
        $this->draws = $draws;
        return $this;
    }

    public function getGoalDifference(): int
    {
        return $this->goalDifference;
    }

    public function setGoalDifference(int $goalDifference): OpenLigaDBStanding
    {
        $this->goalDifference = $goalDifference;
        return $this;
    }
}
