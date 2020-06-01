<?php

namespace Inpsyde\AGBConnector\Middleware;

use Inpsyde\AGBConnector\CustomExceptions\CountryException;
use Inpsyde\AGBConnector\CustomExceptions\GeneralException;
use Inpsyde\AGBConnector\CustomExceptions\LanguageException;
use Inpsyde\AGBConnector\CustomExceptions\PdfMD5Exception;
use Inpsyde\AGBConnector\CustomExceptions\PdfUrlException;
use Inpsyde\AGBConnector\CustomExceptions\PostPageException;
use Inpsyde\AGBConnector\CustomExceptions\WPFilesystemException;
use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;
use SimpleXMLElement;
use UnexpectedValueException;
use WP_Filesystem_Base;
use WP_Post;

/**
 * Class CheckPostXml
 *
 * @package Inpsyde\AGBConnector\Middleware
 */
class CheckPostXml extends Middleware
{
    const FTPHOSTNAME = 'hostname';
    const FTPUSERNAME = 'username';
    const FTPPASSWORD = 'password';
    /**
     * @var array $textAllocations
     */
    protected $textAllocations;

    /**
     * CheckPostXml constructor.
     *
     * @param $textAllocations
     */
    public function __construct($textAllocations)
    {
        $this->textAllocations = $textAllocations;
    }

    /**
     * @param SimpleXMLElement $xml
     *
     * @return bool
     * @throws XmlApiException
     */
    public function process($xml)
    {
        $foundAllocation = $this->processAllocation($xml);
        $post = $this->processPost($xml, $foundAllocation);
        $this->pushPdfFile($xml);
        $this->processSavePost($post);
        $targetUrl = $this->processPermalink($post);

        return parent::process($targetUrl);
    }
    /**
     * Find the Allocation for that XML request
     *
     * @param SimpleXMLElement $xml
     *
     * @return array
     */
    protected function findAllocation(SimpleXMLElement $xml)
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
     * Transfers the PDF file to uploads
     *
     * phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
     * @param SimpleXMLElement $xml The XML Object.
     *
     * @return int returns error code
     * @throws XmlApiException
     *
     */
    protected function pushPdfFile(SimpleXMLElement $xml)
    {
        if ('impressum' === (string)$xml->rechtstext_type) {
            return 0;
        }
        $foundAllocation = $this->findAllocation($xml);
        if (!$foundAllocation) {
            throw new LanguageException(
                'The allocation was not found'
            );
        }

        require_once ABSPATH . 'wp-admin/includes/image.php';

        $uploads = wp_upload_dir();

        $file = trailingslashit($uploads['basedir']) .
            trim((string)$xml->rechtstext_pdf_filename_suggestion);

        $pdf = $this->receiveFileContent((string)$xml->rechtstext_pdf_url);
        if (!$pdf) {
            throw new PdfUrlException(
                'Pdf not found'
            );
        }
        if (strpos($pdf, '%PDF') !== 0) {
            throw new PdfUrlException(
                'The file provided is not pdf'
            );
        }
        if ($xml->rechtstext_pdf_md5hash === null) {
            throw new PdfMD5Exception(
                'The pdf hash provided is null'
            );
        }
        if ((string)$xml->rechtstext_pdf_md5hash !== md5($pdf)) {
            throw new PdfMD5Exception(
                'The pdf hash does not match'
            );
        }
        if ($foundAllocation['savePdfFile'] === '1') {
            $result = $this->writeContentToFile($file, $pdf);
            if (!$result) {
                throw new PdfUrlException(
                    'WriteContentToFile failed. Result not found'
                );
            }
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
        $attachmentId = wp_insert_attachment($args);
        if (!$attachmentId) {
            throw new PdfUrlException(
                'Insert attachment failed'
            );
        }
        if (is_wp_error($attachmentId)) {
            throw new PdfUrlException(
                'An error occurred while inserting the attachment'
            );
        }
        wp_generate_attachment_metadata($attachmentId, $file);

        return 0;
    }
    /**
     * Save post and pdf after checks
     *
     * @param WP_Post $post The post object.
     *
     * @return bool
     */
    protected function savePost(WP_Post $post)
    {
        remove_filter('content_save_pre', 'wp_filter_post_kses');

        $postId = wp_update_post($post);

        add_filter('content_save_pre', 'wp_filter_post_kses');

        return !is_wp_error($postId);
    }
    /**
     * Download a file and return its content.
     *
     * @param string $url The URL to the file.
     *
     * @return string
     */
    protected function receiveFileContent($url)
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
     * Get attachment id by post id for pdf files
     * @param int $postId
     *
     * @return int
     */
    public static function attachmentIdByPostParent($postId)
    {
        $attachments = get_posts(
            [
                'post_parent' => (int)$postId,
                'post_type' => 'attachment',
                'post_mime_type' => 'application/pdf',
                'numberposts' => 1,
                'fields' => 'ids',
                'suppress_filters' => true,
            ]
        );

        if ($attachments && isset($attachments[0])) {
            return (int) $attachments[0];
        }

        return 0;
    }

    /**
     * Write content to a file
     *
     * @param string $file
     * @param string $content
     *
     * @return bool
     * @throws WPFilesystemException
     */
    private function writeContentToFile($file, $content)
    {
        global $wp_filesystem;

        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH
                . '/wp-admin/includes/file.php';
        }
        $args = [];
        $ftpCredentials = get_option('ftp_credentials');
        if (is_array($ftpCredentials)) {
            $args = [
                self::FTPHOSTNAME => self::findKeyOrDefault($ftpCredentials, self::FTPHOSTNAME, ''),
                self::FTPUSERNAME => self::findKeyOrDefault($ftpCredentials, self::FTPUSERNAME, ''),
                self::FTPPASSWORD => self::findKeyOrDefault($ftpCredentials, self::FTPPASSWORD, ''),
            ];
        }

        $initilized = WP_Filesystem($args);

        if (!$initilized || !$wp_filesystem instanceof WP_Filesystem_Base) {
            throw new UnexpectedValueException('Wp_FileSystem cannot be initialized');
        }

        if ($wp_filesystem->errors->has_errors()) {
            throw new WPFilesystemException(
                $wp_filesystem->errors,
                "There where problems in setup the filesystem {$wp_filesystem->method}"
            );
        }

        if (!$wp_filesystem instanceof WP_Filesystem_Base) {
            return false;
        }

        return $wp_filesystem->put_contents($file, $content);
    }

    protected static function findKeyOrDefault(array $haystack, $key, $default)
    {
        return isset($haystack[$key]) ? $haystack[$key] : $default;
    }

    /**
     *
     * @param $xml
     *
     * @return array
     * @throws CountryException
     * @throws LanguageException
     */
    protected function processAllocation($xml)
    {
        $foundAllocation = $this->findAllocation($xml);
        if (!$foundAllocation) {
            $this->processCountry($xml);
            throw new LanguageException(
                'Allocation not found'
            );
        }
        return $foundAllocation;
    }

    /**
     * @param       $xml
     * @param array $foundAllocation
     *
     * @return array|WP_Post|null
     * @throws PostPageException
     */
    protected function processPost($xml, array $foundAllocation)
    {
        $post = get_post($foundAllocation['pageId']);
        if (!$post instanceof WP_Post) {
            throw new PostPageException(
                'No post page provided'
            );
        }
        if ('trash' === $post->post_status) {
            throw new PostPageException(
                'The post status seems to be trash'
            );
        }
        $post->post_title = trim($xml->rechtstext_title);
        $post->post_content = trim($xml->rechtstext_html);
        return $post;
    }

    /**
     * @param $post
     *
     * @throws GeneralException
     */
    protected function processSavePost($post)
    {
        if (!$this->savePost($post)) {
            throw new GeneralException(
                'Failed to save the post'
            );
        }
    }

    /**
     * @param $post
     *
     * @return false|string
     */
    protected function processPermalink($post)
    {
        $targetUrl = '';
        if ('publish' === $post->post_status) {
            $targetUrl = get_permalink($post);
        }
        return $targetUrl;
    }

    /**
     * @param $xml
     *
     * @throws CountryException
     */
    protected function processCountry($xml)
    {
        $foundCountry = false;
        foreach ($this->textAllocations[(string)$xml->rechtstext_type] as $allocation) {
            if ((string)$xml->rechtstext_country === $allocation['country']) {
                $foundCountry = true;
                break;
            }
        }
        if (!$foundCountry) {
            throw new CountryException(
                "Country {$xml->rechtstext_country} not found"
            );
        }
    }
}
