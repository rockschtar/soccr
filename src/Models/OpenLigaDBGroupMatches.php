<?php

namespace Rockschtar\WordPress\Soccr\Models;

use JsonSerializable;

class OpenLigaDBGroupMatches implements JsonSerializable
{
    private OpenLigaDBGroup $group;

    private ?OpenLigaDBGroup $previousGroup = null;

    private ?OpenLigaDBGroup $nextGroup = null;

    private int $groupCount = 0;

    private string $leagueShortcut = '';

    private int $leagueSeason = 0;

    /**
     * @var OpenLigaDBMatch[]
     */
    private array $matches;

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

    /**
     * @param int $leagueSeason
     */
    public function setLeagueSeason(int $leagueSeason): void
    {
        $this->leagueSeason = $leagueSeason;
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
     * @return int
     */
    public function getGroupCount(): int
    {
        return $this->groupCount;
    }

    /**
     * @param int $groupCount
     * @return OpenLigaDBGroupMatches
     */
    public function setGroupCount(int $groupCount): OpenLigaDBGroupMatches
    {
        $this->groupCount = $groupCount;
        return $this;
    }

    /**
     * @return OpenLigaDBGroup
     */
    public function getGroup(): OpenLigaDBGroup
    {
        return $this->group;
    }

    /**
     * @param OpenLigaDBGroup $group
     * @return OpenLigaDBGroupMatches
     */
    public function setGroup(OpenLigaDBGroup $group): OpenLigaDBGroupMatches
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @return OpenLigaDBMatch[]
     */
    public function getMatches(): array
    {
        return $this->matches;
    }

    /**
     * @param OpenLigaDBMatch[] $matches
     * @return OpenLigaDBGroupMatches
     */
    public function setMatches(array $matches): OpenLigaDBGroupMatches
    {
        $this->matches = $matches;
        return $this;
    }

    public function addMatch(OpenLigaDBMatch $match): OpenLigaDBGroupMatches
    {
        $this->matches[] = $match;
        return $this;
    }

    /**
     * @return OpenLigaDBGroup|null
     */
    public function getPreviousGroup(): ?OpenLigaDBGroup
    {
        return $this->previousGroup;
    }

    /**
     * @param OpenLigaDBGroup|null $previousGroup
     * @return OpenLigaDBGroupMatches
     */
    public function setPreviousGroup(
        ?OpenLigaDBGroup $previousGroup
    ): OpenLigaDBGroupMatches {
        $this->previousGroup = $previousGroup;
        return $this;
    }

    /**
     * @return OpenLigaDBGroup|null
     */
    public function getNextGroup(): ?OpenLigaDBGroup
    {
        return $this->nextGroup;
    }

    /**
     * @param OpenLigaDBGroup|null $nextGroup
     * @return OpenLigaDBGroupMatches
     */
    public function setNextGroup(
        ?OpenLigaDBGroup $nextGroup
    ): OpenLigaDBGroupMatches {
        $this->nextGroup = $nextGroup;
        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'leagueShortcut' => $this->getLeagueShortcut(),
            'leagueSeason' => $this->getLeagueSeason(),
            'leagueSeasonDisplay' => $this->getLeagueSeasonDisplay(),
            'group' => $this->getGroup(),
            'nextGroup' => $this->getNextGroup(),
            'previousGroup' => $this->getPreviousGroup(),
            'groupCount' => $this->getGroupCount(),
            'matches' => $this->getMatches(),
        ];
    }
}
