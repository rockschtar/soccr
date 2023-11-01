<?php

namespace ClubfansUnited\Blocks;

use ClubfansUnited\Manager\OpenLigaDBManager;
use ClubfansUnited\Models\OpenLigaDBStanding;
use ClubfansUnited\Utils\TeamPredictionCalculator;

class PointPressureBlock extends Block
{
    protected function render(array $attributes, string $content = ''): string
    {
        $defaultAttributes = [
            'leagueShortcut' => '',
            'leagueSeason' => 0,
            'teamId' => 0,
            'align' => 'left',
            'pointsNeeded' => 40,
            'headline' => __('Punktdruckmesser', 'clubfans-united'),
        ];

        $parsedAttributes = wp_parse_args($attributes, $defaultAttributes);
        $cssClasses = $this->blockClasses($parsedAttributes);
        $teamId = (int) $parsedAttributes['teamId'];
        $pointsNeeded = (int) $parsedAttributes['pointsNeeded'];

        try {
            $standings = OpenLigaDBManager::getStandings(
                $parsedAttributes['leagueShortcut'],
                $parsedAttributes['leagueSeason'],
            );

            $standing = from($standings->getStandings())
                ->where(static function (OpenLigaDBStanding $standing) use (
                    $teamId
                ) {
                    return $standing->getTeam()->getTeamId() === $teamId;
                })
                ->firstOrDefault();

            if (!$standing) {
                return '';
            }

            $teamPredictionCalculator = new TeamPredictionCalculator(
                34,
                $standing,
                $pointsNeeded,
            );

            switch ($teamPredictionCalculator->getPredictionStage()) {
                case TeamPredictionCalculator::PREDICTION_STAGE_1:
                    $arrowClass = 'arrow-down';
                    break;
                case TeamPredictionCalculator::PREDICTION_STAGE_2:
                    $arrowClass = 'arrow-down-half';
                    break;
                case TeamPredictionCalculator::PREDICTION_STAGE_3:
                    $arrowClass = 'arrow-middle';
                    break;
                case TeamPredictionCalculator::PREDICTION_STAGE_4:
                    $arrowClass = 'arrow-up-half';
                    break;
                case TeamPredictionCalculator::PREDICTION_STAGE_5:
                    $arrowClass = 'arrow-up';
                    break;
            }
        } catch (\Exception $e) {
            /** @noinspection ForgottenDebugOutputInspection */
            error_log($e->getMessage());
            return $e->getMessage();
        }

        return <<<HTML
            <div class="$cssClasses">
                <h2>{$parsedAttributes['headline']}</h2>
                <div style="display: table">
                    <div style="display: table-row">
                        <div style="display: table-cell; vertical-align: middle;">
                           <img class="arrow $arrowClass" style="display: table-cell" alt="Kurs: {$teamPredictionCalculator->getPointDifferenceText()}" />
                        </div>
                        <div style="display: table-cell; vertical-align: middle; padding-left: 10px">
                            <span class="points">{$standing->getPoints()}/$pointsNeeded</span>
                        </div>
                    </div>
                </div>
                <div style="display: block; text-align: left">
                    Kurs {$teamPredictionCalculator->getPointDifferenceText()}
                    <br />
                    <br/>Saisonziel <strong>$pointsNeeded Punkte</strong>, aktuell
                    <strong>{$teamPredictionCalculator->getAveragePointsCurrent()} Punkte</strong> und
                    <strong>{$teamPredictionCalculator->getAverageGoalsScoredCurrent()} Tore</strong>
                    im Schnitt pro Spiel
                    <br/>Hochrechnung nach dem XX. Spieltag: <strong>{$teamPredictionCalculator->getPointsPredicted()}
                    Punkte</strong>
                <div>
                </div>
            </div>
            </div>
        HTML;
    }

    public function blockDirectory(): string
    {
        return '/web/dist/wp/PointPressure';
    }
}
