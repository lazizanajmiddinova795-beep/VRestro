<template>
  <ChefLayout>
    <div class="max-w-6xl mx-auto space-y-6 pb-12">
      <!-- Top Title & Search -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <div>
          <h2 class="text-xl font-black text-slate-900 tracking-tight flex items-center space-x-2">
            <BookOpen class="w-6 h-6 text-indigo-600" />
            <span>Taomlar Retsepti</span>
          </h2>
          <p class="text-xs text-slate-500 font-bold mt-1">Har bir taomning tarkibiy masalliqlar ro'yxati va aniq miqdorlari</p>
        </div>

        <div class="relative w-full md:w-72">
          <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Taom nomini izlash..."
            class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:border-indigo-500 transition"
          />
        </div>
      </div>

      <!-- Loading / Error States -->
      <div v-if="loading" class="flex flex-col items-center justify-center py-24 space-y-3">
        <Loader2 class="w-10 h-10 text-indigo-600 animate-spin" />
        <span class="text-xs text-slate-500 font-bold">Retseptlar yuklanmoqda...</span>
      </div>

      <div v-else-if="error" class="p-6 bg-red-50 border border-red-200 rounded-2xl text-center space-y-3">
        <AlertTriangle class="w-10 h-10 text-red-500 mx-auto" />
        <p class="text-sm font-bold text-red-700">{{ error }}</p>
      </div>

      <!-- Foods & Recipes List -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="food in filteredFoods"
          :key="food.id"
          :id="`food-card-${food.id}`"
          class="bg-white border border-slate-200 rounded-2xl shadow-sm hover:border-slate-300 transition duration-200"
          :class="{
            'border-indigo-200 bg-indigo-50/30': expandedFoodId === food.id,
            'ring-4 ring-emerald-300/60 border-emerald-300': flashFoodId === food.id
          }">
          <div
            class="flex items-center space-x-3 p-4 md:p-5 md:pb-3 md:border-b md:border-slate-100 cursor-pointer md:cursor-default"
            @click="toggleFood(food.id)"
          >
            <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden shrink-0 flex items-center justify-center">
              <img v-if="food.image_url" :src="food.image_url" :alt="food.name" class="w-full h-full object-cover" />
              <Utensils v-else class="w-5 h-5 md:w-6 md:h-6 text-slate-400" />
            </div>
            <div class="overflow-hidden flex-grow">
              <h3 class="text-sm md:text-base font-black text-slate-900 truncate">{{ food.name }}</h3>
              <span class="text-xs font-bold text-slate-500 block">{{ food.category?.name || 'Kategoriya' }}</span>
            </div>
            <!-- Mobile expand chevron -->
            <ChevronDown
              class="w-5 h-5 text-slate-400 shrink-0 transition-transform duration-200 md:hidden"
              :class="{'rotate-180 text-indigo-600': expandedFoodId === food.id}"
            />
            <button
              v-if="canEdit"
              @click.stop="openEditModal(food)"
              class="p-2 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 hover:bg-indigo-600 hover:text-white transition duration-200 shrink-0 hidden md:block"
              title="Retseptni tahrirlash"
            >
              <Edit3 class="w-4 h-4" />
            </button>
          </div>

          <!-- Recipe Ingredients List: always visible on md+, collapsible on mobile -->
          <div
            class="overflow-hidden transition-all duration-200"
            :class="expandedFoodId === food.id ? 'max-h-96 opacity-100' : 'max-h-0 opacity-0 md:max-h-none md:opacity-100'"
          >
            <div class="px-4 pb-4 md:p-5 md:pt-0 space-y-2">
              <!-- Mobile edit button -->
              <button
                v-if="canEdit"
                @click.stop="openEditModal(food)"
                class="w-full py-2 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 text-xs font-bold flex items-center justify-center space-x-1 md:hidden mb-2"
              >
                <Edit3 class="w-3.5 h-3.5" />
                <span>Retseptni tahrirlash</span>
              </button>
              <h4 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider">Tarkibiy Masalliqlar:</h4>
              <div v-if="food.recipes && food.recipes.length > 0" class="divide-y divide-slate-100">
                <div
                  v-for="recipe in food.recipes"
                  :key="recipe.id"
                  class="py-2 flex items-center justify-between text-xs"
                >
                  <span class="font-bold text-slate-800">{{ recipe.ingredient?.name || 'Masalliq' }}</span>
                  <span class="font-extrabold text-indigo-600 bg-indigo-50 border border-indigo-100 px-2 py-0.5 rounded-md">
                    {{ recipe.quantity_required }} {{ recipe.ingredient?.unit || 'kg' }}
                  </span>
                </div>
              </div>
              <div v-else class="text-xs text-slate-400 italic py-2">
                Retsept masalliqlari kiritilmagan.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL: Edit Recipe (Admin / Chef with permission) -->
    <div
      v-if="showEditModal"
      class="fixed inset-0 z-50 backdrop-blur-sm bg-slate-900/40 flex items-center justify-center p-4 md:p-6"
      @click.self="showEditModal = false"
    >
      <div class="w-full max-w-xl bg-white border border-slate-200 rounded-3xl shadow-2xl animate-scaleIn max-h-[90vh] flex flex-col overflow-hidden">

        <!-- Header -->
        <div class="flex items-start justify-between gap-4 px-6 py-5 border-b border-slate-100 shrink-0 bg-gradient-to-br from-indigo-50/60 to-white">
          <div class="flex items-center space-x-3.5 min-w-0">
            <div class="w-11 h-11 rounded-2xl bg-white border border-slate-200 overflow-hidden shrink-0 flex items-center justify-center shadow-sm">
              <img v-if="editingFood?.image_url" :src="editingFood.image_url" :alt="editingFood.name" class="w-full h-full object-cover" />
              <Utensils v-else class="w-5 h-5 text-slate-400" />
            </div>
            <div class="min-w-0">
              <h3 class="text-base font-black text-slate-900 truncate">{{ editingFood?.name }}</h3>
              <p class="text-xs text-slate-500 font-bold mt-0.5">
                Retsept tarkibi &middot; {{ validRowCount }} ta masalliq
              </p>
            </div>
          </div>
          <button @click="showEditModal = false" class="p-1.5 rounded-lg bg-white border border-slate-200 text-slate-500 hover:text-slate-900 hover:border-slate-300 transition shrink-0">
            <X class="w-4 h-4" />
          </button>
        </div>

        <!-- Column labels -->
        <div v-if="editRows.length > 0" class="hidden sm:flex items-center gap-2.5 px-6 pt-4 shrink-0 text-3xs font-extrabold uppercase tracking-wider text-slate-400">
          <span class="w-6"></span>
          <span class="flex-grow">Masalliq</span>
          <span class="w-32">Miqdori</span>
          <span class="w-8"></span>
        </div>

        <!-- Ingredient Rows -->
        <div class="flex-grow overflow-y-auto px-6 py-3 space-y-2.5">
          <div
            v-for="(row, idx) in editRows"
            :key="idx"
            class="flex gap-2.5 items-center bg-slate-50 border rounded-2xl p-3 transition-colors"
            :class="isDuplicateRow(row, idx) ? 'border-rose-300 bg-rose-50' : 'border-slate-200'"
          >
            <span class="w-6 h-6 rounded-full bg-white border border-slate-200 text-slate-400 text-3xs font-black flex items-center justify-center shrink-0">
              {{ idx + 1 }}
            </span>
            <select
              v-model.number="row.ingredient_id"
              class="flex-grow px-3 py-2.5 rounded-xl bg-white border border-slate-200 focus:border-indigo-500 text-xs font-semibold text-slate-900 focus:outline-none transition"
            >
              <option value="" disabled>Masalliqni tanlang...</option>
              <option v-for="ing in ingredientsStore.ingredients" :key="ing.id" :value="ing.id">
                {{ ing.name }}
              </option>
            </select>
            <div class="w-32 relative shrink-0">
              <input
                v-model.number="row.quantity_required"
                type="number"
                step="0.001"
                min="0"
                placeholder="0"
                class="w-full pl-3 pr-12 py-2.5 rounded-xl bg-white border border-slate-200 focus:border-indigo-500 text-xs font-bold text-slate-900 focus:outline-none transition"
              />
              <span class="absolute right-3 top-1/2 -translate-y-1/2 text-3xs font-extrabold text-slate-400 uppercase">
                {{ unitFor(row.ingredient_id) }}
              </span>
            </div>
            <button
              type="button"
              @click="editRows.splice(idx, 1)"
              class="p-2 rounded-xl bg-white border border-red-100 text-red-500 hover:bg-red-500 hover:text-white hover:border-red-500 transition shrink-0"
              title="O'chirish"
            >
              <Trash2 class="w-3.5 h-3.5" />
            </button>
          </div>

          <!-- Empty state -->
          <div v-if="editRows.length === 0" class="text-center py-10 space-y-2">
            <div class="w-12 h-12 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center mx-auto">
              <ChefHat class="w-6 h-6 text-slate-400" />
            </div>
            <p class="text-xs font-bold text-slate-400">Retsept hali bo'sh — birinchi masalliqni qo'shing</p>
          </div>

          <div v-if="hasDuplicateRows" class="flex items-center space-x-2 px-3 py-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-600 text-3xs font-bold">
            <AlertTriangle class="w-3.5 h-3.5 shrink-0" />
            <span>Bir xil masalliq bir necha marta tanlangan — qizil qatorlarni birlashtiring yoki o'chiring.</span>
          </div>

          <button
            type="button"
            @click="editRows.push({ ingredient_id: '', quantity_required: '' })"
            class="w-full px-3 py-3 rounded-xl bg-indigo-50 border border-dashed border-indigo-200 text-indigo-600 hover:bg-indigo-600 hover:text-white hover:border-solid text-xs font-bold transition flex items-center justify-center space-x-1.5"
          >
            <Plus class="w-4 h-4" />
            <span>Masalliq qo'shish</span>
          </button>
        </div>

        <!-- Footer -->
        <div class="flex justify-end space-x-2 px-6 py-4 border-t border-slate-100 shrink-0 bg-slate-50/60">
          <button @click="showEditModal = false" class="px-4 py-2.5 bg-white hover:bg-slate-100 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 transition">
            Bekor qilish
          </button>
          <button
            @click="saveRecipe"
            :disabled="saving || hasDuplicateRows"
            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl text-xs font-bold transition flex items-center space-x-2 shadow-md shadow-indigo-600/20"
          >
            <Loader2 v-if="saving" class="w-3.5 h-3.5 animate-spin" />
            <Save v-else class="w-3.5 h-3.5" />
            <span>{{ saving ? 'Saqlanmoqda...' : 'Retseptni saqlash' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Success Toast -->
    <Transition name="toast-fade">
      <div
        v-if="showSuccessToast"
        class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[60] bg-slate-900 text-white px-5 py-3 rounded-2xl shadow-2xl flex items-center space-x-2.5"
      >
        <CheckCircle2 class="w-4.5 h-4.5 text-emerald-400 shrink-0" />
        <span class="text-xs font-bold">{{ editingFoodName }} retsepti saqlandi!</span>
      </div>
    </Transition>
  </ChefLayout>
</template>

<script setup>
import { useSettingsStore } from '@/stores/settings';
const settingsStore = useSettingsStore();
import { ref, computed, onMounted } from 'vue';
import ChefLayout from '@/components/ChefLayout.vue';
import { useAuthStore } from '@/stores/auth';
import { useIngredientsStore } from '@/stores/ingredients';
import { BookOpen, Search, Loader2, AlertTriangle, Utensils, Edit3, X, Trash2, ChevronDown, Plus, ChefHat, Save, CheckCircle2 } from 'lucide-vue-next';

const authStore = useAuthStore();
const ingredientsStore = useIngredientsStore();
const foods = ref([]);
const loading = ref(true);
const error = ref('');
const searchQuery = ref('');
const expandedFoodId = ref(null);

const toggleFood = (foodId) => {
  expandedFoodId.value = expandedFoodId.value === foodId ? null : foodId;
};

// Chefs can view every recipe; only Admins (and Chefs, per the restaurant's
// own workflow) are allowed to change ingredient quantities here.
const canEdit = computed(() => {
  const role = authStore.user?.roles?.[0];
  return role === 'Admin' || role === 'Chef';
});

const fetchRecipes = async () => {
  loading.value = true;
  error.value = '';
  try {
    const response = await fetch('/api/menu/foods', {
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${authStore.token}`
      }
    });

    if (!response.ok) throw new Error('Retseptlarni yuklab bo\'lmadi.');
    const data = await response.json();
    foods.value = Array.isArray(data) ? data : (data.data || []);
  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
};

const filteredFoods = computed(() => {
  if (!searchQuery.value) return foods.value;
  const q = searchQuery.value.toLowerCase();
  return foods.value.filter(f => f.name.toLowerCase().includes(q));
});

// Edit modal state
const showEditModal = ref(false);
const editingFood = ref(null);
const editingFoodName = ref('');
const editRows = ref([]);
const saving = ref(false);
const showSuccessToast = ref(false);
const flashFoodId = ref(null);

const openEditModal = (food) => {
  editingFood.value = food;
  editRows.value = (food.recipes || []).map(r => ({
    ingredient_id: r.ingredient_id,
    quantity_required: parseFloat(r.quantity_required)
  }));
  showEditModal.value = true;
};

// Ingredient unit shown next to the quantity field, e.g. "kg" / "l" / "dona"
const unitFor = (ingredientId) => {
  const ing = ingredientsStore.ingredients.find(i => i.id === ingredientId);
  return ing?.unit || '';
};

const validRowCount = computed(() => editRows.value.filter(r => r.ingredient_id && r.quantity_required > 0).length);

const isDuplicateRow = (row, idx) => {
  if (!row.ingredient_id) return false;
  return editRows.value.some((r, i) => i !== idx && r.ingredient_id === row.ingredient_id);
};

const hasDuplicateRows = computed(() => editRows.value.some((row, idx) => isDuplicateRow(row, idx)));

const saveRecipe = async () => {
  const ingredients = editRows.value.filter(r => r.ingredient_id && r.quantity_required > 0);

  if (hasDuplicateRows.value) {
    alert('Bir xil masalliq bir necha marta tanlangan. Ularni birlashtiring.');
    return;
  }

  saving.value = true;
  try {
    const foodId = editingFood.value.id;
    const response = await fetch(`/api/menu/foods/${foodId}/recipe`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${authStore.token}`
      },
      body: JSON.stringify({ ingredients })
    });

    const data = await response.json();
    if (!response.ok) {
      const messages = data.errors ? Object.values(data.errors).flat().join('\n') : (data.message || 'Retseptni saqlashda xatolik.');
      throw new Error(messages);
    }

    editingFoodName.value = editingFood.value.name;
    showEditModal.value = false;
    await fetchRecipes();

    // Return the chef straight to that dish's card in the list, expanded and
    // briefly highlighted, so it's obvious the save actually landed - instead
    // of just closing the modal and leaving them wondering.
    expandedFoodId.value = foodId;
    flashFoodId.value = foodId;
    showSuccessToast.value = true;
    setTimeout(() => {
      document.getElementById(`food-card-${foodId}`)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }, 50);
    setTimeout(() => { flashFoodId.value = null; }, 2000);
    setTimeout(() => { showSuccessToast.value = false; }, 3000);
  } catch (err) {
    alert(err.message);
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchRecipes();
  ingredientsStore.fetchIngredients();
});
</script>

<style scoped>
.animate-scaleIn {
  animation: scaleIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes scaleIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}

.toast-fade-enter-active,
.toast-fade-leave-active {
  transition: all 0.25s ease;
}
.toast-fade-enter-from,
.toast-fade-leave-to {
  opacity: 0;
  transform: translate(-50%, 10px);
}
</style>
