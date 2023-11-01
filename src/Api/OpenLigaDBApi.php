<?php


namespace Rockschtar\WordPress\Soccr\Api;

use JsonException;
use Rockschtar\WordPress\Soccr\Exceptions\RemoteRequestException;
use Rockschtar\WordPress\Soccr\Factories\OpenLigaDBGroupFactory;
use Rockschtar\WordPress\Soccr\Factories\OpenLigaDBLeagueFactory;
use Rockschtar\WordPress\Soccr\Factories\OpenLigaDBMatchFactory;
use Rockschtar\WordPress\Soccr\Factories\OpenLigaDBTeamFactory;
use Rockschtar\WordPress\Soccr\Models\OpenLigaDBGroup;
use Rockschtar\WordPress\Soccr\Models\OpenLigaDBGroupMatches;
use Rockschtar\WordPress\Soccr\Models\OpenligaDBLeague;
use Rockschtar\WordPress\Soccr\Models\OpenLigaDBLeagueQuery;
use Rockschtar\WordPress\Soccr\Models\OpenLigaDBMatch;
use Rockschtar\WordPress\Soccr\Models\OpenLigaDBMatchQuery;
use Rockschtar\WordPress\Soccr\Models\OpenLigaDBStanding;
use Rockschtar\WordPress\Soccr\Models\OpenLigaDBStandings;
use Rockschtar\WordPress\Soccr\Models\OpenLigaDBTeam;
use Rockschtar\WordPress\Soccr\Utils\RemoteRequest;
use RuntimeException;

class OpenLigaDBApi
{
    /**
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
            $openligaDBMatches = array_filter(
                $openligaDBMatches,
                static function (OpenLigaDBMatch $match) use ($query) {
                    return $match->getTeam1()->getTeamId() ===
                        $query->getTeamId() ||
                        $match->getTeam2()->getTeamId() === $query->getTeamId();
                }
            );
        }

        return $openligaDBMatches;
    }

    /**
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

        $currentGroup = array_filter($openLigaDBGroups, static function (OpenLigaDBGroup $group) use ($groutOrderId) {
            return $group->getGroupOrderId() === $groutOrderId;
        });

        $currentGroup = count($currentGroup) === 0 ? null : array_shift($currentGroup);

        if ($currentGroup === null) {
            throw new RuntimeException('Invalid Group');
        }


        $currentGroupIndex = array_search($currentGroup, $openLigaDBGroups);

        if($currentGroupIndex === false) {
            throw new RuntimeException('Invalid Group');
        }

        if($currentGroupIndex === 0) {
            $previousGroup = null;
        } else {
            $previousGroup = $openLigaDBGroups[$currentGroupIndex - 1];
        }

        if($currentGroupIndex === count($openLigaDBGroups) - 1) {
            $nextGroup = null;
        } else {
            $nextGroup = $openLigaDBGroups[$currentGroupIndex + 1];
        }

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

        $sortByTimestamp = static function (OpenLigaDBMatch $match1, OpenLigaDBMatch $match2) {

            if($match1->getDateTime()->getTimestamp() === $match2->getDateTime()->getTimestamp()) {
                return 0;
            }

            return $match1->getDateTime()->getTimestamp() > $match2->getDateTime()->getTimestamp() ? 1 : -1;
        };

        usort($openLigaDBMatches, $sortByTimestamp);

        wp_cache_set($cacheKey, $openLigaDBMatches, 'openligadb', 60 * 60 * 1);

        return $openLigaDBMatches;
    }

    /**
     * @param string $leagueShortcut
     * @return OpenLigaDBGroup
     * @throws \JsonException
     * @throws \Rockschtar\WordPress\Soccr\Exceptions\RemoteRequestException
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
     * @return OpenligaDBLeague[]
     * @throws RemoteRequestException
     * @throws JsonException
     */
    public static function queryLeagues(OpenLigaDBLeagueQuery $leagueQuery): array {
        $leagues = self::getAvailableLeagues();


        $leagues = array_filter($leagues, static function (OpenligaDBLeague $league) use ($leagueQuery) {
            if (!$leagueQuery->getLeagueShortcut()) {
                return true;
            }

            return $league->getLeagueShortcut() === $leagueQuery->getLeagueShortcut();
        });

        $leagues = array_filter($leagues, static function (OpenligaDBLeague $league) use ($leagueQuery) {
            if (!$leagueQuery->getLeagueSeasonGreaterThan()) {
                return true;
            }

            return $league->getLeagueSeason() > $leagueQuery->getLeagueSeasonGreaterThan();
        });

        $sortByLeagueName = static function (OpenligaDBLeague $league1, OpenligaDBLeague $league2) {

            if($league1->getLeagueName() === $league2->getLeagueName()) {
                return 0;
            }

            return $league1->getLeagueName() > $league2->getLeagueName() ? 1 : -1;
        };

        usort($leagues, $sortByLeagueName);

        return $leagues;
    }

    /**
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

        $openLigaDBLeaguesByShortcut =array_filter($openLigaDBLeagues, static function (OpenligaDBLeague $league) use ($leagueShortcut) {
            return $league->getLeagueShortcut() === $leagueShortcut;
        });

        if (count($openLigaDBLeaguesByShortcut) === 0) {
            throw new RuntimeException('LeagueShortcut not found');
        }
        $sortByLeagueSeason = static function (OpenligaDBLeague $league1, OpenligaDBLeague $league2) {

            if($league1->getLeagueSeason() === $league2->getLeagueSeason()) {
                return 0;
            }

            return $league1->getLeagueSeason() > $league2->getLeagueSeason() ? -1 : 1;
        };

        usort($openLigaDBLeaguesByShortcut, $sortByLeagueSeason);

        $openLigaDBLeague = array_shift($openLigaDBLeaguesByShortcut);

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
    public static function getLeagueSeason(string $leagueShortcut, int $leagueSeason): OpenligaDBLeague {
        $openLigaDBLeagues = self::getAvailableLeagues();

        $openLigaDBLeague = array_filter($openLigaDBLeagues, static function (OpenligaDBLeague $league) use ($leagueShortcut, $leagueSeason) {
            return $league->getLeagueShortcut() === $leagueShortcut && $league->getLeagueSeason() === $leagueSeason;
        });

        $openLigaDBLeague = array_shift($openLigaDBLeague);

        if ($openLigaDBLeague === null) {
            throw new RuntimeException('League not found');
        }

        return $openLigaDBLeague;
    }

    /**
     * @throws RemoteRequestException
     * @throws JsonException
     */
    public static function getCurrentGroupMatches(string $leagueShortcut): OpenLigaDBGroupMatches {
        $openLigaDBLeague = self::getCurrentLeagueSeason($leagueShortcut);
        $group = self::getCurrentGroup($leagueShortcut);
        return self::getGroupMatches(
            $leagueShortcut,
            $openLigaDBLeague->getLeagueSeason(),
            $group->getGroupOrderId(),
        );
    }

    /**
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
