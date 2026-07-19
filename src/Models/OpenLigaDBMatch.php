<?php

namespace Rockschtar\Soccr\Models;

use DateTime;
use JsonSerializable;

class OpenLigaDBMatch implements JsonSerializable
{
    private int $matchId;

    private DateTime $dateTime;

    private int $leagueId;

    private string $leagueShortcut;

    private int $leagueSeason;

    private OpenLigaDBGroup $group;

    private OpenLigaDBTeam $team1;

    private OpenLigaDBTeam $team2;

    private bool $isFinished = false;

    /**
     * @var OpenLigaDBMatchResult[]
     */
    private array $results = [];

    private ?OpenLigaDBLocation $location = null;

    private ?int $numberOfViewers = null;

    public function addResult(OpenLigaDBMatchResult $result): OpenLigaDBMatch
    {
        $this->results[] = $result;
        return $this;
    }

    public function getResultByType(int $typeId): ?OpenLigaDBMatchResult
    {
        if (!$this->isFinished()) {
            return null;
        }

        $match = current(array_filter($this->getResults(), static function (OpenLigaDBMatchResult $result) use (
            $typeId
        ) {
            return $result->getTypeId() === $typeId;
        }));

        return $match === false ? null : $match;
    }

    public function getResult(): ?OpenLigaDBMatchResult
    {
        if (!$this->isFinished()) {
            return null;
        }

        $match = current(array_filter($this->getResults(), static function (OpenLigaDBMatchResult $result) {
            return $result->getTypeId() === 0 || $result->getTypeId() === 2;
        }));

        return $match === false ? null : $match;
    }

    public function isFinished(): bool
    {
        return $this->isFinished;
    }

    public function setIsFinished(bool $isFinished): OpenLigaDBMatch
    {
        $this->isFinished = $isFinished;
        return $this;
    }

    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * @param OpenLigaDBMatchResult[] $results
     */
    public function setResults(array $results): OpenLigaDBMatch
    {
        $this->results = $results;
        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'matchId' => $this->getMatchId(),
            'dateTime' => $this->getDateTime()->format(DATE_ATOM),
            'timestamp' => $this->getDateTime()->getTimestamp(),
            'leagueId' => $this->getLeagueId(),
            'leagueShortcut' => $this->getLeagueShortcut(),
            'leagueSeason' => $this->getLeagueSeason(),
            'group' => $this->getGroup(),
            'team1' => $this->getTeam1(),
            'team2' => $this->getTeam2(),
            'isFinished' => $this->isFinished(),
            'results' => $this->getResults(),
            'location' => $this->getLocation(),
            'numberOfViewers' => $this->getNumberOfViewers(),
        ];
    }

    public function getMatchId(): int
    {
        return $this->matchId;
    }

    public function setMatchId(int $matchId): OpenLigaDBMatch
    {
        $this->matchId = $matchId;
        return $this;
    }

    public function getDateTime(): DateTime
    {
        return $this->dateTime;
    }

    public function setDateTime(DateTime $dateTime): OpenLigaDBMatch
    {
        $this->dateTime = $dateTime;
        return $this;
    }

    public function getLeagueId(): int
    {
        return $this->leagueId;
    }

    public function setLeagueId(int $leagueId): OpenLigaDBMatch
    {
        $this->leagueId = $leagueId;
        return $this;
    }

    public function getLeagueShortcut(): string
    {
        return $this->leagueShortcut;
    }

    public function setLeagueShortcut(string $leagueShortcut): OpenLigaDBMatch
    {
        $this->leagueShortcut = $leagueShortcut;
        return $this;
    }

    public function getLeagueSeason(): int
    {
        return $this->leagueSeason;
    }

    public function setLeagueSeason(int $leagueSeason): OpenLigaDBMatch
    {
        $this->leagueSeason = $leagueSeason;
        return $this;
    }

    public function getGroup(): OpenLigaDBGroup
    {
        return $this->group;
    }

    public function setGroup(OpenLigaDBGroup $group): OpenLigaDBMatch
    {
        $this->group = $group;
        return $this;
    }

    public function getTeam1(): OpenLigaDBTeam
    {
        return $this->team1;
    }

    public function setTeam1(OpenLigaDBTeam $team1): OpenLigaDBMatch
    {
        $this->team1 = $team1;
        return $this;
    }

    public function getTeam2(): OpenLigaDBTeam
    {
        return $this->team2;
    }

    public function setTeam2(OpenLigaDBTeam $team2): OpenLigaDBMatch
    {
        $this->team2 = $team2;
        return $this;
    }

    public function getLocation(): ?OpenLigaDBLocation
    {
        return $this->location;
    }

    public function setLocation(?OpenLigaDBLocation $location): OpenLigaDBMatch
    {
        $this->location = $location;
        return $this;
    }

    public function getNumberOfViewers(): ?int
    {
        return $this->numberOfViewers;
    }

    public function setNumberOfViewers(?int $numberOfViewers): OpenLigaDBMatch
    {
        $this->numberOfViewers = $numberOfViewers;
        return $this;
    }
}
