<?php # -*- coding: utf-8 -*-

namespace Inpsyde\AGBConnector;

use Exception;
use Inpsyde\AGBConnector\customExceptions\countryException;
use Inpsyde\AGBConnector\customExceptions\generalException;
use Inpsyde\AGBConnector\customExceptions\languageException;
use Inpsyde\AGBConnector\customExceptions\pdfMD5Exception;
use Inpsyde\AGBConnector\customExceptions\pdfUrlException;
use Inpsyde\AGBConnector\customExceptions\postPageException;
use Inpsyde\AGBConnector\Middleware\MiddlewareRequestHandler;

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
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
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

        $handler = new MiddlewareRequestHandler($this->userAuthToken, $this->textAllocations);
        $error = $handler->handle($xml);
        if ($error) {
            return $this->returnXmlWithError($error);
        }

        try {
            $foundAllocation = $this->findAllocation($xml);
            if (! $foundAllocation) {
                $foundCountry = false;
                foreach ($this->textAllocations[(string)$xml->rechtstext_type] as $allocation) {
                    if ((string)$xml->rechtstext_country === $allocation['country']) {
                        $foundCountry = true;
                        break;
                    }
                }
                if (!$foundCountry) {
                    throw new countryException(
                        "Country Exception: not found {$xml->rechtstext_country} provided",
                        17
                    );
                }
                throw new languageException(
                    'languageException: Allocation not found',
                    9
                );
            }
        }
        catch (countryException $exception) {
            return $this->returnXmlWithError($exception);
        }
        catch (languageException $exception) {
            return $this->returnXmlWithError($exception);
        }

        try {
            $post = get_post($foundAllocation['pageId']);
            if (! $post instanceof \WP_Post) {
                throw new postPageException(
                    'postPageException: not a Post provided',
                    81
                );
            }
            if ('trash' === $post->post_status) {
                throw new postPageException(
                    'postPageException: Post status trash',
                    81
                );
            }
        }
        catch (postPageException $exception){
            return $this->returnXmlWithError($exception);
        }

        $post->post_title = trim($xml->rechtstext_title);
        $post->post_content = trim($xml->rechtstext_html);
        $error = $this->pushPdfFile($xml);
        if ($error) {
            return $this->returnXmlWithError($error);
        }
        try {
            if (! $this->savePost($post)) {
                throw new generalException(
                    'generalException: savePost failed',
                    99
                );
            }
        }catch (generalException $exception){
            return $this->returnXmlWithError($exception);
        }

        $targetUrl = '';
        if ('publish' === $post->post_status) {
            $targetUrl = get_permalink($post);
        }

        return $this->returnXmlWithSuccess(0, $targetUrl);
    }

    /**
     * Returns the XML positive answer
     *
     * @param int $code Error code 0 on success.
     * @param string $targetUrl The url of the site where to find the legal text
     *
     * @return string with xml response
     */
    public function returnXmlWithSuccess($code, $targetUrl= null)
    {
        global $wp_version;

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?><response></response>');
        $xml->addChild('status', 'success');
        if (!$code && $targetUrl) {
            $targetUrlChild = $xml->addChild('target_url');
            $node = dom_import_simplexml($targetUrlChild);
            $no = $node->ownerDocument;
            $node->appendChild($no->createCDATASection($targetUrl));
        }
        $xml->addChild('meta_shopversion', $wp_version);
        $xml->addChild('meta_modulversion', Plugin::VERSION);
        $xml->addChild('meta_phpversion', PHP_VERSION);

        return $xml->asXML();
    }

    /**
     * Returns the XML answer with the error
     *
     * @param Exception $exception Error code 0 on success.
     *
     * @return string with xml response
     */
    public function returnXmlWithError($exception)
    {
        global $wp_version;

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?><response></response>');
        $xml->addChild('status',  'error');
        if ($exception) {
            $xml->addChild('error', $exception->getCode());
            $messageChild = $xml->addChild('error_message');
            $node = dom_import_simplexml($messageChild);
            $no = $node->ownerDocument;
            $node->appendChild($no->createCDATASection($exception->getMessage()));
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
        try {
            $foundAllocation = $this->findAllocation($xml);
            if (! $foundAllocation) {
                throw new languageException(
                    'languageException: Allocation not found',
                    9
                );
            }
        }catch (languageException $exception){
            return $exception;
        }

        require_once ABSPATH . 'wp-admin/includes/image.php';

        $uploads = wp_upload_dir();

        $file = trailingslashit($uploads['basedir']) .
                trim((string)$xml->rechtstext_pdf_filename_suggestion);

        try {
            $pdf = $this->receiveFileContent((string)$xml->rechtstext_pdf_url);
            if (! $pdf ) {
                throw new pdfUrlException(
                    'pdfUrlException: pdf not found',
                    7
                );
            }
            if (0 !== strpos($pdf, '%PDF')) {
                throw new pdfUrlException(
                    'pdfUrlException: file is not pdf',
                    7
                );
            }
        }catch (pdfUrlException $exception){
            return $exception;
        }
        try {
            if (null === $xml->rechtstext_pdf_md5hash ) {
                throw new pdfMD5Exception(
                    'pdfMD5Exception: pdf MD5 is null',
                    8
                );
            }
            if ((string)$xml->rechtstext_pdf_md5hash !== md5($pdf)) {
                throw new pdfMD5Exception(
                    'pdfMD5Exception: MD5 hash does not match',
                    8
                );
            }
        }catch (pdfMD5Exception $exception){
            return $exception;
        }
        try {
            $result = $this->writeContentToFile($file, $pdf);
            if (! $result ) {
                throw new pdfUrlException(
                    'pdfUrlException: writeContentToFile failed. Result not found',
                    7
                );
            }
        }catch (pdfUrlException $exception){
            return $exception;
        }

        $attachmentId = self::attachmentIdByPostParent($foundAllocation['pageId']);
        if ($attachmentId && get_attached_file($attachmentId)) {
            update_attached_file($attachmentId, $file);
            wp_generate_attachment_metadata($attachmentId, $file);
            return 0;
        }

        $title = $xml->rechtstext_title . ' (' .
                 $xml->rechtstext_language . '-' .
                 $xml->rechtstext_country . ')';

        $args = [
            'post_mime_type' => 'application/pdf',
            'post_parent' => (int)$foundAllocation['pageId'],
            'post_type' => 'attachment',
            'file' => $file,
            'post_title' => $title,
        ];
        try {
            $attachmentId = wp_insert_attachment($args);
            if (! $attachmentId ) {
                throw new pdfUrlException(
                    'pdfUrlException: wp_insert_attachment failed. $attachmentId not found',
                    7
                );
            }
            if (is_wp_error($attachmentId)) {
                throw new pdfUrlException(
                    'pdfUrlException: is_wp_error thrown',
                    7
                );
            }
        }catch (pdfUrlException $exception){
            return $exception;
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

        return $wp_filesystem->put_contents($file, $content);
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
        $response = wp_remote_get($url, ['timeout' => 30]);
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
            'pt' => __('Portuguese', 'agb-connector'),
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
            'GB' => __('England', 'agb-connector'),
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
            'PT' => __('Portugal', 'agb-connector'),
        ];
    }

    /**
     * Get supported text types
     * @return array
     */
    public static function supportedTextTypes()
    {
        return [
            'agb' => __('Terms and Conditions', 'agb-connector'),
            'datenschutz' => __('Privacy', 'agb-connector'),
            'widerruf' => __('Revocation', 'agb-connector'),
            'impressum' => __('Imprint', 'agb-connector'),
        ];
    }
}
