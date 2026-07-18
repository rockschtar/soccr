<?php

namespace Rockschtar\Soccr\Tests\Unit\Factories;

use Brain\Monkey\Functions;
use Rockschtar\Soccr\Factories\OpenLigaDBMatchFactory;
use Rockschtar\Soccr\Tests\Unit\UnitTestCase;

class OpenLigaDBMatchFactoryTest extends UnitTestCase
{
    private function makeMatchJson(array $overrides = []): \stdClass
    {
        return (object) array_merge([
            'matchID'          => 123,
            'matchDateTimeUTC' => '2024-09-14T13:30:00+00:00',
            'leagueId'         => 1,
            'leagueSeason'     => 2024,
            'leagueShortcut'   => 'bl1',
            'matchIsFinished'  => true,
            'location'         => null,
            'numberOfViewers'  => null,
            'group'            => (object) [
                'groupName'    => 'Spieltag 1',
                'groupOrderID' => '1',
                'groupID'      => '100',
            ],
            'team1' => (object) [
                'teamId'        => 40,
                'teamName'      => 'Bayern München',
                'shortName'     => 'Bayern',
                'teamIconUrl'   => null,
                'teamGroupName' => null,
            ],
            'team2' => (object) [
                'teamId'        => 7,
                'teamName'      => 'Borussia Dortmund',
                'shortName'     => 'BVB',
                'teamIconUrl'   => null,
                'teamGroupName' => null,
            ],
            'matchResults' => [],
        ], $overrides);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Functions\when('wp_timezone')->justReturn(new \DateTimeZone('UTC'));
    }

    public function test_creates_match_from_json(): void
    {
        $match = OpenLigaDBMatchFactory::createFromJSON($this->makeMatchJson());

        $this->assertSame(123, $match->getMatchId());
        $this->assertTrue($match->isFinished());
        $this->assertSame('Bayern München', $match->getTeam1()->getTeamName());
        $this->assertSame('Borussia Dortmund', $match->getTeam2()->getTeamName());
        $this->assertSame('bl1', $match->getLeagueShortcut());
        $this->assertSame(2024, $match->getLeagueSeason());
    }

    public function test_parses_datetime_correctly(): void
    {
        $match = OpenLigaDBMatchFactory::createFromJSON($this->makeMatchJson());

        $this->assertSame('2024-09-14', $match->getDateTime()->format('Y-m-d'));
        $this->assertSame('13:30', $match->getDateTime()->format('H:i'));
    }

    public function test_sets_timezone_from_wp_timezone(): void
    {
        Functions\when('wp_timezone')->justReturn(new \DateTimeZone('Europe/Berlin'));

        $match = OpenLigaDBMatchFactory::createFromJSON($this->makeMatchJson());

        $this->assertSame('Europe/Berlin', $match->getDateTime()->getTimezone()->getName());
    }

    public function test_hydrates_match_results(): void
    {
        $json = $this->makeMatchJson([
            'matchResults' => [
                (object) [
                    'resultID'          => 1,
                    'resultName'        => 'Endergebnis',
                    'resultDescription' => null,
                    'resultOrderID'     => 1,
                    'resultTypeID'      => 2,
                    'pointsTeam1'       => 3,
                    'pointsTeam2'       => 1,
                ],
            ],
        ]);

        $match = OpenLigaDBMatchFactory::createFromJSON($json);

        $this->assertCount(1, $match->getResults());
        $this->assertSame('3:1', (string) $match->getResults()[0]);
    }

    public function test_handles_location(): void
    {
        $json = $this->makeMatchJson([
            'location' => (object) [
                'locationID'      => 5,
                'locationCity'    => 'München',
                'locationStadium' => 'Allianz Arena',
            ],
        ]);

        $match = OpenLigaDBMatchFactory::createFromJSON($json);

        $this->assertNotNull($match->getLocation());
        $this->assertSame('München', $match->getLocation()->getCity());
        $this->assertSame('Allianz Arena', $match->getLocation()->getName());
    }
}
