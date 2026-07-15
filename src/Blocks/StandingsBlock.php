<?php

namespace Rockschtar\WordPress\Soccr\Blocks;

use Exception;
use Rockschtar\WordPress\Soccr\Api\OpenLigaDBApi;

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
            if (is_admin()) {
                $detailedMessage = $e->getMessage();
            }

            if (defined('WP_DEBUG') && true === WP_DEBUG) {
                $detailedMessage = $e->getMessage();
            }

            if ($e->getCode() === 404) {
                return '<p>'
                    . __(
                        'Fehler: Spieltag, Liga oder Saison nicht gefunden',
                        'soccr',
                    )
                    . '</p>';
            }

            do_action('soccr_exception', $e);

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
            $headlineHTML = <<<HTML
                    <div class="{$this->blockClass('header')}">
                        <h2 class="{$this->blockClass('headline')}">{$this->esc($title)}</h2>
                    </div>
               HTML;
        }

        $standingsHTMLHeader = <<<HTML
           <div class="{$this->blockClass('thead')}">
                <div class="{$this->blockClass('tr')}">
                    $headlineHTML
                </div>
                <div class="{$this->blockClass('tr')}">
                    <div class="{$this->blockClass('th')} {$this->blockClass('position')}"></div>
                    <div class="{$this->blockClass('th')} {$this->blockClass('team')}"></div>
                    <div class="{$this->blockClass('th')} {$this->blockClass('matches')}">{$this->esc(__('Sp', 'soccr'))}</div>
                    <div class="{$this->blockClass('th')} {$this->blockClass('wins')}">{$this->esc(__('S', 'soccr'))}</div>
                    <div class="{$this->blockClass('th')} {$this->blockClass('draws')}">{$this->esc(__('U', 'soccr'))}</div>
                    <div class="{$this->blockClass('th')} {$this->blockClass('looses')}">{$this->esc(__('N', 'soccr'))}</div>
                    <div class="{$this->blockClass('th')} {$this->blockClass('goals')}">{$this->esc(__('Tore', 'soccr'))}</div>
                    <div class="{$this->blockClass('th')} {$this->blockClass('goals-difference')}">{$this->esc(__('Diff', 'soccr'))}</div>
                    <div class="{$this->blockClass('th')} {$this->blockClass('points')}">{$this->esc(__('Pkt', 'soccr'))}</div>
                </div>

           </div>
        HTML;

        $standingsHTMLBody = '';

        $standingsPosition = 0;

        foreach ($openLigaDBStandings->getStandings() as $openLigaDBStanding) {
            $standingsPosition++;

            $teamId = $openLigaDBStanding->getTeam()->getTeamId();
            $highlightClass = ($highlightTeamId > 0 && $teamId === $highlightTeamId)
                ? ' ' . $this->blockClass('row--highlighted')
                : '';

            $standingsHTMLBody .= <<<HTML
                <div class="{$this->blockClass('row')} {$this->blockClass('team-' . $teamId)}{$highlightClass}">
                    <div class="{$this->blockClass('position')} {$this->blockClass('position')}-$standingsPosition">{$standingsPosition}</div>
                    <div class="{$this->blockClass('team')}">
                        <span class="{$this->blockClass('team-name')}">{$this->esc($openLigaDBStanding->getTeam()->getTeamName())}</span>
                        <span class="{$this->blockClass('team-shortname')}">{$this->esc($openLigaDBStanding->getTeam()->getShortName())}</span>
                    </div>

                    <div class="{$this->blockClass('matches')}">{$openLigaDBStanding->getMatches()}</div>
                    <div class="{$this->blockClass('wins')}">{$openLigaDBStanding->getWins()}</div>
                    <div class="{$this->blockClass('draws')}">{$openLigaDBStanding->getDraws()}</div>
                    <div class="{$this->blockClass('looses')}">{$openLigaDBStanding->getLooses()}</div>
                    <div class="{$this->blockClass('goals')}">{$openLigaDBStanding->getGoalsScored()}:{$openLigaDBStanding->getGoalsConceded()}</div>
                    <div class="{$this->blockClass('goals-difference')}">{$openLigaDBStanding->getGoalDifference()}</div>
                    <div class="{$this->blockClass('points')}">{$openLigaDBStanding->getPoints()}</div>
                </div>
            HTML;
        }



        $wrapperAttributes = get_block_wrapper_attributes([
            'class' => $cssClasses,
        ]);

        $attributionHtml = $this->attributionHtml();

        return <<<HTML
            <div {$wrapperAttributes}>
                <div class="{$this->blockClass('content')}">
                    {$standingsHTMLHeader}
                    <div class="{$this->blockClass('tbody')}">
                        {$standingsHTMLBody}
                    </div>
                </div>
                {$attributionHtml}
            </div>
        HTML;
    }

    public function blockDirectory(): string
    {
        return '/dist/OpenLigaDBStandings';
    }
}
