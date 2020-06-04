<?php # -*- coding: utf-8 -*-

namespace Inpsyde\AGBConnector\Admin\Notice;

/**
 * Class Notice
 * @package Inpsyde\AGBConnector\Admin\Notice
 */
class Notice implements Noticeable
{
    /**
     * @var
     */
    protected $type;
    /**
     * @var
     */
    protected $message;
    /**
     * @var
     */
    protected $isDismissable;
    /**
     * @var
     */
    protected $id;

    /**
     * Notice constructor.
     *
     * @param $type
     * @param $message
     * @param $isDismissible
     * @param $id
     */
    public function __construct($type, $message, $isDismissible, $id)
    {
        assert(is_string($type));
        assert(is_string($message));
        assert(is_bool($isDismissible));
        assert(is_string($id));

        $this->type = $type;
        $this->message = $message;
        $this->isDismissable = $isDismissible;
        $this->id = $id;
    }

    /**
     * @inheritDoc
     */
    public function type()
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function message()
    {
        return $this->message;
    }

    /**
     * @inheritDoc
     */
    public function isDismissable()
    {
        return $this->isDismissable;
    }

    /**
     * @inheritDoc
     */
    public function id()
    {
        return $this->id;
    }
}
