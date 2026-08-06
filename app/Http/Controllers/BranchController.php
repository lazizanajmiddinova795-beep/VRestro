<?php

namespace App\Http\Controllers;

use App\Services\BranchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    protected BranchService $branchService;

    public function __construct(BranchService $branchService)
    {
        $this->branchService = $branchService;
    }

    /**
     * List branches.
     * SuperAdmin sees all, Manager sees only their own.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->is_superadmin) {
            $branches = $this->branchService->getAllBranches();
        } else {
            $branches = $this->branchService->getBranchById($user->branch_id);
            $branches = $branches ? [$branches] : [];
        }

        return response()->json($branches);
    }

    /**
     * Create a new branch. SuperAdmin only.
     */
    public function store(Request $request): JsonResponse
    {
        if (!$request->user()->is_superadmin) {
            abort(403, 'Faqat Tizim Administratori filial yarata oladi.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:branches,name',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
        ]);

        $branch = $this->branchService->createBranch($validated);

        return response()->json($branch, 201);
    }

    /**
     * Update a branch. SuperAdmin only.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        if (!$request->user()->is_superadmin) {
            abort(403, 'Faqat Tizim Administratori filalni tahrirlashi mumkin.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $branch = $this->branchService->updateBranch($id, $validated);

        return response()->json($branch);
    }

    /**
     * Delete a branch. SuperAdmin only.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        if (!$request->user()->is_superadmin) {
            abort(403, 'Faqat Tizim Administratori filalni o\'chirishi mumkin.');
        }

        $this->branchService->deleteBranch($id);

        return response()->json(['message' => 'Filial muvaffaqiyatli o\'chirildi.']);
    }

    /**
     * Switch active branch context (SuperAdmin only).
     * This temporarily sets the SuperAdmin's branch_id so they see
     * data scoped to a specific branch.
     */
    public function switch(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!$user->is_superadmin) {
            abort(403, 'Faqat Tizim Administratori filallar orasida o\'ta oladi.');
        }

        $branch = $this->branchService->switchBranch($user, $id);

        return response()->json([
            'message' => "'{$branch->name}' filaliga o'tildi.",
            'branch' => $branch,
        ]);
    }

    /**
     * Clear branch context — SuperAdmin returns to global view.
     */
    public function clearContext(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->is_superadmin) {
            abort(403);
        }

        $user->update(['branch_id' => null]);

        return response()->json(['message' => 'Global ko\'rinishga qaytildi.']);
    }
}
