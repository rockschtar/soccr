<?php

namespace Rockschtar\Soccr\Models;

class OpenLigaDBLeagueQuery
{
    private ?int $leagueSeasonGreaterThan = null;

    private ?string $includeLeagueShortcut = null;

    private ?int $includeLeagueSeason = null;

    /** @var string[] */
    private array $leagueShortcuts = [];

    public function __construct() {}

    public function getLeagueSeasonGreaterThan(): ?int
    {
        return $this->leagueSeasonGreaterThan;
    }

    public function setLeagueSeasonGreaterThan(
        int $leagueSeasonGreaterThan,
    ): OpenLigaDBLeagueQuery {
        $this->leagueSeasonGreaterThan = $leagueSeasonGreaterThan;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getLeagueShortcuts(): array
    {
        return $this->leagueShortcuts;
    }

    /**
     * @param string[] $leagueShortcuts
     */
    public function setLeagueShortcuts(array $leagueShortcuts): OpenLigaDBLeagueQuery
    {
        $this->leagueShortcuts = $leagueShortcuts;
        return $this;
    }

    public function getIncludeLeagueShortcut(): ?string
    {
        return $this->includeLeagueShortcut;
    }

    public function setIncludeLeagueShortcut(?string $includeLeagueShortcut): OpenLigaDBLeagueQuery
    {
        $this->includeLeagueShortcut = $includeLeagueShortcut;
        return $this;
    }

    public function getIncludeLeagueSeason(): ?int
    {
        return $this->includeLeagueSeason;
    }

    public function setIncludeLeagueSeason(?int $includeLeagueSeason): OpenLigaDBLeagueQuery
    {
        $this->includeLeagueSeason = $includeLeagueSeason;
        return $this;
    }
}
