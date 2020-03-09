<?php

namespace Inpsyde\AGBConnector\CustomExceptions;

/**
 * Class PdfFilenameException
 *
 * @package Inpsyde\AGBConnector\CustomExceptions
 */
class PdfFilenameException extends XmlApiException
{
    public function __construct($message, $code = 19, XmlApiException $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
