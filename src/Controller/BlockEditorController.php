<?php

namespace Rockschtar\Soccr\Controller;

use Rockschtar\Soccr\Blocks\GroupMatchesBlock;
use Rockschtar\Soccr\Blocks\StandingsBlock;
use Rockschtar\Soccr\Blocks\TeamMatchBlock;
use Rockschtar\Soccr\Traits\Singelton;

class BlockEditorController
{
    use Singelton;

    private function __construct()
    {
        add_filter('block_categories_all', $this->addBlockCategories(...), 10, 1);
        add_filter('block_type_metadata', $this->blockTypeMetadata(...), 10, 1);

        GroupMatchesBlock::init();
        StandingsBlock::init();
        TeamMatchBlock::init();
    }


    private function blockTypeMetadata(array $metaData): array
    {

        if ($metaData['name'] === 'soccr/group-matches') {
            $metaData['attributes']['leagueSeason']['default'] = date('Y');
        }

        if ($metaData['name'] === 'soccr/standings') {
            $metaData['attributes']['leagueSeason']['default'] = date('Y');
        }

        if ($metaData['name'] === 'soccr/team-match') {
            $metaData['attributes']['leagueSeason']['default'] = date('Y');
        }

        return $metaData;
    }

    private function addBlockCategories(array $categories): array
    {
        return array_merge($categories, [
            [
                'slug' => 'soccr',
                'title' => __('Soccr', 'soccr'),
            ],
        ]);
    }
}
