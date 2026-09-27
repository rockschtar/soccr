<?php

namespace Rockschtar\Soccr\Controller;

use Rockschtar\Soccr\Traits\Singelton;

class DonationNoticeController
{
    use Singelton;

    public const string OPTION_INSTALLED = 'soccr_installed';

    public const string META_DISMISSED = 'soccr_donation_notice_dismissed';

    public const string META_SNOOZED_UNTIL = 'soccr_donation_notice_snoozed_until';

    private const string AJAX_ACTION = 'soccr_donation_notice';

    private const int DELAY = 14 * DAY_IN_SECONDS;

    private const int SNOOZE = 30 * DAY_IN_SECONDS;

    private const array SCREENS = ['dashboard', 'plugins'];

    private bool $rendered = false;

    private function __construct()
    {
        add_action('admin_init', $this->storeInstallDate(...));
        add_action('admin_notices', $this->render(...));
        add_action('admin_footer', $this->printScript(...));
        add_action('wp_ajax_' . self::AJAX_ACTION, $this->handleAction(...));
    }

    private function storeInstallDate(): void
    {
        if (get_option(self::OPTION_INSTALLED) === false) {
            add_option(self::OPTION_INSTALLED, time(), '', false);
        }
    }

    private function shouldShow(): bool
    {
        if (!current_user_can('manage_options')) {
            return false;
        }

        $screen = get_current_screen();

        if ($screen === null || !in_array($screen->id, self::SCREENS, true)) {
            return false;
        }

        if ((int) get_option(self::OPTION_INSTALLED, time()) + self::DELAY > time()) {
            return false;
        }

        $userId = get_current_user_id();

        if (get_user_meta($userId, self::META_DISMISSED, true)) {
            return false;
        }

        return (int) get_user_meta($userId, self::META_SNOOZED_UNTIL, true) <= time();
    }

    private function render(): void
    {
        if (!$this->shouldShow()) {
            return;
        }

        $snoozed = (bool) get_user_meta(get_current_user_id(), self::META_SNOOZED_UNTIL, true);

        ?>
        <div class="notice notice-info is-dismissible soccr-donation-notice">
            <p><strong><?php esc_html_e('Enjoying Soccr?', 'soccr'); ?></strong></p>
            <p><?php esc_html_e("Soccr is free and developed in my spare time. If it makes your website a little better, I'd be happy about a small donation.", 'soccr'); ?></p>
            <p>
                <a class="button button-primary" data-soccr-action="donate" href="<?php echo esc_url(SOCCR_DONATE_URL); ?>" target="_blank" rel="noopener noreferrer">
                    <?php esc_html_e('Buy me a coffee', 'soccr'); ?> &#9749;
                </a>
                <?php if (!$snoozed) : ?>
                    <button type="button" class="button" data-soccr-action="later"><?php esc_html_e('Remind me later', 'soccr'); ?></button>
                <?php endif; ?>
                <button type="button" class="button-link" data-soccr-action="never"><?php esc_html_e("Don't show again", 'soccr'); ?></button>
            </p>
        </div>
        <?php

        $this->rendered = true;
    }

    private function printScript(): void
    {
        if (!$this->rendered) {
            return;
        }

        $config = [
            'url' => admin_url('admin-ajax.php'),
            'action' => self::AJAX_ACTION,
            'nonce' => wp_create_nonce(self::AJAX_ACTION),
        ];

        $script = <<<'JS'
            (function (config) {
                var notice = document.querySelector('.soccr-donation-notice');
                if (!notice) {
                    return;
                }
                notice.addEventListener('click', function (event) {
                    var target = event.target.closest('[data-soccr-action], .notice-dismiss');
                    if (!target) {
                        return;
                    }
                    var body = new FormData();
                    body.append('action', config.action);
                    body.append('_ajax_nonce', config.nonce);
                    body.append('soccr_action', target.dataset.soccrAction || 'later');
                    fetch(config.url, { method: 'POST', credentials: 'same-origin', body: body, keepalive: true });
                    notice.remove();
                });
            })(%s);
            JS;

        wp_print_inline_script_tag(sprintf($script, wp_json_encode($config)));
    }

    private function handleAction(): void
    {
        check_ajax_referer(self::AJAX_ACTION);

        if (!current_user_can('manage_options')) {
            wp_send_json_error(null, 403);
        }

        $userId = get_current_user_id();
        $action = sanitize_key(wp_unslash($_POST['soccr_action'] ?? ''));

        // "Remind me later" (also the close button) snoozes only once, afterwards it counts as a permanent dismissal.
        if ($action === 'later' && !get_user_meta($userId, self::META_SNOOZED_UNTIL, true)) {
            update_user_meta($userId, self::META_SNOOZED_UNTIL, time() + self::SNOOZE);
        } else {
            update_user_meta($userId, self::META_DISMISSED, 1);
        }

        wp_send_json_success();
    }

    public static function uninstall(): void
    {
        delete_option(self::OPTION_INSTALLED);
        delete_metadata('user', 0, self::META_DISMISSED, '', true);
        delete_metadata('user', 0, self::META_SNOOZED_UNTIL, '', true);
    }
}
