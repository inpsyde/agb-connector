<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Document;

/**
 * Represents a place for the document in the system. Contains information about:
 *  - the document this allocation is for (type, language and country),
 *  - the settings related to this document (whether to attach to WC emails, whether to save PDF, etc)
 *  - the post ID used to save the document data (referred as Document Allocation Id) and the page id the document is available on.
 *
 * Interface DocumentAllocationInterface
 *
 * @package Inpsyde\AGBConnector\Document\Allocation
 */
interface DocumentAllocationInterface
{
    /**
     * Allocation id (the same as the post id containing the document).
     *
     * @return int
     */
    public function getId(): int;

    /**
     * Set the allocation id.
     *
     * @param int $allocationId
     */
    public function setId(int $allocationId): void;

    /**
     * Get the document country.
     */
    public function getCountry(): string;

    /**
     * Get the type of the document this allocation is for.
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Set the type of the document this allocation is for.
     */
    public function setType(string $type): void;

    /**
     * Set the document allocation country.
     *
     * @param string $country
     */
    public function setCountry(string $country): void;

    /**
     * Get the document language.
     *
     * @return string
     */
    public function getLanguage(): string;

    /**
     * Set the document allocation language.
     *
     * @param string $language
     */
    public function setLanguage(string $language): void;

    /**
     * Get whether document should be attached to WC emails.
     *
     * @return bool
     */
    public function getAttachToWcEmail(): bool;

    /**
     * Set 'attach to WC emails' setting.
     *
     * @param bool $shouldAttach
     */
    public function setAttachToWcEmail(bool $shouldAttach): void;

    /**
     * Get whether the PDF version of the document should be saved.
     *
     * @return bool
     */
    public function getSavePdf(): bool;

    /**
     * Set 'Store PDF file' option.
     *
     * @param bool $shouldSavePdf
     */
    public function setSavePdf(bool $shouldSavePdf): void;

    /**
     * Get the ID of the public page displaying the document on the site.
     *
     * @return int
     */
    public function getDisplayingPageId(): int;

    /**
     * Set the ID of the public page displaying the document.
     *
     * @param int $displayingPageId
     */
    public function setDisplayingPageId(int $displayingPageId): void;
}
