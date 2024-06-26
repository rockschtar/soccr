<?php

namespace Rockschtar\WordPress\Soccr\Factories;

use Rockschtar\WordPress\Soccr\Models\OpenLigaDBGroup;
use Rockschtar\WordPress\Soccr\Models\OpenLigaDBLocation;
use Rockschtar\WordPress\Soccr\Models\OpenLigaDBMatch;
use Rockschtar\WordPress\Soccr\Models\OpenLigaDBMatchResult;

class OpenLigaDBMatchFactory
{
    public static function createFromJSON(\stdClass $match): OpenLigaDBMatch
    {
        $dateTime = \DateTime::createFromFormat(DATE_ATOM, $match->matchDateTimeUTC);

        $openLigaDBMatch = new OpenLigaDBMatch();
        $openLigaDBMatch->setDateTime($dateTime);
        $openLigaDBMatch->setMatchId($match->matchID);
        $openLigaDBMatch->setLeagueId($match->leagueId);
        $openLigaDBMatch->setLeagueSeason($match->leagueSeason);
        $openLigaDBMatch->setLeagueShortcut($match->leagueShortcut);
        $openLigaDBMatch->setIsFinished($match->matchIsFinished);

        if ($match->location !== null) {
            $openLigaDBMatch->setLocation(
                new OpenLigaDBLocation(
                    $match->location->locationID,
                    $match->location->locationCity,
                    $match->location->locationStadium,
                ),
            );
        }

        $openLigaDBMatch->setNumberOfViewers($match->numberOfViewers);
        $openLigaDBMatch->setGroup(
            new OpenLigaDBGroup(
                $match->group->groupName,
                $match->group->groupOrderID,
                $match->group->groupID,
            ),
        );

        $openLigaDBMatch->setTeam1(
            OpenLigaDBTeamFactory::createFromJSON($match->team1),
        );
        $openLigaDBMatch->setTeam2(
            OpenLigaDBTeamFactory::createFromJSON($match->team2),
        );

        foreach ($match->matchResults as $matchResult) {
            $openligaDbMatchResult = new OpenLigaDBMatchResult();
            $openligaDbMatchResult->setId($matchResult->resultID);
            $openligaDbMatchResult->setName($matchResult->resultName);
            $openligaDbMatchResult->setDescription(
                $matchResult->resultDescription,
            );
            $openligaDbMatchResult->setOrderId($matchResult->resultOrderID);
            $openligaDbMatchResult->setTypeId($matchResult->resultTypeID);
            $openligaDbMatchResult->setPointsTeam1($matchResult->pointsTeam1);
            $openligaDbMatchResult->setPointsTeam2($matchResult->pointsTeam2);
            $openLigaDBMatch->addResult($openligaDbMatchResult);
        }

        return $openLigaDBMatch;
    }
}
