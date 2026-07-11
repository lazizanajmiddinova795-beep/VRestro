<?php

namespace App\Repositories;

use App\Models\Discount;
use App\Repositories\Contracts\DiscountRepositoryInterface;
use Illuminate\Support\Collection;

class DiscountRepository implements DiscountRepositoryInterface
{
    /**
     * Get all campaigns based on active status or dates filters.
     *
     * @param array $filters
     * @return Collection
     */
    public function getAll(array $filters): Collection
    {
        $query = Discount::query();

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('code', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderByDesc('created_at')->get();
    }

    /**
     * Get active campaigns valid today.
     *
     * @return Collection
     */
    public function getActiveCampaigns(): Collection
    {
        $now = now();
        return Discount::where('is_active', true)
            ->where(function ($query) use ($now) {
                $query->whereNull('starts_at')
                      ->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>=', $now);
            })
            ->get();
    }

    /**
     * Find campaign by exact string code.
     *
     * @param string $code
     * @return Discount|null
     */
    public function findByCode(string $code): ?Discount
    {
        return Discount::where('code', $code)->first();
    }

    /**
     * Find campaign by ID.
     *
     * @param int $id
     * @return Discount|null
     */
    public function findById(int $id): ?Discount
    {
        return Discount::find($id);
    }

    /**
     * Create new discount.
     *
     * @param array $data
     * @return Discount
     */
    public function create(array $data): Discount
    {
        return Discount::create($data);
    }

    /**
     * Update discount.
     *
     * @param int $id
     * @param array $data
     * @return Discount
     */
    public function update(int $id, array $data): Discount
    {
        $discount = Discount::findOrFail($id);
        $discount->update($data);
        return $discount;
    }

    /**
     * Delete discount.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $discount = Discount::findOrFail($id);
        return $discount->delete();
    }
}
