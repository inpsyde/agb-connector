<?php # -*- coding: utf-8 -*-

namespace Inpsyde\AGBConnector;

use Inpsyde\AGBConnector\Document\Factory\WpPostBasedDocumentFactory;
use Inpsyde\AGBConnector\Document\Factory\XmlBasedDocumentFactory;
use Inpsyde\AGBConnector\Document\Repository\DocumentRepository;
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
    protected $userAuthToken;

    /**
     * Define some values.
     *
     * @param string $userAuthToken User Auth Token.
     */
    public function __construct($userAuthToken)
    {
        $this->userAuthToken = $userAuthToken;
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

        $documentFactory = new WpPostBasedDocumentFactory();

        $handler = new MiddlewareRequestHandler(
            $this->userAuthToken,
            new XmlApiSupportedService(),
            new DocumentRepository($documentFactory),
            new XmlBasedDocumentFactory()
        );

        return $handler->handle($xml);
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
}
