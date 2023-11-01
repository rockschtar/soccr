<?php

namespace Rockschtar\WordPress\Soccr\Models;

class OpenLigaDBLeagueQuery
{
    private ?int $leagueSeasonGreaterThan = null;

    private ?string $leagueShortcut = null;

    public function __construct()
    {
    }

    /**
     * @return int
     */
    public function getLeagueSeasonGreaterThan(): ?int
    {
        return $this->leagueSeasonGreaterThan;
    }

    /**
     * @param int $leagueSeasonGreaterThan
     * @return OpenLigaDBLeagueQuery
     */
    public function setLeagueSeasonGreaterThan(
        int $leagueSeasonGreaterThan
    ): OpenLigaDBLeagueQuery {
        $this->leagueSeasonGreaterThan = $leagueSeasonGreaterThan;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLeagueShortcut(): ?string
    {
        return $this->leagueShortcut;
    }

    /**
     * @param string|null $leagueShortcut
     * @return OpenLigaDBLeagueQuery
     */
    public function setLeagueShortcut(
        ?string $leagueShortcut
    ): OpenLigaDBLeagueQuery {
        $this->leagueShortcut = $leagueShortcut;
        return $this;
    }
}
