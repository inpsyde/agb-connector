<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Middleware;

use Inpsyde\AGBConnector\CustomExceptions\PdfMD5Exception;
use Inpsyde\AGBConnector\CustomExceptions\PdfUrlException;
use Inpsyde\AGBConnector\CustomExceptions\WPFilesystemException;
use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;
use Inpsyde\AGBConnector\Document\Factory\XmlBasedDocumentFactory;
use Inpsyde\AGBConnector\Document\Repository\DocumentRepositoryInterface;
use SimpleXMLElement;
use UnexpectedValueException;
use WP_Filesystem_Base;

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
     * @var DocumentRepositoryInterface
     */
    protected $documentRepository;
    /**
     * @var XmlBasedDocumentFactory
     */
    protected $documentFactory;

    /**
     * CheckPostXml constructor.
     *
     * @param DocumentRepositoryInterface $documentRepository
     * @param XmlBasedDocumentFactory $documentFactory
     */
    public function __construct(
        DocumentRepositoryInterface $documentRepository,
        XmlBasedDocumentFactory $documentFactory
    ){
        $this->documentRepository = $documentRepository;
        $this->documentFactory = $documentFactory;
    }

    /**
     * @param SimpleXMLElement $xml
     *
     * @return bool
     * @throws XmlApiException
     */
    public function process($xml)
    {
        $document = $this->documentFactory->createDocument($xml);
        $savedDocumentId = $this->documentRepository->saveDocument($document);
        $this->pushPdfFile($xml, $savedDocumentId);
        $targetUrl = $this->processPermalink($savedDocumentId);

        return parent::process($targetUrl);
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
    protected function pushPdfFile(SimpleXMLElement $xml, int $documentId)
    {
        $document = $this->documentRepository->getDocumentById($documentId);

        if (! $document->getSettings()->getSavePdf()) {
            return 0;
        }


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

        $result = $this->writeContentToFile($file, $pdf);

        if (!$result) {
            throw new PdfUrlException(
                'WriteContentToFile failed. Result not found'
            );
        }

        if(! function_exists('wp_generate_attachment_metadata')) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        $attachmentId = self::attachmentIdByPostParent($documentId);
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
            'post_parent' => $documentId,
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
                self::FTPHOSTNAME => $ftpCredentials[self::FTPHOSTNAME] ?? '',
                self::FTPUSERNAME => $ftpCredentials[self::FTPUSERNAME] ?? '',
                self::FTPPASSWORD => $ftpCredentials[self::FTPPASSWORD] ?? '',
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

        return $wp_filesystem->put_contents($file, $content);
    }

    /**
     * @param int The id of the saving document.
     *
     * @return false|string
     */
    protected function processPermalink(int $savedDocumentId)
    {
        $targetUrl = '';
        if ('publish' === get_post_status($savedDocumentId)) {
            $targetUrl = get_permalink($savedDocumentId);
        }
        return $targetUrl;
    }
}
