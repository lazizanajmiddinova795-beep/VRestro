<template>
  <div class="relative min-h-screen bg-slate-50 text-slate-900 font-bold font-sans overflow-x-hidden selection:bg-slate-900 selection:text-white">
    <!-- App shell wrapper -->
    <div class="relative z-10 min-h-screen flex flex-col">
      <!-- Header KPI Bar -->
      <header class="sticky top-0 z-50 bg-white border-b border-slate-200 px-6 py-4 shadow-sm">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <!-- Logo & Chef Details -->
          <div class="flex items-center space-x-4">
            <div class="w-11 h-11 rounded-xl bg-slate-900 flex items-center justify-center text-white shadow-sm">
              <ChefHat class="w-6 h-6 text-white" />
            </div>
            <div>
              <h1 class="text-lg font-black text-slate-900 tracking-tight">
                Oshpaz Monitori
              </h1>
              <div class="flex items-center space-x-2 text-xs text-slate-500 font-bold">
                <User class="w-3.5 h-3.5 text-slate-700" />
                <span>{{ authStore.user?.name || 'Asilbek Povar' }}</span>
              </div>
            </div>
          </div>

          <!-- KDS Sub-Navigation Navigation Tabs -->
          <div class="flex items-center space-x-1 bg-slate-100 p-1 rounded-xl border border-slate-200">
            <router-link 
              to="/kitchen" 
              class="px-4 py-2 rounded-lg text-xs font-bold transition-all duration-200"
              :class="route.path === '/kitchen' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
            >
              Buyurtmalar
            </router-link>
            <router-link 
              to="/kitchen/stop-list" 
              class="px-4 py-2 rounded-lg text-xs font-bold transition-all duration-200"
              :class="route.path === '/kitchen/stop-list' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
            >
              Stop-List
            </router-link>
            <router-link 
              to="/kitchen/recipes" 
              class="px-4 py-2 rounded-lg text-xs font-bold transition-all duration-200"
              :class="route.path === '/kitchen/recipes' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
            >
              Retseptlar
            </router-link>
            <router-link 
              to="/kitchen/settings" 
              class="px-4 py-2 rounded-lg text-xs font-bold transition-all duration-200"
              :class="route.path === '/kitchen/settings' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
            >
              Sozlamalar
            </router-link>
          </div>

          <!-- Real-Time Time and KPI stats -->
          <div class="flex flex-wrap items-center gap-4">
            <!-- Digital Ticking Clock -->
            <div class="px-4 py-2 rounded-xl bg-white border border-slate-200 font-mono text-sm tracking-widest text-amber-800 shadow-sm flex items-center space-x-2">
              <Clock class="w-4 h-4 text-amber-600 animate-pulse" />
              <span>{{ currentTime }}</span>
            </div>

            <!-- Pending KPI Badge -->
            <div 
              class="px-4 py-2 rounded-xl bg-white border transition-all duration-300 flex items-center space-x-2 shadow-sm"
              :class="pendingCount > 0 ? 'border-red-300 bg-red-50 text-red-800 animate-pulse' : 'border-slate-200 text-slate-700'"
            >
              <span class="w-2.5 h-2.5 rounded-full bg-red-600" :class="{ 'animate-ping': pendingCount > 0 }"></span>
              <span class="text-xs font-bold">Kutilmoqda:</span>
              <span class="font-black text-sm">{{ pendingCount }}</span>
            </div>

            <!-- Cooking KPI Badge -->
            <div class="px-4 py-2 rounded-xl bg-white border border-blue-200 bg-blue-50 text-blue-800 flex items-center space-x-2 shadow-sm">
              <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
              <span class="text-xs font-bold">Tayyorlanmoqda:</span>
              <span class="font-black text-sm">{{ cookingCount }}</span>
            </div>

            <!-- Logout action -->
            <button 
              @click="handleLogout" 
              class="px-4 py-2 text-xs font-bold rounded-xl bg-red-50 border border-red-300 text-red-700 hover:bg-red-600 hover:text-white transition duration-300 flex items-center space-x-1"
            >
              <LogOut class="w-4 h-4" />
              <span>Chiqish</span>
            </button>
          </div>
        </div>
      </header>

      <!-- Grid Monitor Canvas -->
      <main class="flex-grow max-w-7xl mx-auto w-full p-6">
        <slot></slot>
      </main>

      <!-- Kitchen Status Info Bar -->
      <footer class="border-t border-slate-200 bg-white py-4 text-center text-xs text-slate-600 relative z-10 shadow-inner font-semibold">
        KDS Real-time Kitchen Grid • Auto-polling har 4 soniyada faol
      </footer>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useChefStore } from '@/stores/chef';
import AnimatedBackground from '@/components/AnimatedBackground.vue';
import { ChefHat, Clock, User, LogOut } from 'lucide-vue-next';

const authStore = useAuthStore();
const chefStore = useChefStore();
const route = useRoute();
const router = useRouter();

const currentTime = ref('');
let clockInterval = null;

const pendingCount = computed(() => {
  return chefStore.activeItems.filter(item => item.status === 'pending').length;
});

const cookingCount = computed(() => {
  return chefStore.activeItems.filter(item => item.status === 'cooking').length;
});

const updateClock = () => {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString('uz-UZ', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
};

const handleLogout = () => {
  chefStore.stopPolling();
  authStore.logout();
  router.push({ name: 'login' });
};

onMounted(() => {
  updateClock();
  clockInterval = setInterval(updateClock, 1000);
});

onUnmounted(() => {
  if (clockInterval) clearInterval(clockInterval);
});
</script>
