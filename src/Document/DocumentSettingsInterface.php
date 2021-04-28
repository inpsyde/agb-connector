<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Document;

/**
 * Represents a place for the document in the system. Contains information about:
 *  - the document this allocation is for (type, language and country),
 *  - the settings related to this document (whether to attach to WC emails, whether to save PDF, etc)
 *  - the post ID used to save the document data (referred as Document Allocation Id) and the page id the document is available on.
 *
 * Interface DocumentSettingsInterface
 *
 * @package Inpsyde\AGBConnector\Document\Allocation
 */
interface DocumentSettingsInterface
{
    /**
     * Allocation id (the same as the post id containing the document).
     *
     * @return int
     */
    public function getDocumentId(): int;

    /**
     * Set the allocation id.
     *
     * @param int $allocationId
     */
    public function setDocumentId(int $allocationId): void;

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
