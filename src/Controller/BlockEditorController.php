<?php

namespace Rockschtar\WordPress\Soccr\Controller;

use Rockschtar\WordPress\Soccr\Blocks\GroupMatchesBlock;
use Rockschtar\WordPress\Soccr\Blocks\StandingsBlock;
use Rockschtar\WordPress\Soccr\Traits\Singelton;

class BlockEditorController
{
    use Singelton;

    private function __construct()
    {
        add_filter('block_categories_all', $this->addBlockCategories(...), 10, 1);
        add_filter('block_type_metadata', $this->blockTypeMetadata(...), 10, 1);

        GroupMatchesBlock::init();
        StandingsBlock::init();
    }


    private function blockTypeMetadata(array $metaData): array
    {

        if ($metaData['name'] === 'soccr/group-matches') {
            $metaData['attributes']['leagueSeason']['default'] = date('Y');
        }

        if ($metaData['name'] === 'soccr/standings') {
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
