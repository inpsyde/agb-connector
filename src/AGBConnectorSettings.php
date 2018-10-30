<?php # -*- coding: utf-8 -*-

/**
 * Class AGBConnectorSettings
 *
 * @since 1.0.0
 */
class AGBConnectorSettings
{

    /**
     * The save message.
     *
     * @var string
     */
    private $message = '';

    /**
     * The Plugin version.
     *
     * @var string
     */
    private $pluginVersion;

    /**
     * AGBConnectorSettings constructor.
     *
     * @since 1.0.0
     *
     * @param string $pluginVersion The plugin version.
     */
    public function __construct($pluginVersion)
    {
        $this->pluginVersion = $pluginVersion;
    }

    /**
     * Add the menu entry
     */
    public function add_menu()
    {
        $hook = add_options_page(
            __('Terms & Conditions Connector of IT-Recht Kanzlei', 'agb-connector'),
            'AGB Connector',
            'edit_pages',
            'agb-connector-settings',
            [
                $this,
                'page',
            ]
        );
        add_action('load-' . $hook, [$this, 'load']);
    }

    /**
     * Add Settings link to plugin actions.
     *
     * @param array $links The links.
     *
     * @return array
     */
    public function add_action_links($links)
    {
        $addLinks = [
            '<a href="' . admin_url('options-general.php?page=agb_connector_settings') . '">' . __(
                'Settings',
                'agb-connector'
            ) . '</a>',
        ];

        return array_merge($addLinks, $links);
    }

    /**
     * All things must done in load
     */
    public function load()
    {
        // Load css.
        if (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) {
            wp_enqueue_style(
                'agb-connector',
                plugins_url('/assets/css/style.css', __DIR__),
                [],
                time(),
                'all'
            );
        } else {
            wp_enqueue_style(
                'agb-connector',
                plugins_url('/assets/css/style.min.css', __DIR__),
                [],
                $this->pluginVersion,
                'all'
            );
        }

        // Save changes.
        if (empty($_POST['save']) && ! isset($_GET['regen'])) { // Input var okay.
            return;
        }

        if (isset($_GET['regen'])) { // Input var okay.
            check_admin_referer('agb-connector-settings-page-regen');
            $userAuthToken = md5(wp_generate_password(32, true, true));
            update_option('agb_connector_user_auth_token', $userAuthToken);
            $this->message .= '<p>' . __('New APT-Token generated.', 'agb-connector') . '</p>';

            return;
        }

        check_admin_referer('agb-connector-settings-page');

        $textTypesAllocation = [];
        $appendEmail = [];
        if (! empty($_POST['page_agb']) && get_post(absint($_POST['page_agb'])) !== null) { // Input var okay.
            $textTypesAllocation['agb'] = absint($_POST['page_agb']); // Input var okay.
        }
        if (! empty($_POST['pdf_append_email_agb'])) { // Input var okay.
            $appendEmail['agb'] = true;
        } else {
            $appendEmail['agb'] = false;
        }

        if (! empty($_POST['page_datenschutz']) && get_post(absint($_POST['page_datenschutz'])) !== null) { // Input var okay.
            $textTypesAllocation['datenschutz'] = absint($_POST['page_datenschutz']); // Input var okay.
        }
        if (! empty($_POST['pdf_append_email_datenschutz'])) { // Input var okay.
            $appendEmail['datenschutz'] = true;
        } else {
            $appendEmail['datenschutz'] = false;
        }

        if (! empty($_POST['page_widerruf']) && get_post(absint($_POST['page_widerruf'])) !== null) { // Input var okay.
            $textTypesAllocation['widerruf'] = absint($_POST['page_widerruf']); // Input var okay.
        }
        if (! empty($_POST['pdf_append_email_widerruf'])) { // Input var okay.
            $appendEmail['widerruf'] = true;
        } else {
            $appendEmail['widerruf'] = false;
        }

        if (! empty($_POST['page_impressum']) && get_post(absint($_POST['page_impressum'])) !== null) { // Input var okay.
            $textTypesAllocation['impressum'] = absint($_POST['page_impressum']); // Input var okay.
        }
        update_option('agb_connector_wc_append_email', $appendEmail);
        update_option('agb_connector_text_types_allocation', $textTypesAllocation);

        $this->message .= '<p>' . __('Settings updated.', 'agb-connector') . '</p>';
    }

    /**
     * The settings page content
     */
    public function page()
    {
        $textTypesAllocation = get_option('agb_connector_text_types_allocation', []);
        $appendEmail = get_option('agb_connector_wc_append_email', []);

        ?>
        <div class="wrap" id="agb-connector-settings">
            <div class="settings-container">
                <h2>
                    <?php
                    printf(
                        '%s &raquo %s',
                        esc_html__('Settings', 'agb-connector'),
                        esc_html__('Terms & Conditions Connector of IT-Recht Kanzlei', 'agb-connector')
                    );
                    ?>
                </h2>

                <?php
                if (! empty($this->message)) {
                    echo '<div id="message" class="updated">' . $this->message . '</div>';
                }
                ?>

                <form method="post"
                      action="<?php echo esc_url(add_query_arg(
                          ['page' => 'agb_connector_settings'],
                          admin_url('options-general.php')
                      )); ?>">
                    <?php wp_nonce_field('agb-connector-settings-page'); ?>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><label for="regen"><?php esc_html_e(
                                'Your shop URL',
                                'agb-connector'
                            ); ?></label></th>
                            <td><p><code><?php echo esc_attr(home_url()); ?></code></p></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="regen"><?php esc_html_e(
                                'API-Token',
                                'agb-connector'
                            ); ?></label></th>
                            <td><p><code><?php echo esc_attr(get_option('agb_connector_user_auth_token')); ?></code>
                                    <a class="button" href="<?php echo esc_url(wp_nonce_url(
                                        add_query_arg([
                                        'regen' => '',
                                        'page' => 'agb_connector_settings',
                                        ], admin_url('options-general.php')),
                                                               'agb-connector-settings-page-regen'
                                    )); ?>"><?php esc_html_e(
                                        'Regenerate',
                                        'agb-connector'
                                            ); ?></a>
                                </p>
                                <p class="description"><?php esc_html_e(
                                    'If you change the token, this must also be adjusted in the client portal.',
                                    'agb-connector'
                                                       ); ?></p>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><label for="page_agb"><?php esc_html_e(
                                'Terms and Conditions',
                                'agb-connector'
                                                                  ); ?>
                            </th>
                            <td>
                                <p>
                                    <label for="page_agb"><?php esc_html_e('Page:', 'agb-connector'); ?></label>
                                    <?php
                                    $value = 0;
                                    if (! empty($textTypesAllocation['agb'])) {
                                        $value = $textTypesAllocation['agb'];
                                    }
                                    wp_dropdown_pages([
                                        'id' => 'page_agb',
                                        'name' => 'page_agb',
                                        'echo' => 1,
                                        'show_option_none' => esc_html__('&mdash; Select &mdash;'),
                                        'option_none_value' => '0',
                                        'selected' => (int)$value,
                                    ]);
                                    ?>
                                </p>
                                <p>
                                    <?php
                                    if (function_exists('wc')) : ?>
                                        <label>
                                            <input type="checkbox" value="1"
                                                   name="pdf_append_email_agb" <?php checked(
                                                       ! empty($appendEmail['agb']),
                                                       true
                                                                               ) ?> >
                                            <?php esc_html_e(
                                                'Send PDF with WooCommerce order on hold email.',
                                                'agb-connector'
                                            ); ?>
                                        </label>
                                    <?php endif; ?>
                                </p>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><label for="page_datenschutz"><?php esc_html_e(
                                'Privacy',
                                'agb-connector'
                                                                          ); ?></th>
                            <td>
                                <p>
                                    <label for="page_datenschutz"><?php esc_html_e('Page:', 'agb-connector'); ?></label>
                                    <?php
                                    $value = 0;
                                    if (! empty($textTypesAllocation['datenschutz'])) {
                                        $value = $textTypesAllocation['datenschutz'];
                                    }
                                    wp_dropdown_pages([
                                        'id' => 'page_datenschutz',
                                        'name' => 'page_datenschutz',
                                        'echo' => 1,
                                        'show_option_none' => esc_html__('&mdash; Select &mdash;'),
                                        'option_none_value' => '0',
                                        'selected' => (int)$value,
                                    ]);
                                    ?>
                                </p>
                                <p>
                                    <?php
                                    if (function_exists('wc')) : ?>
                                        <label>
                                            <input type="checkbox" value="1"
                                                   name="pdf_append_email_datenschutz" <?php checked(
                                                       ! empty($appendEmail['datenschutz']),
                                                       true
                                                                                       ); ?> >
                                            <?php esc_html_e(
                                                'Send PDF with WooCommerce order on hold email.',
                                                'agb-connector'
                                            ); ?>
                                        </label>
                                    <?php endif; ?>
                                </p>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><label for="page_widerruf"><?php esc_html_e(
                                'Revocation',
                                'agb-connector'
                                                                       ); ?></th>
                            <td>
                                <p>
                                    <label for="page_datenschutz"><?php esc_html_e('Page:', 'agb-connector'); ?></label>
                                    <?php
                                    $value = 0;
                                    if (! empty($textTypesAllocation['widerruf'])) {
                                        $value = $textTypesAllocation['widerruf'];
                                    }
                                    wp_dropdown_pages([
                                        'id' => 'page_widerruf',
                                        'name' => 'page_widerruf',
                                        'echo' => 1,
                                        'show_option_none' => esc_html__('&mdash; Select &mdash;'),
                                        'option_none_value' => '0',
                                        'selected' => (int)$value,
                                    ]);
                                    ?>
                                </p>
                                <p>
                                    <?php
                                    if (function_exists('wc')) : ?>
                                        <label>
                                            <input type="checkbox" value="1"
                                                   name="pdf_append_email_widerruf" <?php checked(
                                                       ! empty($appendEmail['widerruf']),
                                                       true
                                                                                    ); ?> >
                                            <?php esc_html_e(
                                                'Send PDF with WooCommerce order on hold email.',
                                                'agb-connector'
                                            ); ?>
                                        </label>
                                    <?php endif; ?>
                                </p>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><label for="page_impressum"><?php esc_html_e('Imprint', 'agb-connector'); ?>
                            </th>
                            <td>
                                <p>
                                    <label for="page_impressum"><?php esc_html_e('Page:', 'agb-connector'); ?></label>
                                    <?php
                                    $value = 0;
                                    if (! empty($textTypesAllocation['impressum'])) {
                                        $value = $textTypesAllocation['impressum'];
                                    }
                                    wp_dropdown_pages([
                                        'id' => 'page_impressum',
                                        'name' => 'page_impressum',
                                        'echo' => 1,
                                        'show_option_none' => esc_html__('&mdash; Select &mdash;'),
                                        'option_none_value' => '0',
                                        'selected' => (int)$value,
                                    ]);
                                    ?>
                                </p>
                            </td>
                        </tr>

                    </table>

                    <?php submit_button(__('Save changes', 'agb-connector'), 'primary', 'save'); ?>

                </form>
            </div>
            <div class="box-container">
                <div id="it-recht-kanzlei" class="metabox-holder postbox">
                    <div class="inside">
                        <a href="https://www.it-recht-kanzlei.de" class="it-kanzlei-logo"
                           title="IT-Recht Kanzlei München">IT-Recht Kanzlei München</a><br>
                    </div>
                </div>
                <div id="inpsyde" class="metabox-holder postbox">
                    <div class="inside">
                        <a href="<?php esc_html_e(
                            'https://inpsyde.com/en/?utm_source=AGBConnector&utm_medium=Banner&utm_campaign=Inpsyde',
                            'agb-connector'
                                 ); ?>" class="inpsyde-logo"
                           title="<?php esc_html_e('An Inpsyde GmbH Product', 'agb-connector'); ?>">Inpsyde GmbH</a><br>
                    </div>
                </div>
                <div id="inpsyde" class="metabox-holder postbox">
                    <div class="inside">
                        <h3>Support</h3>
                        <p> <?php esc_html_e(
                            'If you have questions please contact “IT-Recht Kanzlei München” directly',
                            'agb-connector'
                            ); ?><br/>
                            <?php esc_html_e('via +49 89 13014330 or ', 'agb-connector'); ?><a
                                    href="mailto:info@it-recht-kanzlei.de">info@it-recht-kanzlei.de</a></p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
