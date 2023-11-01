<?php

namespace ClubfansUnited\Models;

class OpenLigaDBMatchQuery
{
    /**
     * @var OpenLigaDBLeagueSeason[]
     */
    private array $leagueSeasons = [];

    private ?int $groupOrderId = null;

    private ?int $teamId = null;

    /**
     * @return OpenLigaDBLeagueSeason[]
     */
    public function getLeagueSeasons(): array
    {
        return $this->leagueSeasons;
    }

    public function addLeagueSeason(
        string $leagueShortcut,
        int $leagueSeason
    ): OpenLigaDBMatchQuery {
        $this->leagueSeasons[] = new OpenLigaDBLeagueSeason(
            $leagueShortcut,
            $leagueSeason,
        );
        return $this;
    }

    /**
     * @param OpenLigaDBLeagueSeason[] $leagueSeasons
     * @return OpenLigaDBMatchQuery
     */
    public function setLeagueSeasons(array $leagueSeasons): OpenLigaDBMatchQuery
    {
        $this->leagueSeasons = $leagueSeasons;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getGroupOrderId(): ?int
    {
        return $this->groupOrderId;
    }

    /**
     * @param int|null $groupOrderId
     * @return OpenLigaDBMatchQuery
     */
    public function setGroupOrderId(?int $groupOrderId): OpenLigaDBMatchQuery
    {
        $this->groupOrderId = $groupOrderId;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getTeamId(): ?int
    {
        return $this->teamId;
    }

    /**
     * @param int|null $teamId
     * @return OpenLigaDBMatchQuery
     */
    public function setTeamId(?int $teamId): OpenLigaDBMatchQuery
    {
        $this->teamId = $teamId;
        return $this;
    }
}
