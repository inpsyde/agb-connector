<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Document\Repository;

use Inpsyde\AGBConnector\Document\DocumentInterface;

interface DocumentRepositoryInterface
{
    /**
     * Return document with given id, null if not found.
     *
     * @param int $id
     *
     * @return DocumentInterface|null
     */
    public function getDocumentById(int $id): ?DocumentInterface;

    /**
     * Return all saved documents.
     *
     * @param DocumentInterface $document
     *
     * @return DocumentInterface[]
     */
    public function getAllDocuments(DocumentInterface $document): array;

    /**
     * Handle saving of the document.
     *
     * @param DocumentInterface $document
     */
    public function saveDocument(DocumentInterface $document): void;

}


