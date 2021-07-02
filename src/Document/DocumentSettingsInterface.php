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
     * Get the id of the locally stored version of the document, 0 if no pdf.
     *
     * @return int
     */
    public function getPdfAttachmentId(): int;

    /**
     * Set the id of the locally stored version of the document.
     *
     * @param int $pdfUrl
     */
    public function setPdfAttachmentId(int $pdfUrl): void;

    /**
     * Get the 'Hide title' option.
     *
     * @return bool
     */
    public function getHideTitle(): bool;

    /**
     * Set the 'Hide title' option.
     *
     * @param bool $hideTitle
     */
    public function setHideTitle(bool $hideTitle): void;
}
