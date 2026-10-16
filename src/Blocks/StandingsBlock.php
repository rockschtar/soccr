<?php

namespace Rockschtar\Soccr\Blocks;

use Exception;
use Rockschtar\Soccr\Api\OpenLigaDBApi;

class StandingsBlock extends Block
{
    protected function render(array $attributes, string $content = ''): string
    {
        $defaultAttributes = [
            'leagueShortcut'  => '',
            'leagueSeason'    => 0,
            'align'           => 'left',
            'title'           => '',
            'showTitle'       => true,
            'defaultCurrentSeason' => true,
            'highlightTeamId' => 0,
        ];

        $parsedAttributes = wp_parse_args($attributes, $defaultAttributes);
        $leagueShortcut = $parsedAttributes['leagueShortcut'];
        $leagueSeason = $parsedAttributes['leagueSeason'];
        $title = $parsedAttributes['title'];
        $showTitle = $parsedAttributes['showTitle'];
        $defaultCurrentSeason = $parsedAttributes['defaultCurrentSeason'];
        $highlightTeamId = (int) ($parsedAttributes['highlightTeamId'] ?? 0);

        try {

            if ($defaultCurrentSeason) {
                $openLigaDBCurrentLeagueSeason = OpenLigaDBApi::getCurrentLeagueSeason($leagueShortcut);
                $leagueSeason = $openLigaDBCurrentLeagueSeason->getLeagueSeason();
            }

            $openLigaDBStandings = OpenLigaDBApi::getStandings(
                $leagueShortcut,
                $leagueSeason,
            );
        } catch (Exception $e) {
            do_action('soccr_exception', $e);

            if (defined('WP_DEBUG') && true === WP_DEBUG) {
                return esc_html($e->getMessage());
            }

            if ($e->getCode() === 404) {
                return '<p>'
                    . esc_html__(
                        'Error: matchday, league or season not found',
                        'soccr',
                    )
                    . '</p>';
            }

            return '';
        }

        $additionalClasses = [];
        $additionalClasses[] = $openLigaDBStandings->getLeague()->getLeagueShortcut();
        $additionalClasses[]
            = $openLigaDBStandings->getLeague()->getLeagueShortcut()
            . '-'
            . $openLigaDBStandings->getLeague()->getLeagueSeason();

        $cssClasses = $this->blockClasses($parsedAttributes, $additionalClasses);

        $isEditorPreview = defined('REST_REQUEST') && REST_REQUEST;

        $headlineHTML = '';
        if (!$isEditorPreview && $showTitle && $title !== '') {
            $headlineHTML = strtr(
                '<div class="{headerClass}"><h2 class="{headlineClass}">{title}</h2></div>',
                [
                    '{headerClass}' => esc_attr($this->blockClass('header')),
                    '{headlineClass}' => esc_attr($this->blockClass('headline')),
                    '{title}' => esc_html($title),
                ],
            );
        }

        $headerColumns = [
            'position' => '',
            'team' => '',
            'matches' => _x('P', 'standings table header: matches played', 'soccr'),
            'wins' => _x('W', 'standings table header: wins', 'soccr'),
            'draws' => _x('D', 'standings table header: draws', 'soccr'),
            'looses' => _x('L', 'standings table header: losses', 'soccr'),
            'goals' => __('Goals', 'soccr'),
            'goals-difference' => _x('GD', 'standings table header: goal difference', 'soccr'),
            'points' => _x('Pts', 'standings table header: points', 'soccr'),
        ];

        $headerCells = '';
        foreach ($headerColumns as $column => $label) {
            $headerCells .= strtr(
                '<div class="{thClass} {columnClass}">{label}</div>',
                [
                    '{thClass}' => esc_attr($this->blockClass('th')),
                    '{columnClass}' => esc_attr($this->blockClass($column)),
                    '{label}' => esc_html($label),
                ],
            );
        }

        $standingsHTMLHeader = strtr(
            '<div class="{theadClass}">'
            . '<div class="{trClass}">{headline}</div>'
            . '<div class="{trClass}">{cells}</div>'
            . '</div>',
            [
                '{theadClass}' => esc_attr($this->blockClass('thead')),
                '{trClass}' => esc_attr($this->blockClass('tr')),
                '{headline}' => $headlineHTML,
                '{cells}' => $headerCells,
            ],
        );

        $standingsHTMLBody = '';

        $standingsPosition = 0;

        foreach ($openLigaDBStandings->getStandings() as $openLigaDBStanding) {
            $standingsPosition++;

            $team = $openLigaDBStanding->getTeam();
            $teamId = $team->getTeamId();

            $rowClasses = $this->blockClass('row') . ' ' . $this->blockClass('team-' . $teamId);
            if ($highlightTeamId > 0 && $teamId === $highlightTeamId) {
                $rowClasses .= ' ' . $this->blockClass('row--highlighted');
            }

            $standingsHTMLBody .= strtr(
                '<div class="{rowClasses}">'
                . '<div class="{positionClass} {positionClass}-{position}">{position}</div>'
                . '<div class="{teamClass}">'
                . '<span class="{teamNameClass}">{teamName}</span>'
                . '<span class="{teamShortNameClass}">{teamShortName}</span>'
                . '</div>'
                . '<div class="{matchesClass}">{matches}</div>'
                . '<div class="{winsClass}">{wins}</div>'
                . '<div class="{drawsClass}">{draws}</div>'
                . '<div class="{loosesClass}">{looses}</div>'
                . '<div class="{goalsClass}">{goalsScored}:{goalsConceded}</div>'
                . '<div class="{goalsDifferenceClass}">{goalDifference}</div>'
                . '<div class="{pointsClass}">{points}</div>'
                . '</div>',
                [
                    '{rowClasses}' => esc_attr($rowClasses),
                    '{positionClass}' => esc_attr($this->blockClass('position')),
                    '{position}' => esc_html((string) $standingsPosition),
                    '{teamClass}' => esc_attr($this->blockClass('team')),
                    '{teamNameClass}' => esc_attr($this->blockClass('team-name')),
                    '{teamShortNameClass}' => esc_attr($this->blockClass('team-shortname')),
                    '{teamName}' => esc_html($team->getTeamName()),
                    '{teamShortName}' => esc_html($team->getShortName()),
                    '{matchesClass}' => esc_attr($this->blockClass('matches')),
                    '{matches}' => esc_html((string) $openLigaDBStanding->getMatches()),
                    '{winsClass}' => esc_attr($this->blockClass('wins')),
                    '{wins}' => esc_html((string) $openLigaDBStanding->getWins()),
                    '{drawsClass}' => esc_attr($this->blockClass('draws')),
                    '{draws}' => esc_html((string) $openLigaDBStanding->getDraws()),
                    '{loosesClass}' => esc_attr($this->blockClass('looses')),
                    '{looses}' => esc_html((string) $openLigaDBStanding->getLooses()),
                    '{goalsClass}' => esc_attr($this->blockClass('goals')),
                    '{goalsScored}' => esc_html((string) $openLigaDBStanding->getGoalsScored()),
                    '{goalsConceded}' => esc_html((string) $openLigaDBStanding->getGoalsConceded()),
                    '{goalsDifferenceClass}' => esc_attr($this->blockClass('goals-difference')),
                    '{goalDifference}' => esc_html((string) $openLigaDBStanding->getGoalDifference()),
                    '{pointsClass}' => esc_attr($this->blockClass('points')),
                    '{points}' => esc_html((string) $openLigaDBStanding->getPoints()),
                ],
            );
        }

        $wrapperAttributes = get_block_wrapper_attributes([
            'class' => $cssClasses,
        ]);

        // $wrapperAttributes is escaped by get_block_wrapper_attributes(),
        // header, body and attribution are escaped where they are built.
        return strtr(
            '<div {wrapperAttributes}>'
            . '<div class="{contentClass}">'
            . '{header}'
            . '<div class="{tbodyClass}">{body}</div>'
            . '</div>'
            . '{attribution}'
            . '</div>',
            [
                '{wrapperAttributes}' => $wrapperAttributes,
                '{contentClass}' => esc_attr($this->blockClass('content')),
                '{header}' => $standingsHTMLHeader,
                '{tbodyClass}' => esc_attr($this->blockClass('tbody')),
                '{body}' => $standingsHTMLBody,
                '{attribution}' => $this->attributionHtml(),
            ],
        );
    }

    public function blockDirectory(): string
    {
        return '/dist/OpenLigaDBStandings';
    }
}
