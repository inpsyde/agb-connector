<?php # -*- coding: utf-8 -*-

namespace Inpsyde\AGBConnector;

/**
 * Class ShortCodes
 */
class ShortCodes
{
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
    private $registeredShortCodes = [];

    /**
     * ShortCodes constructor.
     *
     * @param array $supportedCountries
     * @param array $supportedLanguages
     */
    public function __construct(array $supportedCountries, array $supportedLanguages)
    {
        $this->supportedCountries = $supportedCountries;
        $this->supportedLanguages = $supportedLanguages;
    }
    
    /**
     * settings for All AGB shortcodes.
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
     * Helper function to cleanup and do_shortcode on content.
     *
     * @see do_shortcode()
     *
     * @param $content
     *
     * @return string
     */
    public function callbackContent($content)
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
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
     *
     * @see https://wpbakery.atlassian.net/wiki/spaces/VC/pages/524332/vc+map
     */
    public function vcMaps()
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
                            'description' => sprintf(
                                /* translators: %s is the w3c specification link. */
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
                            'value' => $this->supportedLanguages,
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
                            'value' => $this->supportedCountries,
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
     * phpcs:disable Generic.Metrics.NestingLevel.TooHigh
     *
     * @param $attr
     * @param string $content
     * @param string $shortCode
     *
     * @return string
     */
    public function doShortCodeCallback($attr, $content, $shortCode)
    {
        $settings = $this->settings();
        $setting = isset($settings[$shortCode]) ? $settings[$shortCode] : [];
        if (! $setting || empty($this->registeredShortCodes[$shortCode])) {
            return '';
        }

        $attr = (object)shortcode_atts([
            'id' => '',
            'class' => '',
            'country' => '',
            'language' => '',
        ], $attr, $shortCode);

        $locale = get_bloginfo('language');
        list($language, $country) = explode('-', $locale, 2);
        if (!$attr->country) {
            $attr->country = $country;
        }
        if (!$attr->language) {
            $attr->language = $language;
        }

        $textAllocations = get_option(Plugin::OPTION_TEXT_ALLOCATIONS, []);
        $foundAllocation = [];
        if (isset($textAllocations[$setting['setting_key']])) {
            foreach ($textAllocations[$setting['setting_key']] as $allocation) {
                if (strtoupper($attr->country) === $allocation['country'] &&
                    strtolower($attr->language) === $allocation['language']
                ) {
                    $foundAllocation = $allocation;
                    break;
                }
            }
        }

        if (! $foundAllocation) {
            /* translators: %s is the AGB shortcode name. */
            return sprintf(esc_html__('No valid page found for %s.', 'agb-connector'), $setting['name']);
        }

        // Get the Page Content.
        $pageObject = get_post($foundAllocation['pageId']);
        $pageContent = '';

        if (! is_wp_error($pageObject)) {
            $pageContent = $this->callbackContent($pageObject->post_content);
        }

        if (!$pageContent) {
            /* translators: %s is the AGB shortcode name. */
            $pageContent = sprintf(esc_html__('No content found for %s.', 'agb-connector'), $setting['name']);
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
