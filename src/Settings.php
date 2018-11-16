<?php # -*- coding: utf-8 -*-

namespace Inpsyde\AGBConnector;

/**
 * Class Settings
 */
class Settings
{

    /**
     * The save message.
     *
     * @var string
     */
    private $message = '';

    /**
     * Add the menu entry
     */
    public function addMenu()
    {
        $hook = add_options_page(
            __('Terms & Conditions Connector of IT-Recht Kanzlei', 'agb-connector'),
            'AGB Connector',
            'edit_pages',
            'agb_connector_settings',
            [
                $this,
                'page',
            ]
        );
        add_action('load-' . $hook, [$this, 'load']);
    }

    /**
     * Add settings link to plugin actions.
     *
     * @param array $links The links.
     *
     * @return array
     */
    public function addActionLinks($links)
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
     *
     * phpcs:disable Generic.Metrics.NestingLevel.TooHigh
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
     */
    public function load()
    {
        $debug = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG);

        wp_enqueue_style(
            'agb-connector',
            plugins_url('/assets/css/style.' . (! $debug ? 'min.' : '') . 'css', __DIR__),
            [],
            $debug ? time() : Plugin::VERSION,
            'all'
        );

        $getRegen = filter_input(INPUT_GET, 'regen', FILTER_SANITIZE_NUMBER_INT);
        if (null !== $getRegen) {
            check_admin_referer('agb-connector-settings-page-regen');
            $userAuthToken = md5(wp_generate_password(32, true, true));
            update_option(Plugin::OPTION_USER_AUTH_TOKEN, $userAuthToken);
            $this->message = __('New APT-Token generated.', 'agb-connector');

            return;
        }

        $postTextAllocation = filter_input(
            INPUT_POST,
            'text_allocation',
            FILTER_DEFAULT,
            FILTER_REQUIRE_ARRAY
        );
        if (!$postTextAllocation && ! is_array($postTextAllocation)) {
            return;
        }

        check_admin_referer('agb-connector-settings-page');
        $textAllocations = [];
        foreach ($postTextAllocation as $type => $allocations) {
            if (! in_array($type, XmlApi::supportedTextTypes(), true)) {
                continue;
            }
            foreach ($allocations as $allocation) {
                if (! array_key_exists($allocation['country'], XmlApi::supportedCountries())) {
                    continue;
                }
                if (! array_key_exists($allocation['language'], XmlApi::supportedLanguages())) {
                    continue;
                }
                if (! get_post(absint($allocation['page_id']))) {
                    continue;
                }
                $textAllocations[$type][] = [
                    'country' => $allocation['country'],
                    'language' => $allocation['language'],
                    'pageId' => absint($allocation['page_id']),
                    'wcOrderEmailAttachment' => ! empty($allocation['wc_email']),
                ];
            }
        }

        update_option(Plugin::OPTION_TEXT_ALLOCATIONS, $textAllocations);
        $this->message = __('settings updated.', 'agb-connector');
    }

    /**
     * The settings page content
     *
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
     */
    public function page()
    {
        $textAllocations = get_option(Plugin::OPTION_TEXT_ALLOCATIONS, []);
        ?>
        <div class="wrap" id="agb-connector-settings">
            <h2>
                <?php
                printf(
                    '%s &rsaquo; %s',
                    esc_html__('Settings', 'agb-connector'),
                    esc_html__('Terms & Conditions Connector of IT-Recht Kanzlei', 'agb-connector')
                );
                ?>
            </h2>
            <div class="box-container">
                <div id="it-recht-kanzlei" class="metabox-holder postbox">
                    <div class="inside">
                        <a href="https://www.it-recht-kanzlei.de" class="it-kanzlei-logo"
                           title="IT-Recht Kanzlei München">IT-Recht Kanzlei München</a><br>
                    </div>
                </div>
                <div id="inpsyde" class="metabox-holder postbox">
                    <div class="inside">
                        <a href="
                        <?php esc_html_e(
                            'https://inpsyde.com/en/?utm_source=Plugin&utm_medium=Banner&utm_campaign=Inpsyde',
                            'agb-connector'
                        ); ?>
                        " class="inpsyde-logo" title="
                        <?php esc_html_e('An Inpsyde GmbH Product', 'agb-connector'); ?>
                        ">Inpsyde GmbH</a>
                        <br>
                    </div>
                </div>
                <div id="inpsyde" class="metabox-holder postbox">
                    <div class="inside">
                        <h3>Support</h3>
                        <p>
                            <?php esc_html_e(
                                'If you have questions please contact “IT-Recht Kanzlei München” directly',
                                'agb-connector'
                            ); ?>
                            <br/>
                            <?php esc_html_e('via +49 89 13014330 or ', 'agb-connector'); ?>
                            <a href="mailto:info@it-recht-kanzlei.de">info@it-recht-kanzlei.de</a>
                        </p>
                    </div>
                </div>
            </div>
            <div class="settings-container">
                <?php
                if ($this->message) {
                    echo '<div id="message" class="updated"><p>' . esc_html($this->message) . '</p></div>';
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
                            <th scope="row">
                                <label for="regen">
                                    <?php esc_html_e('Your shop URL', 'agb-connector'); ?>
                                </label>
                            </th>
                            <td><p><code><?php echo esc_attr(home_url()); ?></code></p></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="regen">
                                    <?php esc_html_e('API-Token', 'agb-connector'); ?>
                                </label>
                            </th>
                            <td><p>
                                    <code><?php echo esc_attr(get_option(Plugin::OPTION_USER_AUTH_TOKEN)); ?></code>
                                    <a class="button" href="<?php echo esc_url(wp_nonce_url(
                                        add_query_arg([
                                            'regen' => '',
                                            'page' => 'agb_connector_settings',
                                        ], admin_url('options-general.php')),
                                        'agb-connector-settings-page-regen'
                                    )); ?>">
                                        <?php esc_html_e('Regenerate', 'agb-connector'); ?>
                                    </a>
                                </p>
                                <p class="description">
                                    <?php esc_html_e(
                                        'If you change the token, this must also be adjusted in the client portal.',
                                        'agb-connector'
                                    ); ?>
                                </p>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
                                <label for="page_agb">
                                    <?php esc_html_e('Terms and Conditions', 'agb-connector'); ?>
                                </label>
                            </th>
                            <td>
                                <?php
                                if (empty($textAllocations['agb'])) {
                                    $textAllocations['agb'] = [];
                                }
                                $this->getAllocationHtml($textAllocations['agb'], 'agb');
                                ?>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
                                <label for="page_datenschutz">
                                    <?php esc_html_e('Privacy', 'agb-connector'); ?>
                                </label>
                            </th>
                            <td>
                                <?php
                                if (empty($textAllocations['datenschutz'])) {
                                    $textAllocations['datenschutz'] = [];
                                }
                                $this->getAllocationHtml($textAllocations['datenschutz'], 'datenschutz');
                                ?>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
                                <label for="page_widerruf">
                                    <?php esc_html_e('Revocation', 'agb-connector'); ?>
                                </label>
                            </th>
                            <td>
                                <?php
                                if (empty($textAllocations['widerruf'])) {
                                    $textAllocations['widerruf'] = [];
                                }
                                $this->getAllocationHtml($textAllocations['widerruf'], 'widerruf');
                                ?>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
                                <label for="page_impressum">
                                    <?php esc_html_e('Imprint', 'agb-connector'); ?>
                                </label>
                            </th>
                            <td>
                                <?php
                                if (empty($textAllocations['impressum'])) {
                                    $textAllocations['impressum'] = [];
                                }
                                $this->getAllocationHtml($textAllocations['impressum'], 'impressum', false);
                                ?>
                            </td>
                        </tr>

                    </table>

                    <?php submit_button(__('Save changes', 'agb-connector'), 'primary', 'save'); ?>

                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Generate HTML for page allocations
     *
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
     * phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
     *
     * @param array $allocations
     * @param $type
     * @param bool $wcEmail
     */
    private function getAllocationHtml(array $allocations, $type, $wcEmail = true)
    {
        if (!\function_exists('wc')) {
            $wcEmail = false;
        }
        $locale = get_bloginfo('language');
        list($language, $country) = explode('-', $locale, 2);
        if (! $allocations) {
            $allocations[] = [
                'country' => $country,
                'language' => $language,
                'pageId' => 0,
                'wcOrderEmailAttachment' => false,
            ];
        }
        $emptyPages = wp_dropdown_pages([ //phpcs:ignore
            'name' => 'text_allocation[' . esc_attr($type) . '][\' + size + \'][page_id]',
            'echo' => 0,
            'show_option_none' => esc_html__('&mdash; Select &mdash;', 'agb-connector'),
            'option_none_value' => 0,
            'selected' => 0,
        ]);  //phpcs:ignore
        $emptyPages = str_replace(["\n", '\'', '&#039;'], ['', '"', '\''], $emptyPages);

        $emptyCountryOptions = '';
        foreach (XmlApi::supportedCountries() as $countryCode => $countryText) {
            $emptyCountryOptions .= '<option value="' . $countryCode . '"' .
                                    selected($country, $countryCode, false) .
                                    '>' . $countryText . '</option>';
        }
        $emptyCountryOptions = str_replace(["\n", '\'', '&#039;'], ['', '"', '\''], $emptyCountryOptions);

        $emptyLanguageOptions = '';
        foreach (XmlApi::supportedLanguages() as $languageCode => $languageText) {
            $emptyLanguageOptions .= '<option value="' . $languageCode . '"' .
                                     selected($language, $languageCode, false) .
                                     '>' . $languageText . '</option>';
        }
        $emptyLanguageOptions = str_replace(
            ["\n", '\'', '&#039;'],
            ['', '"', '\''],
            $emptyLanguageOptions
        );
        ?>
        <div class="<?php echo esc_attr($type); ?>_input_table_wrapper">
            <table class="widefat <?php echo esc_attr($type); ?>_input_table" cellspacing="0">
                <thead>
                <tr>
                    <th><?php esc_html_e('Country', 'agb-connector'); ?></th>
                    <th><?php esc_html_e('Language', 'agb-connector'); ?></th>
                    <th><?php esc_html_e('Page', 'agb-connector'); ?></th>
                    <?php if ($wcEmail) { ?>
                        <th><?php esc_html_e('Attach PDF on WooCommerce emails', 'agb-connector'); ?></th>
                    <?php } ?>
                </tr>
                </thead>
                <tbody class="<?php echo esc_attr($type); ?>_pages">
                <?php
                $i = -1;
                if ($allocations) {
                    foreach ($allocations as $allocation) {
                        $i++;

                        echo '<tr class="' . esc_attr($type) . '_page">';
                        echo '<td><select name="text_allocation[' .
                             esc_attr($type) . '][' . esc_attr($i) . '][country]" size="1">';
                        foreach (XmlApi::supportedCountries() as $countryCode => $countryText) {
                            echo '<option value="' . esc_attr($countryCode) . '"' .
                                 selected($allocation['country'], $countryCode, false) .
                                 '>' . esc_attr($countryText) . '</option>';
                        }
                        echo '</select></td>';
                        echo '<td><select name="text_allocation[' .
                             esc_attr($type) . '][' . esc_attr($i) . '][language]" size="1">';
                        foreach (XmlApi::supportedLanguages() as $languageCode => $languageText) {
                            echo '<option value="' . esc_attr($languageCode) . '"' .
                                 selected($allocation['language'], $languageCode, false) .
                                 '>' . esc_attr($languageText) . '</option>';
                        }
                        echo '</select></td>';
                        echo '<td>' . wp_dropdown_pages([ //phpcs:ignore
                                'name' => 'text_allocation[' . esc_attr($type) . '][' . esc_attr($i) . '][page_id]',
                                'echo' => 0,
                                'show_option_none' => esc_html__('&mdash; Select &mdash;', 'agb-connector'),
                                'option_none_value' => 0,
                                'selected' => (int)$allocation['pageId'],
                            ]) . '</td>';
                        if ($wcEmail) {
                            echo '<td><input type="checkbox" value="1" name="text_allocation[' .
                                 esc_attr($type) . '][' . esc_attr($i) . '][wc_email]"' .
                                 checked($allocation['wcOrderEmailAttachment'], true, false) .
                                 ' /></td>';
                        }
                        echo '</tr>';
                    }
                }
                ?>
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="4">
                        <a href="#" class="add button">
                            <?php esc_html_e('+ Add page', 'agb-connector'); ?>
                        </a>&nbsp;
                        <a href="#" class="remove_rows button">
                            <?php esc_html_e('Remove selected pages(s)', 'agb-connector'); ?>
                        </a>
                    </th>
                </tr>
                </tfoot>
            </table>
        </div>
        <script type="text/javascript">
            jQuery(function () {
                jQuery('.<?php echo esc_attr($type); ?>_input_table_wrapper').on('click', 'a.add', function () {
                    var size = jQuery('.<?php echo esc_attr($type); ?>_input_table_wrapper')
                        .find('tbody .<?php echo esc_attr($type); ?>_page').length;
                    jQuery('<tr class="<?php echo esc_attr($type); ?>_page">\
                                <td>\
                                    <select name="text_allocation[<?php echo esc_attr($type); ?>][' + size + '][country]" size="1">\
                                    <?php echo $emptyCountryOptions; //phpcs:ignore?>\
                                </td>\
                                <td>\
                                    <select name="text_allocation[<?php echo esc_attr($type); ?>][' + size + '][language]" size="1">\
                                    <?php echo $emptyLanguageOptions; //phpcs:ignore ?>\
                                    </td>\
                                <td>\
                                    <?php echo $emptyPages; //phpcs:ignore ?>\
                                </td>\<?php if ($wcEmail) { //phpcs:ignore ?>
                                <td>\
                                    <input type="checkbox" value="1" name="text_allocation[<?php echo esc_attr($type); ?>][' +
                                    size + '][wc_email]" />\
                                </td>\<?php } //phpcs:ignore ?>
                            </tr>')
                        .appendTo('.<?php echo esc_attr($type); ?>_input_table_wrapper table tbody');
                    return false;
                });
            });
        </script>
        <?php
    }
}
