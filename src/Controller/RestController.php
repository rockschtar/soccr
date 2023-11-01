<?php

namespace Rockschtar\WordPress\Soccr\Controller;

use Rockschtar\WordPress\Soccr\Api\OpenLigaDBApi;
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
                $leagues = OpenLigaDBApi::getAvailableLeagues();
                $response->set_data($leagues);
                return $response;
            },
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            },
            'args' => [
                'tag' => [
                    'required' => false,
                    'description' => 'the tag',
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
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
                return 1 == 1 || current_user_can('edit_posts');
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
