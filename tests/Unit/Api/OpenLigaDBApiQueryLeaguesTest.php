<?php

namespace Rockschtar\WordPress\Soccr\Tests\Unit\Api;

use Brain\Monkey\Functions;
use Rockschtar\WordPress\Soccr\Api\OpenLigaDBApi;
use Rockschtar\WordPress\Soccr\Models\OpenligaDBLeague;
use Rockschtar\WordPress\Soccr\Models\OpenLigaDBLeagueQuery;
use Rockschtar\WordPress\Soccr\Tests\Unit\UnitTestCase;

class OpenLigaDBApiQueryLeaguesTest extends UnitTestCase
{
    /** @var OpenligaDBLeague[] */
    private array $availableLeagues;

    protected function setUp(): void
    {
        parent::setUp();

        $this->availableLeagues = [
            $this->makeLeague(1, 'bl1', '1. Bundesliga', 2024, 1),
            $this->makeLeague(2, 'bl2', '2. Bundesliga', 2024, 1),
            $this->makeLeague(3, 'bl3', '3. Liga', 2024, 1),
            $this->makeLeague(4, 'bl1', '1. Bundesliga', 2023, 1),
            $this->makeLeague(5, 'dfb', 'DFB Pokal', 2024, 1),
            $this->makeLeague(6, 'tennis', 'ATP Tour', 2024, 2), // non-soccer sport
            $this->makeLeague(7, 'oberliga', 'Oberliga', 2024, 1), // soccer but not in defaults
        ];

        Functions\when('get_transient')->alias(function ($key) {
            if ($key === 'soccr-openligadb-available-leagues') {
                return $this->availableLeagues;
            }
            return false;
        });

        Functions\when('apply_filters')->alias(
            static fn($hook, $value) => $value
        );
    }

    private function makeLeague(
        int $id,
        string $shortcut,
        string $name,
        int $season,
        int $sportId
    ): OpenligaDBLeague {
        $league = new OpenligaDBLeague();
        $league->setLeagueId($id);
        $league->setLeagueShortcut($shortcut);
        $league->setLeagueName($name);
        $league->setLeagueSeason($season);
        $league->setSportId($sportId);
        return $league;
    }

    public function test_filters_to_default_shortcuts_when_none_set(): void
    {
        $result = OpenLigaDBApi::queryLeagues(new OpenLigaDBLeagueQuery());

        $shortcuts = array_map(fn($l) => $l->getLeagueShortcut(), $result);

        $this->assertContains('bl1', $shortcuts);
        $this->assertContains('bl2', $shortcuts);
        $this->assertContains('bl3', $shortcuts);
        $this->assertContains('dfb', $shortcuts);
        $this->assertNotContains('oberliga', $shortcuts);
    }

    public function test_filter_hook_customizes_shortcuts(): void
    {
        Functions\when('apply_filters')->alias(
            static fn($hook, $value) => $hook === 'soccr_league_shortcuts' ? ['bl1'] : $value
        );

        $result = OpenLigaDBApi::queryLeagues(new OpenLigaDBLeagueQuery());

        $shortcuts = array_map(fn($l) => $l->getLeagueShortcut(), $result);

        $this->assertContains('bl1', $shortcuts);
        $this->assertNotContains('bl2', $shortcuts);
        $this->assertNotContains('bl3', $shortcuts);
    }

    public function test_query_shortcuts_narrow_within_allowed_shortcuts(): void
    {
        $query = new OpenLigaDBLeagueQuery();
        $query->setLeagueShortcuts(['dfb']);

        $result = OpenLigaDBApi::queryLeagues($query);

        $shortcuts = array_map(fn($l) => $l->getLeagueShortcut(), $result);

        $this->assertContains('dfb', $shortcuts);
        $this->assertNotContains('bl1', $shortcuts);
    }

    public function test_query_shortcuts_cannot_bypass_allowed_shortcuts(): void
    {
        $query = new OpenLigaDBLeagueQuery();
        $query->setLeagueShortcuts(['oberliga']);

        $result = OpenLigaDBApi::queryLeagues($query);

        $this->assertSame([], $result);
    }

    public function test_excludes_non_soccer_sports(): void
    {
        Functions\when('apply_filters')->alias(
            static fn($hook, $value) => $hook === 'soccr_league_shortcuts' ? ['bl1', 'tennis'] : $value
        );

        $result = OpenLigaDBApi::queryLeagues(new OpenLigaDBLeagueQuery());

        $shortcuts = array_map(fn($l) => $l->getLeagueShortcut(), $result);

        $this->assertContains('bl1', $shortcuts);
        $this->assertNotContains('tennis', $shortcuts);
    }

    public function test_filters_by_min_season(): void
    {
        $query = new OpenLigaDBLeagueQuery();
        $query->setLeagueSeasonGreaterThan(2023);

        $result = OpenLigaDBApi::queryLeagues($query);

        foreach ($result as $league) {
            $this->assertGreaterThan(2023, $league->getLeagueSeason());
        }
    }

    public function test_results_are_sorted_by_name(): void
    {
        $result = OpenLigaDBApi::queryLeagues(new OpenLigaDBLeagueQuery());
        $names = array_map(fn($l) => $l->getLeagueName(), $result);

        $sorted = $names;
        sort($sorted);

        $this->assertSame($sorted, array_values($names));
    }

    public function test_include_shortcut_adds_league_not_in_default_list(): void
    {
        $query = new OpenLigaDBLeagueQuery();
        $query->setIncludeLeagueShortcut('dfb');
        $query->setIncludeLeagueSeason(2024);

        $result = OpenLigaDBApi::queryLeagues($query);

        $shortcuts = array_map(fn($l) => $l->getLeagueShortcut(), $result);

        $this->assertContains('dfb', $shortcuts);
    }
}
