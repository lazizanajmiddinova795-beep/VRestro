<template>
  <div class="flex-grow p-6 flex flex-col md:flex-row h-screen overflow-hidden gap-6">

    <!-- Left Column: Categories Management -->
    <aside class="w-full md:w-80 bg-white border border-slate-200 shadow-sm rounded-3xl p-5 flex flex-col justify-between shrink-0 h-full">
      <div class="space-y-6 overflow-hidden flex flex-col h-full">
        <!-- Header -->
        <div class="flex justify-between items-center shrink-0">
          <div>
            <h2 class="text-lg font-bold text-slate-900 tracking-wide">Kategoriyalar</h2>
            <p class="text-3xs text-slate-500">Menyu bo'limlarini boshqarish</p>
          </div>
          <button
            @click="openCategoryModal()"
            class="p-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-md transition"
            title="Kategoriya qo'shish"
          >
            <Plus class="w-4 h-4" />
          </button>
        </div>

        <!-- Categories List -->
        <div class="overflow-y-auto flex-grow pr-1 space-y-1.5">
          <!-- All Categories Selector -->
          <button
            @click="selectCategory(null)"
            class="w-full text-left px-4 py-3 rounded-xl text-sm font-semibold flex justify-between items-center transition"
            :class="!menuStore.selectedCategoryId ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
          >
            <span>Barchasi</span>
            <span
              class="px-2 py-0.5 rounded-full text-3xs font-bold border"
              :class="!menuStore.selectedCategoryId ? 'bg-white/20 border-white/20 text-white' : 'bg-slate-100 border-slate-200 text-slate-500'"
            >
              {{ totalFoodsCount }}
            </span>
          </button>

          <!-- Category items -->
          <div
            v-for="cat in menuStore.categories"
            :key="cat.id"
            class="group w-full flex items-center justify-between px-4 py-2.5 rounded-xl transition hover:bg-slate-50"
            :class="menuStore.selectedCategoryId === cat.id ? 'bg-indigo-50 border border-indigo-100' : ''"
          >
            <button
              @click="selectCategory(cat.id)"
              class="text-left flex-grow text-sm font-semibold truncate"
              :class="menuStore.selectedCategoryId === cat.id ? 'text-indigo-700' : 'text-slate-600 group-hover:text-slate-900'"
            >
              {{ cat.name }}
            </button>

            <div class="flex items-center space-x-1.5 shrink-0 ml-2">
              <span
                class="px-2 py-0.5 rounded-full text-3xs font-bold border bg-slate-100 border-slate-200 text-slate-500"
              >
                {{ cat.foods_count || 0 }}
              </span>

              <!-- Inline edit actions -->
              <button
                @click="openCategoryModal(cat)"
                class="p-1 rounded bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-900 opacity-0 group-hover:opacity-100 transition"
              >
                <Edit3 class="w-3.5 h-3.5" />
              </button>
              <button
                @click="handleDeleteCategory(cat)"
                class="p-1 rounded bg-red-50 text-red-500 hover:bg-red-500 hover:text-white opacity-0 group-hover:opacity-100 transition"
              >
                <Trash2 class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </aside>

    <!-- Right Column: Foods Grid -->
    <main class="flex-grow flex flex-col h-full overflow-hidden">
      <!-- Search & Add Row -->
      <div class="bg-white border border-slate-200 shadow-sm rounded-3xl p-5 mb-6 flex flex-col sm:flex-row items-center justify-between gap-4 shrink-0">
        <!-- Search bar -->
        <div class="relative w-full sm:w-80">
          <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
            <Search class="w-4 h-4" />
          </span>
          <input
            v-model="searchQuery"
            @input="triggerSearch"
            type="text"
            placeholder="Taomlarni qidirish..."
            class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500 text-sm placeholder-slate-400 text-slate-900 focus:outline-none transition"
          />
        </div>

        <!-- Add Food button -->
        <button
          @click="openFoodModal()"
          class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 font-semibold text-sm text-white shadow-md shadow-indigo-600/20 hover:shadow-indigo-600/30 hover:scale-[1.01] transition-all flex items-center justify-center space-x-2"
        >
          <Plus class="w-4.5 h-4.5" />
          <span>Yangi Taom Qo'shish</span>
        </button>
      </div>

      <!-- Foods List Grid -->
      <div v-if="menuStore.loading" class="flex-grow flex flex-col items-center justify-center space-y-4">
        <Loader2 class="w-10 h-10 text-indigo-500 animate-spin" />
        <p class="text-slate-500 text-xs font-medium animate-pulse">Menyu yuklanmoqda...</p>
      </div>

      <div v-else class="flex-grow overflow-y-auto pr-1">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 pb-12">
          <!-- Food Card -->
          <div
            v-for="food in menuStore.foods"
            :key="food.id"
            class="bg-white border border-slate-200 shadow-sm rounded-3xl overflow-hidden hover:border-slate-300 hover:shadow-md transition duration-300 flex flex-col justify-between"
          >
            <!-- Image Area -->
            <div class="h-44 relative bg-slate-50 flex items-center justify-center overflow-hidden border-b border-slate-100">
              <img
                v-if="food.image_url"
                :src="food.image_url"
                :alt="food.name"
                class="w-full h-full object-cover"
              />
              <div v-else class="text-slate-300 flex flex-col items-center space-y-2">
                <ChefHat class="w-12 h-12 stroke-[1.2]" />
                <span class="text-3xs uppercase tracking-wider text-slate-400 font-semibold">Rasm yo'q</span>
              </div>

              <!-- Price Tag -->
              <div class="absolute right-4 bottom-4 px-3 py-1 rounded-xl bg-white/90 border border-slate-200 text-xs font-bold text-slate-900 backdrop-blur shadow-sm">
                {{ formatCurrency(food.price) }}
              </div>
            </div>

            <!-- Details -->
            <div class="p-5 flex-grow flex flex-col justify-between space-y-4">
              <div class="space-y-1.5">
                <div class="flex justify-between items-start gap-2">
                  <h3 class="text-sm font-bold text-slate-900 leading-snug truncate" :title="food.name">
                    {{ food.name }}
                  </h3>
                  <span class="shrink-0 text-3xs bg-slate-100 text-slate-600 border border-slate-200 px-2 py-0.5 rounded-full font-medium">
                    {{ food.category?.name }}
                  </span>
                </div>
                <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed">
                  {{ food.description || 'Taomga izoh berilmagan.' }}
                </p>
              </div>

              <!-- Available Switch & Actions -->
              <div class="border-t border-slate-100 pt-4 flex items-center justify-between">
                <!-- Toggle Availability -->
                <label class="flex items-center space-x-2.5 cursor-pointer select-none">
                  <input
                    type="checkbox"
                    :checked="food.is_available"
                    @change="handleToggleAvailable(food)"
                    class="sr-only peer"
                  />
                  <div class="w-8 h-4 bg-slate-200 rounded-full peer peer-checked:bg-emerald-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:after:translate-x-4 relative"></div>
                  <span class="text-3xs font-bold tracking-wider uppercase" :class="food.is_available ? 'text-emerald-600' : 'text-slate-400'">
                    {{ food.is_available ? 'Mavjud' : 'Tugagan' }}
                  </span>
                </label>

                <!-- Actions -->
                <div class="flex space-x-1.5">
                  <button
                    @click="openFoodModal(food)"
                    class="p-2 rounded-xl bg-slate-100 border border-slate-200 text-slate-600 hover:bg-slate-200 hover:text-slate-900 transition duration-200"
                    title="Tahrirlash"
                  >
                    <Edit3 class="w-4 h-4" />
                  </button>
                  <button
                    @click="handleDeleteFood(food)"
                    class="p-2 rounded-xl bg-red-50 border border-red-100 text-red-500 hover:bg-red-500 hover:text-white transition duration-200"
                    title="O'chirish"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- Empty state -->
        <div v-if="menuStore.foods.length === 0" class="flex flex-col items-center justify-center py-24 space-y-3">
          <ChefHat class="w-12 h-12 text-slate-300" />
          <p class="text-slate-500 text-xs font-medium">Bu bo'limda taomlar mavjud emas</p>
        </div>
      </div>
    </main>

    <!-- Modal 1: Category Add/Edit -->
    <div
      v-if="showCategoryModal"
      class="fixed inset-0 z-50 backdrop-blur-sm bg-slate-900/30 flex items-center justify-center p-6"
      @click.self="showCategoryModal = false"
    >
      <div class="w-full max-w-sm bg-white border border-slate-200 rounded-3xl p-6 shadow-2xl space-y-5 animate-scaleIn">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
          <h3 class="text-base font-bold text-slate-900">
            {{ editingCategory ? 'Kategoriyani Tahrirlash' : 'Yangi Kategoriya' }}
          </h3>
          <button @click="showCategoryModal = false" class="p-1 rounded-lg bg-slate-100 text-slate-500 hover:text-slate-900 transition">
            <X class="w-4 h-4" />
          </button>
        </div>

        <div class="space-y-4">
          <div class="space-y-1.5">
            <label class="text-3xs text-slate-500 font-bold uppercase tracking-wider">Kategoriya nomi</label>
            <input
              v-model="categoryForm.name"
              type="text"
              placeholder="Masalan, Milliy taomlar..."
              class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500 text-sm placeholder-slate-400 text-slate-900 focus:outline-none transition"
            />
          </div>
        </div>

        <div class="flex justify-end space-x-2 pt-2">
          <button @click="showCategoryModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 transition">
            Bekor qilish
          </button>
          <button
            @click="submitCategoryForm"
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold transition"
          >
            Saqlash
          </button>
        </div>
      </div>
    </div>

    <!-- Modal 2: Food Add/Edit -->
    <div
      v-if="showFoodModal"
      class="fixed inset-0 z-50 backdrop-blur-sm bg-slate-900/30 flex items-center justify-center p-6"
      @click.self="showFoodModal = false"
    >
      <div class="w-full max-w-2xl bg-white border border-slate-200 rounded-3xl p-6 shadow-2xl space-y-6 animate-scaleIn">
        <div class="flex justify-between items-center border-b border-slate-100 pb-4">
          <h3 class="text-base font-bold text-slate-900">
            {{ editingFood ? 'Taomni Tahrirlash' : 'Yangi Taom Qo\'shish' }}
          </h3>
          <button @click="showFoodModal = false" class="p-1 rounded-lg bg-slate-100 text-slate-500 hover:text-slate-900 transition">
            <X class="w-4.5 h-4.5" />
          </button>
        </div>

        <!-- Form fields -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-[420px] overflow-y-auto pr-1">
          <div class="space-y-1.5">
            <label class="text-3xs text-slate-500 font-bold uppercase tracking-wider">Taom nomi</label>
            <input
              v-model="foodForm.name"
              type="text"
              placeholder="Masalan, Palov..."
              class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500 text-sm text-slate-900 focus:outline-none transition"
            />
          </div>

          <div class="space-y-1.5">
            <label class="text-3xs text-slate-500 font-bold uppercase tracking-wider">Narxi (UZS)</label>
            <input
              v-model.number="foodForm.price"
              type="number"
              placeholder="Narxi..."
              class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500 text-sm text-slate-900 focus:outline-none transition"
            />
          </div>

          <div class="space-y-1.5">
            <label class="text-3xs text-slate-500 font-bold uppercase tracking-wider">Kategoriya</label>
            <select
              v-model="foodForm.category_id"
              class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500 text-sm text-slate-900 focus:outline-none transition appearance-none"
            >
              <option value="" disabled>Tanlang...</option>
              <option v-for="cat in menuStore.categories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
          </div>

          <div class="space-y-1.5">
            <label class="text-3xs text-slate-500 font-bold uppercase tracking-wider">Mavjudlik holati</label>
            <div class="pt-2">
              <label class="flex items-center space-x-2.5 cursor-pointer">
                <input
                  type="checkbox"
                  v-model="foodForm.is_available"
                  class="sr-only peer"
                />
                <div class="w-9 h-5 bg-slate-200 rounded-full peer peer-checked:bg-emerald-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-4 relative"></div>
                <span class="text-xs font-medium text-slate-600">{{ foodForm.is_available ? 'Mavjud' : 'Mavjud emas' }}</span>
              </label>
            </div>
          </div>

          <div class="space-y-1.5 sm:col-span-2">
            <label class="text-3xs text-slate-500 font-bold uppercase tracking-wider">Taom haqida (Tavsif)</label>
            <textarea
              v-model="foodForm.description"
              rows="2"
              placeholder="Tarkibi, tayyorlanish muddati yoki boshqa ma'lumotlar..."
              class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500 text-sm text-slate-900 focus:outline-none transition"
            ></textarea>
          </div>

          <!-- Recipe / ingredients configuration -->
          <div class="space-y-3 sm:col-span-2 border-t border-slate-100 pt-4">
            <div class="flex items-center justify-between">
              <label class="text-3xs text-slate-500 font-bold uppercase tracking-wider">Retsept (masalliqlar va miqdori)</label>
              <button
                type="button"
                @click="addRecipeRow"
                class="px-2.5 py-1 text-3xs font-bold uppercase tracking-wider rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-600 hover:bg-emerald-600 hover:text-white transition"
              >
                + Masalliq qo'shish
              </button>
            </div>

            <div v-if="recipeRows.length > 0" class="space-y-2">
              <div
                v-for="(row, idx) in recipeRows"
                :key="idx"
                class="flex gap-2 items-center bg-slate-50 border border-slate-200 rounded-xl p-2.5"
              >
                <select
                  v-model.number="row.ingredient_id"
                  class="flex-grow px-2 py-1.5 rounded-lg bg-white border border-slate-200 focus:border-indigo-500 text-xs text-slate-900 focus:outline-none transition"
                >
                  <option value="" disabled>Masalliqni tanlang...</option>
                  <option v-for="ing in ingredientsStore.ingredients" :key="ing.id" :value="ing.id">
                    {{ ing.name }} ({{ ing.unit }})
                  </option>
                </select>
                <input
                  v-model.number="row.quantity_required"
                  type="number"
                  step="0.001"
                  min="0"
                  placeholder="Miqdori..."
                  class="w-28 px-2 py-1.5 rounded-lg bg-white border border-slate-200 focus:border-indigo-500 text-xs text-slate-900 focus:outline-none transition"
                />
                <button
                  type="button"
                  @click="removeRecipeRow(idx)"
                  class="p-1.5 rounded-lg bg-red-50 border border-red-100 text-red-500 hover:bg-red-500 hover:text-white transition shrink-0"
                >
                  <Trash2 class="w-3.5 h-3.5" />
                </button>
              </div>
            </div>
            <p v-else class="text-xxs text-slate-400 italic">Masalliq qo'shilmagan. Taom saqlanganda retsept ham birga yoziladi.</p>
          </div>

          <!-- Sizes configuration -->
          <div class="space-y-3 sm:col-span-2 border-t border-slate-100 pt-4">
            <div class="flex items-center justify-between flex-wrap gap-2">
              <label class="text-3xs text-slate-500 font-bold uppercase tracking-wider">Taom o'lchamlari / porsiyalari (Ixtiyoriy)</label>
              <div class="flex items-center gap-1.5">
                <button
                  type="button"
                  @click="addSizeTemplate('Yarim porsiya', 0.5)"
                  class="px-2.5 py-1 text-3xs font-bold uppercase tracking-wider rounded-lg bg-amber-50 border border-amber-200 text-amber-600 hover:bg-amber-500 hover:text-white transition"
                >
                  + Yarim porsiya
                </button>
                <button
                  type="button"
                  @click="addSizeTemplate('To\'liq porsiya', 1.0)"
                  class="px-2.5 py-1 text-3xs font-bold uppercase tracking-wider rounded-lg bg-amber-50 border border-amber-200 text-amber-600 hover:bg-amber-500 hover:text-white transition"
                >
                  + To'liq porsiya
                </button>
                <button
                  type="button"
                  @click="addSizeRow"
                  class="px-2.5 py-1 text-3xs font-bold uppercase tracking-wider rounded-lg bg-indigo-50 border border-indigo-200 text-indigo-600 hover:bg-indigo-600 hover:text-white transition"
                >
                  + Boshqa o'lcham
                </button>
              </div>
            </div>

            <div v-if="foodForm.sizes && foodForm.sizes.length > 0" class="space-y-2">
              <div
                v-for="(size, idx) in foodForm.sizes"
                :key="idx"
                class="flex gap-2 items-center bg-slate-50 border border-slate-200 rounded-xl p-2.5 relative"
              >
                <div class="flex-grow grid grid-cols-1 sm:grid-cols-3 gap-2">
                  <div class="space-y-1">
                    <span class="text-4xs text-slate-400 font-bold uppercase">Nomi (Masalan: Yarim)</span>
                    <input
                      v-model="size.name"
                      type="text"
                      placeholder="Nomi..."
                      class="w-full px-2 py-1.5 rounded-lg bg-white border border-slate-200 focus:border-indigo-500 text-xs text-slate-900 focus:outline-none transition"
                    />
                  </div>
                  <div class="space-y-1">
                    <span class="text-4xs text-slate-400 font-bold uppercase">Narxi (UZS)</span>
                    <input
                      v-model.number="size.price"
                      type="number"
                      placeholder="Narxi..."
                      class="w-full px-2 py-1.5 rounded-lg bg-white border border-slate-200 focus:border-indigo-500 text-xs text-slate-900 focus:outline-none transition"
                    />
                  </div>
                  <div class="space-y-1">
                    <span class="text-4xs text-slate-400 font-bold uppercase">Masalliq koeffitsiyenti (0.5 = yarim)</span>
                    <input
                      v-model.number="size.recipe_multiplier"
                      type="number"
                      step="0.1"
                      placeholder="Koeffitsiyent..."
                      class="w-full px-2 py-1.5 rounded-lg bg-white border border-slate-200 focus:border-indigo-500 text-xs text-slate-900 focus:outline-none transition"
                    />
                  </div>
                </div>
                <button
                  type="button"
                  @click="removeSizeRow(idx)"
                  class="p-1 rounded bg-red-50 border border-red-100 text-red-500 hover:bg-red-500 hover:text-white transition shrink-0 self-end mb-1"
                >
                  <Trash2 class="w-3.5 h-3.5" />
                </button>
              </div>
            </div>
            <p v-else class="text-xxs text-slate-400 italic">O'lchamlar kiritilmagan. Standart narx va retsept miqdori amalda bo'ladi.</p>
          </div>

          <!-- Drag and drop image selector -->
          <div class="space-y-1.5 sm:col-span-2">
            <label class="text-3xs text-slate-500 font-bold uppercase tracking-wider">Taom rasmi</label>
            <div
              @click="$refs.fileInput.click()"
              class="w-full border-2 border-dashed border-slate-200 hover:border-indigo-400 rounded-2xl p-4 flex flex-col items-center justify-center cursor-pointer transition bg-slate-50 hover:bg-indigo-50/40 space-y-2"
            >
              <input
                ref="fileInput"
                type="file"
                class="hidden"
                accept="image/*"
                @change="handleFileChange"
              />
              <div v-if="imagePreview" class="w-24 h-24 rounded-lg overflow-hidden border border-slate-200">
                <img :src="imagePreview" class="w-full h-full object-cover" />
              </div>
              <div v-else class="text-slate-400 flex flex-col items-center space-y-1">
                <UploadCloud class="w-8 h-8 stroke-[1.2]" />
                <span class="text-xxs font-medium">Rasm yuklash uchun bosing</span>
              </div>
              <span class="text-4xs text-slate-400">JPEG, PNG, WEBP (Maks: 5MB)</span>
            </div>
          </div>
        </div>

        <!-- Total Actions -->
        <div class="border-t border-slate-100 pt-4 flex justify-end space-x-2">
          <button @click="showFoodModal = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 transition">
            Bekor qilish
          </button>
          <button
            @click="submitFoodForm"
            :disabled="submitting"
            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-xl text-xs font-semibold transition"
          >
            {{ submitting ? 'Saqlanmoqda...' : 'Saqlash' }}
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import {
  Plus, Edit3, Trash2, Search, X, Loader2, ChefHat, UploadCloud
} from 'lucide-vue-next';
import { useMenuStore } from '@/stores/menu';
import { useIngredientsStore } from '@/stores/ingredients';
import { useRecipesStore } from '@/stores/recipes';

const menuStore = useMenuStore();
const ingredientsStore = useIngredientsStore();
const recipesStore = useRecipesStore();

const MAX_IMAGE_BYTES = 5 * 1024 * 1024; // 5MB, matches the backend limit

// States
const searchQuery = ref('');
let searchTimeout = null;

const showCategoryModal = ref(false);
const editingCategory = ref(null);
const categoryForm = ref({ name: '' });

const showFoodModal = ref(false);
const editingFood = ref(null);
const submitting = ref(false);
const foodForm = ref({
  name: '',
  price: 0,
  category_id: '',
  description: '',
  is_available: true,
  sizes: []
});
const recipeRows = ref([]);
const imageFile = ref(null);
const imagePreview = ref(null);

// Lifecycle
onMounted(async () => {
  await menuStore.fetchCategories();
  await menuStore.fetchFoods();
  await ingredientsStore.fetchIngredients();
});

// Computed Count calculations
const totalFoodsCount = computed(() => {
  return menuStore.categories.reduce((acc, c) => acc + (c.foods_count || 0), 0);
});

// Category actions
const selectCategory = async (id) => {
  menuStore.selectedCategoryId = id;
  await menuStore.fetchFoods({ search: searchQuery.value });
};

const triggerSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(async () => {
    await menuStore.fetchFoods({ search: searchQuery.value });
  }, 350);
};

const openCategoryModal = (cat = null) => {
  editingCategory.value = cat;
  categoryForm.value.name = cat ? cat.name : '';
  showCategoryModal.value = true;
};

const submitCategoryForm = async () => {
  if (!categoryForm.value.name.trim()) return;

  try {
    if (editingCategory.value) {
      await menuStore.updateCategory(editingCategory.value.id, categoryForm.value.name);
    } else {
      await menuStore.createCategory(categoryForm.value.name);
    }
    showCategoryModal.value = false;
  } catch (err) {
    alert(err.message);
  }
};

const handleDeleteCategory = async (cat) => {
  if (!confirm(`"${cat.name}" kategoriyasini o'chirmoqchimisiz?`)) return;
  try {
    await menuStore.deleteCategory(cat.id);
  } catch (err) {
    alert(err.message);
  }
};

// Recipe row actions
const addRecipeRow = () => {
  recipeRows.value.push({ ingredient_id: '', quantity_required: '' });
};

const removeRecipeRow = (idx) => {
  recipeRows.value.splice(idx, 1);
};

// Size row actions
const addSizeRow = () => {
  if (!foodForm.value.sizes) {
    foodForm.value.sizes = [];
  }
  foodForm.value.sizes.push({
    name: '',
    price: '',
    recipe_multiplier: 1.0
  });
};

const addSizeTemplate = (name, multiplier) => {
  if (!foodForm.value.sizes) {
    foodForm.value.sizes = [];
  }
  const basePrice = parseFloat(foodForm.value.price) || 0;
  foodForm.value.sizes.push({
    name,
    price: multiplier < 1 ? Math.round(basePrice * multiplier) : basePrice,
    recipe_multiplier: multiplier
  });
};

const removeSizeRow = (idx) => {
  foodForm.value.sizes.splice(idx, 1);
};

// Food actions
const openFoodModal = async (food = null) => {
  editingFood.value = food;
  imageFile.value = null;
  imagePreview.value = food ? food.image_url : null;
  recipeRows.value = [];

  foodForm.value = food ? {
    name: food.name,
    price: parseFloat(food.price),
    category_id: food.category_id,
    description: food.description || '',
    is_available: food.is_available,
    sizes: food.sizes ? JSON.parse(JSON.stringify(food.sizes)) : []
  } : {
    name: '',
    price: '',
    category_id: menuStore.selectedCategoryId || '',
    description: '',
    is_available: true,
    sizes: []
  };

  showFoodModal.value = true;

  // Load the existing recipe so ingredient quantities can be edited alongside the dish
  if (food) {
    await recipesStore.fetchRecipeForFood(food.id);
    recipeRows.value = (recipesStore.recipe || []).map(r => ({
      ingredient_id: r.ingredient_id,
      quantity_required: parseFloat(r.quantity_required)
    }));
  }
};

const handleFileChange = (e) => {
  const file = e.target.files[0];
  if (!file) return;

  if (file.size > MAX_IMAGE_BYTES) {
    alert('Rasm hajmi juda katta. Iltimos 5MB dan kichik rasm tanlang.');
    e.target.value = '';
    return;
  }

  imageFile.value = file;
  imagePreview.value = URL.createObjectURL(file);
};

const submitFoodForm = async () => {
  if (!foodForm.value.name.trim() || !foodForm.value.price || !foodForm.value.category_id) {
    alert('Barcha majburiy maydonlarni to\'ldiring.');
    return;
  }

  const formData = new FormData();
  formData.append('name', foodForm.value.name);
  formData.append('price', foodForm.value.price);
  formData.append('category_id', foodForm.value.category_id);
  formData.append('description', foodForm.value.description);
  formData.append('is_available', foodForm.value.is_available ? '1' : '0');

  if (imageFile.value) {
    formData.append('image', imageFile.value);
  }

  // Clean sizes from empty inputs
  const cleanedSizes = (foodForm.value.sizes || []).filter(s => s.name && s.price);
  formData.append('sizes', JSON.stringify(cleanedSizes));

  // Ingredient quantities are saved together with the dish in one request
  const cleanedIngredients = recipeRows.value.filter(r => r.ingredient_id && r.quantity_required > 0);
  formData.append('ingredients', JSON.stringify(cleanedIngredients));

  submitting.value = true;
  try {
    if (editingFood.value) {
      // Adding _method=PUT to bypass PHP file upload body limitations on PUT
      formData.append('_method', 'PUT');
      await menuStore.updateFood(editingFood.value.id, formData);
    } else {
      await menuStore.createFood(formData);
    }
    showFoodModal.value = false;
  } catch (err) {
    if (err.errors) {
      const messages = Object.values(err.errors).flat().join('\n');
      alert(messages || err.message);
    } else {
      alert(err.message);
    }
  } finally {
    submitting.value = false;
  }
};

const handleToggleAvailable = async (food) => {
  try {
    await menuStore.toggleFoodAvailability(food.id);
  } catch (err) {
    alert(err.message);
  }
};

const handleDeleteFood = async (food) => {
  if (!confirm(`"${food.name}" taomini menyudan o'chirmoqchimisiz?`)) return;
  try {
    await menuStore.deleteFood(food.id);
  } catch (err) {
    alert(err.message);
  }
};

// Utilities
const formatCurrency = (val) => {
  return new Intl.NumberFormat('uz-UZ').format(val) + ' UZS';
};
</script>

<style scoped>
.text-xxs {
  font-size: 0.7rem;
}
.text-3xs {
  font-size: 0.6rem;
}
.text-4xs {
  font-size: 0.55rem;
}
.animate-scaleIn {
  animation: scaleIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes scaleIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}
</style>
