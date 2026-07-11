<template>
  <div class="relative min-h-screen bg-slate-950 text-slate-100 font-sans overflow-x-hidden selection:bg-indigo-500 selection:text-white">
    <!-- Animated background -->
    <AnimatedBackground />

    <!-- App shell wrapper -->
    <div class="relative z-10 min-h-screen flex flex-col">
      <!-- Header KPI Bar -->
      <header class="sticky top-0 z-50 backdrop-blur-md border-b border-white/5 bg-slate-950/45 px-6 py-4 shadow-lg">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <!-- Logo & Chef Details -->
          <div class="flex items-center space-x-4">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-orange-500 to-amber-500 flex items-center justify-center shadow-lg shadow-orange-500/20">
              <ChefHat class="w-6 h-6 text-white" />
            </div>
            <div>
              <h1 class="text-lg font-bold bg-clip-text text-transparent bg-gradient-to-r from-white via-slate-200 to-amber-200 tracking-wide">
                Oshpaz Monitori
              </h1>
              <div class="flex items-center space-x-2 text-xs text-slate-400">
                <User class="w-3.5 h-3.5 text-orange-400" />
                <span>{{ authStore.user?.name || 'Asilbek Povar' }}</span>
              </div>
            </div>
          </div>

          <!-- KDS Sub-Navigation Navigation Tabs -->
          <div class="flex items-center space-x-1 bg-white/5 p-1 rounded-xl border border-white/5">
            <router-link 
              to="/kitchen" 
              class="px-4 py-2 rounded-lg text-xs font-bold transition-all duration-200"
              :class="route.path === '/kitchen' ? 'bg-orange-500 text-white shadow-md shadow-orange-500/20' : 'text-slate-400 hover:text-slate-200'"
            >
              Buyurtmalar
            </router-link>
            <router-link 
              to="/kitchen/stop-list" 
              class="px-4 py-2 rounded-lg text-xs font-bold transition-all duration-200"
              :class="route.path === '/kitchen/stop-list' ? 'bg-orange-500 text-white shadow-md shadow-orange-500/20' : 'text-slate-400 hover:text-slate-200'"
            >
              Stop-List
            </router-link>
            <router-link 
              to="/kitchen/settings" 
              class="px-4 py-2 rounded-lg text-xs font-bold transition-all duration-200"
              :class="route.path === '/kitchen/settings' ? 'bg-orange-500 text-white shadow-md shadow-orange-500/20' : 'text-slate-400 hover:text-slate-200'"
            >
              Sozlamalar
            </router-link>
          </div>

          <!-- Real-Time Time and KPI stats -->
          <div class="flex flex-wrap items-center gap-4">
            <!-- Digital Ticking Clock -->
            <div class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 font-mono text-sm tracking-widest text-amber-300 shadow-inner flex items-center space-x-2">
              <Clock class="w-4 h-4 text-amber-400 animate-pulse" />
              <span>{{ currentTime }}</span>
            </div>

            <!-- Pending KPI Badge -->
            <div 
              class="px-4 py-2 rounded-xl bg-white/5 border transition-all duration-300 flex items-center space-x-2 shadow-inner"
              :class="pendingCount > 0 ? 'border-red-500/30 bg-red-500/10 text-red-300 animate-pulse' : 'border-white/10 text-slate-400'"
            >
              <span class="w-2.5 h-2.5 rounded-full bg-red-500" :class="{ 'animate-ping': pendingCount > 0 }"></span>
              <span class="text-xs font-semibold">Kutilmoqda:</span>
              <span class="font-bold text-sm">{{ pendingCount }}</span>
            </div>

            <!-- Cooking KPI Badge -->
            <div class="px-4 py-2 rounded-xl bg-white/5 border border-blue-500/30 bg-blue-500/10 text-blue-300 flex items-center space-x-2 shadow-inner">
              <span class="w-2.5 h-2.5 rounded-full bg-blue-400"></span>
              <span class="text-xs font-semibold">Tayyorlanmoqda:</span>
              <span class="font-bold text-sm">{{ cookingCount }}</span>
            </div>

            <!-- Logout action -->
            <button 
              @click="handleLogout" 
              class="px-4 py-2 text-xs font-semibold rounded-xl bg-red-600/10 border border-red-500/20 hover:bg-red-500 hover:text-white transition duration-300 flex items-center space-x-1"
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
      <footer class="backdrop-blur-md border-t border-white/5 bg-slate-950/20 py-4 text-center text-xs text-slate-500 relative z-10">
        KDS Real-time Kitchen Grid • Auto-polling har 4 soniyada faol
      </footer>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useChefStore } from '@/stores/chef';
import AnimatedBackground from '@/components/AnimatedBackground.vue';
import { ChefHat, Clock, User, LogOut } from 'lucide-vue-next';

const authStore = useAuthStore();
const chefStore = useChefStore();
const route = useRoute();

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
};

onMounted(() => {
  updateClock();
  clockInterval = setInterval(updateClock, 1000);
});

onUnmounted(() => {
  if (clockInterval) clearInterval(clockInterval);
});
</script>
