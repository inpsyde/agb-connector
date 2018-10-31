<?php # -*- coding: utf-8 -*-

/**
 * Class AGBConnectorShortCodes
 */
class AGBConnectorShortCodes
{

    /**
     * @var array
     */
    private $registeredShortCodes = [];

    /**
     * Settings for All AGB shortcodes.
     *
     * @return array
     */
    public function settings()
    {
        return (array)apply_filters('agb_shortcodes', [
            'agb_terms' => [
                'name' => esc_html__('AGB Terms', 'agb-connector'),
                'setting_key' => 'agb',
            ],
            'agb_privacy' => [
                'name' => esc_html__('AGB Privacy', 'agb-connector'),
                'setting_key' => 'datenschutz',
            ],
            'agb_revocation' => [
                'name' => esc_html__('AGB Revocation', 'agb-connector'),
                'setting_key' => 'widerruf',
            ],
            'agb_imprint' => [
                'name' => esc_html__('AGB Imprint', 'agb-connector'),
                'setting_key' => 'impressum',
            ],
        ]);
    }

    /**
     * Returns settings for a AGB Shortcode.
     *
     * @param $shortcode
     *
     * @return array
     */
    public function get_setting($shortcode)
    {
        $settings = $this->settings();

        return isset($settings[$shortcode]) ? $settings[$shortcode] : [];
    }

    /**
     * Helper function to cleanup and do_shortcode on content.
     *
     * @see do_shortcode()
     *
     * @param $content
     *
     * @return string
     */
    public function callback_content($content)
    {
        $array = [
            '<p>[' => '[',
            ']</p>' => ']',
            '<br /></p>' => '</p>',
            ']<br />' => ']',
        ];

        $content = shortcode_unautop(balanceTags(trim($content), true));
        $content = strtr($content, $array);

        return do_shortcode($content);
    }

    /**
     * Register AGB shortcodes.
     */
    public function setup()
    {
        foreach ($this->settings() as $shortCode => $setting) {
            if (! $setting) {
                return;
            }

            $this->registeredShortCodes[$shortCode] = $shortCode;

            remove_shortcode($shortCode);
            add_shortcode($shortCode, [$this, 'doShortCodeCallback']);
        }
    }

    /**
     * Map AGB shorcodes for Visual Composer.
     *
     * @see https://wpbakery.atlassian.net/wiki/spaces/VC/pages/524332/vc+map
     */
    public function vc_maps()
    {
        $locale = get_bloginfo('language');
        list($language, $country) = explode('-', $locale, 2);

        foreach ($this->settings() as $shortCode => $setting) {
            if (! $setting) {
                return;
            }

            vc_map([
                    'name' => $setting['name'],
                    'base' => $shortCode,
                    'class' => "$shortCode-container",
                    'category' => esc_html__('Content', 'agb-connector'),
                    'params' => [
                        [
                            'type' => 'textfield',
                            'holder' => 'div',
                            'class' => "$shortCode-id",
                            'heading' => esc_html__('Element ID', 'agb-connector'),
                            'param_name' => 'id',
                            'value' => '',
                            /* translators: %s is the w3c specification link. */
                            'description' => sprintf(
                                esc_html__(
                                    'Enter element ID (Note: make sure it is unique and valid according to %s).',
                                    'agb-connector'
                                ),
                                '<a href="https://www.w3schools.com/tags/att_global_id.asp">' . esc_html__(
                                    'w3c specification',
                                    'agb-connector'
                                ) . '</a>'
                            ),
                        ],
                        [
                            'type' => 'textfield',
                            'holder' => 'div',
                            'class' => "$shortCode-class",
                            'heading' => esc_html__('Extra class name', 'agb-connector'),
                            'param_name' => 'class',
                            'value' => '',
                            'description' => esc_html__(
                                'Style particular content element differently - add a class name and refer to it in custom CSS.',
                                'agb-connector'
                            ),
                        ],
                        [
                            'type' => 'dropdown',
                            'holder' => 'div',
                            'class' => "$shortCode-language",
                            'heading' => esc_html__('Select language', 'agb-connector'),
                            'param_name' => 'language',
                            'std' => $language,
                            'value' => AGBConnectorAPI::getSupportedLanguages(),
                            'description' => esc_html__(
                                'Language of text that should be displayed',
                                'agb-connector'
                            ),
                        ],
                        [
                            'type' => 'dropdown',
                            'holder' => 'div',
                            'class' => "$shortCode-country",
                            'heading' => esc_html__('Select country', 'agb-connector'),
                            'param_name' => 'country',
                            'std' => $country,
                            'value' => AGBConnectorAPI::getSupportedCountries(),
                            'description' => esc_html__(
                                'Country of text that should be displayed',
                                'agb-connector'
                            ),
                        ],
                    ],
                ]);
        }
    }

    /**
     * Do the shortcode callback.
     *
     * @param $attr
     * @param string $content
     * @param string $shortcode
     *
     * @return string
     */
    public function doShortCodeCallback($attr, $content = '', $shortCode)
    {
        $setting = $this->get_setting($shortCode);
        if (! $setting || empty($this->registeredShortCodes[$shortCode])) {
            return '';
        }

        $attr = (object)shortcode_atts([
            'id' => '',
            'class' => '',
            'country' => '',
            'language' => '',
        ], $attr, $shortCode);

        if (!$attr->country || !$attr->language) {
            $locale = get_bloginfo('language');
            list($attr->language, $attr->country) = explode('-', $locale, 2);
        }

        $textAllocations = get_option(AGBConnectorKeysInterface::OPTION_TEXT_ALLOCATIONS, []);
        $foundAllocation = [];
        if (isset($textAllocations[$setting['setting_key']])) {
            foreach ($textAllocations[$setting['setting_key']] as $allocation) {
                if (strtolower($attr->country) === $allocation['country'] && strtoupper($attr->language) === $allocation['language']) {
                    $foundAllocation = $allocation;
                    break;
                }
            }
        }

        if (! $foundAllocation) {
            /* translators: %s is the AGB shortcode name. */
            return sprintf(esc_html__('No valid page found for %s.'), $setting['name']);
        }

        // Get the Page Content.
        $pageObject = get_post($foundAllocation['pageId']);
        $pageContent = '';

        if (! is_wp_error($pageObject)) {
            $pageContent = $this->callback_content($pageObject->post_content);
        }

        if (!$pageContent) {
            /* translators: %s is the AGB shortcode name. */
            $pageContent = sprintf(esc_html__('No content found for %s.'), $setting['name']);
        }

        $attr->class = preg_split('#\s+#', $attr->class);
        $id = ('' !== $attr->id) ? 'id="' . $attr->id . '"' : '';
        $classes = ['agb_content', $shortCode];
        $classes = array_merge($classes, $attr->class);
        $classes = implode(' ', array_map('sanitize_html_class', array_unique($classes)));

        // Return output for the shortcode.
        return sprintf(
            '<div %1$s class="%2$s">%3$s</div>',
            esc_attr($id),
            esc_attr($classes),
            $pageContent
        );
    }
}
