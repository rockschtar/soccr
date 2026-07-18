<?php

namespace Rockschtar\Soccr\Controller;

use Rockschtar\Soccr\Traits\Singelton;

class PluginController
{
    use Singelton;

    private function __construct()
    {
        BlockEditorController::init();
        RestController::init();
        CommandController::init();
    }
}
