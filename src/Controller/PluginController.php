<?php

namespace Rockschtar\WordPress\Soccr\Controller;

use Rockschtar\WordPress\Soccr\Blocks\StandingsBlock;
use Rockschtar\WordPress\Soccr\Traits\Singelton;

class PluginController
{
    use Singelton;

    private function __construct() {
        BlockEditorController::init();
 		RestController::init();
    }
}
