<?php

namespace Inpsyde\AGBConnector\Middleware;

use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;
use Inpsyde\AGBConnector\Plugin;
use Inpsyde\AGBConnector\XmlApiSupportedService;
use SimpleXMLElement;

/**
 * Class MiddlewareRequestHandler
 *
 * @package Inpsyde\AGBConnector\Middleware
 */
class MiddlewareRequestHandler
{
    /**
     * @var MiddlewareInterface
     */
    protected $middleware;
    /**
     * @var string $userAuthToken
     */
    protected $userAuthToken;
    /**
     * @var array $textAllocations
     */
    protected $allocations;
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
    protected $supportedTextTypes;

    /**
     * MiddlewareRequestHandler constructor.
     *
     * @param $userAuthToken
     * @param $allocations
     * @param XmlApiSupportedService $apiSupportedService
     */
    public function __construct($userAuthToken, $allocations, $apiSupportedService)
    {
        $this->userAuthToken = $userAuthToken;
        $this->allocations = $allocations;
        $this->supportedCountries = $apiSupportedService->supportedCountries();
        $this->supportedLanguages = $apiSupportedService->supportedLanguages();
        $this->supportedTextTypes = $apiSupportedService->supportedTextTypes();
        $this->middleware = $this->checkErrorMiddlewareRoute();
    }

    /**
     * @return CheckInstanceSimpleXml
     */
    protected function checkErrorMiddlewareRoute()
    {
        $middleware = new CheckInstanceSimpleXml();
        $middleware->linkWith(new CheckVersionXml())
            ->linkWith(new CheckCredentialsXml())
            ->linkWith(new CheckAuthXml($this->userAuthToken))
            ->linkWith(new CheckTextTypeXml($this->supportedTextTypes))
            ->linkWith(new CheckCountrySetXml($this->supportedCountries))
            ->linkWith(new CheckTitleXml())
            ->linkWith(new CheckTextXml())
            ->linkWith(new CheckHtmlXml())
            ->linkWith(new CheckPdfUrlXml())
            ->linkWith(new CheckPdfFilenameXml())
            ->linkWith(new CheckLanguageXml($this->supportedLanguages))
            ->linkWith(new CheckActionXml())
            ->linkWith(new CheckConfiguration($this->userAuthToken, $this->allocations))
            ->linkWith(new CheckPostXml($this->allocations));
        return $middleware;
    }

    /**
     * The client can configure the chain of middleware objects.
     *
     * @param MiddlewareInterface $middleware
     */
    public function chainOfMiddleware(MiddlewareInterface $middleware)
    {
        $this->middleware = $middleware;
    }

    /**
     * @param SimpleXMLElement $data
     *
     * @return string with xml response
     */
    public function handle($data)
    {
        try {
            $targetUrl = $this->middleware->process($data);

            return $this->returnXmlWithSuccess(0, $targetUrl);
        } catch (XmlApiException $exception) {
            return $this->returnXmlWithError($exception);
        }
    }

    /**
     * Returns the XML positive answer
     *
     * @param int $code Error code 0 on success.
     * @param string $targetUrl The url of the site where to find the legal text
     *
     * @return string with xml response
     */
    public function returnXmlWithSuccess($code, $targetUrl = null)
    {
        global $wp_version;

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?><response></response>');
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
     * @param XmlApiException $exception Error code 0 on success.
     *
     * @return string with xml response
     */
    public function returnXmlWithError($exception)
    {
        global $wp_version;

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?><response></response>');
        $xml->addChild('status', 'error');
        $xml->addChild('error', $exception->getCode());
        $messageChild = $xml->addChild('error_message');
        $node = dom_import_simplexml($messageChild);
        $no = $node->ownerDocument;
        $node->appendChild($no->createCDATASection($exception->getMessage()));
        $xml->addChild('meta_shopversion', $wp_version);
        $xml->addChild('meta_modulversion', Plugin::VERSION);
        $xml->addChild('meta_phpversion', PHP_VERSION);

        return $xml->asXML();
    }
}
