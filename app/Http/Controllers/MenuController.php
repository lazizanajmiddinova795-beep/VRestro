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
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'], // Max 5MB
            'sizes' => ['nullable'],
            'ingredients' => ['nullable'],
        ]);

        $imageFile = $request->file('image');

        $itemData = collect($data)->except(['image', 'ingredients'])->toArray();
        if ($request->has('is_available')) {
            $itemData['is_available'] = filter_var($data['is_available'], FILTER_VALIDATE_BOOLEAN);
        }

        if ($request->has('sizes')) {
            $itemData['sizes'] = is_string($request->input('sizes')) 
                ? json_decode($request->input('sizes'), true) 
                : $request->input('sizes');
        }

        $food = $this->menuService->createFood($itemData, $imageFile);

        // Sync inline recipe ingredients if provided
        if ($request->has('ingredients')) {
            $ingredients = is_string($request->input('ingredients')) 
                ? json_decode($request->input('ingredients'), true) 
                : $request->input('ingredients');

            if (is_array($ingredients)) {
                \App\Models\Recipe::where('food_id', $food->id)->delete();
                foreach ($ingredients as $ing) {
                    if (!empty($ing['ingredient_id']) && !empty($ing['quantity_required']) && floatval($ing['quantity_required']) > 0) {
                        \App\Models\Recipe::create([
                            'food_id' => $food->id,
                            'ingredient_id' => intval($ing['ingredient_id']),
                            'quantity_required' => floatval($ing['quantity_required']),
                        ]);
                    }
                }
            }
        }

        return response()->json([
            'message' => 'Taom muvaffaqiyatli qo\'shildi.',
            'food' => $food->load('recipes.ingredient')
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $food = $this->menuRepository->getFoodById($id);

        if (!$food) {
            return response()->json(['message' => 'Taom topilmadi.'], 404);
        }

        return response()->json($food);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'is_available' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'sizes' => ['nullable'],
            'ingredients' => ['nullable'],
        ]);

        $imageFile = $request->file('image');

        $itemData = collect($data)->except(['image', 'ingredients'])->toArray();
        if ($request->has('is_available')) {
            $itemData['is_available'] = filter_var($data['is_available'], FILTER_VALIDATE_BOOLEAN);
        }

        if ($request->has('sizes')) {
            $itemData['sizes'] = is_string($request->input('sizes')) 
                ? json_decode($request->input('sizes'), true) 
                : $request->input('sizes');
        }

        $food = $this->menuService->updateFood($id, $itemData, $imageFile);

        // Sync inline recipe ingredients if provided
        if ($request->has('ingredients')) {
            $ingredients = is_string($request->input('ingredients')) 
                ? json_decode($request->input('ingredients'), true) 
                : $request->input('ingredients');

            if (is_array($ingredients)) {
                \App\Models\Recipe::where('food_id', $food->id)->delete();
                foreach ($ingredients as $ing) {
                    if (!empty($ing['ingredient_id']) && !empty($ing['quantity_required']) && floatval($ing['quantity_required']) > 0) {
                        \App\Models\Recipe::create([
                            'food_id' => $food->id,
                            'ingredient_id' => intval($ing['ingredient_id']),
                            'quantity_required' => floatval($ing['quantity_required']),
                        ]);
                    }
                }
            }
        }

        return response()->json([
            'message' => 'Taom muvaffaqiyatli tahrirlandi.',
            'food' => $food->load('recipes.ingredient')
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
