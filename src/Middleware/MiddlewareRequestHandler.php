<?php


namespace Inpsyde\AGBConnector\Middleware;


class MiddlewareRequestHandler
{

    /**
     * @var Middleware
     */
    private $middleware;

    private $userAuthToken;
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
     * The client can configure the chain of middleware objects.
     */
    public function setMiddleware(Middleware $middleware)
    {
        $this->middleware = $middleware;
    }

    /**
     * @return checkInstanceSimpleXml
     */
    private function checkErrorMiddlewareRoute()
    {
        $middleware = new checkInstanceSimpleXml();
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
     * @param $xml
     *
     * @return bool|\Exception|\Inpsyde\AGBConnector\customExceptions\notSimpleXmlInstanceException
     */
    public function handle($xml){
        $response = $this->middleware->process($xml);
        return $response;
    }

}
