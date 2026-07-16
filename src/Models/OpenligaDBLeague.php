<?php

namespace Rockschtar\WordPress\Soccr\Models;

use JsonSerializable;

class OpenligaDBLeague implements JsonSerializable
{
    private int $leagueId;

    private string $leagueName;

    private string $leagueShortcut;

    private int $leagueSeason;

    private int $sportId;

    private string $leagueNameShort;

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

    public function getLeagueName(): string
    {
        return $this->leagueName;
    }

    public function setLeagueName(string $leagueName): void
    {
        $this->leagueName = $leagueName;
    }

    public function getLeagueShortcut(): string
    {
        return $this->leagueShortcut;
    }

    public function setLeagueShortcut(string $leagueShortcut): void
    {
        $this->leagueShortcut = $leagueShortcut;
    }

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

    public function setLeagueSeason(int $leagueSeason): void
    {
        $this->leagueSeason = $leagueSeason;
    }

    public function getSportId(): int
    {
        return $this->sportId;
    }

    public function getLeagueNameShort(): string
    {
        return $this->leagueNameShort;
    }

    public function setLeagueNameShort(string $leagueNameShort): void
    {
        $this->leagueNameShort = $leagueNameShort;
    }

    public function setSportId(int $sportId): void
    {
        $this->sportId = $sportId;
    }

    public function jsonSerialize(): array
    {
        return [
            'leagueId' => $this->getLeagueId(),
            'leagueName' => $this->getLeagueName(),
            'leagueNameShort' => $this->getLeagueNameShort(),
            'leagueShortcut' => $this->getLeagueShortcut(),
            'leagueSeason' => $this->getLeagueSeason(),
        ];
    }
}
