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
     * Return all the documents of given type.
     *
     * @return DocumentInterface[]
     */
    public function getAllOfType(string $type): array;

    /**
     * Find document by it's type, country and language.
     *
     * @param string $type The document type.
     * @param string $country The document country.
     * @param string $language The document language.
     *
     * @return int
     */
    public function getDocumentPostIdByTypeCountryAndLanguage(
        string $type,
        string $country,
        string $language
    ): int;

    /**
     * Handle saving of the document.
     *
     * @param DocumentInterface $document
     */
    public function saveDocument(DocumentInterface $document): void;

}


