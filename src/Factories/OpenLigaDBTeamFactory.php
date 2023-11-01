<?php

namespace Rockschtar\WordPress\Soccr\Factories;

use Rockschtar\WordPress\Soccr\Models\OpenLigaDBTeam;

class OpenLigaDBTeamFactory
{
    public static function createFromJSON(\stdClass $team): OpenLigaDBTeam
    {
        $openLigaDBTeam = new OpenLigaDBTeam();
        $openLigaDBTeam->setTeamId($team->teamId);
        $openLigaDBTeam->setTeamName($team->teamName);
        $openLigaDBTeam->setShortName($team->shortName);
        $openLigaDBTeam->setIconUrl($team->teamIconUrl);
        $openLigaDBTeam->setTeamGroupName($team->teamGroupName);

        return $openLigaDBTeam;
    }
}
