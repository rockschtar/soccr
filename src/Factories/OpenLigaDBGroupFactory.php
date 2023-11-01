<?php

namespace Rockschtar\WordPress\Soccr\Factories;

use Rockschtar\WordPress\Soccr\Models\OpenLigaDBGroup;

class OpenLigaDBGroupFactory
{
    public static function createFromJSON(\stdClass $group): OpenLigaDBGroup
    {
        return new OpenLigaDBGroup(
            $group->groupName,
            $group->groupOrderID,
            $group->groupID,
        );
    }
}
