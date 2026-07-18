<?php

namespace Rockschtar\WordPress\Soccr\Controller;

use Rockschtar\WordPress\Soccr\Api\OpenLigaDBApi;
use Rockschtar\WordPress\Soccr\Models\OpenLigaDBLeagueQuery;
use Rockschtar\WordPress\Soccr\Traits\Singelton;

class RestController
{
    use Singelton;

    private const ICON_ALLOWED_HOSTS = [
        'openligadb.de',
        'upload.wikimedia.org',
    ];

    private const ICON_ALLOWED_TYPES = [
        'image/png',
        'image/jpeg',
        'image/gif',
        'image/webp',
    ];

    private function __construct()
    {
        add_action('rest_api_init', $this->restGetLeagues(...));
        add_action('rest_api_init', $this->restGetAvailableTeams(...));
        add_action('rest_api_init', $this->restGetTeamIcon(...));
    }

    private function restGetLeagues(): void
    {
        register_rest_route('openligadb/v1', '/leagues', [
            'methods' => 'GET',
            'callback' => static function (\WP_REST_Request $request) {
                $response = new \WP_REST_Response();

                $leagueQuery = new OpenLigaDBLeagueQuery();
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

    private function restGetTeamIcon(): void
    {
        register_rest_route('openligadb/v1', '/team-icon', [
            'methods' => 'GET',
            'callback' => static function (\WP_REST_Request $request) {
                $url = $request->get_param('url');

                $host = parse_url($url, PHP_URL_HOST);
                $allowed = array_filter(
                    self::ICON_ALLOWED_HOSTS,
                    static fn(string $allowed) => $host === $allowed || str_ends_with($host, '.' . $allowed),
                );

                if (empty($allowed)) {
                    return new \WP_REST_Response(['error' => 'URL not allowed'], 403);
                }

                $cacheKey = 'soccr-team-icon-' . md5($url);
                $cached = get_transient($cacheKey);

                if ($cached !== false) {
                    header('Content-Type: ' . $cached['type']);
                    header('X-Content-Type-Options: nosniff');
                    header('Cache-Control: public, max-age=86400');
                    echo base64_decode($cached['data']);
                    exit;
                }

                $response = wp_remote_get($url, ['timeout' => 10]);

                if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
                    return new \WP_REST_Response(['error' => 'Could not fetch icon'], 502);
                }

                $body = wp_remote_retrieve_body($response);
                $contentType = wp_remote_retrieve_header($response, 'content-type') ?: 'image/png';
                $contentType = strtolower(trim(explode(';', $contentType)[0]));

                if (!in_array($contentType, self::ICON_ALLOWED_TYPES, true)) {
                    return new \WP_REST_Response(['error' => 'Unsupported image type'], 415);
                }

                set_transient($cacheKey, [
                    'type' => $contentType,
                    'data' => base64_encode($body),
                ], DAY_IN_SECONDS);

                header('Content-Type: ' . $contentType);
                header('X-Content-Type-Options: nosniff');
                header('Cache-Control: public, max-age=86400');
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo $body;
                exit;
            },
            'permission_callback' => '__return_true',
            'args' => [
                'url' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'esc_url_raw',
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
                        return (int) $value;
                    },
                ],
            ],
        ]);
    }
}
