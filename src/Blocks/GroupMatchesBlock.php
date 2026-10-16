<?php

namespace Rockschtar\Soccr\Blocks;

use Exception;
use Rockschtar\Soccr\Api\OpenLigaDBApi;
use Rockschtar\Soccr\Utils\DateFormat;

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
            $leagueNameShort = $openLigaDBLeagueSeason->getLeagueNameShort();

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

                $paginationPreviousHref = strtr(
                    '<a href="{url}">{label}</a>',
                    [
                        '{url}' => esc_url($paginationPreviousUrl),
                        '{label}' => esc_html__('Previous matchday', 'soccr'),
                    ],
                );
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

                $paginationNextHref = strtr(
                    '<a href="{url}">{label}</a>',
                    [
                        '{url}' => esc_url($paginationNextUrl),
                        '{label}' => esc_html__('Next matchday', 'soccr'),
                    ],
                );
            }
        }

        $title = $parsedAttributes['title'];
        $showTitle = $parsedAttributes['showTitle'];

        $leagueSeasonDisplay = $openLigaDBGroupMatches->getLeagueSeasonDisplay();
        $group = $openLigaDBGroupMatches->getGroup();
        $groupName = $openLigaDBGroupMatches->getGroup()->getGroupName();

        $headline = sprintf(
            /* translators: %1$s is the group name, %2$s is the league season */
            __('%1$s | %2$s', 'soccr'),
            esc_html($groupName),
            esc_html($leagueNameShort),
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
            $displayTitle = $title !== '' ? esc_html($title) : $headline;

            $headlineHTML = strtr(
                '<div class="{headerClass}"><h2 class="{headlineClass}">{title}</h2></div>',
                [
                    '{headerClass}' => esc_attr($this->blockClass('header')),
                    '{headlineClass}' => esc_attr($this->blockClass('headline')),
                    '{title}' => $displayTitle,
                ],
            );
        }

        $wrapperAttributes = get_block_wrapper_attributes([
            'class' => $this->blockClasses($parsedAttributes),
        ]);

        // $content is the inner block markup, $wrapperAttributes is escaped
        // by get_block_wrapper_attributes().
        $html = strtr(
            '{content}<div {wrapperAttributes}>{headline}<div class="{contentClass}">',
            [
                '{content}' => $content,
                '{wrapperAttributes}' => $wrapperAttributes,
                '{headline}' => $headlineHTML,
                '{contentClass}' => esc_attr($this->blockClass('content')),
            ],
        );

        $currentMatchDate = null;

        foreach ($openLigaDBGroupMatches->getMatches() as $match) {
            $matchDate = $match->getDateTime()->format('Y-m-d');

            if ($currentMatchDate !== $matchDate) {
                $currentMatchDate = $matchDate;

                $html .= strtr(
                    '<div class="{datetimeClass}">{date}</div>',
                    [
                        '{datetimeClass}' => esc_attr($this->blockClass('datetime')),
                        '{date}' => esc_html(DateFormat::toDate($match->getDateTime())),
                    ],
                );
            }

            $result = $match->getResultByType(2);

            if ($result !== null) {
                $resultContent = esc_html((string) $result);
                $resultClass = $this->blockClass('result');
            } else {
                $resultContent = strtr(
                    '<span class="{weekdayClass}">{weekday}</span><span class="{timeClass}">{time}</span>',
                    [
                        '{weekdayClass}' => esc_attr($this->blockClass('result-weekday')),
                        '{weekday}' => esc_html(date_i18n('D', $match->getDateTime()->getTimestamp())),
                        '{timeClass}' => esc_attr($this->blockClass('result-time')),
                        '{time}' => esc_html(DateFormat::toTime($match->getDateTime())),
                    ],
                );
                $resultClass = $this->blockClass('result') . ' ' . $this->blockClass('result-kickoff');
            }

            $html .= strtr(
                '<div class="{rowClass}">'
                . '<div class="{teamHomeClass}">'
                . '<span class="{teamNameClass}">{homeName}</span>'
                . '<span class="{teamShortNameClass}">{homeShortName}</span>'
                . '</div>'
                . '<div class="{resultClass}"><span title="{kickoff}">{result}</span></div>'
                . '<div class="{teamAwayClass}">'
                . '<span class="{teamNameClass}">{awayName}</span>'
                . '<span class="{teamShortNameClass}">{awayShortName}</span>'
                . '</div>'
                . '</div>',
                [
                    '{rowClass}' => esc_attr($this->blockClass('row')),
                    '{teamHomeClass}' => esc_attr($this->blockClass('team-home')),
                    '{teamAwayClass}' => esc_attr($this->blockClass('team-away')),
                    '{teamNameClass}' => esc_attr($this->blockClass('team-name')),
                    '{teamShortNameClass}' => esc_attr($this->blockClass('team-shortname')),
                    '{resultClass}' => esc_attr($resultClass),
                    '{homeName}' => esc_html($match->getTeam1()->getTeamName()),
                    '{homeShortName}' => esc_html($match->getTeam1()->getShortName()),
                    '{awayName}' => esc_html($match->getTeam2()->getTeamName()),
                    '{awayShortName}' => esc_html($match->getTeam2()->getShortName()),
                    '{kickoff}' => esc_attr(DateFormat::toWordPress($match->getDateTime())),
                    '{result}' => $resultContent,
                ],
            );
        }

        $html .= '</div>';

        if ($pagination) {
            $html .= strtr(
                '<div class="{paginationClass}">'
                . '<div class="{leftClass}">{previous}</div>'
                . '<div class="{rightClass}">{next}</div>'
                . '</div>',
                [
                    '{paginationClass}' => esc_attr($this->blockClass('pagination')),
                    '{leftClass}' => esc_attr($this->blockClass('pagination-left')),
                    '{rightClass}' => esc_attr($this->blockClass('pagination-right')),
                    '{previous}' => $paginationPreviousHref,
                    '{next}' => $paginationNextHref,
                ],
            );
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
