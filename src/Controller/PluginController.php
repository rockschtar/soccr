<?php

namespace Rockschtar\Soccr\Controller;

use Rockschtar\Soccr\Traits\Singelton;

class PluginController
{
    use Singelton;

    private function __construct()
    {
        // Bundled translations as fallback until the wp.org language pack
        // exists — must load before the blocks register on init (priority 10).
        add_action('init', static function (): void {
            load_plugin_textdomain(
                'soccr',
                false,
                dirname(plugin_basename(SOCCR_PLUGIN_DIR . 'soccr.php')) . '/languages',
            );
        }, 1);

        add_filter('plugin_action_links_' . SOCCR_PLUGIN, $this->donationLink(...));

        BlockEditorController::init();
        RestController::init();
        CommandController::init();
        OpenLigaDBController::init();
    }

    private function donationLink(array $actions): array
    {
        array_unshift($actions, sprintf(
            '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
            esc_url(SOCCR_DONATE_URL),
            esc_html__('Donate', 'soccr'),
        ));

        return $actions;
    }
}
