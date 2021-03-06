<?php

namespace Inpsyde\AGBConnector\Middleware;

use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;
use SimpleXMLElement;

/**
 * Class Middleware
 *
 * @package Inpsyde\AGBConnector\Middleware
 */
interface MiddlewareInterface
{
    /**
     * Method to build a chain of middleware objects (CoR).
     *
     * @param Middleware $next
     *
     * @return Middleware
     */
    public function linkWith(Middleware $next);

    /**
     * Subclasses must override this method to provide their own checks.
     *
     * @param SimpleXMLElement|false|string $data
     *
     * @return int
     * @throws XmlApiException
     */
    public function process($data);
}
