<?php

namespace Rockschtar\WordPress\Soccr\Factories;

use Rockschtar\WordPress\Soccr\Models\OpenligaDBLeague;

class OpenLigaDBLeagueFactory
{
    public static function createFromJSON(\stdClass $league): OpenligaDBLeague
    {
        $openLigaDBLeague = new OpenligaDBLeague();
        $openLigaDBLeague->setLeagueId($league->leagueId);
        $openLigaDBLeague->setLeagueName($league->leagueName);
        $openLigaDBLeague->setLeagueNameShort(self::createLeagueNameShort($league->leagueName));
        $openLigaDBLeague->setLeagueShortcut($league->leagueShortcut);
        $openLigaDBLeague->setLeagueSeason((int) $league->leagueSeason);
        $openLigaDBLeague->setSportId((int) $league->sport->sportId);

        return $openLigaDBLeague;
    }
    private static function createLeagueNameShort(string $leagueName): string
    {
        $leagueNameShort = preg_replace(
            '/^(\d+\.\s*)Fu(?:ß|ss)ball-Bundesliga(\s+\d{4}\/\d{4})$/u',
            '$1Bundesliga$2',
            $leagueName,
        );

        return $leagueNameShort ?? $leagueName;
    }
}
