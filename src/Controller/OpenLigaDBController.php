<?php

namespace Rockschtar\Soccr\Controller;

use Rockschtar\Soccr\Traits\Singelton;

class OpenLigaDBController
{
    use Singelton;

    private function __construct()
    {
        add_filter('soccr_league_season_display', $this->leagueSeasonDisplay(...));
    }

    private function leagueSeasonDisplay(int $leagueSeason): string
    {
        return $leagueSeason . '/' . ($leagueSeason + 1);
    }
}
