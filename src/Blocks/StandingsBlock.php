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

        $headline = sprintf(__('Tabelle | %s', 'soccr'), esc_html($leagueSeasonDisplay));
        $headline = apply_filters('openligab_standings_headline', $headline, $openLigaDBStandings);

        $headlineHTML = <<<HTML
                <div class="{$this->blockClass('header')}">
                    <h1 class="{$this->blockClass('headline')}">$headline</h1>
                </div>
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
           <div class="{$this->blockClass('thead')}">
                <div class="{$this->blockClass('tr')}">
                    $headlineHTML
                </div>
                <div class="{$this->blockClass('tr')}">
                    <div class="{$this->blockClass('th')} {$this->blockClass('position')}"></div>
                    <div class="{$this->blockClass('th')} {$this->blockClass('team')}"></div>
                    <div class="{$this->blockClass('th')} {$this->blockClass('matches')}">Spiele</div>
                    <div class="{$this->blockClass('th')} {$this->blockClass('points')}">Punkte</div>
                    <div class="{$this->blockClass('th')} {$this->blockClass('wins')}">S</div>
                    <div class="{$this->blockClass('th')} {$this->blockClass('draws')}">U</div>
                    <div class="{$this->blockClass('th')} {$this->blockClass('looses')}">N</div>
                    <div class="{$this->blockClass('th')} {$this->blockClass('goals')}">Tore</div>
                    <div class="{$this->blockClass('th')} {$this->blockClass('goals-difference')}">Diff</div>
                </div>

           </div>
        HTML;

        $standingsHTMLBody = '';

        $standingsPosition = 0;

        foreach ($openLigaDBStandings->getStandings() as $openLigaDBStanding) {
            $standingsPosition++;

            $standingsHTMLBody .= <<<HTML
                <div class="{$this->blockClass('row')} {$this->blockClass('team-' . $openLigaDBStanding->getTeam()->getTeamId())}">
                    <div class="{$this->blockClass('position')} {$this->blockClass('position')}-$standingsPosition">{$standingsPosition}</div>
                    <div class="{$this->blockClass('team')}">
                        <span class="{$this->blockClass('team-name')}">{$this->esc($openLigaDBStanding->getTeam()->getTeamName())}</span>
                        <span class="{$this->blockClass('team-shortname')}">{$this->esc($openLigaDBStanding->getTeam()->getShortName())}</span>
                    </div>

                    <div class="{$this->blockClass('matches')}">{$openLigaDBStanding->getMatches()}</div>
                    <div class="{$this->blockClass('points')}">{$openLigaDBStanding->getPoints()}</div>
                    <div class="{$this->blockClass('wins')}">{$openLigaDBStanding->getWins()}</div>
                    <div class="{$this->blockClass('draws')}">{$openLigaDBStanding->getDraws()}</div>
                    <div class="{$this->blockClass('looses')}">{$openLigaDBStanding->getLooses()}</div>
                    <div class="{$this->blockClass('goals')}">{$openLigaDBStanding->getGoalsScored()}:{$openLigaDBStanding->getGoalsConceded()}</div>
                    <div class="{$this->blockClass('goals-difference')}">{$openLigaDBStanding->getGoalDifference()}</div>
                </div>
            HTML;
        }



        $wrapperAttributes = get_block_wrapper_attributes([
            'class' => $cssClasses,
        ]);

        return <<<HTML
            <div {$wrapperAttributes}>
                <div class="{$this->blockClass('content')}">
                    {$standingsHTMLHeader}
                    <div class="{$this->blockClass('tbody')}">
                        {$standingsHTMLBody}
                    </div>
                </div>
            </div>
        HTML;
    }

    public function blockDirectory(): string
    {
        return '/dist/OpenLigaDBStandings';
    }
}
