<?php

namespace Rockschtar\WordPress\Soccr\Blocks;

use Exception;
use Rockschtar\WordPress\Soccr\Api\OpenLigaDBApi;

class StandingsBlock extends Block
{
    protected function render(array $attributes, string $content = ''): string
    {
        $defaultAttributes = [
            'leagueShortcut' => '',
            'leagueSeason' => 0,
            'align' => 'left',
            'hideTitle' => false,
        ];

        $parsedAttributes = wp_parse_args($attributes, $defaultAttributes);
        $leagueShortcut = $parsedAttributes['leagueShortcut'];
        $leagueSeason = $parsedAttributes['leagueSeason'];
        $hideTitle = $parsedAttributes['hideTitle'];

        try {
            $openLigaDBStandings = OpenLigaDBApi::getStandings(
                $leagueShortcut,
                $leagueSeason,
            );
        } catch (Exception $e) {
            if (is_admin()) {
                $detailedMessage = $e->getMessage();
            }

            if (defined('WP_DEBUG') && true === WP_DEBUG) {
                $detailedMessage = $e->getMessage();
            }

            if ($e->getCode() === 404) {
                return '<p>' .
                    __(
                        'Fehler: Spieltag, Liga oder Saison nicht gefunden',
                        'soccr',
                    ) .
                    '</p>';
            }

            do_action('soccr_exception', $e);

            return '';
        }

        $additionalClasses = [];
        $additionalClasses[] = $openLigaDBStandings->getLeague()->getLeagueShortcut();
        $additionalClasses[] =
            $openLigaDBStandings->getLeague()->getLeagueShortcut() .
            '-' .
            $openLigaDBStandings->getLeague()->getLeagueSeason();

        $cssClasses = $this->blockClasses($parsedAttributes, $additionalClasses);

        $leagueSeasonDisplay = $openLigaDBStandings->getLeague()->getLeagueSeasonDisplay();

        $headline = sprintf(__('Tabelle | %s', 'soccr'), $leagueSeasonDisplay);
        $headline = apply_filters('openligab_standings_headline', $headline, $openLigaDBStandings);

        $headlineHTML = <<<HTML
                <tr>
                    <th colspan="9" class="{$this->blockClass('headline')}">
                        <h1>$headline</h1>
                    </th>
                </tr>
           HTML;

        if ($hideTitle) {
            $headlineHTML = '';
        }

        $headlineHTML = apply_filters(
            'openligab_standings_headline_html',
            $headlineHTML,
            $openLigaDBStandings,
        );

        $standingsHTMLHeader = <<<HTML
           <thead>
                <tr>
                    <th colspan="9" class="{$this->blockClass('headline')}">
                        $headlineHTML
                    </th>
                </tr>
                <tr>
                    <th></th>
                    <th></th>
                    <th class="{$this->blockClass('matches')}">Spiele</th>
                    <th class="{$this->blockClass('points')}">Punkte</th>
                    <th class="{$this->blockClass('wins')}">S</th>
                    <th class="{$this->blockClass('draws')}">U</th>
                    <th class="{$this->blockClass('looses')}">N</th>
                    <th class="{$this->blockClass('goals')}">Tore</th>
                    <th class="{$this->blockClass(
            'goals-difference',
        )}"">Diff</td>
                </tr>

           </thead>
        HTML;

        $standingsHTMLBody = '';

        $standingsPosition = 0;

        foreach ($openLigaDBStandings->getStandings() as $openLigaDBStanding) {
            $standingsPosition++;

            $standingsHTMLBody .= <<<HTML
                <tr class="{$this->blockClass('row')} {$this->blockClass('team-' . $openLigaDBStanding->getTeam()->getTeamId())}">
                    <td class="{$this->blockClass('position')} {$this->blockClass('position')}-$standingsPosition">{$standingsPosition}</td>
                    <td class="{$this->blockClass('team',)}">
                    <span class="{$this->blockClass('team-name')}">{$openLigaDBStanding->getTeam()->getTeamName()}</span>
                    <span class="{$this->blockClass('team-shortname')}">{$openLigaDBStanding->getTeam()->getShortName()}</span>
                    </td>
                    
                    <td class="{$this->blockClass(
                'matches',
            )}">{$openLigaDBStanding->getMatches()}</td>
                    <td class="{$this->blockClass(
                'points',
            )}">{$openLigaDBStanding->getPoints()}</td>
                    <td class="{$this->blockClass(
                'wins',
            )}">{$openLigaDBStanding->getWins()}</td>
                    <td class="{$this->blockClass(
                'draws',
            )}">{$openLigaDBStanding->getDraws()}</td>
                    <td class="{$this->blockClass(
                'looses',
            )}">{$openLigaDBStanding->getLooses()}</td>
                    <td class="{$this->blockClass(
                'goals',
            )}">{$openLigaDBStanding->getGoalsScored()}:{$openLigaDBStanding->getGoalsConceded()}</td>
                    <td class="{$this->blockClass(
                'goals-difference',
            )}">{$openLigaDBStanding->getGoalDifference()}</td>
                </tr>
            HTML;
        }

        return <<<HTML
            <div class="$cssClasses">
                <table>
                    {$standingsHTMLHeader}
                    <tbody>
                    {$standingsHTMLBody}
                    </tbody>
                </table>
            </div>
        HTML;
    }

    public function blockDirectory(): string
    {
        return '/dist/OpenLigaDBStandings';
    }
}
