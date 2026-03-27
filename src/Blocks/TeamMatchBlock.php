<?php

namespace Rockschtar\WordPress\Soccr\Blocks;

use Exception;
use Rockschtar\WordPress\Soccr\Api\OpenLigaDBApi;
use Rockschtar\WordPress\Soccr\Models\OpenLigaDBMatch;
use Rockschtar\WordPress\Soccr\Models\OpenLigaDBMatchQuery;
use Rockschtar\WordPress\Soccr\Utils\DateFormat;

class TeamMatchBlock extends Block
{
    protected function render(array $attributes, string $content = ''): string
    {
        $defaultAttributes = [
            'leagueShortcut' => '',
            'leagueSeason' => 0,
            'teamId' => 0,
            'displayMode' => 'current',
            'align' => 'center',
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
                return '<p>' .
                    __(
                        'Kein Spiel gefunden',
                        'soccr',
                    ) .
                    '</p>';
            }
        } catch (Exception $e) {
            do_action('soccr_exception', $e);

            if (defined('WP_DEBUG') && true === WP_DEBUG) {
                return $e->getMessage();
            }

            if ($e->getCode() === 404) {
                return '<p>' .
                    __(
                        'Fehler: Spiel, Liga oder Saison nicht gefunden',
                        'soccr',
                    ) .
                    '</p>';
            }

            return '';
        }

        $matchDateTimeString = DateFormat::toWordPress($match->getDateTime());

        $result = $match->getResult();
        $resultDisplay = $result !== null
            ? $result
            : '-:-';

        $wrapperAttributes = get_block_wrapper_attributes([
            'class' => $this->blockClasses($parsedAttributes),
        ]);

        $attributionHtml = $this->attributionHtml();

        $html = <<<HTML
            $content
            <div $wrapperAttributes>
                <div class="{$this->blockClass('header')}">
                    <div class="{$this->blockClass('datetime')}">{$this->esc($matchDateTimeString)}</div>
                </div>
                <div class="{$this->blockClass('content')}">
                    <div class="{$this->blockClass('row')}">
                        <div class="{$this->blockClass('team-home')}">
                            <span class="{$this->blockClass('team-name')}">{$this->esc($match->getTeam1()->getTeamName())}</span>
                            <span class="{$this->blockClass('team-shortname')}">{$this->esc($match->getTeam1()->getShortName())}</span>
                        </div>
                        <div class="{$this->blockClass('result')}">{$this->esc($resultDisplay)}</div>
                        <div class="{$this->blockClass('team-away')}">
                            <span class="{$this->blockClass('team-name')}">{$this->esc($match->getTeam2()->getTeamName())}</span>
                            <span class="{$this->blockClass('team-shortname')}">{$this->esc($match->getTeam2()->getShortName())}</span>
                        </div>
                    </div>
                </div>
                {$attributionHtml}
            </div>
        HTML;

        return apply_filters(
            'soccr_team_match_html',
            $html,
            $match,
        );
    }

    private function getMatchByDisplayMode(
        OpenLigaDBMatchQuery $query,
        string $displayMode
    ): ?OpenLigaDBMatch {
        return match ($displayMode) {
            'next' => OpenLigaDBApi::getNextMatchByTeamid($query),
            'last' => OpenLigaDBApi::getLastMatchByTeamId($query),
            'current' => $this->getCurrentMatch($query),
            default => $this->getCurrentMatch($query),
        };
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
