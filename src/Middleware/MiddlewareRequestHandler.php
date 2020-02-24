<?php

namespace Inpsyde\AGBConnector\Middleware;

use Exception;
use Inpsyde\AGBConnector\CustomExceptions\NotSimpleXmlInstanceException;

/**
 * Class MiddlewareRequestHandler
 *
 * @package Inpsyde\AGBConnector\Middleware
 */
class MiddlewareRequestHandler
{
    /**
     * @var Middleware
     */
    private $middleware;
    /**
     * @var API $userAuthToken
     */
    private $userAuthToken;
    /**
     * @var API $textAllocations
     */
    private $allocations;

    /**
     * MiddlewareRequestHandler constructor.
     *
     * @param Middleware $middleware
     */
    public function __construct($userAuthToken, $allocations)
    {
        $this->userAuthToken = $userAuthToken;
        $this->allocations = $allocations;
        $this->middleware = $this->checkErrorMiddlewareRoute();
    }

    /**
     * @return CheckInstanceSimpleXml
     */
    private function checkErrorMiddlewareRoute()
    {
        $middleware = new CheckInstanceSimpleXml();
        $middleware->linkWith(new CheckVersionXml())
            ->linkWith(new CheckCredentialsXml())
            ->linkWith(new CheckAuthXml($this->userAuthToken))
            ->linkWith(new CheckTextTypeXml())
            ->linkWith(new CheckCountryXml())
            ->linkWith(new CheckTitleXml())
            ->linkWith(new CheckTextXml())
            ->linkWith(new CheckHtmlXml())
            ->linkWith(new CheckPdfUrlXml())
            ->linkWith(new CheckPdfFilenameXml())
            ->linkWith(new CheckLanguageXml())
            ->linkWith(new CheckActionXml())
            ->linkWith(new CheckConfiguration($this->userAuthToken, $this->allocations));
        return $middleware;
    }

    /**
     * The client can configure the chain of middleware objects.
     */
    public function chainOfMiddleware(Middleware $middleware)
    {
        $this->middleware = $middleware;
    }

    /**
     * @param $xml
     *
     * @return bool|Exception|NotSimpleXmlInstanceException|int
     */
    public function handle($xml)
    {
        $response = $this->middleware->process($xml);

        return $response;
    }
}
