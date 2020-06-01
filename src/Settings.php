<?php # -*- coding: utf-8 -*-

namespace Inpsyde\AGBConnector;

use Walker_PageDropdown;

use function array_key_exists;
use function function_exists;
use function wp_insert_post;

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
     * @var array $supportedCountries
     */
    protected $supportedCountries;
    /**
     * @var array
     */
    protected $supportedLanguages;
    /**
     * @var array
     */
    protected $supportedTextTypes;

    /**
     * Settings constructor.
     *
     * @param array $supportedCountries
     * @param array $supportedLanguages
     * @param array $supportedTextTypes
     */
    public function __construct(
        array $supportedCountries,
        array $supportedLanguages,
        array $supportedTextTypes
    ) {

        $this->supportedCountries = $supportedCountries;
        $this->supportedLanguages = $supportedLanguages;
        $this->supportedTextTypes = $supportedTextTypes;
    }

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
        wp_enqueue_style(
            'agb-connector',
            plugins_url('/assets/css/style.css', __DIR__),
            [],
            Plugin::VERSION,
            'all'
        );

        wp_enqueue_script(
            'agb-connector',
            plugins_url('/assets/js/settings.js', __DIR__),
            ['jquery'],
            Plugin::VERSION,
            true
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
        $supportedTextTypes = $this->supportedTextTypes;
        $textAllocations = [];
        foreach ($postTextAllocation as $type => $allocations) {
            if (! array_key_exists($type, $supportedTextTypes)) {
                continue;
            }
            foreach ($allocations as $allocation) {
                if (! array_key_exists($allocation['country'], $this->supportedCountries)) {
                    continue;
                }
                if (! array_key_exists($allocation['language'], $this->supportedLanguages)) {
                    continue;
                }
                if ('create' === $allocation['page_id']) {
                    $postArray = [
                        'post_type' => 'page',
                        'post_title' => $supportedTextTypes[$type] . ' (' .
                                        $allocation['language'] . '_' .
                                        $allocation['country'] . ')',
                        'post_content' => '',
                        'comment_status' => 'closed',
                        'ping_status' => 'closed',
                    ];
                    $allocation['page_id'] = wp_insert_post($postArray);
                }
                if ($allocation['page_id'] <= 0 || ! get_post(absint($allocation['page_id']))) {
                    continue;
                }
                $textAllocations[$type][] = [
                    'country' => $allocation['country'],
                    'language' => $allocation['language'],
                    'pageId' => absint($allocation['page_id']),
                    'wcOrderEmailAttachment' => ! empty($allocation['wc_email']),
                    'savePdfFile' => $allocation['savePdfFile'],
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
                            <td>
                                <p>
                                    <code>
                                        <?php
                                            //Directly use option that that WPML can't change it
                                            $homeUrl = trailingslashit(get_option('home'));
                                            $homeUrl = set_url_scheme($homeUrl);
                                            echo esc_attr($homeUrl);
                                        ?>
                                    </code>
                                </p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="regen">
                                    <?php esc_html_e('API-Token', 'agb-connector'); ?>
                                </label>
                            </th>
                            <td>
                                <p>
                                    <code><?php echo esc_attr(get_option(Plugin::OPTION_USER_AUTH_TOKEN)); ?></code>
                                    <a class="button" href="
                                    <?php echo esc_url(
                                        wp_nonce_url(
                                            add_query_arg(
                                                [
                                                    'regen' => '',
                                                    'page' => 'agb_connector_settings',
                                                ],
                                                admin_url('options-general.php')
                                            ),
                                            'agb-connector-settings-page-regen'
                                        )
                                    );
                                    ?>">
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
    protected function getAllocationHtml(array $allocations, $type, $wcEmail = true)
    {
        if (!function_exists('wc')) {
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
                'savePdfFile' => true,
            ];
        }

        $emptyPages = $this->dropdownPages(-1);

        $emptyCountryOptions = '';
        foreach ($this->supportedCountries as $countryCode => $countryText) {
            $emptyCountryOptions .= '<option value="' . $countryCode . '"' .
                                    selected($country, $countryCode, false) .
                                    '>' . $countryText . '</option>';
        }

        $emptyLanguageOptions = '';
        foreach ($this->supportedLanguages as $languageCode => $languageText) {
            $emptyLanguageOptions .= '<option value="' . $languageCode . '"' .
                                     selected($language, $languageCode, false) .
                                     '>' . $languageText . '</option>';
        }
        ?>
        <div class="<?php echo esc_attr($type); ?>_input_table_wrapper">
            <table class="widefat <?php echo esc_attr($type); ?>_input_table" cellspacing="0">
                <thead>
                <tr>
                    <th><?php esc_html_e('Country', 'agb-connector'); ?></th>
                    <th><?php esc_html_e('Language', 'agb-connector'); ?></th>
                    <th><?php esc_html_e('Page', 'agb-connector'); ?></th>
                    <?php if (esc_attr($type) !== 'impressum') { ?>
                    <th><?php esc_html_e('Store Pdf File', 'agb-connector'); ?></th>
                    <?php } ?>
                    <?php if ($wcEmail) { ?>
                        <th id="mailOptTitle"><?php
                            esc_html_e('Attach PDF on WooCommerce emails', 'agb-connector'); ?>
                        </th>
                    <?php } ?>
                    <th colspan="6">&nbsp;</th>
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
                        foreach ($this->supportedCountries as $countryCode => $countryText) {
                            echo '<option value="' . esc_attr($countryCode) . '"' .
                                 selected($allocation['country'], $countryCode, false) .
                                 '>' . esc_attr($countryText) . '</option>';
                        }
                        echo '</select></td>';
                        echo '<td><select name="text_allocation[' .
                             esc_attr($type) . '][' . esc_attr($i) . '][language]" size="1">';
                        foreach ($this->supportedLanguages as $languageCode => $languageText) {
                            echo '<option value="' . esc_attr($languageCode) . '"' .
                                 selected($allocation['language'], $languageCode, false) .
                                 '>' . esc_attr($languageText) . '</option>';
                        }
                        echo '</select></td>';
                        echo '<td><select name="text_allocation[' .
                            esc_attr($type) . '][' . esc_attr($i) . '][page_id]" size="1">';
                        echo $this->dropdownPages((int)$allocation['pageId']);  //phpcs:ignore
                        echo '</select></td>';
                        if (esc_attr($type) !== 'impressum') {
                            echo '<td><input type="checkbox" class="pdfOption" value="1" name="text_allocation[' .
                                esc_attr($type) . '][' . esc_attr($i) . '][savePdfFile]"' .
                                checked($allocation['savePdfFile'], true, false) .
                                ' /></td>';
                        }
                        if ($wcEmail && $allocation['savePdfFile']) {
                            echo '<td><input type="checkbox" value="1" name="text_allocation[' .
                                 esc_attr($type) . '][' . esc_attr($i) . '][wc_email]"' .
                                 checked($allocation['wcOrderEmailAttachment'], true, false) .
                                 ' /></td>';
                        } else {//phpcs:ignore
                            echo '<td id="text_allocation[' .
                                esc_attr($type) . '][' . esc_attr($i) . '][hidden]"></td>';
                        }
                        echo '<td><a class="remove" style="float:right" href="#" title="'
                            . esc_html__('Delete page', 'agb-connector') .
                            '"><span class="dashicons dashicons-trash"></span></a></td>';

                        echo '</tr>';
                    }
                }
                ?>
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="6">
                        <a href="#" class="add button">
                            <?php esc_html_e('+ Add page', 'agb-connector'); ?>
                        </a>&nbsp;
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
                                    <?php echo substr(json_encode($emptyCountryOptions, JSON_HEX_APOS), 1, -1); //phpcs:ignore?>\
                                    </select>\
                                </td>\
                                <td>\
                                    <select name="text_allocation[<?php echo esc_attr($type); ?>][' + size + '][language]" size="1">\
                                    <?php echo substr(json_encode($emptyLanguageOptions, JSON_HEX_APOS), 1, -1); //phpcs:ignore ?>\
                                    </select>\
                                </td>\
                                <td>\
                                    <select name="text_allocation[<?php echo esc_attr($type); ?>][' + size + '][page_id]" size="1">\
                                    <?php echo substr(json_encode($emptyPages, JSON_HEX_APOS), 1, -1); //phpcs:ignore ?>\
                                    </select>\
                                </td>\<?php if ($wcEmail) { //phpcs:ignore ?>
                                <td>\
                                    <input type="checkbox" value="1" name="text_allocation[<?php echo esc_attr($type); ?>][' +
                                    size + '][wc_email]" />\
                                </td>\<?php } //phpcs:ignore ?>
                                <td>\
                                    <input type="checkbox" value="1" name="text_allocation[<?php echo esc_attr($type); ?>][' +
                                    size + '][savePdfFile]" checked/>\
                                </td>\
                                <td>\
                                    <a class="remove" href="#" title="<?php esc_html_e('Delete page', 'agb-connector'); ?>">\
                                    <span class="dashicons dashicons-trash"></span></a>\
                                </td>\
                            </tr>')
                        .appendTo('.<?php echo esc_attr($type); ?>_input_table_wrapper table tbody');
                    removePages();
                    return false;
                });
                jQuery('.<?php echo esc_attr($type); ?>_input_table_wrapper')
                    .on('click', 'input[name^="text_allocation"]', function (event) {
                    let checked = event.currentTarget.checked
                    let optName = event.currentTarget.name
                    let mailName = optName.substring(0, optName.length - 12).concat("wc_email]")
                    let mailCheckbox = jQuery('[name="' + mailName + '"]')
                    if (!checked) {
                        mailCheckbox.hide()
                    } else {
                        if(mailCheckbox.length === 0){
                            let hiddenName = optName.substring(0, optName.length - 12).concat("hidden]")
                            document.getElementById(hiddenName).remove()
                            let optBlock = jQuery('[name="' + optName + '"]').parent("td")
                            jQuery(optBlock).after('<td>\<input type="checkbox" value="1" name="'+ mailName +'"/>\</td>')
                        }else{
                            mailCheckbox.show()
                        }
                    }
                })
            });
        </script>
        <?php
    }

    /**
     * Pages Dropdown
     *
     * @param int $selected
     *
     * @return string
     */
    protected function dropdownPages($selected)
    {
        $output = "\t<option value=\"-1\">" . esc_html__('&mdash; Select &mdash;', 'agb-connector') .
                   "</option>\n";
        $output .= "\t<option value=\"create\">" .
                   esc_html__('&mdash; Create new page &mdash;', 'agb-connector') . "</option>\n";

        //Use get_posts with suppress_filters so that we get unfiltered pages list (work with WPML)
        $pages = get_posts([
            'post_status' => ['publish', 'draft', 'pending', 'future'],
            'post_type' => 'page',
            'suppress_filters' => true,
            'numberposts' => -1,
            'order' => 'ASC',
            'orderby' => 'post_title',
        ]);

        if ($pages) {
            $walker = new Walker_PageDropdown();
            $output .= $walker->walk($pages, 0, [
                    'selected' => $selected,
            ]);
        }

        return $output;
    }
}
