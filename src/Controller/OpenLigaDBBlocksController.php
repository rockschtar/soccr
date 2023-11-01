<?php

namespace ClubfansUnited\Controller;

use ClubfansUnited\Blocks\OpenLigaDBGroupMatchesBlock;
use ClubfansUnited\Blocks\OpenLigaDBStandingsBlock;
use ClubfansUnited\Blocks\PointPressureBlock;
use ClubfansUnited\Blocks\SocialIcons;
use ClubfansUnited\Manager\OpenLigaDBManager;
use Rockschtar\WordPress\Controller\HookController;

class OpenLigaDBBlocksController
{
    use \Singelton;

    private function __construct()
    {
        add_filter('block_categories_all', 'addBlockCategories', 10, 1);
        $this->addAction('rest_api_init', 'restGetLeagues');
        $this->addAction('rest_api_init', 'restGetAvailableTeams');
        OpenLigaDBGroupMatchesBlock::init();
        OpenLigaDBStandingsBlock::init();
    }

    private function addBlockCategories(array $categories): array
    {
        return array_merge($categories, [
            [
                'slug' => 'openligadb',
                'title' => __('OpenLigaDB', 'clubfans-united'),
            ],
        ]);
    }

    public function restGetLeagues(): void
    {
        register_rest_route('openligadb/v1', '/leagues', [
            'methods' => 'GET',
            'callback' => static function (\WP_REST_Request $request) {
                $response = new \WP_REST_Response();
                $leagues = OpenLigaDBManager::getAvailableLeagues();
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

    public function restGetAvailableTeams(): void
    {
        register_rest_route('openligadb/v1', '/teams', [
            'methods' => 'GET',
            'callback' => static function (\WP_REST_Request $request) {
                $response = new \WP_REST_Response();

                $leagueShortcut = $request->get_param('leagueShortcut');
                $leagueSeason = $request->get_param('leagueSeason');

                $teams = OpenLigaDBManager::getAvailableTeams(
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
                        return (int) $value;
                    },
                ],
            ],
        ]);
    }
}
