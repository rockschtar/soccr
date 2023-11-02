<?php

namespace Rockschtar\WordPress\Soccr\Controller;

use Rockschtar\WordPress\Soccr\Blocks\GroupMatchesBlock;
use Rockschtar\WordPress\Soccr\Traits\Singelton;

class BlockEditorController
{
    use Singelton;

    private function __construct()
    {
        add_filter('block_categories_all', $this->addBlockCategories(...), 10, 1);

        GroupMatchesBlock::init();
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
