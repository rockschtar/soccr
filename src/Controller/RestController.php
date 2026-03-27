<?php

namespace Rockschtar\WordPress\Soccr\Controller;

use Rockschtar\WordPress\Soccr\Api\OpenLigaDBApi;
use Rockschtar\WordPress\Soccr\Models\OpenLigaDBLeagueQuery;
use Rockschtar\WordPress\Soccr\Traits\Singelton;

class RestController
{
    use Singelton;

    private function __construct()
    {
        add_action('rest_api_init', $this->restGetLeagues(...));
        add_action('rest_api_init', $this->restGetAvailableTeams(...));
    }

    private function restGetLeagues(): void
    {
        register_rest_route('openligadb/v1', '/leagues', [
            'methods' => 'GET',
            'callback' => static function (\WP_REST_Request $request) {
                $response = new \WP_REST_Response();

                $allowedShortcuts = apply_filters(
                    'soccr_allowed_league_shortcuts',
                    ['bl1', 'bl2', 'bl3']
                );

                $leagueQuery = new OpenLigaDBLeagueQuery();
                $leagueQuery->setLeagueShortcuts($allowedShortcuts);
                $leagueQuery->setLeagueSeasonGreaterThan((int) $request->get_param('minSeason'));

                $includeShortcut = $request->get_param('includeShortcut');
                $includeSeason = $request->get_param('includeSeason');

                if ($includeShortcut !== null && $includeSeason !== null) {
                    $leagueQuery->setIncludeLeagueShortcut($includeShortcut);
                    $leagueQuery->setIncludeLeagueSeason((int) $includeSeason);
                }

                $leagues = OpenLigaDBApi::queryLeagues($leagueQuery);
                $response->set_data($leagues);
                return $response;
            },
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            },
            'args' => [
                'minSeason' => [
                    'required' => false,
                    'default' => (int) date('Y') - 3,
                    'description' => 'Only return leagues from this season onwards',
                    'type' => 'integer',
                    'sanitize_callback' => static function ($value) {
                        return (int) $value;
                    },
                ],
                'includeShortcut' => [
                    'required' => false,
                    'description' => 'Always include this league shortcut regardless of minSeason',
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'includeSeason' => [
                    'required' => false,
                    'description' => 'Season of the league to always include',
                    'type' => 'integer',
                    'sanitize_callback' => static function ($value) {
                        return (int) $value;
                    },
                ],
            ],
        ]);
    }

    private function restGetAvailableTeams(): void
    {
        register_rest_route('openligadb/v1', '/teams', [
            'methods' => 'GET',
            'callback' => static function (\WP_REST_Request $request) {
                $response = new \WP_REST_Response();

                $leagueShortcut = $request->get_param('leagueShortcut');
                $leagueSeason = $request->get_param('leagueSeason');

                $teams = OpenLigaDBApi::getAvailableTeams(
                    $leagueShortcut,
                    $leagueSeason,
                );
                $response->set_data($teams);
                return $response;
            },
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            },
            'args' => [
                'leagueShortcut' => [
                    'required' => true,
                    'description' => 'OpenLigaDB League Shortcut',
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'leagueSeason' => [
                    'required' => true,
                    'description' => 'OpenLigaDB League Season',
                    'type' => 'integer',
                    'sanitize_callback' => static function ($value) {
                        return (int)$value;
                    },
                ],
            ],
        ]);
    }
}
