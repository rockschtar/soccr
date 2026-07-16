<?php

namespace Rockschtar\WordPress\Soccr\Controller;

use Rockschtar\WordPress\Soccr\Commands\OpenLigaDBCommand;
use Rockschtar\WordPress\Soccr\Traits\Singelton;

class CommandController
{
    use Singelton;

    private function __construct()
    {
        if (defined('WP_CLI') && WP_CLI) {
            \WP_CLI::add_command('soccr', OpenLigaDBCommand::class);
        }
    }
}
