<?php

namespace App\Services;

use App\Repositories\Contracts\MenuRepositoryInterface;
use App\Models\Category;
use App\Models\Food;
use App\Models\OrderItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class MenuService
{
    protected MenuRepositoryInterface $menuRepository;

    public function __construct(MenuRepositoryInterface $menuRepository)
    {
        $this->menuRepository = $menuRepository;
    }

    // --- Category Services ---

    /**
     * Create a category.
     *
     * @param array $data
     * @return Category
     */
    public function createCategory(array $data): Category
    {
        Cache::forget('menu_categories');
        return $this->menuRepository->createCategory($data);
    }

    /**
     * Update a category.
     *
     * @param int $id
     * @param array $data
     * @return Category
     */
    public function updateCategory(int $id, array $data): Category
    {
        Cache::forget('menu_categories');
        return $this->menuRepository->updateCategory($id, $data);
    }

    /**
     * Delete a category safely.
     *
     * @param int $id
     * @return bool
     * @throws ValidationException
     */
    public function deleteCategory(int $id): bool
    {
        $category = $this->menuRepository->getCategoryById($id);
        
        if (!$category) {
            throw ValidationException::withMessages([
                'category' => ['Kategoriya topilmadi.'],
            ]);
        }

        // Relational safety: cannot delete if category has foods
        if ($category->foods()->exists()) {
            throw ValidationException::withMessages([
                'category' => ['Kategoriyada taomlar mavjud. Uni o\'chirish uchun avval taomlarni boshqa kategoriyaga o\'tkazing yoki o\'chiring.'],
            ]);
        }

        Cache::forget('menu_categories');
        return $this->menuRepository->deleteCategory($id);
    }

    // --- Food Services ---

    /**
     * Create a new food with optional image.
     *
     * @param array $data
     * @param UploadedFile|null $image
     * @return Food
     */
    public function createFood(array $data, ?UploadedFile $image = null): Food
    {
        DB::beginTransaction();

        try {
            if ($image) {
                // Store image in public disk under 'foods' directory
                $data['image_path'] = $image->store('foods', 'public');
            }

            $food = $this->menuRepository->createFood($data);

            DB::commit();
            return $food;
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($data['image_path'])) {
                Storage::disk('public')->delete($data['image_path']);
            }
            throw $e;
        }
    }

    /**
     * Update an existing food.
     *
     * @param int $id
     * @param array $data
     * @param UploadedFile|null $image
     * @return Food
     */
    public function updateFood(int $id, array $data, ?UploadedFile $image = null): Food
    {
        DB::beginTransaction();

        try {
            $food = $this->menuRepository->getFoodById($id);

            if (!$food) {
                throw ValidationException::withMessages([
                    'food' => ['Taom topilmadi.'],
                ]);
            }

            if ($image) {
                // Delete old image if exists
                if ($food->image_path) {
                    Storage::disk('public')->delete($food->image_path);
                }
                
                // Store new image
                $data['image_path'] = $image->store('foods', 'public');
            }

            $updatedFood = $this->menuRepository->updateFood($id, $data);

            DB::commit();
            return $updatedFood;
        } catch (\Exception $e) {
            DB::rollBack();
            if ($image && isset($data['image_path'])) {
                Storage::disk('public')->delete($data['image_path']);
            }
            throw $e;
        }
    }

    /**
     * Toggle the availability status of a food.
     *
     * @param int $id
     * @return Food
     */
    public function toggleAvailability(int $id): Food
    {
        $food = $this->menuRepository->getFoodById($id);
        
        if (!$food) {
            throw ValidationException::withMessages([
                'food' => ['Taom topilmadi.'],
            ]);
        }

        $food->update([
            'is_available' => !$food->is_available
        ]);

        return $food;
    }

    /**
     * Delete a food item safely if not in active orders.
     *
     * @param int $id
     * @return bool
     * @throws ValidationException
     */
    public function deleteFood(int $id): bool
    {
        DB::beginTransaction();

        try {
            $food = $this->menuRepository->getFoodById($id);

            if (!$food) {
                throw ValidationException::withMessages([
                    'food' => ['Taom topilmadi.'],
                ]);
            }

            // Relational safety: cannot delete if linked to active (new, cooking, ready) orders
            $hasActiveOrders = OrderItem::where('food_id', $id)
                ->whereHas('order', function ($q) {
                    $q->whereIn('status', ['new', 'cooking', 'ready']);
                })
                ->exists();

            if ($hasActiveOrders) {
                throw ValidationException::withMessages([
                    'food' => ['Ushbu taom faol buyurtmalarga biriktirilgan, hozircha uni o\'chirish mumkin emas.'],
                ]);
            }

            $imageToDelete = $food->image_path;

            $this->menuRepository->deleteFood($id);

            if ($imageToDelete) {
                Storage::disk('public')->delete($imageToDelete);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
