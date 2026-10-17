<?php

namespace Rockschtar\Soccr\Controller;

use Rockschtar\Soccr\Traits\Singelton;

class PluginController
{
    use Singelton;

    private function __construct()
    {
        register_uninstall_hook(SOCCR_PLUGIN_DIR . 'soccr.php', [self::class, 'onUninstall']);
        add_filter('plugin_action_links_' . SOCCR_PLUGIN, $this->donationLink(...));

        BlockEditorController::init();
        RestController::init();
        CommandController::init();
        OpenLigaDBController::init();
        DonationNoticeController::init();
    }

    public static function onUninstall(): void
    {
        DonationNoticeController::uninstall();
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
