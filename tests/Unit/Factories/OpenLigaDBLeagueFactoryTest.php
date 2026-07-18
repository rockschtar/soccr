<?php

namespace Rockschtar\Soccr\Tests\Unit\Factories;

use Rockschtar\Soccr\Factories\OpenLigaDBLeagueFactory;
use Rockschtar\Soccr\Tests\Unit\UnitTestCase;

class OpenLigaDBLeagueFactoryTest extends UnitTestCase
{
    public function test_creates_league_from_json(): void
    {
        $json = (object) [
            'leagueId'       => 1,
            'leagueName'     => '1. Bundesliga',
            'leagueShortcut' => 'bl1',
            'leagueSeason'   => '2024',
            'sport'          => (object) ['sportId' => '1'],
        ];

        $league = OpenLigaDBLeagueFactory::createFromJSON($json);

        $this->assertSame(1, $league->getLeagueId());
        $this->assertSame('1. Bundesliga', $league->getLeagueName());
        $this->assertSame('bl1', $league->getLeagueShortcut());
        $this->assertSame(2024, $league->getLeagueSeason());
        $this->assertSame(1, $league->getSportId());
    }

    public function test_shortens_bundesliga_name_with_eszett(): void
    {
        $json = (object) [
            'leagueId'       => 3,
            'leagueName'     => '1. Fußball-Bundesliga 2024/2025',
            'leagueShortcut' => 'bl1',
            'leagueSeason'   => '2024',
            'sport'          => (object) ['sportId' => '1'],
        ];

        $league = OpenLigaDBLeagueFactory::createFromJSON($json);

        $this->assertSame('1. Bundesliga 2024/2025', $league->getLeagueNameShort());
    }

    public function test_shortens_bundesliga_name_with_double_s(): void
    {
        $json = (object) [
            'leagueId'       => 4,
            'leagueName'     => '1. Fussball-Bundesliga 2024/2025',
            'leagueShortcut' => 'bl1',
            'leagueSeason'   => '2024',
            'sport'          => (object) ['sportId' => '1'],
        ];

        $league = OpenLigaDBLeagueFactory::createFromJSON($json);

        $this->assertSame('1. Bundesliga 2024/2025', $league->getLeagueNameShort());
    }

    public function test_casts_season_and_sport_id_to_int(): void
    {
        $json = (object) [
            'leagueId'       => 2,
            'leagueName'     => '2. Bundesliga',
            'leagueShortcut' => 'bl2',
            'leagueSeason'   => '2023',
            'sport'          => (object) ['sportId' => '1'],
        ];

        $league = OpenLigaDBLeagueFactory::createFromJSON($json);

        $this->assertIsInt($league->getLeagueSeason());
        $this->assertIsInt($league->getSportId());
    }
}
