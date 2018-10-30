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
     * @return bool|array
     */
    public function get_setting($shortcode)
    {
        $settings = $this->settings();

        return isset($settings[$shortcode]) ? $settings[$shortcode] : false;
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
        foreach ($this->settings() as $shortcode => $setting) {
            if (! $setting) {
                return;
            }

            $this->registeredShortCodes[$shortcode] = $shortcode;

            remove_shortcode($shortcode);
            add_shortcode($shortcode, [$this, 'do_shortcode_callback']);
        }
    }

    /**
     * Map AGB shorcodes for Visual Composer.
     *
     * @see https://wpbakery.atlassian.net/wiki/spaces/VC/pages/524332/vc+map
     */
    public function vc_maps()
    {
        foreach ($this->settings() as $shortcode => $setting) {
            if (! $setting) {
                return;
            }

            vc_map([
                    'name' => $setting['name'],
                    'base' => $shortcode,
                    'class' => "$shortcode-container",
                    'category' => esc_html__('Content', 'agb-connector'),
                    'params' => [
                        [
                            'type' => 'textfield',
                            'holder' => 'div',
                            'class' => "$shortcode-id",
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
                            'class' => "$shortcode-class",
                            'heading' => esc_html__('Extra class name', 'agb-connector'),
                            'param_name' => 'class',
                            'value' => '',
                            'description' => esc_html__(
                                'Style particular content element differently - add a class name and refer to it in custom CSS.',
                                'agb-connector'
                            ),
                        ],
                    ],
                ]);
        } // Endforeach().
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
    public function do_shortcode_callback($attr, $content = '', $shortcode)
    {
        $setting = $this->get_setting($shortcode);

        if (! $setting || empty($this->registeredShortCodes[$shortcode])) {
            return '';
        }

        // Get Page ID from settings.
        $pageId = 0;
        $pageSettings = get_option('agb_connector_text_types_allocation', []);

        if (! empty($pageSettings[$setting['setting_key']])) {
            $pageId = (int)$pageSettings[$setting['setting_key']];
        }

        if (! $pageId) {
            /* translators: %s is the AGB shortcode name. */
            return sprintf(esc_html__('No valid page found for %s.'), $setting['name']);
        }

        // Get the Page Content.
        $pageObject = get_post($pageId);

        if (! is_wp_error($pageObject)) {
            $pageContent = $this->callback_content($pageObject->post_content);
        }

        if (empty($pageContent)) {
            /* translators: %s is the AGB shortcode name. */
            $pageContent = sprintf(esc_html__('No content found for %s.'), $setting['name']);
        }

        // Prepare the output.
        $attr = (object)shortcode_atts([
            'id' => '',
            'class' => '',
        ], $attr, $shortcode);

        $attr->class = preg_split('#\s+#', $attr->class);

        $id = ($attr->id !== '') ? 'id="' . $attr->id . '"' : '';
        $classes = ['agb_content', $shortcode];
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
