<?php

namespace Inpsyde\AGBConnector\Middleware;

/**
 * Class Middleware
 *
 * @package Inpsyde\AGBConnector\Middleware
 */
abstract class Middleware
{
    /**
     * @var Middleware
     */
    private $next;

    /**
     * Method to build a chain of middleware objects (CoR).
     */
    public function linkWith(Middleware $next)
    {
        $this->next = $next;

        return $next;
    }

    /**
     * Subclasses must override this method to provide their own checks.
     */
    public function process($xml)
    {
        if (!$this->next) {
            return 0;
        }

        return $this->next->process($xml);
    }
}
