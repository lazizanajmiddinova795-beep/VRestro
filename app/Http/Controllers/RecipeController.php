<?php

namespace App\Http\Controllers;

use App\Services\RecipeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    protected RecipeService $recipeService;

    public function __construct(RecipeService $recipeService)
    {
        $this->recipeService = $recipeService;
    }

    /**
     * Display the recipe details and capacity metrics for a food.
     *
     * @param int $foodId
     * @return JsonResponse
     */
    public function show(int $foodId): JsonResponse
    {
        $recipe = $this->recipeService->getRecipeForFood($foodId);
        $capacity = $this->recipeService->calculatePortionCapacity($foodId);

        return response()->json([
            'recipe' => $recipe,
            'portion_capacity' => $capacity
        ]);
    }

    /**
     * Store or update recipe ingredients mapping for a dish.
     *
     * @param Request $request
     * @param int $foodId
     * @return JsonResponse
     */
    public function store(Request $request, int $foodId): JsonResponse
    {
        $data = $request->validate([
            'ingredients' => ['present', 'array'],
            'ingredients.*.ingredient_id' => ['required', 'integer', 'exists:ingredients,id'],
            'ingredients.*.quantity_required' => ['required', 'numeric', 'min:0.001'],
        ]);

        $this->recipeService->saveRecipe($foodId, $data['ingredients']);

        return response()->json([
            'message' => 'Retsept muvaffaqiyatli saqlandi.',
        ]);
    }
}
