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
     * Get document content as text.
     *
     * @return string
     */
    public function getContentAsText(): string;

    /**
     * Get content as HTML format.
     *
     * @return string
     */
    public function getContentAsHtml(): string;

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
    public function getPDFUrl(): string;
}
