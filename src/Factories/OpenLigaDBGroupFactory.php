<?php

namespace ClubfansUnited\Factories;

use Aws\S3\Enum\Group;
use ClubfansUnited\Models\OpenLigaDBGroup;

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
