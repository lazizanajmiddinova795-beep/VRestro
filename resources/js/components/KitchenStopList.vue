<template>
  <ChefLayout>
    <div class="space-y-6">
      <!-- Loading spinner -->
      <div v-if="chefStore.stopListLoading" class="flex flex-col items-center justify-center py-20 space-y-4">
        <div class="w-12 h-12 rounded-full border-4 border-orange-500/20 border-t-orange-500 animate-spin"></div>
        <p class="text-slate-400 font-medium font-sans">Menyu yuklanmoqda...</p>
      </div>

      <!-- Error message -->
      <div v-else-if="chefStore.error" class="p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 flex items-center justify-between">
        <span>{{ chefStore.error }}</span>
        <button @click="chefStore.fetchMenu" class="px-3 py-1 bg-red-500/20 rounded-lg text-xs font-semibold hover:bg-red-500/30 transition">
          Qayta yuklash
        </button>
      </div>

      <template v-else>
        <!-- Category Navigation Rail -->
        <div class="flex items-center space-x-2 overflow-x-auto pb-3 scrollbar-thin scrollbar-thumb-white/10">
          <button 
            @click="activeCategory = null"
            class="px-5 py-3 rounded-xl border font-bold text-sm transition-all duration-200 shrink-0 flex items-center space-x-2 backdrop-blur-md"
            :class="activeCategory === null 
              ? 'bg-orange-500 border-orange-500 text-white shadow-lg shadow-orange-500/20' 
              : 'bg-white/5 border-white/5 text-slate-300 hover:bg-white/10 hover:border-white/10'"
          >
            <span>Barchasi</span>
            <span 
              class="px-2 py-0.5 rounded-full text-xs font-extrabold"
              :class="activeCategory === null ? 'bg-white/20 text-white' : 'bg-white/10 text-slate-400'"
            >
              {{ totalStopListedCount }}
            </span>
          </button>

          <button 
            v-for="category in chefStore.menu" 
            :key="category.id"
            @click="activeCategory = category.id"
            class="px-5 py-3 rounded-xl border font-bold text-sm transition-all duration-200 shrink-0 flex items-center space-x-2 backdrop-blur-md"
            :class="activeCategory === category.id 
              ? 'bg-orange-500 border-orange-500 text-white shadow-lg shadow-orange-500/20' 
              : 'bg-white/5 border-white/5 text-slate-300 hover:bg-white/10 hover:border-white/10'"
          >
            <span>{{ category.name }}</span>
            <span 
              v-if="getCategoryStopCount(category) > 0"
              class="px-2 py-0.5 rounded-full text-xs font-extrabold bg-red-500/30 border border-red-500/30 text-red-300 animate-pulse"
            >
              {{ getCategoryStopCount(category) }}
            </span>
            <span 
              v-else 
              class="px-2 py-0.5 rounded-full text-xs font-extrabold bg-white/10 text-slate-400"
            >
              0
            </span>
          </button>
        </div>

        <!-- Food availability Grid matrix -->
        <div v-for="category in filteredMenu" :key="category.id" class="space-y-3">
          <h3 class="text-sm font-bold text-slate-400 tracking-wider uppercase pl-1 border-l-2 border-orange-500">
            {{ category.name }}
          </h3>
          
          <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            <div 
              v-for="food in category.foods" 
              :key="food.id"
              @click="toggleAvailability(food)"
              class="group relative flex flex-col justify-between rounded-2xl border bg-slate-900/40 backdrop-blur-md overflow-hidden cursor-pointer select-none transition-all duration-300 active:scale-[0.97]"
              :class="[
                food.is_available 
                  ? 'border-white/5 hover:border-emerald-500/30 shadow-black/20 hover:shadow-emerald-950/10' 
                  : 'border-red-500/20 bg-red-950/5 opacity-40 hover:opacity-50'
              ]"
            >
              <!-- Card Top Image / Icon -->
              <div class="relative aspect-video w-full bg-slate-950 overflow-hidden flex items-center justify-center border-b border-white/5">
                <img 
                  v-if="food.image_url" 
                  :src="food.image_url" 
                  :alt="food.name"
                  class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                />
                <div v-else class="flex flex-col items-center justify-center text-slate-600">
                  <ChefHat class="w-8 h-8 opacity-40" />
                </div>

                <!-- Glow effects / status badges on image -->
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 to-transparent"></div>
                <div class="absolute top-2 right-2">
                  <span 
                    class="text-[10px] font-bold px-2 py-0.5 rounded-full border tracking-wide uppercase"
                    :class="[
                      food.is_available 
                        ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400 shadow-sm shadow-emerald-500/10' 
                        : 'bg-red-500/20 border-red-500/30 text-red-300 animate-pulse'
                    ]"
                  >
                    {{ food.is_available ? 'Faol' : 'Stop-list' }}
                  </span>
                </div>
              </div>

              <!-- Card Body / Info -->
              <div class="p-3 space-y-1.5 flex-grow flex flex-col justify-between">
                <div>
                  <h4 class="text-xs font-bold text-slate-200 line-clamp-1 group-hover:text-white transition">
                    {{ food.name }}
                  </h4>
                  <p class="text-[10px] text-slate-400 line-clamp-2 mt-0.5">
                    {{ food.description || 'Taom tavsifi yo\'q.' }}
                  </p>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-white/5">
                  <span class="text-xs font-extrabold text-amber-400">
                    {{ Number(food.price).toLocaleString('uz-UZ') }} UZS
                  </span>

                  <!-- Status Pill indicator -->
                  <div class="flex items-center space-x-1">
                    <span 
                      class="w-1.5 h-1.5 rounded-full"
                      :class="food.is_available ? 'bg-emerald-400 shadow-[0_0_8px_#34d399]' : 'bg-red-500 animate-ping'"
                    ></span>
                  </div>
                </div>
              </div>

              <!-- Overlaid touch lock indicator -->
              <div 
                v-if="!food.is_available" 
                class="absolute inset-0 bg-red-950/20 pointer-events-none flex items-center justify-center border border-red-500/20 rounded-2xl"
              >
                <div class="bg-red-600/90 text-white text-[10px] font-black tracking-widest px-3 py-1 rounded-full uppercase shadow-lg shadow-red-900/30 border border-red-500/30 animate-pulse">
                  Stop-List
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>
  </ChefLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useChefStore } from '@/stores/chef';
import ChefLayout from '@/components/ChefLayout.vue';
import { ChefHat } from 'lucide-vue-next';

const chefStore = useChefStore();
const activeCategory = ref(null);

const filteredMenu = computed(() => {
  if (activeCategory.value === null) {
    return chefStore.menu.filter(cat => cat.foods && cat.foods.length > 0);
  }
  return chefStore.menu.filter(cat => cat.id === activeCategory.value && cat.foods && cat.foods.length > 0);
});

const totalStopListedCount = computed(() => {
  let count = 0;
  chefStore.menu.forEach(cat => {
    if (cat.foods) {
      count += cat.foods.filter(f => !f.is_available).length;
    }
  });
  return count;
});

const getCategoryStopCount = (category) => {
  if (!category.foods) return 0;
  return category.foods.filter(f => !f.is_available).length;
};

const toggleAvailability = async (food) => {
  const nextState = !food.is_available;
  try {
    await chefStore.toggleFoodAvailability(food.id, nextState);
  } catch (err) {
    console.error("Stop-List update error: ", err);
  }
};

onMounted(() => {
  chefStore.fetchMenu();
});
</script>

<style scoped>
/* Custom thin scrollbar */
.scrollbar-thin::-webkit-scrollbar {
  height: 6px;
}
.scrollbar-thin::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.02);
  border-radius: 999px;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 999px;
}
.scrollbar-thin::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.2);
}
</style>
