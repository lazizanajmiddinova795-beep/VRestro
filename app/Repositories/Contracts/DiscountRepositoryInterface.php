<?php

namespace App\Repositories\Contracts;

use App\Models\Discount;
use Illuminate\Support\Collection;

interface DiscountRepositoryInterface
{
    /**
     * Get all campaigns based on active status or dates filters.
     *
     * @param array $filters
     * @return Collection
     */
    public function getAll(array $filters): Collection;

    /**
     * Get active campaigns valid today.
     *
     * @return Collection
     */
    public function getActiveCampaigns(): Collection;

    /**
     * Find campaign by exact string code.
     *
     * @param string $code
     * @return Discount|null
     */
    public function findByCode(string $code): ?Discount;

    /**
     * Find campaign by ID.
     *
     * @param int $id
     * @return Discount|null
     */
    public function findById(int $id): ?Discount;

    /**
     * Create new discount.
     *
     * @param array $data
     * @return Discount
     */
    public function create(array $data): Discount;

    /**
     * Update discount.
     *
     * @param int $id
     * @param array $data
     * @return Discount
     */
    public function update(int $id, array $data): Discount;

    /**
     * Delete discount.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
