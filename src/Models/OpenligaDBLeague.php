<?php

namespace Rockschtar\WordPress\Soccr\Models;

use JsonSerializable;

class OpenligaDBLeague implements JsonSerializable
{
    private int $leagueId;

    private string $leagueName;

    private string $leagueShortcut;

    private int $leagueSeason;

    /**
     * @return int
     */
    public function getLeagueId(): int
    {
        return $this->leagueId;
    }

    /**
     * @param int $leagueId
     */
    public function setLeagueId(int $leagueId): void
    {
        $this->leagueId = $leagueId;
    }

    /**
     * @return string
     */
    public function getLeagueName(): string
    {
        return $this->leagueName;
    }

    /**
     * @param string $leagueName
     */
    public function setLeagueName(string $leagueName): void
    {
        $this->leagueName = $leagueName;
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
     */
    public function setLeagueShortcut(string $leagueShortcut): void
    {
        $this->leagueShortcut = $leagueShortcut;
    }

    /**
     * @return int
     */
    public function getLeagueSeason(): int
    {
        return $this->leagueSeason;
    }

    public function getLeagueSeasonDisplay(): string
    {
        return apply_filters(
            'openligadb-league-season-display',
            $this->getLeagueSeason(),
            $this->getLeagueShortcut(),
        );
    }

    /**
     * @param int $leagueSeason
     */
    public function setLeagueSeason(int $leagueSeason): void
    {
        $this->leagueSeason = $leagueSeason;
    }

    public function jsonSerialize(): array
    {
        return [
            'leagueId' => $this->getLeagueId(),
            'leagueName' => $this->getLeagueName(),
            'leagueShortcut' => $this->getLeagueShortcut(),
            'leagueSeason' => $this->getLeagueSeason(),
        ];
    }
}
