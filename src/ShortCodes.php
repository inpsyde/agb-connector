<?php # -*- coding: utf-8 -*-

namespace Inpsyde\AGBConnector;

use Inpsyde\AGBConnector\Document\DocumentInterface;
use Inpsyde\AGBConnector\Document\Repository\DocumentRepository;

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
     * @var DocumentRepository
     */
    protected $documentRepository;
    /**
     * @var array
     */
    private $registeredShortCodes = [];

    /**
     * ShortCodes constructor.
     *
     * @param array $supportedCountries
     * @param array $supportedLanguages
     * @param DocumentRepository $documentRepository
     */
    public function __construct(array $supportedCountries, array $supportedLanguages, DocumentRepository $documentRepository)
    {
        $this->supportedCountries = $supportedCountries;
        $this->supportedLanguages = $supportedLanguages;
        $this->documentRepository = $documentRepository;
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
                'name' => esc_html__('Terms and Conditions', 'agb-connector'),
                'setting_key' => 'agb',
            ],
            'agb_privacy' => [
                'name' => esc_html__('Privacy', 'agb-connector'),
                'setting_key' => 'datenschutz',
            ],
            'agb_revocation' => [
                'name' => esc_html__('Revocation', 'agb-connector'),
                'setting_key' => 'widerruf',
            ],
            'agb_imprint' => [
                'name' => esc_html__('Imprint', 'agb-connector'),
                'setting_key' => 'impressum',
            ],
        ]);
    }

    /**
     * Return list of shortcodes registered by plugin
     *
     * @return string[]
     */
    public function getShortcodeTags(): array
    {
        return array_keys($this->settings());
    }

    /**
     * Get document type by plugin shortcode.
     *
     * @param string $shortcode
     *
     * @return string Document type or empty string on error.
     */
    public function getDocumentTypeByShortcodeTag(string $shortcode): string
    {
        $shortcodeSettings = $this->settings();

        return isset($shortcodeSettings[$shortcode]) ? $shortcodeSettings[$shortcode]['setting_key'] : '';
    }

    /**
     * Generate a shortcode for the document.
     *
     * @param DocumentInterface $document
     *
     * @return string
     */
    public function generateShortcodeForDocument(DocumentInterface $document): string
    {
        return sprintf(
            '[%1$s country="%2$s" language="%3$s"]',
            $this->getShortcodeTagByDocumentType($document->getType()),
            $document->getCountry(),
            $document->getLanguage()
        );
    }

    /**
     * Return code for document block.
     *
     * This can be used in post content to make WordPress display document as reusable block.
     *
     * @param int $documentId
     *
     * @return string
     */
    public function generateBlockCodeForDocumentId(int $documentId): string
    {
        return sprintf(
            '<!-- wp:block {"ref":%1$d} /-->',
            $documentId
        );
    }

    /**
     * Return shortcode tag for given document type.
     *
     * @param string $documentType
     *
     * @return string
     */
    public function getShortcodeTagByDocumentType(string $documentType): string
    {
        foreach ($this->settings() as $shortcodeTag => $shortcodeConfig) {
            if ($documentType === $shortcodeConfig['setting_key']) {
                return $shortcodeTag;
            }
        }

        return '';
    }

    public function getDocumentTypeNameByType(string $documentType): string
    {
        foreach ($this->settings() as $shortcodeConfig) {
            if ($documentType === $shortcodeConfig['setting_key']) {
                return $shortcodeConfig['name'];
            }
        }

        return '';
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
        if (! function_exists('vc_map')) {
            return;
        }

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

        $documentType = $setting['setting_key'] ?? '';

        $documentId = $this->documentRepository->getDocumentPostIdByTypeCountryAndLanguage(
            $documentType,
            $attr->country,
            $attr->language
        );

        $document = $this->documentRepository->getDocumentById($documentId);

        if (! $document) {
            /* translators: %s is the AGB shortcode name. */
            return sprintf(esc_html__('No valid page found for %s.', 'agb-connector'), $setting['name']);
        }

        $pageContent = $document->getContent();

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
