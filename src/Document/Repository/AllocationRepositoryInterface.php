<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Document\Repository;

use Inpsyde\AGBConnector\Document\DocumentSettingsInterface;

interface AllocationRepositoryInterface
{

    /**
     * Get the allocation by id.
     *
     * @param int $id
     *
     * @return DocumentSettingsInterface
     */
    public function getById(int $id): DocumentSettingsInterface;

    /**
     * Get the allocation for the given document type, country and language.
     *
     * @param string $type
     * @param string $country
     * @param string $language
     *
     * @return DocumentSettingsInterface|null
     */
    public function getByTypeCountryAndLanguage(string $type, string $country, string $language): ?DocumentSettingsInterface;

    /**
     * Get all document allocations of the given type.
     *
     * @param string $type
     *
     * @return array
     */
    public function getAllOfType(string $type): array;

    /**
     * Save the allocation to the DB.
     *
     * @param DocumentSettingsInterface $allocation
     */
    public function saveDocumentAllocation(DocumentSettingsInterface $allocation): void;

}
