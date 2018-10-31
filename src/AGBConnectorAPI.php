<?php # -*- coding: utf-8 -*-

/**
 * Class AGBConnectorAPI
 *
 * @since 1.0.0
 */
class AGBConnectorAPI
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
     * The text types
     *
     * @var array with txt types
     */
    public static $textTypes = ['agb', 'datenschutz', 'widerruf', 'impressum'];

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
     * Plugin Version
     *
     * @var string
     */
    private $pluginVersion;

    /**
     * Define some values.
     *
     * @param string $pluginVersion Plugin Version.
     * @param string $userAuthToken User Auth Token.
     * @param array $textAllocations allocations for Texts.
     */
    public function __construct($pluginVersion, $userAuthToken, array $textAllocations = null)
    {
        $this->pluginVersion = $pluginVersion;
        $this->userAuthToken = $userAuthToken;
        $this->textAllocations = $textAllocations;
    }

    /**
     * Get the request and answers it.
     *
     * @param string $xml XML from push.
     *
     * @return string xml response
     */
    public function handleRequest($xml)
    {
        $xml = trim(stripslashes($xml));
        if ($xml) {
            $xml = @simplexml_load_string($xml);
        }

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
            return $this->returnXml(81);
        }

        $post = get_post($foundAllocation['pageId']);
        if (! $post instanceof WP_Post) {
            return $this->returnXml(81);
        }
        if (null !== $xml->rechtstext_title) {
            $post->post_title = (string)$xml->rechtstext_title;
        }
        $post->post_content = (string)$xml->rechtstext_html;

        $error = $this->pushPdfFile($xml);
        if ($error) {
            return $this->returnXml($error);
        }

        $error = $this->savePost($post);

        return $this->returnXml($error);
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

        foreach (self::$textTypes as $textType) {
            if (empty($this->textAllocations[$textType])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check XML for errors.
     *
     * @since 1.0.0
     *
     * @param SimpleXMLElement $xml The XML object.
     *
     * @return int Error code
     */
    public function checkXmlForError($xml)
    {
        if (! $xml || ! $xml instanceof SimpleXMLElement) {
            return 12;
        }

        if (self::VERSION !== (string)$xml->api_version) {
            return 1;
        }

        if (self::USERNAME !== (string)$xml->api_username && self::PASSWORD !== (string)$xml->api_password) {
            return 2;
        }

        if (null === $xml->user_auth_token || (string)$xml->user_auth_token !== $this->userAuthToken) {
            return 3;
        }

        if (null === $xml->rechtstext_type || ! in_array((string)$xml->rechtstext_type, self::$textTypes, true)) {
            return 4;
        }

        if (null === $xml->rechtstext_country || ! array_key_exists(
            (string)$xml->rechtstext_country,
            self::getSupportedCountries()
        )) {
            return 17;
        }

        if (null === $xml->rechtstext_title || strlen((string)$xml->rechtstext_title) < 3) {
            return 18;
        }

        if (strlen((string)$xml->rechtstext_text) < 50) {
            return 5;
        }

        if (strlen((string)$xml->rechtstext_html) < 50) {
            return 6;
        }

        if ('impressum' !== strtolower((string)$xml->rechtstext_type)) {
            if (null === $xml->rechtstext_pdf_url) {
                return 7;
            }

            $pdf = $this->getFile((string)$xml->rechtstext_pdf_url);
            if (empty($pdf) || 0 !== strpos($pdf, '%PDF')) {
                return 7;
            }

            if (null === $xml->rechtstext_pdf_md5hash || strtolower((string)$xml->rechtstext_pdf_md5hash) !== md5($pdf)) {
                return 8;
            }
        }

        if (null === $xml->rechtstext_language || ! array_key_exists(
            (string)$xml->rechtstext_language,
            self::getSupportedLanguages()
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

        $response = '<?xml version="1.0" encoding="UTF-8" ?>' . PHP_EOL;
        $response .= '<response>' . PHP_EOL;
        if (! $code) {
            $response .= '	<status>success</status>' . PHP_EOL;
        } else {
            $response .= '	<status>error</status>' . PHP_EOL;
            $response .= '	<error>' . $code . '</error>' . PHP_EOL;
        }
        if (! empty($wp_version)) {
            $response .= '	<meta_shopversion>' . $wp_version . '</meta_shopversion>' . PHP_EOL;
        }
        if (! empty($this->pluginVersion)) {
            $response .= '	<meta_modulversion>' . $this->pluginVersion . '</meta_modulversion>' . PHP_EOL;
        }
        $response .= '</response>';

        return $response;
    }

    /**
     * Transfers the PDF file to uploads
     *
     * @param SimpleXMLElement $xml The XML Object.
     *
     * @return int returns error code
     * @global $wpdb wpdb
     */
    private function pushPdfFile(SimpleXMLElement $xml)
    {
        if ('impressum' !== (string)$xml->rechtstext_type) {
            return 0;
        }

        $foundAllocation = $this->findAllocation($xml);
        if (! $foundAllocation) {
            return 81;
        }

        $uploads = wp_upload_dir();
        $file = trailingslashit($uploads['basedir']) . $xml->rechtstext_type . '_' . $xml->rechtstext_language . '-' . $xml->rechtstext_country . '.pdf';

        if (file_exists($file)) {
            unlink($file);
        }

        $pdf = $this->getFile((string)$xml->rechtstext_pdf_url);
        if (! $pdf) {
            return 7;
        }
        $result = file_put_contents($file, $pdf);
        chmod($file, 0644);
        if (! $result) {
            return 7;
        }

        $attachmentId = self::getAttachmentIdByPostParent($foundAllocation['postId']);
        if ($attachmentId && get_attached_file($attachmentId)) {
            $result = update_attached_file($attachmentId, $file);
            if (! $result) {
                return 7;
            }
            wp_generate_attachment_metadata($attachmentId, $file);
            return 0;
        }

        $args = [
            'post_mime_type' => 'application/pdf',
            'post_parent' => (int)$foundAllocation['postId'],
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
     * Save post and pdf after checks
     *
     * @param WP_Post $post The post object.
     *
     * @return int returns error code
     */
    private function savePost(WP_Post $post)
    {
        $postId = wp_update_post($post);

        if (is_wp_error($postId)) {
            return 99;
        }

        return 0;
    }

    /**
     * Download a file and return its content.
     *
     * @param string $url The URL to the file.
     *
     * @return false|string
     */
    private function getFile($url)
    {
        $response = wp_remote_get($url);
        if (is_wp_error($response)) {
            return false;
        }

        if ('200' !== (string)wp_remote_retrieve_response_code($response)) {
            return false;
        }

        $content = wp_remote_retrieve_body($response);
        if (empty($content)) {
            return false;
        }

        return $content;
    }

    /**
     * Find the Allocation for that XML request
     *
     * @param SimpleXMLElement $xml
     *
     * @return array
     */
    private function findAllocation(\SimpleXMLElement $xml)
    {
        if (isset($this->textAllocations[(string)$xml->rechtstext_type])) {
            foreach ($this->textAllocations[(string)$xml->rechtstext_type] as $allocation) {
                if ((string)$xml->rechtstext_country === $allocation['country'] && (string)$xml->rechtstext_language === $allocation['language']) {
                    return $allocation;
                }
            }
        }

        return [];
    }

    /**
     * Get attachment id by post id for pdfs
     * @param $postID
     *
     * @return int
     */
    public static function getAttachmentIdByPostParent($postID)
    {
        $attachments = get_posts([
            'post_parent' => (int)$postID,
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
    public static function getSupportedLanguages()
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
    public static function getSupportedCountries()
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
}
