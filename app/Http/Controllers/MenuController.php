<?php

namespace App\Http\Controllers;

use App\Services\MenuService;
use App\Repositories\Contracts\MenuRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    protected MenuService $menuService;
    protected MenuRepositoryInterface $menuRepository;

    public function __construct(MenuService $menuService, MenuRepositoryInterface $menuRepository)
    {
        $this->menuService = $menuService;
        $this->menuRepository = $menuRepository;
    }

    /**
     * Get list of food items.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'search' => ['nullable', 'string', 'max:100'],
            'is_available' => ['nullable', 'boolean'],
        ]);

        $foods = $this->menuRepository->getAllFoods($filters);

        return response()->json($foods);
    }

    /**
     * Store a new food item.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'is_available' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'], // Max 2MB
        ]);

        $imageFile = $request->file('image');

        // Extract image out of direct database updates
        $itemData = collect($data)->except('image')->toArray();
        if ($request->has('is_available')) {
            $itemData['is_available'] = filter_var($data['is_available'], FILTER_VALIDATE_BOOLEAN);
        }

        $food = $this->menuService->createFood($itemData, $imageFile);

        return response()->json([
            'message' => 'Taom muvaffaqiyatli qo\'shildi.',
            'food' => $food
        ], 201);
    }

    /**
     * View specific food details.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $food = $this->menuRepository->getFoodById($id);

        if (!$food) {
            return response()->json(['message' => 'Taom topilmadi.'], 404);
        }

        return response()->json($food);
    }

    /**
     * Update an existing food item.
     * Note: We use POST with _method=PUT to support image file uploads under PHP limitation.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'is_available' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $imageFile = $request->file('image');

        $itemData = collect($data)->except('image')->toArray();
        if ($request->has('is_available')) {
            $itemData['is_available'] = filter_var($data['is_available'], FILTER_VALIDATE_BOOLEAN);
        }

        $food = $this->menuService->updateFood($id, $itemData, $imageFile);

        return response()->json([
            'message' => 'Taom muvaffaqiyatli tahrirlandi.',
            'food' => $food
        ]);
    }

    /**
     * Toggle availability status of a food.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function toggleAvailability(int $id): JsonResponse
    {
        $food = $this->menuService->toggleAvailability($id);

        return response()->json([
            'message' => 'Taom holati o\'zgartirildi.',
            'food' => $food
        ]);
    }

    /**
     * Delete a food item.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $this->menuService->deleteFood($id);

        return response()->json([
            'message' => 'Taom muvaffaqiyatli o\'chirildi.'
        ]);
    }
}
