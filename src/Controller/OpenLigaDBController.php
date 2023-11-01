<?php

namespace ClubfansUnited\Controller;

use ClubfansUnited\Enum\Capabilities;
use ClubfansUnited\Manager\OpenLigaDBManager;
use Rockschtar\WordPress\Controller\HookController;
use WP_REST_Request;
use WP_REST_Response;

class OpenLigaDBController
{
    use HookController;

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
