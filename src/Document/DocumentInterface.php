<?php

declare(strict_types=1);

namespace Inpsyde\AGBConnector\Document;

/**
 * Represents a single legal document.
 */
interface DocumentInterface
{

    /**
     * Get document title.
     *
     * @return string
     */
    public function getTitle(): string;

    /**
     * Get content as HTML format.
     *
     * @return string
     */
    public function getContent(): string;

    /**
     * Get document country.
     *
     * @return string
     */
    public function getCountry(): string;

    /**
     * Get document language.
     *
     * @return string
     */
    public function getLanguage(): string;

    /**
     * Get document type, one of the following: agb, datenschutz, widerruf, impressum.
     *
     * Translation for document types:
     * agb - general terms and conditions;
     * datenschutz - data protection;
     * widerruf - revocation;
     * impressum - imprint.
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Get the link to the pdf version of the document, empty string if no pdf version.
     *
     * @return string
     */
    public function getPdfUrl(): string;

    /**
     * Get the document settings object.
     *
     * @return DocumentSettingsInterface
     */
    public function getSettings(): DocumentSettingsInterface;
}
