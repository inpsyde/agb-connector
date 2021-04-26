<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Document;

/**
 * WP Post based document allocation.
 *
 * Class DocumentAllocation
 *
 * @package Inpsyde\AGBConnector\Document
 */
class DocumentAllocation implements DocumentAllocationInterface
{
    protected $id = 0;

    protected $type = '';

    protected $country = '';

    protected $language = '';

    protected $attachToWcEmail = false;

    protected $savePdf = false;

    protected $displayingPageId = 0;

    /**
     * @inheritDoc
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function setId(int $allocationId): void
    {
        $this->id = $allocationId;
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @inheritDoc
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @inheritDoc
     */
    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    /**
     * @inheritDoc
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @inheritDoc
     */
    public function setLanguage(string $language): void
    {
        $this->language = $language;
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

    /**
     * @inheritDoc
     */
    public function getDisplayingPageId(): int
    {
        return $this->displayingPageId;
    }

    /**
     * @inheritDoc
     */
    public function setDisplayingPageId(int $displayingPageId): void
    {
        $this->displayingPageId = $displayingPageId;
    }
}
