<?php

namespace ClubfansUnited\Factories;

use ClubfansUnited\Models\OpenligaDBLeague;

class OpenLigaDBLeagueFactory
{
    public static function createFromJSON(\stdClass $league): OpenligaDBLeague
    {
        $openLigaDBLeague = new OpenligaDBLeague();

        $openLigaDBLeague->setLeagueId($league->leagueId);
        $openLigaDBLeague->setLeagueName($league->leagueName);
        $openLigaDBLeague->setLeagueShortcut($league->leagueShortcut);
        $openLigaDBLeague->setLeagueSeason((int) $league->leagueSeason);

        return $openLigaDBLeague;
    }
}
