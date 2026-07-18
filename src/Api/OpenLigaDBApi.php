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
                    return $match->getTeam1()->getTeamId()
                        === $query->getTeamId()
                        || $match->getTeam2()->getTeamId() === $query->getTeamId();
                },
            );
        }

        return $openligaDBMatches;
    }

    /**
     * @throws \JsonException
     * @throws RemoteRequestException
     */
    public static function getNextMatchByTeamid(OpenLigaDBMatchQuery $query): ?OpenLigaDBMatch
    {
        $matches = self::matchQuery($query);

        $matches = array_filter($matches, static function (OpenLigaDBMatch $match) {
            return $match->isFinished() === false;
        });

        $sortByTimestamp = static function (OpenLigaDBMatch $match1, OpenLigaDBMatch $match2) {
            if ($match1->getDateTime()->getTimestamp() === $match2->getDateTime()->getTimestamp()) {
                return 0;
            }

            return $match1->getDateTime()->getTimestamp() > $match2->getDateTime()->getTimestamp() ? 1 : -1;
        };

        usort($matches, $sortByTimestamp);

        return array_shift($matches);
    }

    /**
     * @throws \JsonException
     * @throws RemoteRequestException
     */
    public static function getLastMatchByTeamId(OpenLigaDBMatchQuery $query): ?OpenLigaDBMatch
    {
        $matches = self::matchQuery($query);

        $matches = array_filter($matches, static function (OpenLigaDBMatch $match) {
            return $match->isFinished() === true;
        });

        $sortByTimestamp = static function (OpenLigaDBMatch $match1, OpenLigaDBMatch $match2) {
            if ($match1->getDateTime()->getTimestamp() === $match2->getDateTime()->getTimestamp()) {
                return 0;
            }

            return $match1->getDateTime()->getTimestamp() > $match2->getDateTime()->getTimestamp() ? -1 : 1;
        };

        usort($matches, $sortByTimestamp);

        return array_shift($matches);
    }

    /**
     * @throws RemoteRequestException
     * @throws JsonException
     */
    public static function getGroupMatches(
        string $leagueShortcut,
        int $leagueSeason,
        int $groutOrderId,
    ): OpenLigaDBGroupMatches {

        $cacheKey = self::getCacheKey('soccr-oldb-group-matches', [$leagueShortcut, $leagueSeason, $groutOrderId]);

        $openLigaDBGroupMatches = get_transient($cacheKey);

        if ($openLigaDBGroupMatches !== false) {
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

        if ($currentGroupIndex === false) {
            throw new RuntimeException('Invalid Group');
        }

        if ($currentGroupIndex === 0) {
            $previousGroup = null;
        } else {
            $previousGroup = $openLigaDBGroups[$currentGroupIndex - 1];
        }

        if ($currentGroupIndex === count($openLigaDBGroups) - 1) {
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

        set_transient($cacheKey, $openLigaDBGroupMatches, HOUR_IN_SECONDS);
        return $openLigaDBGroupMatches;
    }

    /**
     * @return OpenLigaDBMatch[]
     * @throws RemoteRequestException
     * @throws JsonException
     */
    public static function getMatches(string $leagueShortcut, int $leagueSeason, ?int $groupOrderId = null): array
    {
        $cacheKey = self::getCacheKey('soccr-oldb-matches', [$leagueShortcut, $leagueSeason, $groupOrderId]);

        $openLigaDBMatches = get_transient($cacheKey);

        if ($openLigaDBMatches !== false) {
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
            if ($match1->getDateTime()->getTimestamp() === $match2->getDateTime()->getTimestamp()) {
                return 0;
            }

            return $match1->getDateTime()->getTimestamp() > $match2->getDateTime()->getTimestamp() ? 1 : -1;
        };

        usort($openLigaDBMatches, $sortByTimestamp);

        set_transient($cacheKey, $openLigaDBMatches, HOUR_IN_SECONDS);

        return $openLigaDBMatches;
    }


    /**
     * @param string $leagueShortcut
     * @return OpenLigaDBGroup
     * @throws JsonException
     * @throws RemoteRequestException
     */
    public static function getCurrentGroup(string $leagueShortcut): OpenLigaDBGroup
    {
        $cacheKey = self::getCacheKey('soccr-oldb-current-group', [$leagueShortcut]);

        $openLigaDBGroup = get_transient($cacheKey);

        if ($openLigaDBGroup !== false) {
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

        set_transient($cacheKey, $openLigaDBGroup, 2 * HOUR_IN_SECONDS);

        return $openLigaDBGroup;
    }

    /**
     * @param string $leagueShortcut
     * @param string $leagueSeason
     * @return OpenLigaDBGroup[]
     * @throws RemoteRequestException
     * @throws JsonException
     */
    public static function getAvailableGroups(string $leagueShortcut, string $leagueSeason): array
    {
        $cacheKey = self::getCacheKey('soccr-oldb-available-groups', [$leagueShortcut, $leagueSeason]);

        $openLigaDBGroups = get_transient($cacheKey);

        if ($openLigaDBGroups !== false) {
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

        set_transient($cacheKey, $openLigaDBGroups, 12 * HOUR_IN_SECONDS);

        return $openLigaDBGroups;
    }

    /**
     * @return OpenligaDBLeague[]
     * @throws RemoteRequestException
     * @throws JsonException
     */
    public static function getAvailableLeagues(): array
    {
        $cacheKey = self::getCacheKey('soccr-oldb-available-leagues');

        $openLigaDBLeagues = get_transient($cacheKey);

        if ($openLigaDBLeagues !== false) {
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

        set_transient($cacheKey, $openLigaDBLeagues, DAY_IN_SECONDS);

        return $openLigaDBLeagues;
    }

    /**
     * @return OpenLigaDBTeam[]
     * @throws RemoteRequestException
     * @throws JsonException
     */
    public static function getAvailableTeams(string $leagueShortcut, int $leagueSeason): array
    {
        $cacheKey = self::getCacheKey('soccr-oldb-available-teams', [$leagueShortcut, $leagueSeason]);

        $openLigaDBTeams = get_transient($cacheKey);

        if ($openLigaDBTeams !== false) {
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

        set_transient($cacheKey, $openLigaDBTeams, DAY_IN_SECONDS);

        return $openLigaDBTeams;
    }

    /**
     * @return OpenligaDBLeague[]
     * @throws RemoteRequestException
     * @throws JsonException
     */
    public static function queryLeagues(OpenLigaDBLeagueQuery $leagueQuery): array
    {
        $leagues = self::getAvailableLeagues();

        $leagues = array_filter($leagues, static function (OpenligaDBLeague $league) {
            return in_array($league->getSportId(), [1, 79], true);
        });

        $allowedShortcuts = apply_filters('soccr_league_shortcuts', ['bl1', 'bl2', 'bl3', 'fbl1', 'fbl2', 'ucl', 'uel', 'uecl','dfb']);

        $leagues = array_filter(
            $leagues,
            static function (OpenligaDBLeague $league) use ($allowedShortcuts) {
                return in_array($league->getLeagueShortcut(), $allowedShortcuts, true);
            },
        );

        $queryShortcuts = $leagueQuery->getLeagueShortcuts();

        if (!empty($queryShortcuts)) {
            $leagues = array_filter(
                $leagues,
                static function (OpenligaDBLeague $league) use ($queryShortcuts) {
                    return in_array($league->getLeagueShortcut(), $queryShortcuts, true);
                },
            );
        }

        $leagues = array_filter($leagues, static function (OpenligaDBLeague $league) use ($leagueQuery) {
            if (!$leagueQuery->getLeagueSeasonGreaterThan()) {
                return true;
            }

            return $league->getLeagueSeason() > $leagueQuery->getLeagueSeasonGreaterThan();
        });

        $sortByLeagueName = static function (OpenligaDBLeague $league1, OpenligaDBLeague $league2) {
            if ($league1->getLeagueName() === $league2->getLeagueName()) {
                return 0;
            }

            return $league1->getLeagueName() > $league2->getLeagueName() ? 1 : -1;
        };

        usort($leagues, $sortByLeagueName);

        $includeShortcut = $leagueQuery->getIncludeLeagueShortcut();
        $includeSeason = $leagueQuery->getIncludeLeagueSeason();

        if ($includeShortcut !== null && $includeSeason !== null) {
            $alreadyIncluded = array_filter(
                $leagues,
                static function (OpenligaDBLeague $league) use ($includeShortcut, $includeSeason) {
                    return $league->getLeagueShortcut() === $includeShortcut
                        && $league->getLeagueSeason() === $includeSeason;
                },
            );

            if (count($alreadyIncluded) === 0) {
                $allLeagues = self::getAvailableLeagues();
                $found = array_filter(
                    $allLeagues,
                    static function (OpenligaDBLeague $league) use ($includeShortcut, $includeSeason) {
                        return $league->getLeagueShortcut() === $includeShortcut
                            && $league->getLeagueSeason() === $includeSeason;
                    },
                );

                $includeLeague = array_shift($found);

                if ($includeLeague !== null) {
                    array_unshift($leagues, $includeLeague);
                }
            }
        }

        return $leagues;
    }

    /**
     * @throws RemoteRequestException
     * @throws JsonException
     */
    public static function getCurrentLeagueSeason(string $leagueShortcut): OpenligaDBLeague
    {
        $cacheKey = self::getCacheKey('soccr-oldb-current-league-season', [$leagueShortcut]);

        $openLigaDBLeague = get_transient($cacheKey);
        if ($openLigaDBLeague !== false) {
            return $openLigaDBLeague;
        }

        $openLigaDBLeagues = self::getAvailableLeagues();

        $openLigaDBLeaguesByShortcut = array_filter(
            $openLigaDBLeagues,
            static function (OpenligaDBLeague $league) use ($leagueShortcut) {
                return $league->getLeagueShortcut() === $leagueShortcut;
            },
        );

        if (count($openLigaDBLeaguesByShortcut) === 0) {
            throw new RuntimeException('LeagueShortcut not found');
        }
        $sortByLeagueSeason = static function (OpenligaDBLeague $league1, OpenligaDBLeague $league2) {
            if ($league1->getLeagueSeason() === $league2->getLeagueSeason()) {
                return 0;
            }

            return $league1->getLeagueSeason() > $league2->getLeagueSeason() ? -1 : 1;
        };

        usort($openLigaDBLeaguesByShortcut, $sortByLeagueSeason);

        $openLigaDBLeague = false;

        foreach ($openLigaDBLeaguesByShortcut as $currentOpenLigaDBLeague) {
            $matches = self::getMatches(
                $currentOpenLigaDBLeague->getLeagueShortcut(),
                $currentOpenLigaDBLeague->getLeagueSeason(),
            );

            if (count($matches) > 0) {
                $openLigaDBLeague = $currentOpenLigaDBLeague;
                break;
            }
        }

        if (!$openLigaDBLeague) {
            $openLigaDBLeague = array_shift($openLigaDBLeaguesByShortcut);
        }

        set_transient($cacheKey, $openLigaDBLeague, 3 * DAY_IN_SECONDS);

        return $openLigaDBLeague;
    }

    /**
     * @throws RemoteRequestException
     * @throws JsonException
     */
    public static function getLeagueSeason(string $leagueShortcut, int $leagueSeason): OpenligaDBLeague
    {
        $openLigaDBLeagues = self::getAvailableLeagues();

        $openLigaDBLeague = array_filter(
            $openLigaDBLeagues,
            static function (OpenligaDBLeague $league) use ($leagueShortcut, $leagueSeason) {
                return $league->getLeagueShortcut() === $leagueShortcut && $league->getLeagueSeason() === $leagueSeason;
            },
        );

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
    public static function getCurrentGroupMatches(string $leagueShortcut): OpenLigaDBGroupMatches
    {
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
    public static function getStandings(string $leagueShortcut, int $leagueSeason): OpenLigaDBStandings
    {
        $cacheKey = self::getCacheKey('soccr-oldb-standings', [$leagueShortcut, $leagueSeason]);

        $url = "https://api.openligadb.de/getbltable/$leagueShortcut/$leagueSeason";

        $openLigaDBStandings = get_transient($cacheKey);

        if ($openLigaDBStandings !== false) {
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

        set_transient($cacheKey, $openLigaDBStandings, HOUR_IN_SECONDS);

        return $openLigaDBStandings;
    }

    private static function getCacheKey(string $key, array $values = []): string
    {
        if (empty($values)) {
            return $key . "-" . SOCCR_VERSION;
        }

        return $key . '-' . implode('-', $values) . "-" . SOCCR_VERSION;
    }
}
