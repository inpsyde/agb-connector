<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Document;

/**
 * WP Post based document allocation.
 *
 * Class DocumentSettings
 *
 * @package Inpsyde\AGBConnector\Document
 */
class DocumentSettings implements DocumentSettingsInterface
{
    protected $id = 0;

    protected $type = '';

    protected $country = '';

    protected $language = '';

    protected $attachToWcEmail = false;

    protected $savePdf = false;

    /**
     * @inheritDoc
     */
    public function getDocumentId(): int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function setDocumentId(int $allocationId): void
    {
        $this->id = $allocationId;
    }

    /**
     * @inheritDoc
     */
    public function getAttachToWcEmail(): bool
    {
        return $this->attachToWcEmail;
    }

    /**
     * @inheritDoc
     */
    public function setAttachToWcEmail(bool $shouldAttach): void
    {
        $this->attachToWcEmail = $shouldAttach;
    }

    /**
     * @inheritDoc
     */
    public function getSavePdf(): bool
    {
        return $this->savePdf;
    }

    /**
     * @inheritDoc
     */
    public function setSavePdf(bool $shouldSavePdf): void
    {
        $this->savePdf = $shouldSavePdf;
    }
}
