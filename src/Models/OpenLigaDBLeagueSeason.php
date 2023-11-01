<?php

namespace ClubfansUnited\Models;

class OpenLigaDBLeagueSeason
{
    private string $leagueShortcut;

    private int $leagueSeason;

    /**
     * @param string $leagueShortcut
     * @param int $leagueSeason
     */
    public function __construct(string $leagueShortcut, int $leagueSeason)
    {
        $this->leagueShortcut = $leagueShortcut;
        $this->leagueSeason = $leagueSeason;
    }

    /**
     * @return string
     */
    public function getLeagueShortcut(): string
    {
        return $this->leagueShortcut;
    }

    /**
     * @param string $leagueShortcut
     * @return OpenLigaDBLeagueSeason
     */
    public function setLeagueShortcut(
        string $leagueShortcut
    ): OpenLigaDBLeagueSeason {
        $this->leagueShortcut = $leagueShortcut;
        return $this;
    }

    /**
     * @return int
     */
    public function getLeagueSeason(): int
    {
        return $this->leagueSeason;
    }

    /**
     * @param int $leagueSeason
     * @return OpenLigaDBLeagueSeason
     */
    public function setLeagueSeason(int $leagueSeason): OpenLigaDBLeagueSeason
    {
        $this->leagueSeason = $leagueSeason;
        return $this;
    }
}
