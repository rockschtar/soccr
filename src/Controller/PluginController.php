<?php

namespace Rockschtar\WordPress\Soccr\Controller;

use Rockschtar\WordPress\Soccr\Traits\Singelton;

class PluginController
{
    use Singelton;

    private function __construct() {
        add_filter('the_title', $this->theTitle(...));
    }

    private function theTitle(string $title) : string {
        return "Was geht ab?";
    }
}
