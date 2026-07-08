<template>
  <div class="flex-grow p-6 flex flex-col md:flex-row h-screen overflow-hidden gap-6">
    
    <!-- Left Column: Food Selector -->
    <aside class="w-full md:w-80 backdrop-blur-md bg-slate-900/40 border border-white/5 rounded-3xl p-5 flex flex-col shrink-0 h-full overflow-hidden">
      <div class="space-y-4 flex flex-col h-full overflow-hidden">
        <!-- Header -->
        <div class="shrink-0">
          <h2 class="text-lg font-bold text-white tracking-wide">Taomlar Ro'yxati</h2>
          <p class="text-3xs text-slate-400">Retsept kiritish uchun taomni tanlang</p>
        </div>

        <!-- Search foods -->
        <div class="relative shrink-0">
          <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
            <Search class="w-3.5 h-3.5" />
          </span>
          <input 
            v-model="foodSearch"
            type="text" 
            placeholder="Taomni qidirish..."
            class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-xs placeholder-slate-500 text-white focus:outline-none transition"
          />
        </div>

        <!-- Foods list -->
        <div class="overflow-y-auto flex-grow pr-1 space-y-2">
          <button 
            v-for="food in filteredFoods" 
            :key="food.id"
            @click="selectFood(food)"
            class="w-full text-left p-3.5 rounded-xl border flex items-center justify-between transition duration-200"
            :class="selectedFood?.id === food.id 
              ? 'bg-indigo-600/20 border-indigo-500/50 shadow-lg text-white' 
              : 'bg-white/2 border-white/5 text-slate-400 hover:bg-white/5 hover:text-slate-200'"
          >
            <div class="truncate pr-2">
              <span class="block text-xs font-bold truncate">{{ food.name }}</span>
              <span class="block text-4xs uppercase tracking-wider text-slate-500 mt-0.5">{{ food.category?.name }}</span>
            </div>
            <ChefHat class="w-4 h-4 shrink-0 opacity-40" />
          </button>
        </div>
      </div>
    </aside>

    <!-- Right Column: Recipe Builder Panel -->
    <main class="flex-grow flex flex-col h-full overflow-hidden">
      <!-- Welcome State -->
      <div 
        v-if="!selectedFood" 
        class="flex-grow backdrop-blur-md bg-slate-900/40 border border-white/5 rounded-3xl p-6 flex flex-col items-center justify-center space-y-4"
      >
        <div class="w-16 h-16 rounded-3xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
          <BookOpen class="w-8 h-8" />
        </div>
        <div class="text-center space-y-1">
          <h3 class="text-sm font-bold text-white">Retsept Sozlagich</h3>
          <p class="text-xs text-slate-400 max-w-xs leading-relaxed">Menyudagi taom tarkibini sozlashingiz uchun chap tarafdan biron bir taomni tanlang.</p>
        </div>
      </div>

      <!-- Active Builder State -->
      <div v-else class="flex-grow flex flex-col h-full overflow-hidden space-y-6">
        <!-- Top Status Bar -->
        <div class="backdrop-blur-md bg-slate-900/40 border border-white/5 rounded-3xl p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shrink-0">
          <div class="flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl overflow-hidden bg-slate-950 border border-white/5 shrink-0">
              <img v-if="selectedFood.image_url" :src="selectedFood.image_url" class="w-full h-full object-cover" />
              <div v-else class="w-full h-full flex items-center justify-center text-slate-600">
                <ChefHat class="w-6 h-6" />
              </div>
            </div>
            <div>
              <h2 class="text-base font-bold text-white tracking-wide">{{ selectedFood.name }}</h2>
              <span class="px-2 py-0.5 rounded bg-white/5 text-slate-400 border border-white/10 text-4xs uppercase tracking-wider font-semibold">
                {{ selectedFood.category?.name }}
              </span>
            </div>
          </div>

          <!-- Portions Capacity Alert -->
          <div 
            class="px-4 py-2.5 rounded-2xl border flex items-center space-x-3 text-xs"
            :class="portionsCapacityClass"
          >
            <Sparkles class="w-4 h-4 shrink-0" />
            <div>
              <span class="block text-4xs uppercase tracking-wider opacity-60 font-bold">Pishirish Imkoniyati</span>
              <span class="font-bold">
                {{ formatCapacityText }}
              </span>
            </div>
          </div>
        </div>

        <!-- Recipe Workspace -->
        <div class="flex-grow backdrop-blur-md bg-slate-900/40 border border-white/5 rounded-3xl p-6 flex flex-col h-full overflow-hidden justify-between">
          <div class="space-y-4 flex flex-col h-full overflow-hidden">
            <!-- Header -->
            <div class="flex justify-between items-center border-b border-white/5 pb-3 shrink-0">
              <h3 class="text-sm font-bold text-white">Kerakli Masalliqlar</h3>
              <button 
                @click="addRow"
                class="px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-3xs font-bold transition flex items-center space-x-1"
              >
                <Plus class="w-3.5 h-3.5" />
                <span>Masalliq Qo'shish</span>
              </button>
            </div>

            <!-- Dynamic Rows List -->
            <div class="flex-grow overflow-y-auto pr-1 space-y-3.5 pb-6">
              <div 
                v-for="(row, idx) in recipeRows" 
                :key="idx"
                class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center bg-white/2 hover:bg-white/4 border border-white/5 rounded-2xl p-3.5 transition animate-rowIn"
              >
                <!-- Searchable Ingredient selector -->
                <div class="sm:col-span-6 space-y-1">
                  <label class="text-4xs text-slate-500 font-bold uppercase tracking-wider block sm:hidden">Masalliq</label>
                  <select 
                    v-model="row.ingredient_id"
                    @change="handleSelectIngredient($event, idx)"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 text-xs text-white focus:outline-none transition appearance-none"
                  >
                    <option value="" disabled class="bg-slate-900 text-slate-500">Tanlang...</option>
                    <option 
                      v-for="ing in ingredientsStore.ingredients" 
                      :key="ing.id" 
                      :value="ing.id" 
                      class="bg-slate-900"
                    >
                      {{ ing.name }} (SKU: {{ ing.sku }})
                    </option>
                  </select>
                </div>

                <!-- Quantity Required input -->
                <div class="sm:col-span-4 relative space-y-1">
                  <label class="text-4xs text-slate-500 font-bold uppercase tracking-wider block sm:hidden">Miqdori</label>
                  <input 
                    v-model.number="row.quantity_required"
                    type="number" 
                    step="0.001"
                    placeholder="Kerakli miqdor..."
                    class="w-full pl-3.5 pr-12 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 text-xs text-white focus:outline-none transition"
                  />
                  <span class="absolute right-4 bottom-2.5 text-xs text-slate-500 font-semibold uppercase">
                    {{ row.unit || 'birlik' }}
                  </span>
                </div>

                <!-- Remove Action -->
                <div class="sm:col-span-2 text-right pt-2 sm:pt-0">
                  <button 
                    @click="removeRow(idx)"
                    class="p-2.5 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500 hover:text-white transition duration-200"
                    title="Masalliqni o'chirish"
                  >
                    <Trash2 class="w-4 h-4 mx-auto sm:mx-0" />
                  </button>
                </div>
              </div>

              <!-- Empty formulas message -->
              <div v-if="recipeRows.length === 0" class="flex flex-col items-center justify-center py-20 text-slate-500 space-y-2">
                <ChefHat class="w-10 h-10 stroke-[1.2]" />
                <p class="text-xxs font-medium">Bu taom tarkibida hali masalliqlar mavjud emas.</p>
              </div>
            </div>
          </div>

          <!-- Sync Action bottom bar -->
          <div class="border-t border-white/5 pt-4 flex justify-between items-center shrink-0">
            <span class="text-3xs text-slate-500">* Miqdorlar 1 porsiya taom uchun hisoblab kiritiladi.</span>
            
            <button 
              @click="submitRecipe"
              :disabled="recipesStore.loading"
              class="px-6 py-3 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 font-semibold text-xs text-white shadow-lg transition duration-200 flex items-center justify-center space-x-2"
            >
              <Loader2 v-if="recipesStore.loading" class="w-4 h-4 animate-spin text-white" />
              <Save v-else class="w-4 h-4" />
              <span>Retseptni Saqlash</span>
            </button>
          </div>
        </div>
      </div>
    </main>

  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { 
  Search, ChefHat, BookOpen, Trash2, Plus, Sparkles, Loader2, Save
} from 'lucide-vue-next';
import { useMenuStore } from '@/stores/menu';
import { useIngredientsStore } from '@/stores/ingredients';
import { useRecipesStore } from '@/stores/recipes';

const menuStore = useMenuStore();
const ingredientsStore = useIngredientsStore();
const recipesStore = useRecipesStore();

// States
const foodSearch = ref('');
const selectedFood = ref(null);
const recipeRows = ref([]);

// Lifecycle
onMounted(async () => {
  await menuStore.fetchFoods();
  await ingredientsStore.fetchIngredients();
});

// Computed foods filter
const filteredFoods = computed(() => {
  if (!foodSearch.value.trim()) {
    return menuStore.foods;
  }
  const query = foodSearch.value.toLowerCase();
  return menuStore.foods.filter(f => f.name.toLowerCase().includes(query));
});

// Select food load recipe formula
const selectFood = async (food) => {
  selectedFood.value = food;
  recipeRows.value = [];
  
  await recipesStore.fetchRecipeForFood(food.id);
  
  // Map pivot records to repeater model
  recipeRows.value = recipesStore.recipe.map(r => ({
    ingredient_id: r.ingredient_id,
    quantity_required: parseFloat(r.quantity_required),
    unit: r.ingredient?.unit || ''
  }));
};

// Selection dropdown unit tracker
const handleSelectIngredient = (e, index) => {
  const ingId = parseInt(e.target.value);
  const found = ingredientsStore.ingredients.find(i => i.id === ingId);
  if (found) {
    recipeRows.value[index].unit = found.unit;
  }
};

// Row modifiers
const addRow = () => {
  recipeRows.value.push({
    ingredient_id: '',
    quantity_required: '',
    unit: ''
  });
};

const removeRow = (index) => {
  recipeRows.value.splice(index, 1);
};

// Submit payload
const submitRecipe = async () => {
  // Validate duplicates
  const ingIds = recipeRows.value.map(r => r.ingredient_id).filter(id => id !== '');
  const duplicates = ingIds.filter((item, index) => ingIds.indexOf(item) !== index);
  
  if (duplicates.length > 0) {
    alert('Bir xil masalliq bir necha marta tanlangan. Ularni birlashtiring.');
    return;
  }

  // Validate quantities
  const invalid = recipeRows.value.some(r => !r.ingredient_id || isNaN(parseFloat(r.quantity_required)) || parseFloat(r.quantity_required) <= 0);
  if (invalid) {
    alert("Barcha maydonlarni to'g'ri to'ldiring (Miqdor noldan katta bo'lishi shart).");
    return;
  }

  try {
    const payload = recipeRows.value.map(r => ({
      ingredient_id: r.ingredient_id,
      quantity_required: parseFloat(r.quantity_required)
    }));

    await recipesStore.saveRecipe(selectedFood.value.id, payload);
    alert('Retsept muvaffaqiyatli saqlandi!');
  } catch (err) {
    alert(err.message);
  }
};

// Portions yield styling computed helpers
const portionsCapacityClass = computed(() => {
  const cap = recipesStore.portionCapacity;
  if (cap === null) return 'bg-slate-800 border-slate-700 text-slate-400';
  if (cap === 0) return 'bg-red-500/10 border-red-500/20 text-red-400';
  if (cap < 10) return 'bg-yellow-500/10 border-yellow-500/20 text-yellow-400';
  return 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400';
});

const formatCapacityText = computed(() => {
  const cap = recipesStore.portionCapacity;
  if (cap === null) return 'Masalliq kiritilmagan';
  if (cap === 0) return 'Tayyorlash imkonsiz!';
  return `${cap} ta porsiya`;
});
</script>

<style scoped>
.text-3xs {
  font-size: 0.6rem;
}
.text-4xs {
  font-size: 0.55rem;
}

.animate-scaleIn {
  animation: scaleIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
.animate-rowIn {
  animation: rowIn 0.2s ease-out forwards;
}

@keyframes scaleIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}

@keyframes rowIn {
  from { opacity: 0; transform: translateY(4px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
