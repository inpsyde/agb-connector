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
     *
     * @param Middleware $next
     *
     * @return Middleware
     */
    public function linkWith(Middleware $next)
    {
        $this->next = $next;

        return $next;
    }

    /**
     * Subclasses must override this method to provide their own checks.
     *
     * @param $data
     *
     * @return int
     */

    public function process($data)
    {
        if (!$this->next) {
            return $data;
        }

        return $this->next->process($data);
    }
}
