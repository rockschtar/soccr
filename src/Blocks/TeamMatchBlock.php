<?php

namespace Rockschtar\Soccr\Blocks;

use Exception;
use Rockschtar\Soccr\Api\OpenLigaDBApi;
use Rockschtar\Soccr\Models\OpenLigaDBMatch;
use Rockschtar\Soccr\Models\OpenLigaDBMatchQuery;
use Rockschtar\Soccr\Models\OpenLigaDBTeam;
use Rockschtar\Soccr\Utils\DateFormat;

class TeamMatchBlock extends Block
{
    // Wikimedia thumbnails are only rendered at fixed bucket widths
    // (20/40/60/120/250/330/500/960, see https://w.wiki/GHai); 120px
    // covers the 48px display size on 2x screens.
    private const int WIKIMEDIA_THUMB_WIDTH = 120;

    protected function render(array $attributes, string $content = ''): string
    {
        $defaultAttributes = [
            'leagueShortcut' => '',
            'leagueSeason' => 0,
            'teamId' => 0,
            'displayMode' => 'current',
            'align' => 'center',
            'title' => '',
            'showTitle' => true,
            'showTeamIcons' => true,
        ];

        $parsedAttributes = wp_parse_args($attributes, $defaultAttributes);

        $leagueShortcut = $parsedAttributes['leagueShortcut'];
        $leagueSeason = (int) $parsedAttributes['leagueSeason'];
        $teamId = (int) $parsedAttributes['teamId'];
        $displayMode = $parsedAttributes['displayMode'];

        if (empty($leagueShortcut) || $leagueSeason === 0 || $teamId === 0) {
            return '';
        }

        try {
            $query = new OpenLigaDBMatchQuery();
            $query->addLeagueSeason($leagueShortcut, $leagueSeason);
            $query->setTeamId($teamId);

            $match = $this->getMatchByDisplayMode($query, $displayMode);

            if ($match === null) {
                return '<p>'
                    . esc_html__(
                        'No match found',
                        'soccr',
                    )
                    . '</p>';
            }
        } catch (Exception $e) {
            do_action('soccr_exception', $e);

            if (defined('WP_DEBUG') && true === WP_DEBUG) {
                return esc_html($e->getMessage());
            }

            if ($e->getCode() === 404) {
                return '<p>'
                    . esc_html__(
                        'Error: match, league or season not found',
                        'soccr',
                    )
                    . '</p>';
            }

            return '';
        }

        $matchDateTimeString = DateFormat::toWordPress($match->getDateTime());

        $result = $match->getResult();
        $resultDisplay = $result !== null
            ? $result
            : '-:-';

        $displayModeLabels = [
            'current' => __('Current match', 'soccr'),
            'next' => __('Next match', 'soccr'),
            'last' => __('Last match', 'soccr'),
        ];

        $title = $parsedAttributes['title'] !== ''
            ? $parsedAttributes['title']
            : ($displayModeLabels[$displayMode] ?? __('Current match', 'soccr'));
        $showTitle = $parsedAttributes['showTitle'];
        $showTeamIcons = (bool) $parsedAttributes['showTeamIcons'];
        $isEditorPreview = defined('REST_REQUEST') && REST_REQUEST;

        $headlineHTML = '';
        if (!$isEditorPreview && $showTitle && $title !== '') {
            $headlineHTML = strtr(
                '<div class="{headerClass}"><h4 class="{headlineClass}">{title}</h4></div>',
                [
                    '{headerClass}' => esc_attr($this->blockClass('header')),
                    '{headlineClass}' => esc_attr($this->blockClass('headline')),
                    '{title}' => esc_html($title),
                ],
            );
        }

        $wrapperAttributes = get_block_wrapper_attributes([
            'class' => $this->blockClasses($parsedAttributes),
        ]);

        // $content is the inner block markup, $wrapperAttributes is escaped
        // by get_block_wrapper_attributes(), icons and attribution are
        // escaped where they are built.
        $html = strtr(
            '{content}'
            . '<div {wrapperAttributes}>'
            . '{headline}'
            . '<div class="{headerClass}"><div class="{datetimeClass}">{datetime}</div></div>'
            . '<div class="{contentClass}">'
            . '<div class="{rowClass}">'
            . '<div class="{teamHomeClass}">'
            . '{homeIcon}'
            . '<span class="{teamNameClass}">{homeName}</span>'
            . '<span class="{teamShortNameClass}">{homeShortName}</span>'
            . '</div>'
            . '<div class="{resultClass}">{result}</div>'
            . '<div class="{teamAwayClass}">'
            . '{awayIcon}'
            . '<span class="{teamNameClass}">{awayName}</span>'
            . '<span class="{teamShortNameClass}">{awayShortName}</span>'
            . '</div>'
            . '</div>'
            . '</div>'
            . '{attribution}'
            . '</div>',
            [
                '{content}' => $content,
                '{wrapperAttributes}' => $wrapperAttributes,
                '{headline}' => $headlineHTML,
                '{headerClass}' => esc_attr($this->blockClass('header')),
                '{datetimeClass}' => esc_attr($this->blockClass('datetime')),
                '{datetime}' => esc_html($matchDateTimeString),
                '{contentClass}' => esc_attr($this->blockClass('content')),
                '{rowClass}' => esc_attr($this->blockClass('row')),
                '{teamHomeClass}' => esc_attr($this->blockClass('team-home')),
                '{teamAwayClass}' => esc_attr($this->blockClass('team-away')),
                '{teamNameClass}' => esc_attr($this->blockClass('team-name')),
                '{teamShortNameClass}' => esc_attr($this->blockClass('team-shortname')),
                '{resultClass}' => esc_attr($this->blockClass('result')),
                '{homeIcon}' => $this->teamIconHtml($match->getTeam1(), $showTeamIcons),
                '{homeName}' => esc_html($match->getTeam1()->getTeamName()),
                '{homeShortName}' => esc_html($match->getTeam1()->getShortName()),
                '{result}' => esc_html((string) $resultDisplay),
                '{awayIcon}' => $this->teamIconHtml($match->getTeam2(), $showTeamIcons),
                '{awayName}' => esc_html($match->getTeam2()->getTeamName()),
                '{awayShortName}' => esc_html($match->getTeam2()->getShortName()),
                '{attribution}' => $this->attributionHtml(),
            ],
        );

        return apply_filters(
            'soccr_team_match_html',
            $html,
            $match,
        );
    }

    private function getMatchByDisplayMode(
        OpenLigaDBMatchQuery $query,
        string $displayMode,
    ): ?OpenLigaDBMatch {
        return match ($displayMode) {
            'next' => OpenLigaDBApi::getNextMatchByTeamid($query),
            'last' => OpenLigaDBApi::getLastMatchByTeamId($query),
            'current' => $this->getCurrentMatch($query),
            default => $this->getCurrentMatch($query),
        };
    }

    private function teamIconHtml(OpenLigaDBTeam $team, bool $showTeamIcons): string
    {
        if (!$showTeamIcons || empty($team->getIconUrl())) {
            return '';
        }

        $iconUrl = $team->getIconUrl();
        $teamName = $team->getTeamName();

        $safeUrl = esc_url_raw($this->normalizeIconUrl($iconUrl));
        $proxyUrl = add_query_arg([
            'url' => rawurlencode($safeUrl),
            'sig' => wp_hash($safeUrl),
        ], rest_url('openligadb/v1/team-icon'));

        return strtr(
            '<img class="{class}" src="{src}" alt="{alt}" />',
            [
                '{class}' => esc_attr($this->blockClass('team-icon')),
                '{src}' => esc_url($proxyUrl),
                '{alt}' => esc_attr($teamName),
            ],
        );
    }

    /**
     * OpenLigaDB delivers some team icons as raw Wikimedia SVGs, which the
     * icon proxy rejects. Rewrite those to the pre-rendered PNG thumbnail.
     */
    private function normalizeIconUrl(string $iconUrl): string
    {
        $pattern = '#^(https://upload\.wikimedia\.org/wikipedia/[^/]+)/([0-9a-f])/([0-9a-f]{2})/([^/?\#]+\.svg)$#i';

        if (preg_match($pattern, $iconUrl, $matches) !== 1) {
            return $iconUrl;
        }

        return sprintf(
            '%s/thumb/%s/%s/%s/%dpx-%s.png',
            $matches[1],
            $matches[2],
            $matches[3],
            $matches[4],
            self::WIKIMEDIA_THUMB_WIDTH,
            $matches[4],
        );
    }

    private function getCurrentMatch(OpenLigaDBMatchQuery $query): ?OpenLigaDBMatch
    {
        $nextMatch = OpenLigaDBApi::getNextMatchByTeamid($query);

        if ($nextMatch !== null) {
            return $nextMatch;
        }

        return OpenLigaDBApi::getLastMatchByTeamId($query);
    }

    public function blockDirectory(): string
    {
        return '/dist/OpenLigaDBTeamMatch';
    }
}
