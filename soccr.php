<?php

/*
* Plugin Name: Soccr
* Plugin URI: https://wordpress.org/plugins/soccr/
* Description: Display football match results, standings, and upcoming matches from OpenLigaDB as Gutenberg blocks.
* Author: rockschtar
* Author URI: http://www.eracer.de
* Version: develop
* Requires at least: 7.0
* Requires PHP: 8.4
* License: MIT
* Text Domain: soccr
*/

define('SOCCR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SOCCR_PLUGIN_URL', plugin_dir_url(__FILE__));

if(file_exists(SOCCR_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once SOCCR_PLUGIN_DIR . 'vendor/autoload.php';
} else {
    spl_autoload_register(static function ($class) {
        $baseDir = __DIR__ . '/src/';
        $prefix = 'Rockschtar\\WordPress\\Soccr\\';
        $len = strlen($prefix);

        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }

        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            include $file;
        }
    });
}

use Rockschtar\WordPress\Soccr\Controller\PluginController;

PluginController::init();
