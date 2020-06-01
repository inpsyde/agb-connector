<?php # -*- coding: utf-8 -*-

namespace Inpsyde\AGBConnector;

use RuntimeException;

/**
 * Class ServiceProvider
 */
class WPFilesystemException extends RuntimeException
{
    private $wpError;

    public function __construct(\WP_Error $wpError, $message = "", $code = 0, $previous = null)
    {
        $this->wpError = $wpError;
        parent::__construct($message, $code, $previous);
    }

    public function errorData($code = '')
    {
        if (empty($code)) {
            $code = $this->wpError->get_error_code();
        }

        if (isset($this->wpError->error_data[$code])) {
            return $this->wpError->error_data[$code];
        }
        return 'No error code';
    }
}
