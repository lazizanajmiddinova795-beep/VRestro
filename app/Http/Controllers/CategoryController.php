<?php

namespace App\Http\Controllers;

use App\Services\MenuService;
use App\Repositories\Contracts\MenuRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected MenuService $menuService;
    protected MenuRepositoryInterface $menuRepository;

    public function __construct(MenuService $menuService, MenuRepositoryInterface $menuRepository)
    {
        $this->menuService = $menuService;
        $this->menuRepository = $menuRepository;
    }

    /**
     * Get list of categories.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $categories = $this->menuRepository->getAllCategories();
        return response()->json($categories);
    }

    /**
     * Store a new category.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category = $this->menuService->createCategory($data);

        return response()->json([
            'message' => 'Kategoriya muvaffaqiyatli yaratildi.',
            'category' => $category
        ], 201);
    }

    /**
     * Update an existing category.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name,' . $id],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category = $this->menuService->updateCategory($id, $data);

        return response()->json([
            'message' => 'Kategoriya muvaffaqiyatli tahrirlandi.',
            'category' => $category
        ]);
    }

    /**
     * Delete a category.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $this->menuService->deleteCategory($id);

        return response()->json([
            'message' => 'Kategoriya o\'chirildi.'
        ]);
    }
}
