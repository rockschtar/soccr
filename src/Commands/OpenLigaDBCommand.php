<?php

namespace Rockschtar\Soccr\Commands;

use Exception;
use Rockschtar\Soccr\Api\OpenLigaDBApi;
use Rockschtar\Soccr\Models\OpenLigaDBLeagueQuery;
use WP_CLI;

class OpenLigaDBCommand
{
    private const array LEAGUE_FIELDS = ['leagueId', 'leagueName', 'leagueNameShort', 'leagueShortcut', 'leagueSeason'];

    /**
     * Lists leagues via OpenLigaDBApi::queryLeagues().
     *
     * ## OPTIONS
     *
     * [--shortcuts=<shortcuts>]
     * : Comma-separated league shortcuts, e.g. bl1,bl2
     *
     * [--season-greater-than=<year>]
     * : Only leagues with a season greater than <year>
     *
     * [--include-shortcut=<shortcut>]
     * : Include this league as well (together with --include-season)
     *
     * [--include-season=<year>]
     * : Season for --include-shortcut
     *
     * [--format=<format>]
     * : Output format: table, json, csv, yaml, count. Default: table
     *
     * ## EXAMPLES
     *
     *     wp soccr leagues --shortcuts=bl1,bl2 --season-greater-than=2024
     */
    public function leagues(array $args, array $assocArgs): void
    {
        $query = new OpenLigaDBLeagueQuery();

        if (!empty($assocArgs['shortcuts'])) {
            $query->setLeagueShortcuts(explode(',', $assocArgs['shortcuts']));
        }

        if (!empty($assocArgs['season-greater-than'])) {
            $query->setLeagueSeasonGreaterThan((int) $assocArgs['season-greater-than']);
        }

        if (!empty($assocArgs['include-shortcut'])) {
            $query->setIncludeLeagueShortcut($assocArgs['include-shortcut']);
        }

        if (!empty($assocArgs['include-season'])) {
            $query->setIncludeLeagueSeason((int) $assocArgs['include-season']);
        }

        try {
            $leagues = OpenLigaDBApi::queryLeagues($query);
        } catch (Exception $e) {
            WP_CLI::error($e->getMessage());
        }

        $items = array_values(array_map(
            static fn($league) => $league->jsonSerialize(),
            $leagues,
        ));

        \WP_CLI\Utils\format_items(
            $assocArgs['format'] ?? 'table',
            $items,
            self::LEAGUE_FIELDS,
        );
    }

    /**
     * Fetches a league via OpenLigaDBApi::getLeagueSeason().
     *
     * ## OPTIONS
     *
     * <shortcut>
     * : League shortcut, e.g. bl1
     *
     * <season>
     * : Season, e.g. 2026
     *
     * [--format=<format>]
     * : Output format: table, json, csv, yaml. Default: table
     *
     * ## EXAMPLES
     *
     *     wp soccr league bl2 2026
     */
    public function league(array $args, array $assocArgs): void
    {
        [$shortcut, $season] = $args;

        try {
            $league = OpenLigaDBApi::getLeagueSeason($shortcut, (int) $season);
        } catch (Exception $e) {
            WP_CLI::error($e->getMessage());
        }

        \WP_CLI\Utils\format_items(
            $assocArgs['format'] ?? 'table',
            [$league->jsonSerialize()],
            self::LEAGUE_FIELDS,
        );
    }
}
