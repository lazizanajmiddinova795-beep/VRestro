<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Order;
use App\Models\User;

class BranchService
{
    /**
     * Get all branches with counts.
     */
    public function getAllBranches(): array
    {
        return Branch::withCount(['users', 'tables', 'orders'])
            ->with(['users' => function($q) {
                $q->whereHas('roles', fn($r) => $r->where('name', 'Manager'))
                  ->select('id', 'name', 'phone', 'branch_id');
            }])
            ->orderBy('id')
            ->get()
            ->map(function($branch) {
                $arr = $branch->toArray();
                $manager = collect($arr['users'])->first();
                $arr['manager'] = $manager;
                unset($arr['users']);
                return $arr;
            })
            ->toArray();
    }

    /**
     * Get a single branch by ID.
     */
    public function getBranchById(int $id): ?Branch
    {
        return Branch::find($id);
    }

    /**
     * Create a new branch.
     */
    public function createBranch(array $data): Branch
    {
        return Branch::create($data);
    }

    /**
     * Update an existing branch.
     */
    public function updateBranch(int $id, array $data): Branch
    {
        $branch = Branch::findOrFail($id);
        $branch->update($data);
        return $branch->fresh();
    }

    /**
     * Delete a branch (only if no orders exist).
     */
    public function deleteBranch(int $id): void
    {
        $branch = Branch::findOrFail($id);

        // Safety: cannot delete if branch has orders
        $orderCount = Order::withoutGlobalScopes()
            ->where('branch_id', $id)
            ->count();

        if ($orderCount > 0) {
            abort(422, "Bu filialda {$orderCount} ta buyurtma mavjud. Avval buyurtmalarni ko'chiring yoki o'chiring.");
        }

        // Safety: cannot delete the last branch
        if (Branch::count() <= 1) {
            abort(422, 'Oxirgi filalni o\'chirib bo\'lmaydi.');
        }

        // Reassign users to null
        User::where('branch_id', $id)->update(['branch_id' => null]);

        $branch->delete();
    }

    /**
     * Switch SuperAdmin's branch context.
     */
    public function switchBranch(User $user, int $branchId): Branch
    {
        $branch = Branch::findOrFail($branchId);
        $user->update(['branch_id' => $branch->id]);
        return $branch;
    }

    public function assignManager(int $branchId, ?int $managerId): void
    {
        // Remove old manager from this branch
        User::whereHas('roles', fn($q) => $q->where('name', 'Manager'))
            ->where('branch_id', $branchId)
            ->update(['branch_id' => null]);

        // Assign new manager if provided
        if ($managerId) {
            User::where('id', $managerId)->update(['branch_id' => $branchId]);
        }
    }
}
