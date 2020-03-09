<?php

namespace Inpsyde\AGBConnector\CustomExceptions;

/**
 * Class PdfMD5Exception
 *
 * @package Inpsyde\AGBConnector\CustomExceptions
 */
class PdfMD5Exception extends XmlApiException
{
    public function __construct($message, $code = 8, XmlApiException $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
