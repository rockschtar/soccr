<?php

namespace Rockschtar\WordPress\Soccr\Controller;

use Rockschtar\WordPress\Soccr\Traits\Singelton;


class OpenLigaDBController
{
    use Singelton;

    private function __construct()
    {
        $this->addFilter(
            'openligadb-league-season-display',
            'leagueSeasonDisplay',
        );
    }

    private function leagueSeasonDisplay(int $leagueSeason): string
    {
        return $leagueSeason . '/' . ($leagueSeason + 1);
    }
}
