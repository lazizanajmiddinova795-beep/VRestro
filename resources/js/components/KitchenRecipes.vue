<template>
  <ChefLayout>
    <div class="max-w-6xl mx-auto space-y-6 pb-12">
      <!-- Top Title & Search -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <div>
          <h2 class="text-xl font-black text-slate-900 tracking-tight flex items-center space-x-2">
            <BookOpen class="w-6 h-6 text-indigo-600" />
            <span>Taomlar Retsepti (Kuzatish)</span>
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
            <div class="overflow-hidden">
              <h3 class="text-base font-black text-slate-900 truncate">{{ food.name }}</h3>
              <span class="text-xs font-bold text-slate-500 block">{{ food.category?.name || 'Kategoriya' }}</span>
            </div>
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
  </ChefLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import ChefLayout from '@/components/ChefLayout.vue';
import { useAuthStore } from '@/stores/auth';
import { BookOpen, Search, Loader2, AlertTriangle, Utensils } from 'lucide-vue-next';

const authStore = useAuthStore();
const foods = ref([]);
const loading = ref(true);
const error = ref('');
const searchQuery = ref('');

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

onMounted(() => {
  fetchRecipes();
});
</script>
