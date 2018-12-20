<?php # -*- coding: utf-8 -*-

namespace Inpsyde\AGBConnector;

/**
 * Class XmlApi
 */
class XmlApi
{

    /**
     * API Version
     *
     * @var string
     */
    const VERSION = '1.0';

    /**
     * API Username that must match. Left empty fo no checking
     *
     * @var string
     */
    const USERNAME = 'inpsyde';

    /**
     * API Password that must match
     *
     * @var string
     */
    const PASSWORD = 'oIN9pBGPp98g';

    /**
     * User auth token that must match
     *
     * @var string
     */
    private $userAuthToken;

    /**
     * Text allocations
     *
     * @var array
     */
    private $textAllocations;

    /**
     * Define some values.
     *
     * @param string $userAuthToken User Auth Token.
     * @param array $textAllocations allocations for Texts.
     */
    public function __construct($userAuthToken, array $textAllocations = null)
    {
        $this->userAuthToken = $userAuthToken;
        $this->textAllocations = $textAllocations;
    }

    /**
     * Get the request and answers it.
     *
     * phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
     * phpcs:disable Generic.Metrics.NestingLevel.TooHigh
     *
     * @param string $xml XML from push.
     *
     * @return string xml response
     */
    public function handleRequest($xml)
    {
        $xmlErrorState = libxml_use_internal_errors(true);
        $xml = trim(stripslashes($xml));
        if ($xml) {
            $xml = simplexml_load_string($xml);
        }
        libxml_use_internal_errors($xmlErrorState);

        $error = $this->checkXmlForError($xml);
        if ($error) {
            return $this->returnXml($error);
        }

        if (! $this->checkConfiguration()) {
            return $this->returnXml(80);
        }

        if ('push' !== (string)$xml->action) {
            return $this->returnXml(10);
        }

        $foundAllocation = $this->findAllocation($xml);
        if (! $foundAllocation) {
            $foundCountry = false;
            foreach ($this->textAllocations[(string)$xml->rechtstext_type] as $allocation) {
                if ((string)$xml->rechtstext_country === $allocation['country']) {
                    $foundCountry = true;
                    break;
                }
            }
            if (! $foundCountry) {
                return $this->returnXml(17);
            }
            return $this->returnXml(9);
        }

        $post = get_post($foundAllocation['pageId']);
        if (! $post instanceof \WP_Post || 'trash' === $post->post_status) {
            return $this->returnXml(81);
        }

        $post->post_title = trim($xml->rechtstext_title);
        $post->post_content = trim($xml->rechtstext_html);

        $error = $this->pushPdfFile($xml);
        if ($error) {
            return $this->returnXml($error);
        }

        if (! $this->savePost($post)) {
            return $this->returnXml(99);
        }

        return $this->returnXml(0);
    }

    /**
     * Check XML for errors.
     *
     * @since 1.1.0
     *
     * @return bool
     */
    public function checkConfiguration()
    {
        if (! $this->userAuthToken) {
            return false;
        }

        return true;
    }

    /**
     * Check XML for errors.
     *
     * phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
     *
     * @since 1.0.0
     *
     * @param \SimpleXMLElement $xml The XML object.
     *
     * @return int Error code
     */
    public function checkXmlForError($xml)
    {
        if (! $xml || ! $xml instanceof \SimpleXMLElement) {
            return 12;
        }

        if (self::VERSION !== (string)$xml->api_version) {
            return 1;
        }

        if (self::USERNAME !== (string)$xml->api_username &&
            self::PASSWORD !== (string)$xml->api_password
        ) {
            return 2;
        }

        if (null === $xml->user_auth_token || (string)$xml->user_auth_token !== $this->userAuthToken) {
            return 3;
        }

        if (null === $xml->rechtstext_type ||
            ! in_array((string)$xml->rechtstext_type, self::supportedTextTypes(), true)
        ) {
            return 4;
        }

        if (null === $xml->rechtstext_country ||
            ! array_key_exists((string)$xml->rechtstext_country, self::supportedCountries())
        ) {
            return 17;
        }

        if (null === $xml->rechtstext_title || strlen((string)$xml->rechtstext_title) < 3) {
            return 18;
        }

        if (null === $xml->rechtstext_text || strlen((string)$xml->rechtstext_text) < 50) {
            return 5;
        }

        if (null === $xml->rechtstext_html || strlen((string)$xml->rechtstext_html) < 50) {
            return 6;
        }

        if ('impressum' !== (string)$xml->rechtstext_type &&
            (null === $xml->rechtstext_pdf_url || '' === (string)$xml->rechtstext_pdf_url)
            ) {
            return 7;
        }

        if (null === $xml->rechtstext_language || ! array_key_exists(
            (string)$xml->rechtstext_language,
            self::supportedLanguages()
        )) {
            return 9;
        }

        if (null === $xml->action || 'push' !== (string)$xml->action) {
            return 10;
        }

        return 0;
    }

    /**
     * Returns the XML answer
     *
     * @param int $code Error code 0 on success.
     *
     * @return string with xml response
     */
    private function returnXml($code)
    {
        global $wp_version;

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?><response></response>');
        $xml->addChild('status', $code ? 'error' : 'success');
        if ($code) {
            $xml->addChild('error', $code);
        }
        $xml->addChild('meta_shopversion', $wp_version);
        $xml->addChild('meta_modulversion', Plugin::VERSION);
        $xml->addChild('meta_phpversion', PHP_VERSION);

        return $xml->asXML();
    }

    /**
     * Transfers the PDF file to uploads
     *
     * phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
     *
     * @param \SimpleXMLElement $xml The XML Object.
     *
     * @return int returns error code
     */
    private function pushPdfFile(\SimpleXMLElement $xml)
    {
        if ('impressum' === (string)$xml->rechtstext_type) {
            return 0;
        }

        $foundAllocation = $this->findAllocation($xml);
        if (! $foundAllocation) {
            return 9;
        }

        require_once ABSPATH . 'wp-admin/includes/image.php';

        $uploads = wp_upload_dir();
        $file = trailingslashit($uploads['basedir']) .
                $xml->rechtstext_type . '_' .
                $xml->rechtstext_language . '-' .
                $xml->rechtstext_country . '.pdf';

        $pdf = $this->receiveFileContent((string)$xml->rechtstext_pdf_url);
        if (! $pdf || 0 !== strpos($pdf, '%PDF')) {
            return 7;
        }
        if (null === $xml->rechtstext_pdf_md5hash ||
            (string)$xml->rechtstext_pdf_md5hash !== md5($pdf)
        ) {
            return 8;
        }

        $result = $this->writeContentToFile($file, $pdf);
        if (! $result) {
            return 7;
        }

        $attachmentId = self::attachmentIdByPostParent($foundAllocation['pageId']);
        if ($attachmentId && get_attached_file($attachmentId)) {
            update_attached_file($attachmentId, $file);
            wp_generate_attachment_metadata($attachmentId, $file);
            return 0;
        }

        $args = [
            'post_mime_type' => 'application/pdf',
            'post_parent' => (int)$foundAllocation['pageId'],
            'post_type' => 'attachment',
            'file' => $file,
            'post_title' => basename($file),
        ];

        $attachmentId = wp_insert_attachment($args);
        if (! $attachmentId || is_wp_error($attachmentId)) {
            return 7;
        }

        wp_generate_attachment_metadata($attachmentId, $file);

        return 0;
    }

    /**
     * Write content to a file
     *
     * @param string $file
     * @param string $content
     *
     * @return bool
     */
    private function writeContentToFile($file, $content)
    {
        global $wp_filesystem;

        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        if (!$wp_filesystem) {
            WP_Filesystem();
        }

        if (!$wp_filesystem instanceof \WP_Filesystem_Base) {
            return false;
        }

        return $wp_filesystem->put_contents($file, $content, FS_CHMOD_FILE);
    }

    /**
     * Save post and pdf after checks
     *
     * @param \WP_Post $post The post object.
     *
     * @return bool
     */
    private function savePost(\WP_Post $post)
    {
        remove_filter('content_save_pre', 'wp_filter_post_kses');

        $postId = wp_update_post($post);

        return !is_wp_error($postId);
    }

    /**
     * Download a file and return its content.
     *
     * @param string $url The URL to the file.
     *
     * @return string
     */
    private function receiveFileContent($url)
    {
        $response = wp_remote_get($url);
        if (is_wp_error($response) ||
            '200' !== (string)wp_remote_retrieve_response_code($response)
        ) {
            return '';
        }

        return wp_remote_retrieve_body($response);
    }

    /**
     * Find the Allocation for that XML request
     *
     * @param \SimpleXMLElement $xml
     *
     * @return array
     */
    private function findAllocation(\SimpleXMLElement $xml)
    {
        $foundAllocation = [];

        if (! isset($this->textAllocations[(string)$xml->rechtstext_type])) {
            return $foundAllocation;
        }

        foreach ($this->textAllocations[(string)$xml->rechtstext_type] as $allocation) {
            if ((string)$xml->rechtstext_country === $allocation['country'] &&
                (string)$xml->rechtstext_language === $allocation['language']
            ) {
                $foundAllocation = $allocation;
                break;
            }
        }

        return $foundAllocation;
    }

    /**
     * Get attachment id by post id for pdf files
     * @param int $postId
     *
     * @return int
     */
    public static function attachmentIdByPostParent($postId)
    {
        $attachments = get_posts([
            'post_parent' => (int)$postId,
            'post_type' => 'attachment',
            'post_mime_type' => 'application/pdf',
            'numberposts' => 1,
            'fields' => 'ids',
            'suppress_filters' => true,
        ]);

        if ($attachments && isset($attachments[0])) {
            return (int) $attachments[0];
        }

        return 0;
    }

    /**
     * Get Supported languages
     *
     * @return array
     */
    public static function supportedLanguages()
    {
        return [
            'de' => __('German', 'agb-connector'),
            'fr' => __('French', 'agb-connector'),
            'en' => __('English', 'agb-connector'),
            'es' => __('Spanish', 'agb-connector'),
            'it' => __('Italian', 'agb-connector'),
            'nl' => __('Dutch', 'agb-connector'),
            'pl' => __('Polish', 'agb-connector'),
            'sv' => __('Swedish', 'agb-connector'),
            'da' => __('Danish', 'agb-connector'),
            'cs' => __('Czech', 'agb-connector'),
            'sl' => __('Slovenian', 'agb-connector'),
        ];
    }

    /**
     * Get Supported countries
     *
     * @return array
     */
    public static function supportedCountries()
    {
        return [
            'DE' => __('Germany', 'agb-connector'),
            'AT' => __('Austria', 'agb-connector'),
            'CH' => __('Switzerland', 'agb-connector'),
            'SE' => __('Sweden', 'agb-connector'),
            'ES' => __('Spain', 'agb-connector'),
            'IT' => __('Italy', 'agb-connector'),
            'PL' => __('Poland', 'agb-connector'),
            'UK' => __('England', 'agb-connector'),
            'FR' => __('France', 'agb-connector'),
            'BE' => __('Belgium', 'agb-connector'),
            'NL' => __('Netherlands', 'agb-connector'),
            'US' => __('USA', 'agb-connector'),
            'CA' => __('Canada', 'agb-connector'),
            'IE' => __('Ireland', 'agb-connector'),
            'CZ' => __('Czech Republic', 'agb-connector'),
            'DK' => __('Denmark', 'agb-connector'),
            'LU' => __('Luxembourg', 'agb-connector'),
            'SI' => __('Slovenia', 'agb-connector'),
            'AU' => __('Australia', 'agb-connector'),
        ];
    }

    /**
     * Get supported text types
     * @return array
     */
    public static function supportedTextTypes()
    {
        return [
            'agb',
            'datenschutz',
            'widerruf',
            'impressum',
        ];
    }
}
