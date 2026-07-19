<?php

namespace Rockschtar\Soccr\Models;

class OpenLigaDBLeagueSeason
{
    private string $leagueShortcut;

    private int $leagueSeason;

    public function __construct(string $leagueShortcut, int $leagueSeason)
    {
        $this->leagueShortcut = $leagueShortcut;
        $this->leagueSeason = $leagueSeason;
    }

    public function getLeagueShortcut(): string
    {
        return $this->leagueShortcut;
    }

    public function setLeagueShortcut(
        string $leagueShortcut,
    ): OpenLigaDBLeagueSeason {
        $this->leagueShortcut = $leagueShortcut;
        return $this;
    }

    public function getLeagueSeason(): int
    {
        return $this->leagueSeason;
    }

    public function setLeagueSeason(int $leagueSeason): OpenLigaDBLeagueSeason
    {
        $this->leagueSeason = $leagueSeason;
        return $this;
    }
}
