<?php

namespace Rockschtar\WordPress\Soccr\Blocks;

use Exception;
use Rockschtar\WordPress\Soccr\Api\OpenLigaDBApi;
use Rockschtar\WordPress\Soccr\Utils\DateFormat;

class GroupMatchesBlock extends Block
{
    protected function render(array $attributes, string $content = ''): string
    {
        $defaultAttributes = [
            'leagueShortcut' => 'bl1',
            'leagueSeason' => 2021,
            'groupOrderId' => 1,
            'defaultCurrentGroup' => true,
            'pagination' => false,
            'align' => 'center',
            'blockId' => null,
            'title' => '',
            'showTitle' => true,
        ];

        $parsedAttributes = wp_parse_args($attributes, $defaultAttributes);

        $leagueShortcut = $parsedAttributes['leagueShortcut'];
        $leagueSeason = $parsedAttributes['leagueSeason'];
        $groupOrderId = $parsedAttributes['groupOrderId'];
        $pagination = $parsedAttributes['pagination'];
        $defaultCurrentGroup = $parsedAttributes['defaultCurrentGroup'];
        $blockId = $parsedAttributes['blockId'];

        try {
            if ($defaultCurrentGroup) {

                $openLigaDBCurrentGroup = OpenLigaDBApi::getCurrentGroup($leagueShortcut);
                $openLigaDBLeagueSeason  = OpenLigaDBApi::getCurrentLeagueSeason($leagueShortcut);

                $leagueSeason = $openLigaDBLeagueSeason->getLeagueSeason();
                $groupOrderId = $openLigaDBCurrentGroup->getGroupOrderId();
            } else {
                $openLigaDBLeagueSeason  = OpenLigaDBApi::getLeagueSeason($leagueShortcut, $leagueSeason);
            }

            $leagueName = $openLigaDBLeagueSeason->getLeagueName();

            if ($pagination) {
                $blockIdInput = filter_input(
                    INPUT_GET,
                    'oldb-block-id',
                    FILTER_SANITIZE_SPECIAL_CHARS,
                );

                $blockIdInput = $blockIdInput !== null ? sanitize_text_field($blockIdInput) : $blockIdInput;

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
                return '<p>'
                    . __(
                        'Fehler, Spieltag, Liga oder Saison nicht gefunden',
                        'soccr',
                    )
                    . '</p>';
            }

            return '';
        }

        global $post;
        $paginationUrl = $post ? get_permalink($post) : home_url(add_query_arg([]));

        $paginationPreviousHref = '';
        $paginationNextHref = '';

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

                $paginationPreviousHref
                    = '<a href="'
                    . esc_url($paginationPreviousUrl)
                    . '">'
                    . __('Vorheriger Spieltag', 'soccr')
                    . '</a>';
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

                $paginationNextHref
                    = '<a href="'
                    . esc_url($paginationNextUrl)
                    . '">'
                    . __('Nächster Spieltag', 'soccr')
                    . '</a>';
            }
        }

        $title = $parsedAttributes['title'];
        $showTitle = $parsedAttributes['showTitle'];

        $leagueSeasonDisplay = $openLigaDBGroupMatches->getLeagueSeasonDisplay();
        $group = $openLigaDBGroupMatches->getGroup();
        $groupName = $openLigaDBGroupMatches->getGroup()->getGroupName();

        /* translators: %1$s is the group name, %2$s is the league season */
        $headline = sprintf(
            __('%1$s | %2$s', 'soccr'),
            esc_html($groupName),
            esc_html($leagueName),
        );

        $headline = apply_filters(
            'soccr_group_matches_headline',
            $headline,
            $group,
            [
                'league'              => $openLigaDBLeagueSeason,
                'leagueShortcut'      => $leagueShortcut,
                'leagueSeason'        => $leagueSeason,
                'leagueSeasonDisplay' => $leagueSeasonDisplay,
                'groupOrderId'        => $group->getGroupOrderId(),
                'groupName'           => $group->getGroupName(),
                'groupId'             => $group->getGroupId(),
            ],
        );

        $headlineHTML = '';
        if ($showTitle) {
            $displayTitle = $title !== '' ? $this->esc($title) : $headline;
            $headlineHTML = <<<HTML
                <div class="{$this->blockClass('header')}">
                     <h2 class="{$this->blockClass('headline')}">$displayTitle</h2>
                </div>
            HTML;
        }

        $wrapperAttributes = get_block_wrapper_attributes([
            'class' => $this->blockClasses($parsedAttributes),
        ]);

        $html = <<<HTML
            $content
            <div $wrapperAttributes>
                $headlineHTML
                <div class="{$this->blockClass('content')}">
        HTML;

        $currentMatchDate = null;

        foreach ($openLigaDBGroupMatches->getMatches() as $match) {
            $matchDate = $match->getDateTime()->format('Y-m-d');

            if ($currentMatchDate !== $matchDate) {
                $currentMatchDate = $matchDate;
                $matchDateString = $this->esc(DateFormat::toDate($match->getDateTime()));
                $html .= <<<HTML
                    <div class="{$this->blockClass('datetime')}">$matchDateString</div>
                HTML;
            }

            $result = $match->getResultByType(2);
            $fullDateTimeTooltip = esc_attr(DateFormat::toWordPress($match->getDateTime()));

            if ($result !== null) {
                $resultContent = $this->esc((string) $result);
                $resultClass = $this->blockClass('result');
            } else {
                $weekdayEscaped = $this->esc(date_i18n('D', $match->getDateTime()->getTimestamp()));
                $timeEscaped = $this->esc(DateFormat::toTime($match->getDateTime()));
                $weekdayClass = $this->blockClass('result-weekday');
                $timeClass = $this->blockClass('result-time');
                $resultContent = "<span class=\"{$weekdayClass}\">{$weekdayEscaped}</span><span class=\"{$timeClass}\">{$timeEscaped}</span>";
                $resultClass = $this->blockClass('result') . ' ' . $this->blockClass('result-kickoff');
            }

            $html .= <<<HTML
                <div class="{$this->blockClass('row')}">
                    <div class='{$this->blockClass('team-home')}'>
                        <span class="{$this->blockClass('team-name')}">{$this->esc($match->getTeam1()->getTeamName())}</span>
                        <span class="{$this->blockClass('team-shortname')}">{$this->esc($match->getTeam1()->getShortName())}</span>
                    </div>
                    <div class="{$resultClass}"><span title="{$fullDateTimeTooltip}">{$resultContent}</span></div>
                    <div class="{$this->blockClass('team-away')}">
                        <span class="{$this->blockClass('team-name')}">{$this->esc($match->getTeam2()->getTeamName())}</span>
                        <span class="{$this->blockClass('team-shortname')}">{$this->esc($match->getTeam2()->getShortName())}</span>
                    </div>
                </div>
            HTML;
        }

        $html .= '</div>';

        if ($parsedAttributes['pagination']) {
            $html .= <<<HTML
                <div class="{$this->blockClass('pagination')}">
                    <div class='{$this->blockClass('pagination-left')}'>$paginationPreviousHref</div>
                    <div class='{$this->blockClass('pagination-right')}'>$paginationNextHref</div>
                </div>
            HTML;
        }

        $html .= $this->attributionHtml();
        $html .= '</div>';

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
