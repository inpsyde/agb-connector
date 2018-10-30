<?php # -*- coding: utf-8 -*-

// phpcs:disable NeutronStandard.StrictTypes.RequireStrictTypes

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
     * User auth token that must match
     *
     * @var string
     */
    private $userAuthToken;

    /**
     * Supported actions
     *
     * @var array
     */
    private $supportedActions = [
        'push',
    ];

    /**
     * The text types
     *
     * @var array with txt types
     */
    private $textTypes = ['agb', 'datenschutz', 'widerruf', 'impressum'];

    /**
     * Txt types and post ids
     *
     * @var array
     */
    private $textTypesAllocation;

    /**
     * Supported Language
     *
     * @var string
     */
    private $supportedLanguage = 'de';

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
     * @param array $textTypesAllocation Page ids for Text.
     */
    public function __construct(
        $pluginVersion,
        $userAuthToken,
        array $textTypesAllocation = [
            'agb' => 0,
            'datenschutz' => 0,
            'widerruf' => 0,
            'impressum' => 0,
        ]
    ) {

        $this->pluginVersion = $pluginVersion;
        $this->userAuthToken = $userAuthToken;
        $this->textTypesAllocation = $textTypesAllocation;
    }

    /**
     * Language in with the Text will be stored ISO 639-1.
     *
     * @param string $lang The language code.
     *
     * @return bool
     */
    public function setSupportedLanguage($lang)
    {
        if (! $lang || is_numeric($lang) || 2 !== strlen($lang)) {
            return false;
        }

        $this->supportedLanguage = $lang;

        return true;
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

        $checkPdf = true;
        if (isset($xml->rechtstext_type) && 'impressum' === strtolower((string)$xml->rechtstext_type)) {
            $checkPdf = false;
        }
        $error = $this->checkXmlForError($xml, $checkPdf);
        if ($error) {
            return $this->returnXml($error);
        }

        if ('push' === (string)$xml->action) {
            if (! isset($this->textTypesAllocation[(string)$xml->rechtstext_type])) {
                return $this->returnXml(0);
            }

            $post = get_post($this->textTypesAllocation[(string)$xml->rechtstext_type]);
            if (! $post instanceof WP_Post) {
                return $this->returnXml(99);
            }
            $post->post_content = (string)$xml->rechtstext_html;

            if ('impressum' !== (string)$xml->rechtstext_type) {
                $error = $this->pushPdfFile($xml);
                if ($error) {
                    return $this->returnXml($error);
                }
            }

            $error = $this->savePost($post);

            return $this->returnXml($error);
        }

        return $this->returnXml(99);
    }

    /**
     * Check XML for errors.
     *
     * @since 1.0.0
     *
     * @param SimpleXMLElement $xml The XML object.
     * @param boolean $checkPdf Whether to check the PDF or not..
     *
     * @return int Error code
     */
    public function checkXmlForError($xml, $checkPdf)
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

        if (empty($xml->user_auth_token) || (string)$xml->user_auth_token !== $this->userAuthToken) {
            return 3;
        }

        if (empty($xml->rechtstext_type) || ! in_array((string)$xml->rechtstext_type, $this->textTypes, true)) {
            return 4;
        }

        if (empty($xml->rechtstext_country) || 'DE' !== strtoupper((string)$xml->rechtstext_country)) {
            return 17;
        }

        if (strlen((string)$xml->rechtstext_text) < 50) {
            return 5;
        }

        if (strlen((string)$xml->rechtstext_html) < 50) {
            return 6;
        }

        if ($checkPdf) {
            if (empty($xml->rechtstext_pdf_url)) {
                return 7;
            }

            $pdf = $this->getFile((string)$xml->rechtstext_pdf_url);
            if (empty($pdf) || substr($pdf, 0, 4) !== '%PDF') {
                return 7;
            }

            if (empty($xml->rechtstext_pdf_md5hash) || strtolower((string)$xml->rechtstext_pdf_md5hash) !== md5($pdf)) {
                return 8;
            }
        }

        if (empty($xml->rechtstext_language) || (string)$xml->rechtstext_language !== $this->supportedLanguage) {
            return 9;
        }

        if (empty($xml->action) || ! in_array((string)$xml->action, $this->supportedActions, true)) {
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
    private function returnXml($code = 0)
    {
        global $wp_version;

        $response = '<?xml version="1.0" encoding="UTF-8" ?>' . PHP_EOL;
        $response .= '<response>' . PHP_EOL;
        if (!$code) {
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
        global $wpdb;

        $uploads = wp_upload_dir();
        $file = trailingslashit($uploads['basedir']) . (string)$xml->rechtstext_type . '.pdf';

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

        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $guid = trailingslashit($uploads['baseurl']) . (string)$xml->rechtstext_type . '.pdf';
        $attachmentId = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE guid = %s LIMIT 1", $guid));
        $postParent = 0;
        if (! empty($this->textTypesAllocation[(string)$xml->rechtstext_type])) {
            $postParent = $this->textTypesAllocation[(string)$xml->rechtstext_type];
        }
        $attachment = [
            'post_mime_type' => 'application/pdf',
            'guid' => $guid,
            'post_parent' => $postParent,
            'post_type' => 'attachment',
            'file' => $file,
            'post_title' => (string)$xml->rechtstext_type,
        ];
        if ($attachmentId) {
            $attachment['ID'] = $attachmentId;
            $post_id = wp_update_post($attachment);
        } else {
            $post_id = wp_insert_post($attachment);
        }

        if (is_wp_error($post_id)) {
            return 7;
        }

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
}
