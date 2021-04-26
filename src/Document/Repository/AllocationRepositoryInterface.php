<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Document\Repository;

use Inpsyde\AGBConnector\Document\DocumentAllocationInterface;

interface AllocationRepositoryInterface
{

    /**
     * Get the allocation by id.
     *
     * @param int $id
     *
     * @return DocumentAllocationInterface
     */
    public function getById(int $id): DocumentAllocationInterface;

    /**
     * Get the allocation for the given document type, country and language.
     *
     * @param string $type
     * @param string $country
     * @param string $language
     *
     * @return DocumentAllocationInterface|null
     */
    public function getByTypeCountryAndLanguage(string $type, string $country, string $language): ?DocumentAllocationInterface;

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
     * @param DocumentAllocationInterface $allocation
     */
    public function saveDocumentAllocation(DocumentAllocationInterface $allocation): void;

}
