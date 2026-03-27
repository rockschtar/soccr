<?php

namespace Rockschtar\WordPress\Soccr\Models;

class OpenLigaDBLeagueQuery
{
    private ?int $leagueSeasonGreaterThan = null;

    private ?string $leagueShortcut = null;

    private ?string $includeLeagueShortcut = null;

    private ?int $includeLeagueSeason = null;

    /** @var string[] */
    private array $leagueShortcuts = [];

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
