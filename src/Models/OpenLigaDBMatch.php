<?php

namespace ClubfansUnited\Models;

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

    /**
     * @return int
     */
    public function getMatchTimestamp(): int
    {
        return $this->matchTimestamp;
    }

    /**
     * @param int $matchTimestamp
     * @return OpenLigaDBMatch
     */
    public function setMatchTimestamp(int $matchTimestamp): OpenLigaDBMatch
    {
        $this->matchTimestamp = $matchTimestamp;
        return $this;
    }

    /**
     * @param OpenLigaDBMatchResult $result
     * @return OpenLigaDBMatch
     */
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

        return from($this->getResults())
            ->where(static function (OpenLigaDBMatchResult $result) use (
                $typeId
            ) {
                return $result->getTypeId() === $typeId;
            })
            ->firstOrDefault();
    }

    /**
     * @return bool
     */
    public function isFinished(): bool
    {
        return $this->isFinished;
    }

    /**
     * @param bool $isFinished
     * @return OpenLigaDBMatch
     */
    public function setIsFinished(bool $isFinished): OpenLigaDBMatch
    {
        $this->isFinished = $isFinished;
        return $this;
    }

    /**
     * @return OpenLigaDBMatchResult[]
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * @param OpenLigaDBMatchResult[] $results
     * @return OpenLigaDBMatch
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

    /**
     * @return int
     */
    public function getMatchId(): int
    {
        return $this->matchId;
    }

    /**
     * @param int $matchId
     * @return OpenLigaDBMatch
     */
    public function setMatchId(int $matchId): OpenLigaDBMatch
    {
        $this->matchId = $matchId;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateTime(): DateTime
    {
        return $this->dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @return OpenLigaDBMatch
     */
    public function setDateTime(DateTime $dateTime): OpenLigaDBMatch
    {
        $this->dateTime = $dateTime;
        return $this;
    }

    /**
     * @return int
     */
    public function getLeagueId(): int
    {
        return $this->leagueId;
    }

    /**
     * @param int $leagueId
     * @return OpenLigaDBMatch
     */
    public function setLeagueId(int $leagueId): OpenLigaDBMatch
    {
        $this->leagueId = $leagueId;
        return $this;
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
     * @return OpenLigaDBMatch
     */
    public function setLeagueShortcut(string $leagueShortcut): OpenLigaDBMatch
    {
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
     * @return OpenLigaDBMatch
     */
    public function setLeagueSeason(int $leagueSeason): OpenLigaDBMatch
    {
        $this->leagueSeason = $leagueSeason;
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
     * @return OpenLigaDBMatch
     */
    public function setGroup(OpenLigaDBGroup $group): OpenLigaDBMatch
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @return OpenLigaDBTeam
     */
    public function getTeam1(): OpenLigaDBTeam
    {
        return $this->team1;
    }

    /**
     * @param OpenLigaDBTeam $team1
     * @return OpenLigaDBMatch
     */
    public function setTeam1(OpenLigaDBTeam $team1): OpenLigaDBMatch
    {
        $this->team1 = $team1;
        return $this;
    }

    /**
     * @return OpenLigaDBTeam
     */
    public function getTeam2(): OpenLigaDBTeam
    {
        return $this->team2;
    }

    /**
     * @param OpenLigaDBTeam $team2
     * @return OpenLigaDBMatch
     */
    public function setTeam2(OpenLigaDBTeam $team2): OpenLigaDBMatch
    {
        $this->team2 = $team2;
        return $this;
    }

    /**
     * @return OpenLigaDBLocation|null
     */
    public function getLocation(): ?OpenLigaDBLocation
    {
        return $this->location;
    }

    /**
     * @param OpenLigaDBLocation|null $location
     * @return OpenLigaDBMatch
     */
    public function setLocation(?OpenLigaDBLocation $location): OpenLigaDBMatch
    {
        $this->location = $location;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getNumberOfViewers(): ?int
    {
        return $this->numberOfViewers;
    }

    /**
     * @param int|null $numberOfViewers
     * @return OpenLigaDBMatch
     */
    public function setNumberOfViewers(?int $numberOfViewers): OpenLigaDBMatch
    {
        $this->numberOfViewers = $numberOfViewers;
        return $this;
    }
}
