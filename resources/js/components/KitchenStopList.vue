<template>
  <!-- {{ settingsStore.t('app_title') }} -->
  <ChefLayout>
    <div class="space-y-6">
      <!-- Loading spinner -->
      <div v-if="chefStore.stopListLoading" class="flex flex-col items-center justify-center py-20 space-y-4">
        <div class="w-12 h-12 rounded-full border-4 border-orange-200 border-t-orange-500 animate-spin"></div>
        <p class="text-slate-500 font-medium font-sans">Menyu yuklanmoqda...</p>
      </div>

      <!-- Error message -->
      <div v-else-if="chefStore.error" class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-600 flex items-center justify-between">
        <span>{{ chefStore.error }}</span>
        <button @click="chefStore.fetchMenu" class="px-3 py-1 bg-red-100 rounded-lg text-xs font-semibold hover:bg-red-200 transition">
          Qayta yuklash
        </button>
      </div>

      <template v-else>
        <!-- Category Navigation Rail -->
        <div class="flex items-center space-x-2 overflow-x-auto pb-3">
          <button
            @click="activeCategory = null"
            class="px-5 py-3 rounded-xl border font-bold text-sm transition-all duration-200 shrink-0 flex items-center space-x-2"
            :class="activeCategory === null
              ? 'bg-orange-500 border-orange-500 text-white shadow-md shadow-orange-500/20'
              : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50 hover:border-slate-300 shadow-sm'"
          >
            <span>Barchasi</span>
            <span
              class="px-2 py-0.5 rounded-full text-xs font-extrabold"
              :class="activeCategory === null ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500'"
            >
              {{ totalStopListedCount }}
            </span>
          </button>

          <button
            v-for="category in chefStore.menu"
            :key="category.id"
            @click="activeCategory = category.id"
            class="px-5 py-3 rounded-xl border font-bold text-sm transition-all duration-200 shrink-0 flex items-center space-x-2"
            :class="activeCategory === category.id
              ? 'bg-orange-500 border-orange-500 text-white shadow-md shadow-orange-500/20'
              : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50 hover:border-slate-300 shadow-sm'"
          >
            <span>{{ category.name }}</span>
            <span
              v-if="getCategoryStopCount(category) > 0"
              class="px-2 py-0.5 rounded-full text-xs font-extrabold bg-red-100 border border-red-200 text-red-600 animate-pulse"
            >
              {{ getCategoryStopCount(category) }}
            </span>
            <span
              v-else
              class="px-2 py-0.5 rounded-full text-xs font-extrabold bg-slate-100 text-slate-500"
            >
              0
            </span>
          </button>
        </div>

        <!-- Food availability Grid matrix -->
        <div v-for="category in filteredMenu" :key="category.id" class="space-y-3">
          <h3 class="text-sm font-bold text-slate-500 tracking-wider uppercase pl-1 border-l-2 border-orange-500">
            {{ category.name }}
          </h3>

          <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            <div
              v-for="food in category.foods"
              :key="food.id"
              @click="toggleAvailability(food)"
              class="group relative flex flex-col justify-between rounded-2xl border bg-white shadow-sm overflow-hidden cursor-pointer select-none transition-all duration-300 active:scale-[0.97]"
              :class="[
                food.is_available
                  ? 'border-slate-200 hover:border-emerald-300 hover:shadow-md'
                  : 'border-red-200 bg-red-50/40 opacity-60 hover:opacity-75'
              ]"
            >
              <!-- Card Top Image / Icon -->
              <div class="relative aspect-video w-full bg-slate-50 overflow-hidden flex items-center justify-center border-b border-slate-100">
                <img
                  v-if="food.image_url"
                  :src="food.image_url"
                  :alt="food.name"
                  class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                />
                <div v-else class="flex flex-col items-center justify-center text-slate-300">
                  <ChefHat class="w-8 h-8 opacity-40" />
                </div>

                <!-- Glow effects / status badges on image -->
                <div class="absolute top-2 right-2">
                  <span
                    class="text-[10px] font-bold px-2 py-0.5 rounded-full border tracking-wide uppercase"
                    :class="[
                      food.is_available
                        ? 'bg-emerald-50 border-emerald-200 text-emerald-600'
                        : 'bg-red-100 border-red-200 text-red-600 animate-pulse'
                    ]"
                  >
                    {{ food.is_available ? 'Faol' : 'Stop-list' }}
                  </span>
                </div>
              </div>

              <!-- Card Body / Info -->
              <div class="p-3 space-y-1.5 flex-grow flex flex-col justify-between">
                <div>
                  <h4 class="text-xs font-bold text-slate-800 line-clamp-1 group-hover:text-slate-900 transition">
                    {{ food.name }}
                  </h4>
                  <p class="text-[10px] text-slate-500 line-clamp-2 mt-0.5">
                    {{ food.description || 'Taom tavsifi yo\'q.' }}
                  </p>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                  <span class="text-xs font-extrabold text-amber-600">
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
                class="absolute inset-0 bg-red-50/40 pointer-events-none flex items-center justify-center border border-red-200 rounded-2xl"
              >
                <div class="bg-red-600/90 text-white text-[10px] font-black tracking-widest px-3 py-1 rounded-full uppercase shadow-lg shadow-red-900/20 border border-red-500/30 animate-pulse">
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
import { useSettingsStore } from '@/stores/settings';
const settingsStore = useSettingsStore();
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

onMounted(async () => {
  await chefStore.fetchMenu();
});
</script>
