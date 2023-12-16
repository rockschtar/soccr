<?php

namespace Rockschtar\WordPress\Soccr\Blocks;

use Exception;
use Rockschtar\WordPress\Soccr\Api\OpenLigaDBApi;
use Rockschtar\WordPress\Soccr\Utils\DateFormat;

class GroupMatchesBlock extends Block
{
    protected function render(array $attributes, string $content = ''): string
    {
        global $post;

        $defaultAttributes = [
            'leagueShortcut' => 'bl1',
            'leagueSeason' => 2021,
            'groupOrderId' => 1,
            'defaultCurrentGroup' => true,
            'pagination' => false,
            'align' => 'center',
            'blockId' => null,
        ];

        $parsedAttributes = wp_parse_args($attributes, $defaultAttributes);

        $cssClasses = $this->blockClasses($parsedAttributes);
        $leagueShortcut = $parsedAttributes['leagueShortcut'];
        $leagueSeason = $parsedAttributes['leagueSeason'];
        $groupOrderId = $parsedAttributes['groupOrderId'];
        $pagination = $parsedAttributes['pagination'];
        $defaultCurrentGroup = $parsedAttributes['defaultCurrentGroup'];
        $blockId = $parsedAttributes['blockId'];

        try {
            if ($defaultCurrentGroup) {
                $openLigaDBCurrentGroup = OpenLigaDBApi::getCurrentGroup(
                    $leagueShortcut,
                );
                $openLigaDBCurrentLeagueSeason = OpenLigaDBApi::getCurrentLeagueSeason(
                    $leagueShortcut,
                );
                $leagueSeason = $openLigaDBCurrentLeagueSeason->getLeagueSeason();
                $groupOrderId = $openLigaDBCurrentGroup->getGroupOrderId();
            }

            if ($pagination) {
                $blockIdInput = filter_input(
                    INPUT_GET,
                    'oldb-block-id',
                    FILTER_UNSAFE_RAW,
                );

                $blockIdInput = $blockIdInput !== null ? html_entity_decode($blockIdInput) : $blockIdInput;

                if ($blockIdInput === $blockId) {
                    $groupOrderIdInput = filter_input(
                        INPUT_GET,
                        'oldb-group-order-id',
                        FILTER_SANITIZE_NUMBER_INT,
                    );

                    if ($groupOrderIdInput) {
                        $groupOrderId = $groupOrderIdInput;
                    }
                }
            }

            $openLigaDBGroupMatches = OpenLigaDBApi::getGroupMatches(
                $leagueShortcut,
                $leagueSeason,
                $groupOrderId,
            );
        } catch (Exception $e) {
            do_action('openligadb_exception', $e);

            if (defined('WP_DEBUG') && true === WP_DEBUG) {
                return $e->getMessage();
            }

            if ($e->getCode() === 404) {
                return '<p>' .
                    __(
                        'Fehler, Spieltag, Liga oder Saison nicht gefunden',
                        'soccr',
                    ) .
                    '</p>';
            }

            return '';
        }

        $paginationUrl = get_permalink($post);
        $paginationPreviousHref = '';
        $paginationNextHref = '#';

        if ($pagination) {
            if ($openLigaDBGroupMatches->getPreviousGroup() !== null) {
                $paginationPreviousUrl = add_query_arg(
                    [
                        'oldb-group-order-id' => $openLigaDBGroupMatches
                            ->getPreviousGroup()
                            ->getGroupOrderId(),
                        'oldb-block-id' => $blockId,
                    ],
                    $paginationUrl,
                );

                $paginationPreviousHref =
                    '<a href="' .
                    $paginationPreviousUrl .
                    '">' .
                    __('Vorheriger Spieltag', 'soccr') .
                    '</a>';
            }

            if ($openLigaDBGroupMatches->getNextGroup() !== null) {
                $paginationNextUrl = add_query_arg(
                    [
                        'oldb-group-order-id' => $openLigaDBGroupMatches
                            ->getNextGroup()
                            ->getGroupOrderId(),
                        'oldb-block-id' => $blockId,
                    ],
                    $paginationUrl,
                );

                $paginationNextHref =
                    '<a href="' .
                    $paginationNextUrl .
                    '">' .
                    __('Nächster Spieltag', 'soccr') .
                    '</a>';
            }
        }

        $leagueSeasonDisplay = $openLigaDBGroupMatches->getLeagueSeasonDisplay();
        $group = $openLigaDBGroupMatches->getGroup();
        $groupName = $openLigaDBGroupMatches->getGroup()->getGroupName();

        /* translators: %1$s is the group name, %2$s is the league season */
        $headline = sprintf(__('%1$s | Saison %2$s', 'soccr'), $groupName, $leagueSeasonDisplay);

        $headline = apply_filters(
            'soccr_group_matchtes_headline',
            $headline,
            $group,
        );

        $html = <<<HTML
            $content
            <div class='$cssClasses'>
                <table>
                    <tr>
                        <td colspan="3">
                            <h1>$headline</h1>
                        </td>
                    <tr>
        HTML;

        $currentMatchTimestamp = null;


        foreach ($openLigaDBGroupMatches->getMatches() as $match) {
            if (
                $currentMatchTimestamp !== $match->getDateTime()->getTimestamp()
            ) {
                $currentMatchTimestamp = $match->getDateTime()->getTimestamp();
                $matchDateTimeString = DateFormat::toWordPress($match->getDateTime());
                $html .= <<<HTML
                    <tr>
                        <td colspan='3' class="{$this->blockClass('datetime')}">$matchDateTimeString</td>
                    </tr>
                HTML;
            }

            $html .= <<<HTML
                <tr class="{$this->blockClass('row')}">
                    <td class='{$this->blockClass('team-home')}'>
                        <span class="{$this->blockClass('team-name')}">{$match->getTeam1()->getTeamName()}</span>
                        <span class="{$this->blockClass('team-shortname')}">{$match->getTeam1()->getShortName()}</span>                     
                    </td>
                    <td class="{$this->blockClass('result')}">{$match->getResultByType(2,)}</td>
                    <td class="{$this->blockClass('team-away')}">
                        <span class="{$this->blockClass('team-name')}">{$match->getTeam2()->getTeamName()}</span>
                        <span class="{$this->blockClass('team-shortname')}">{$match->getTeam2()->getShortName()}</span>
                    </td>
                </tr>
            HTML;

        }

        if ($parsedAttributes['pagination']) {
            $html .= <<<HTML
                <tr>
                    <td colspan='2' class='{$this->blockClass('pagination-left')}'>$paginationPreviousHref</td>
                    <td class='{$this->blockClass('pagination-right')}'>$paginationNextHref</td>
                </tr>
            HTML;

        }

        $html .= '</table></div>';

        return apply_filters(
            'soccr_group_matches_html',
            $html,
            $openLigaDBGroupMatches,
        );
    }

    public function blockDirectory(): string
    {
        return '/dist/OpenLigaDBGroupMatches';
    }
}
