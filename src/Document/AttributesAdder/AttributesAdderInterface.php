<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Document\AttributesAdder;

use Inpsyde\AGBConnector\Document\DocumentInterface;

/**
 * Service able to add and read document HTML attributes.
 */
interface AttributesAdderInterface
{
    /**
     * Check whether document HTML elements contain plugin-specific attributes.
     *
     * @param DocumentInterface $document
     *
     * @return bool Check result.
     */
    public function hasAttributes(DocumentInterface $document): bool;

    /**
     * Add plugin-specific attributes to the document HTML tags.
     *
     * @param DocumentInterface $document
     *
     * @return DocumentInterface Document with attributes added to its content.
     */
    public function addAttributes(DocumentInterface $document): DocumentInterface;
}
