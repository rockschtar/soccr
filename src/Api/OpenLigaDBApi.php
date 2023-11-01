<?php


use ClubfansUnited\Exceptions\RemoteRequestException;
use ClubfansUnited\Factories\OpenLigaDBGroupFactory;
use ClubfansUnited\Factories\OpenLigaDBLeagueFactory;
use ClubfansUnited\Factories\OpenLigaDBMatchFactory;
use ClubfansUnited\Factories\OpenLigaDBTeamFactory;
use ClubfansUnited\Models\OpenLigaDBGroup;
use ClubfansUnited\Models\OpenLigaDBGroupMatches;
use ClubfansUnited\Models\OpenligaDBLeague;
use ClubfansUnited\Models\OpenLigaDBLeagueQuery;
use ClubfansUnited\Models\OpenLigaDBMatch;
use ClubfansUnited\Models\OpenLigaDBMatchQuery;
use ClubfansUnited\Models\OpenLigaDBStanding;
use ClubfansUnited\Models\OpenLigaDBStandings;
use ClubfansUnited\Models\OpenLigaDBTeam;
use ClubfansUnited\Utils\RemoteRequest;
use JsonException;
use RuntimeException;

class OpenLigaDBApi
{
    /**
     * @param OpenLigaDBMatchQuery $query
     * @return OpenLigaDBMatch[]
     * @throws RemoteRequestException|JsonException
     */
    public static function matchQuery(OpenLigaDBMatchQuery $query): array
    {
        $openligaDBMatches = [];

        foreach ($query->getLeagueSeasons() as $leagueSeason) {
            $openligaDBMatches[] = self::getMatches(
                $leagueSeason->getLeagueShortcut(),
                $leagueSeason->getLeagueSeason(),
                $query->getGroupOrderId(),
            );
        }
        $openligaDBMatches = array_merge(...$openligaDBMatches);

        if ($query->getTeamId() !== null) {
            return \ClubfansUnited\Manager\from($openligaDBMatches)
                ->where(static function (OpenLigaDBMatch $match) use ($query) {
                    return $match->getTeam1()->getTeamId() ===
                        $query->getTeamId() ||
                        $match->getTeam2()->getTeamId() === $query->getTeamId();
                })
                ->toArray();
        }

        return $openligaDBMatches;
    }

    /**
     * @param string $leagueShortcut
     * @param int $leagueSeason
     * @param int $groutOrderId
     * @return OpenLigaDBGroupMatches
     * @throws RemoteRequestException
     * @throws JsonException
     */
    public static function getGroupMatches(
        string $leagueShortcut,
        int $leagueSeason,
        int $groutOrderId
    ): OpenLigaDBGroupMatches {
        $cacheKey = "cu-openligadb-group-matches-$leagueShortcut-$leagueSeason-$groutOrderId";

        $openLigaDBGroupMatches = wp_cache_get($cacheKey, 'openligadb');

        if ($openLigaDBGroupMatches) {
            return $openLigaDBGroupMatches;
        }

        $openLigaDBGroups = self::getAvailableGroups(
            $leagueShortcut,
            $leagueSeason,
        );

        $currentGroup = \ClubfansUnited\Manager\from($openLigaDBGroups)
            ->where(static function (OpenLigaDBGroup $group) use (
                $groutOrderId
            ) {
                return $group->getGroupOrderId() === $groutOrderId;
            })
            ->firstOrDefault();

        if ($currentGroup === null) {
            throw new RuntimeException('Invalid Group');
        }

        $nextGroup = \ClubfansUnited\Manager\from($openLigaDBGroups)
            ->skipWhile(static function ($group) use ($currentGroup) {
                return $group->getGroupOrderId() !==
                    $currentGroup->getGroupOrderId();
            })
            ->skip(1)
            ->firstOrDefault();

        $previousGroup = \ClubfansUnited\Manager\from(array_reverse($openLigaDBGroups))
            ->skipWhile(static function ($group) use ($currentGroup) {
                return $group->getGroupOrderId() !==
                    $currentGroup->getGroupOrderId();
            })
            ->skip(1)
            ->firstOrDefault();

        $groupCount = count($openLigaDBGroups);
        $matches = self::getMatches(
            $leagueShortcut,
            $leagueSeason,
            $groutOrderId,
        );

        $openLigaDBGroupMatches = new OpenLigaDBGroupMatches();
        $openLigaDBGroupMatches->setGroup($currentGroup);
        $openLigaDBGroupMatches->setNextGroup($nextGroup);
        $openLigaDBGroupMatches->setPreviousGroup($previousGroup);
        $openLigaDBGroupMatches->setGroupCount($groupCount);
        $openLigaDBGroupMatches->setMatches($matches);
        $openLigaDBGroupMatches->setLeagueShortcut($leagueShortcut);
        $openLigaDBGroupMatches->setLeagueSeason($leagueSeason);

        wp_cache_set(
            $cacheKey,
            $openLigaDBGroupMatches,
            'openligadb',
            60 * 60 * 1,
        );
        return $openLigaDBGroupMatches;
    }

    /**
     * @param string $leagueShortcut
     * @param int $leagueSeason
     * @param int|null $groupOrderId
     * @return OpenLigaDBMatch[]
     * @throws RemoteRequestException
     * @throws JsonException
     */
    public static function getMatches(
        string $leagueShortcut,
        int $leagueSeason,
        ?int $groupOrderId = null
    ): array {
        $cacheKey = "cu-openligadb-matches-$leagueShortcut-$leagueSeason-$groupOrderId";

        $openLigaDBMatches = wp_cache_get($cacheKey, 'openligadb');

        if ($openLigaDBMatches) {
            return $openLigaDBMatches;
        }

        $url = "https://api.openligadb.de/getmatchdata/$leagueShortcut/$leagueSeason";

        if ($groupOrderId) {
            $url .= "/$groupOrderId";
        }

        $remoteRequest = new RemoteRequest($url);

        $result = $remoteRequest->execute();

        $matches = json_decode(
            $result->getBody(),
            false,
            512,
            JSON_THROW_ON_ERROR,
        );

        $openLigaDBMatches = [];

        foreach ($matches as $match) {
            $openLigaDBMatches[] = OpenLigaDBMatchFactory::createFromJSON(
                $match,
            );
        }

        $openLigaDBMatches = \ClubfansUnited\Manager\from($openLigaDBMatches)
            ->orderBy(static function (OpenLigaDBMatch $match) {
                return $match->getDateTime()->getTimestamp();
            })
            ->toArray();

        wp_cache_set($cacheKey, $openLigaDBMatches, 'openligadb', 60 * 60 * 1);

        return $openLigaDBMatches;
    }

    /**
     * @param string $leagueShortcut
     * @return OpenLigaDBGroup
     * @throws RemoteRequestException
     * @throws JsonException
     */
    public static function getCurrentGroup(
        string $leagueShortcut
    ): OpenLigaDBGroup {
        $cacheKey = "cu-openligadb-current-group-$leagueShortcut";

        $openLigaDBGroup = wp_cache_get($cacheKey, 'openligadb');

        if ($openLigaDBGroup) {
            return $openLigaDBGroup;
        }

        $remoteRequest = new RemoteRequest(
            "https://api.openligadb.de/getcurrentgroup/$leagueShortcut",
        );
        $result = $remoteRequest->execute();
        $group = json_decode(
            $result->getBody(),
            false,
            512,
            JSON_THROW_ON_ERROR,
        );
        $openLigaDBGroup = OpenLigaDBGroupFactory::createFromJSON($group);

        wp_cache_set($cacheKey, $openLigaDBGroup, 'openligadb', 60 * 60 * 2);

        return $openLigaDBGroup;
    }

    /**
     * @param string $leagueShortcut
     * @param string $leagueSeason
     * @return OpenLigaDBGroup[]
     * @throws RemoteRequestException
     * @throws JsonException
     */
    public static function getAvailableGroups(
        string $leagueShortcut,
        string $leagueSeason
    ): array {
        $cacheKey = "cu-openligadb-available-groups-$leagueShortcut-$leagueSeason";

        $openLigaDBGroups = wp_cache_get($cacheKey, 'openligadb');

        if ($openLigaDBGroups) {
            return $openLigaDBGroups;
        }

        $openLigaDBGroups = [];

        $remoteRequest = new RemoteRequest(
            "https://api.openligadb.de/getavailablegroups/$leagueShortcut/$leagueSeason",
        );

        $result = $remoteRequest->execute();

        $groups = json_decode(
            $result->getBody(),
            false,
            512,
            JSON_THROW_ON_ERROR,
        );

        foreach ($groups as $group) {
            $openLigaDBGroups[] = OpenLigaDBGroupFactory::createFromJSON(
                $group,
            );
        }

        wp_cache_set($cacheKey, $openLigaDBGroups, 'openligadb', 60 * 60 * 12);

        return $openLigaDBGroups;
    }

    /**
     * @return OpenligaDBLeague[]
     * @throws RemoteRequestException
     * @throws JsonException
     */
    public static function getAvailableLeagues(): array
    {
        $cacheKey = 'cu-openligadb-available-leagues';

        $openLigaDBLeagues = wp_cache_get($cacheKey, 'openligadb');

        if ($openLigaDBLeagues) {
            return $openLigaDBLeagues;
        }

        $openLigaDBLeagues = [];

        $remoteRequest = new RemoteRequest(
            'https://api.openligadb.de/getavailableleagues',
        );

        $result = $remoteRequest->execute();

        $groups = json_decode(
            $result->getBody(),
            false,
            512,
            JSON_THROW_ON_ERROR,
        );

        foreach ($groups as $group) {
            $openLigaDBLeagues[] = OpenLigaDBLeagueFactory::createFromJSON(
                $group,
            );
        }

        wp_cache_set($cacheKey, $openLigaDBLeagues, 'openligadb', 60 * 60 * 24);

        return $openLigaDBLeagues;
    }

    /**
     * @param string $leagueShortcut
     * @param int $leagueSeason
     * @return OpenLigaDBTeam[]
     * @throws RemoteRequestException
     * @throws JsonException
     */
    public static function getAvailableTeams(
        string $leagueShortcut,
        int $leagueSeason
    ): array {
        $cacheKey = "cu-openligadb-available-teams-$leagueShortcut-$leagueSeason";

        $openLigaDBTeams = wp_cache_get($cacheKey);

        if ($openLigaDBTeams) {
            return $openLigaDBTeams;
        }

        $openLigaDBTeams = [];

        $remoteRequest = new RemoteRequest(
            "https://api.openligadb.de/getavailableteams/$leagueShortcut/$leagueSeason",
        );

        $result = $remoteRequest->execute();

        $teams = json_decode(
            $result->getBody(),
            false,
            512,
            JSON_THROW_ON_ERROR,
        );

        foreach ($teams as $team) {
            $openLigaDBTeams[] = OpenLigaDBTeamFactory::createFromJSON($team);
        }

        wp_cache_set($cacheKey, $openLigaDBTeams, 'openligadb', 60 * 60 * 24);

        return $openLigaDBTeams;
    }

    /**
     * @param OpenLigaDBLeagueQuery $leagueQuery
     * @return OpenligaDBLeague[]
     * @throws RemoteRequestException
     * @throws JsonException
     */
    public static function queryLeagues(
        OpenLigaDBLeagueQuery $leagueQuery
    ): array {
        $leagues = self::getAvailableLeagues();

        $leagues = \ClubfansUnited\Manager\from($leagues)
            ->where(static function (OpenligaDBLeague $league) use (
                $leagueQuery
            ) {
                if (!$leagueQuery->getLeagueShortcut()) {
                    return true;
                }

                return $league->getLeagueShortcut() ===
                    $leagueQuery->getLeagueShortcut();
            })
            ->where(static function (OpenligaDBLeague $league) use (
                $leagueQuery
            ) {
                if (!$leagueQuery->getLeagueSeasonGreaterThan()) {
                    return true;
                }

                return $league->getLeagueSeason() >
                    $leagueQuery->getLeagueSeasonGreaterThan();
            })
            ->orderBy(static function (OpenligaDBLeague $league) {
                return $league->getLeagueName();
            })
            ->toArray();

        return $leagues;
    }

    /**
     * @param string $leagueShortcut
     * @return OpenligaDBLeague
     * @throws RemoteRequestException
     * @throws JsonException
     */
    public static function getCurrentLeagueSeason(
        string $leagueShortcut
    ): OpenligaDBLeague {
        $cacheKey = "cu-openligadb-current-league-season-$leagueShortcut";

        $openLigaDBLeagues = wp_cache_get($cacheKey, 'openligadb');

        if ($openLigaDBLeagues) {
            return $openLigaDBLeagues;
        }

        $openLigaDBLeagues = self::getAvailableLeagues();

        /* @var $openLigaDBLeague OpenligaDBLeague|null */
        $openLigaDBLeague = \ClubfansUnited\Manager\from($openLigaDBLeagues)
            ->where(static function (OpenligaDBLeague $openLigaDBLeague) use (
                $leagueShortcut
            ) {
                return $openLigaDBLeague->getLeagueShortcut() ===
                    $leagueShortcut;
            })
            ->orderByDescending(static function (
                OpenligaDBLeague $openLigaDBLeague
            ) {
                return $openLigaDBLeague->getLeagueSeason();
            })
            ->firstOrDefault();

        if ($openLigaDBLeague === null) {
            throw new RuntimeException('LeagueShortcut not found');
        }

        wp_cache_set(
            $cacheKey,
            $openLigaDBLeague,
            'openligadb',
            WEEK_IN_SECONDS,
        );

        return $openLigaDBLeague;
    }

    /**
     * @param string $leagueShortcut
     * @param int $leagueSeason
     * @return OpenligaDBLeague
     * @throws RemoteRequestException
     * @throws JsonException
     */
    public static function getLeagueSeason(
        string $leagueShortcut,
        int $leagueSeason
    ): OpenligaDBLeague {
        $openLigaDBLeagues = self::getAvailableLeagues();

        /* @var $openLigaDBLeague OpenligaDBLeague|null */
        $openLigaDBLeague = \ClubfansUnited\Manager\from($openLigaDBLeagues)
            ->where(static function (OpenligaDBLeague $openLigaDBLeague) use (
                $leagueShortcut,
                $leagueSeason
            ) {
                return $openLigaDBLeague->getLeagueShortcut() ===
                    $leagueShortcut &&
                    $openLigaDBLeague->getLeagueSeason() === $leagueSeason;
            })
            ->firstOrDefault();

        if ($openLigaDBLeague === null) {
            throw new RuntimeException('League not found');
        }

        return $openLigaDBLeague;
    }

    /**
     * @param string $leagueShortcut
     * @return OpenLigaDBGroupMatches
     * @throws RemoteRequestException
     * @throws JsonException
     */
    public static function getCurrentGroupMatches(
        string $leagueShortcut
    ): OpenLigaDBGroupMatches {
        $openLigaDBLeague = self::getCurrentLeagueSeason($leagueShortcut);
        $group = self::getCurrentGroup($leagueShortcut);
        return self::getGroupMatches(
            $leagueShortcut,
            $openLigaDBLeague->getLeagueSeason(),
            $group->getGroupOrderId(),
        );
    }

    /**
     * @param string $leagueShortcut
     * @param int $leagueSeason
     * @return OpenLigaDBStandings
     * @throws RemoteRequestException
     * @throws JsonException
     */
    public static function getStandings(
        string $leagueShortcut,
        int $leagueSeason
    ): OpenLigaDBStandings {
        $cacheKey = "cu-openligadb-standings-$leagueShortcut-$leagueSeason";

        $url = "https://api.openligadb.de/getbltable/$leagueShortcut/$leagueSeason";

        $openLigaDBStandings = wp_cache_get($cacheKey, 'openligadb');

        if ($openLigaDBStandings) {
            return $openLigaDBStandings;
        }

        $remoteRequest = new RemoteRequest($url);
        $result = $remoteRequest->execute();
        $table = json_decode(
            $result->getBody(),
            false,
            512,
            JSON_THROW_ON_ERROR,
        );

        $openLigaDBStandings = new OpenLigaDBStandings();
        $openLigaDBStandings->setLeague(
            self::getLeagueSeason($leagueShortcut, $leagueSeason),
        );
        foreach ($table as $row) {
            $openLigaDBTeam = new OpenLigaDBTeam();
            $openLigaDBTeam->setTeamId($row->teamInfoId);
            $openLigaDBTeam->setTeamName($row->teamName);
            $openLigaDBTeam->setShortName($row->shortName);
            $openLigaDBTeam->setIconUrl($row->teamIconUrl);

            $openLigaDBStanding = new OpenLigaDBStanding();
            $openLigaDBStanding->setTeam($openLigaDBTeam);
            $openLigaDBStanding->setMatches($row->matches);
            $openLigaDBStanding->setPoints($row->points);
            $openLigaDBStanding->setWins($row->won);
            $openLigaDBStanding->setDraws($row->draw);
            $openLigaDBStanding->setLooses($row->lost);
            $openLigaDBStanding->setGoalsScored($row->goals);
            $openLigaDBStanding->setGoalsConceded($row->opponentGoals);
            $openLigaDBStanding->setGoalDifference($row->goalDiff);

            $openLigaDBStandings->addStanding($openLigaDBStanding);
        }

        wp_cache_set($cacheKey, $openLigaDBStandings, 'openligadb', 60 * 60);

        return $openLigaDBStandings;
    }
}
