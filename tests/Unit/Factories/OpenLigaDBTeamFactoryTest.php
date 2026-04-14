<?php

namespace Rockschtar\WordPress\Soccr\Tests\Unit\Factories;

use Rockschtar\WordPress\Soccr\Factories\OpenLigaDBTeamFactory;
use Rockschtar\WordPress\Soccr\Tests\Unit\UnitTestCase;

class OpenLigaDBTeamFactoryTest extends UnitTestCase
{
    public function test_creates_team_from_json(): void
    {
        $json = (object) [
            'teamId'        => 40,
            'teamName'      => 'Bayern München',
            'shortName'     => 'Bayern',
            'teamIconUrl'   => 'https://example.com/icon.png',
            'teamGroupName' => null,
        ];

        $team = OpenLigaDBTeamFactory::createFromJSON($json);

        $this->assertSame(40, $team->getTeamId());
        $this->assertSame('Bayern München', $team->getTeamName());
        $this->assertSame('Bayern', $team->getShortName());
        $this->assertSame('https://example.com/icon.png', $team->getIconUrl());
        $this->assertNull($team->getTeamGroupName());
    }

    public function test_handles_null_optional_fields(): void
    {
        $json = (object) [
            'teamId'        => 7,
            'teamName'      => 'Borussia Dortmund',
            'shortName'     => null,
            'teamIconUrl'   => null,
            'teamGroupName' => null,
        ];

        $team = OpenLigaDBTeamFactory::createFromJSON($json);

        $this->assertNull($team->getShortName());
        $this->assertNull($team->getIconUrl());
    }
}
