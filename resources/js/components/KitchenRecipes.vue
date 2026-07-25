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
          class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4 hover:border-slate-300 transition duration-200"
        >
          <div class="flex items-center space-x-3 pb-3 border-b border-slate-100">
            <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden shrink-0 flex items-center justify-center">
              <img v-if="food.image_url" :src="food.image_url" :alt="food.name" class="w-full h-full object-cover" />
              <Utensils v-else class="w-6 h-6 text-slate-400" />
            </div>
            <div class="overflow-hidden flex-grow">
              <h3 class="text-base font-black text-slate-900 truncate">{{ food.name }}</h3>
              <span class="text-xs font-bold text-slate-500 block">{{ food.category?.name || 'Kategoriya' }}</span>
            </div>
            <button
              v-if="canEdit"
              @click="openEditModal(food)"
              class="p-2 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 hover:bg-indigo-600 hover:text-white transition duration-200 shrink-0"
              title="Retseptni tahrirlash"
            >
              <Edit3 class="w-4 h-4" />
            </button>
          </div>

          <!-- Recipe Ingredients List -->
          <div class="space-y-2">
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

    <!-- MODAL: Edit Recipe (Admin / Chef with permission) -->
    <div
      v-if="showEditModal"
      class="fixed inset-0 z-50 backdrop-blur-sm bg-slate-900/30 flex items-center justify-center p-6"
      @click.self="showEditModal = false"
    >
      <div class="w-full max-w-lg bg-white border border-slate-200 rounded-3xl p-6 shadow-2xl space-y-5 animate-scaleIn max-h-[85vh] flex flex-col">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3 shrink-0">
          <h3 class="text-base font-bold text-slate-900">{{ editingFood?.name }} — Retseptni Tahrirlash</h3>
          <button @click="showEditModal = false" class="p-1 rounded-lg bg-slate-100 text-slate-500 hover:text-slate-900 transition">
            <X class="w-4 h-4" />
          </button>
        </div>

        <div class="flex-grow overflow-y-auto space-y-3 pr-1">
          <div
            v-for="(row, idx) in editRows"
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
              @click="editRows.splice(idx, 1)"
              class="p-1.5 rounded-lg bg-red-50 border border-red-100 text-red-500 hover:bg-red-500 hover:text-white transition shrink-0"
            >
              <Trash2 class="w-3.5 h-3.5" />
            </button>
          </div>
          <button
            type="button"
            @click="editRows.push({ ingredient_id: '', quantity_required: '' })"
            class="w-full px-3 py-2 rounded-lg bg-indigo-50 border border-indigo-100 text-indigo-600 hover:bg-indigo-600 hover:text-white text-xs font-bold transition"
          >
            + Masalliq qo'shish
          </button>
        </div>

        <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100 shrink-0">
          <button @click="showEditModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 transition">
            Bekor qilish
          </button>
          <button
            @click="saveRecipe"
            :disabled="saving"
            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-xl text-xs font-semibold transition"
          >
            {{ saving ? 'Saqlanmoqda...' : 'Saqlash' }}
          </button>
        </div>
      </div>
    </div>
  </ChefLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import ChefLayout from '@/components/ChefLayout.vue';
import { useAuthStore } from '@/stores/auth';
import { useIngredientsStore } from '@/stores/ingredients';
import { BookOpen, Search, Loader2, AlertTriangle, Utensils, Edit3, X, Trash2 } from 'lucide-vue-next';

const authStore = useAuthStore();
const ingredientsStore = useIngredientsStore();
const foods = ref([]);
const loading = ref(true);
const error = ref('');
const searchQuery = ref('');

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
const editRows = ref([]);
const saving = ref(false);

const openEditModal = (food) => {
  editingFood.value = food;
  editRows.value = (food.recipes || []).map(r => ({
    ingredient_id: r.ingredient_id,
    quantity_required: parseFloat(r.quantity_required)
  }));
  showEditModal.value = true;
};

const saveRecipe = async () => {
  const ingredients = editRows.value.filter(r => r.ingredient_id && r.quantity_required > 0);

  if (ingredients.some(r => editRows.value.filter(x => x.ingredient_id === r.ingredient_id).length > 1)) {
    alert('Bir xil masalliq bir necha marta tanlangan. Ularni birlashtiring.');
    return;
  }

  saving.value = true;
  try {
    const response = await fetch(`/api/menu/foods/${editingFood.value.id}/recipe`, {
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

    showEditModal.value = false;
    await fetchRecipes();
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
</style>
