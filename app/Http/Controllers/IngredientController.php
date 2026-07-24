<?php

namespace App\Http\Controllers;

use App\Services\IngredientService;
use App\Repositories\Contracts\IngredientRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    protected IngredientService $ingredientService;
    protected IngredientRepositoryInterface $ingredientRepository;

    public function __construct(IngredientService $ingredientService, IngredientRepositoryInterface $ingredientRepository)
    {
        $this->ingredientService = $ingredientService;
        $this->ingredientRepository = $ingredientRepository;
    }

    /**
     * Get list of ingredients with optional filters.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'low_stock' => ['nullable', 'string', 'in:true,false'],
        ]);

        $ingredients = $this->ingredientRepository->getAllIngredients($filters);

        return response()->json($ingredients);
    }

    /**
     * Store a new ingredient.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:ingredients,name'],
            'sku' => ['nullable', 'string', 'max:50', 'unique:ingredients,sku'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'in:kg,g,l,ml,dona,pachka'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'sell_price' => ['nullable', 'numeric', 'min:0'],
            'low_stock_threshold' => ['required', 'numeric', 'min:0'],
        ]);

        $ingredient = $this->ingredientService->createIngredient($data);

        return response()->json([
            'message' => 'Masalliq muvaffaqiyatli qo\'shildi.',
            'ingredient' => $ingredient
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $ingredient = $this->ingredientRepository->getIngredientById($id);

        if (!$ingredient) {
            return response()->json(['message' => 'Masalliq topilmadi.'], 404);
        }

        return response()->json($ingredient);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:ingredients,name,' . $id],
            'sku' => ['required', 'string', 'max:50', 'unique:ingredients,sku,' . $id],
            'unit' => ['required', 'string', 'in:kg,g,l,ml,dona,pachka'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'sell_price' => ['nullable', 'numeric', 'min:0'],
            'low_stock_threshold' => ['required', 'numeric', 'min:0'],
        ]);

        $ingredient = $this->ingredientService->updateIngredient($id, $data);

        return response()->json([
            'message' => 'Masalliq tahrirlandi.',
            'ingredient' => $ingredient
        ]);
    }

    public function adjust(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.001'],
            'type' => ['required', 'string', 'in:add,subtract'],
        ]);

        $ingredient = $this->ingredientService->adjustStock($id, (float)$data['amount'], $data['type']);

        return response()->json([
            'message' => 'Masalliq qoldig\'i o\'zgartirildi.',
            'ingredient' => $ingredient
        ]);
    }

    /**
     * Delete an ingredient with recipe constraint check.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $ingredient = \App\Models\Ingredient::find($id);
        if (!$ingredient) {
            return response()->json(['message' => 'Masalliq topilmadi.'], 404);
        }

        $usingRecipes = \App\Models\Recipe::where('ingredient_id', $id)
            ->with('food')
            ->get();

        if ($usingRecipes->isNotEmpty()) {
            $foodNames = $usingRecipes->pluck('food.name')->filter()->unique()->implode(', ');
            return response()->json([
                'message' => "Bu masalliq quyidagi taomlar retseptida ishlatilgan, avval ularni tahrirlang yoki o'chiring: " . ($foodNames ?: 'Retseptlar')
            ], 422);
        }

        try {
            $this->ingredientService->deleteIngredient($id);
            return response()->json([
                'message' => 'Masalliq muvaffaqiyatli o\'chirildi.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Masalliqni o\'chirishda xatolik yuz berdi: ' . $e->getMessage()
            ], 422);
        }
    }
}
