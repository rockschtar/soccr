<?php

namespace Rockschtar\Soccr\Controller;

use Rockschtar\Soccr\Commands\OpenLigaDBCommand;
use Rockschtar\Soccr\Traits\Singelton;

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
