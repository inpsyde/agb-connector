<?php

namespace Inpsyde\AGBConnector\Middleware;

use Inpsyde\AGBConnector\CustomExceptions\CountryException;
use Inpsyde\AGBConnector\CustomExceptions\GeneralException;
use Inpsyde\AGBConnector\CustomExceptions\LanguageException;
use Inpsyde\AGBConnector\CustomExceptions\PdfMD5Exception;
use Inpsyde\AGBConnector\CustomExceptions\PdfUrlException;
use Inpsyde\AGBConnector\CustomExceptions\PostPageException;
use Inpsyde\AGBConnector\CustomExceptions\TextTypeException;
use Inpsyde\AGBConnector\CustomExceptions\WPFilesystemException;
use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;
use Inpsyde\AGBConnector\Document\DocumentAllocationInterface;
use Inpsyde\AGBConnector\Document\Factory\XmlBasedDocumentFactory;
use Inpsyde\AGBConnector\Document\Map\WpPostMetaFields;
use Inpsyde\AGBConnector\Document\Repository\AllocationRepositoryInterface;
use Inpsyde\AGBConnector\Document\Repository\DocumentRepositoryInterface;
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
     * @var DocumentRepositoryInterface
     */
    protected $documentRepository;
    /**
     * @var AllocationRepositoryInterface
     */
    protected $allocationRepository;
    /**
     * @var XmlBasedDocumentFactory
     */
    protected $documentFactory;

    /**
     * CheckPostXml constructor.
     *
     * @param $textAllocations
     * @param DocumentRepositoryInterface $documentRepository
     * @param AllocationRepositoryInterface $allocationRepository
     * @param XmlBasedDocumentFactory $documentFactory
     */
    public function __construct(
        $textAllocations,
        DocumentRepositoryInterface $documentRepository,
        AllocationRepositoryInterface $allocationRepository,
        XmlBasedDocumentFactory $documentFactory
    ){
        $this->textAllocations = $textAllocations;
        $this->documentRepository = $documentRepository;
        $this->allocationRepository = $allocationRepository;
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
        $allocation = $this->findAllocation($xml);

        if(! $allocation){
            $this->handleNotFoundAllocation($xml);

            // If something went wrong and the problem wasn't found.
            throw new GeneralException(
                'Couldn\'t find post to save document'
            );
        }

        $post = $this->checkPost($allocation->getDisplayingPageId());
        $document = $this->documentFactory->createDocument($xml);
        $this->pushPdfFile($xml);
        $this->documentRepository->saveDocument($document, $allocation->getId());
        $targetUrl = $this->processPermalink($post);

        return parent::process($targetUrl);
    }
    /**
     * Find the Allocation for the document from request
     *
     * @param SimpleXMLElement $xml
     *
     * @return DocumentAllocationInterface
     */
    protected function findAllocation(SimpleXMLElement $xml): DocumentAllocationInterface
    {
        return $this->allocationRepository->getByTypeCountryAndLanguage(
            $xml->offsetGet(WpPostMetaFields::WP_POST_DOCUMENT_TYPE),
            $xml->offsetGet(WpPostMetaFields::WP_POST_DOCUMENT_COUNTRY),
            $xml->offsetGet(WpPostMetaFields::WP_POST_DOCUMENT_LANGUAGE)
        );
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
        if ($foundAllocation->getSavePdf()) {
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
            'post_parent' => $foundAllocation->getId(),
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
     * Detect what is wrong: a type, a country or a language and throw the proper exception.
     *
     * @param $xml
     *
     * @throws CountryException If no such country in user documents.
     * @throws LanguageException If no such language in user documents.
     * @throws TextTypeException If no such document type in user documents.
     */
    protected function handleNotFoundAllocation($xml): void
    {
        $type = $xml->offsetGet(WpPostMetaFields::WP_POST_DOCUMENT_TYPE);

        $language = $xml->offsetGet(WpPostMetaFields::WP_POST_DOCUMENT_LANGUAGE);
        $country = $xml->offsetGet(WpPostMetaFields::WP_POST_DOCUMENT_COUNTRY);

        $allOfType = $this->allocationRepository->getAllOfType($type);

        $this->checkType($type, $allOfType);
        $this->checkCountry($country, $allOfType);
        $this->checkLanguage($language, $allOfType);
    }

    /**
     * Check if page that should display document is available and published.
     *
     * @param int   $postId
     *
     * @return array|WP_Post|null
     * @throws PostPageException
     */
    protected function checkPost(int $postId): void
    {
        $post = get_post($postId);
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
     * Throw an exception if no such type in user documents.
     *
     * @throws TextTypeException
     */
    protected function checkType(string $type, array $allOfType): void
    {
        if(! $allOfType){
            throw new TextTypeException(
                sprintf('The text type %1$s is not found', $type)
            );
        }
    }

    /**
     * Throw an exception if no such country in user documents.
     *
     * @param string $country
     * @param DocumentAllocationInterface[] $allocationsOfType
     *
     * @throws CountryException
     */
    protected function checkCountry(string $country, array $allocationsOfType): void
    {
        foreach($allocationsOfType as $allocation){
            if($allocation->getCountry() === $country){
                return;
            }
        }

        throw new CountryException(
            sprintf('Country %1$s not found', $country)
        );
    }

    /**
     * Throw an exception if no such language in user documents.
     *
     * @param string $language
     * @param DocumentAllocationInterface[] $allocationsOfType
     *
     * @throws LanguageException
     */
    protected function checkLanguage(string $language, array $allocationsOfType): void
    {
        foreach ($allocationsOfType as $allocation){
            if($allocation->getLanguage() === $language){
                return;
            }
        }

        throw new LanguageException(
            sprintf(
                'Language %1$s is not found',
                $language
            )
        );
    }
}
