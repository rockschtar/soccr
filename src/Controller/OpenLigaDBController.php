<?php

namespace Rockschtar\WordPress\Soccr\Controller;

use Rockschtar\WordPress\Soccr\Traits\Singelton;

class OpenLigaDBController
{
    use Singelton;

    private function __construct()
    {
        add_filter('openligadb-league-season-display', $this->leagueSeasonDisplay(...));
    }

    private function leagueSeasonDisplay(int $leagueSeason): string
    {
        return $leagueSeason . '/' . ($leagueSeason + 1);
    }
}
