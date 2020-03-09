<?php

namespace Inpsyde\AGBConnector\CustomExceptions;

/**
 * Class PdfUrlException
 *
 * @package Inpsyde\AGBConnector\CustomExceptions
 */
class PdfUrlException extends XmlApiException
{
    public function __construct($message, $code = 7, XmlApiException $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
