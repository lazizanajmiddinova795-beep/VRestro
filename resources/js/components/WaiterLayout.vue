<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 font-sans pb-24 relative overflow-hidden">
    <!-- Premium background glowing design -->
    <div class="absolute w-96 h-96 rounded-full bg-violet-600/10 blur-[120px] -top-12 -left-12 pointer-events-none"></div>
    <div class="absolute w-96 h-96 rounded-full bg-indigo-600/10 blur-[120px] -bottom-12 -right-12 pointer-events-none"></div>

    <!-- Live Toast Notification Overlay -->
    <div 
      v-if="waiterStore.toastMessage"
      class="fixed top-4 left-4 right-4 z-50 p-4 rounded-2xl bg-slate-900/90 border border-emerald-500/35 backdrop-blur-xl shadow-2xl flex items-start space-x-3 animate-slideDown pointer-events-auto"
    >
      <div class="w-8 h-8 rounded-full bg-emerald-500/10 border border-emerald-500/25 flex items-center justify-center text-emerald-400 shrink-0">
        <BellRing class="w-4 h-4 animate-swing" />
      </div>
      <div class="min-w-0 flex-grow pt-0.5">
        <h4 class="text-xs font-bold text-white leading-none">{{ t('dish_ready') }}</h4>
        <p class="text-2xs text-slate-300 mt-1 leading-snug">{{ waiterStore.toastMessage }}</p>
      </div>
      <button @click="waiterStore.toastMessage = null" class="text-slate-500 hover:text-slate-300">
        <X class="w-4 h-4" />
      </button>
    </div>

    <!-- Header Navigation -->
    <header class="sticky top-0 z-40 backdrop-blur-md bg-slate-950/80 border-b border-white/5 px-6 py-4 flex items-center justify-between">
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-violet-600 to-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow-md shadow-indigo-500/20">
          {{ avatarInitials }}
        </div>
        <div>
          <h2 class="text-sm font-bold text-white">{{ waiterName }}</h2>
          <span class="inline-flex items-center text-[10px] font-semibold text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-full mt-0.5">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1 animate-pulse"></span>
            {{ t('active_badge') }}
          </span>
        </div>
      </div>

      <button @click="handleLogout" class="p-2 rounded-xl bg-white/5 border border-white/10 text-slate-400 hover:text-white transition duration-200">
        <LogOut class="w-5 h-5" />
      </button>
    </header>

    <!-- Main Mobile Content Area -->
    <main class="px-4 pt-4">
      <router-view></router-view>
    </main>

    <!-- Bottom Navigation Bar -->
    <nav class="fixed bottom-0 left-0 right-0 z-50 backdrop-blur-xl bg-slate-950/80 border-t border-white/10 px-6 py-3 flex justify-between items-center shadow-lg shadow-black/80">
      <!-- Stollar -->
      <button 
        @click="navTo('stollar')" 
        class="flex flex-col items-center space-y-1 transition duration-200" 
        :class="waiterStore.currentTab === 'stollar' ? 'text-violet-400' : 'text-slate-500 hover:text-slate-300'"
      >
        <LayoutGrid class="w-6 h-6" />
        <span class="text-[10px] font-bold">{{ t('tables_tab') }}</span>
      </button>

      <!-- Yangi Buyurtma -->
      <button 
        @click="navTo('yangi-buyurtma')" 
        class="relative -top-5 flex items-center justify-center w-14 h-14 rounded-full bg-gradient-to-tr from-violet-600 to-indigo-600 text-white shadow-lg shadow-indigo-500/35 hover:scale-105 active:scale-95 transition-all duration-200 z-10 border-4 border-slate-950"
      >
        <Plus class="w-7 h-7" />
      </button>

      <!-- Holatlar -->
      <button 
        @click="navTo('holatlar')" 
        class="flex flex-col items-center space-y-1 transition duration-200"
        :class="waiterStore.currentTab === 'holatlar' ? 'text-violet-400' : 'text-slate-500 hover:text-slate-300'"
      >
        <ClipboardList class="w-6 h-6" />
        <span class="text-[10px] font-bold">{{ t('status_tab') }}</span>
      </button>

      <!-- Profil -->
      <button 
        @click="navTo('profil')" 
        class="flex flex-col items-center space-y-1 transition duration-200"
        :class="waiterStore.currentTab === 'profil' ? 'text-violet-400' : 'text-slate-500 hover:text-slate-300'"
      >
        <User class="w-6 h-6" />
        <span class="text-[10px] font-bold">{{ t('profile_tab') }}</span>
      </button>
    </nav>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useWaiterStore } from '@/stores/waiter';
import { LayoutGrid, Plus, ClipboardList, User, LogOut, BellRing, X } from 'lucide-vue-next';

const authStore = useAuthStore();
const waiterStore = useWaiterStore();
const router = useRouter();

onMounted(() => {
  waiterStore.startPolling();
});

onUnmounted(() => {
  waiterStore.stopPolling();
});

const currentLang = computed(() => localStorage.getItem('waiter_lang') || 'uz');

const dictionary = {
  uz: {
    dish_ready: "Taom tayyor!",
    active_badge: "Faol",
    tables_tab: "Stollar",
    status_tab: "Holatlar",
    profile_tab: "Profil",
    select_table_alert: "Avval stollar sahifasidan biror stolni tanlang."
  },
  ru: {
    dish_ready: "Блюдо готово!",
    active_badge: "Активен",
    tables_tab: "Столы",
    status_tab: "Статусы",
    profile_tab: "Профиль",
    select_table_alert: "Сначала выберите стол на странице столов."
  }
};

const t = (key) => {
  return dictionary[currentLang.value]?.[key] || key;
};

const waiterName = computed(() => authStore.user?.name || 'Ofitsiant');
const avatarInitials = computed(() => {
  return waiterName.value.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
});

const navTo = (tab) => {
  waiterStore.setTab(tab);
  if (tab === 'stollar') {
    router.push({ name: 'waiter-tables' });
  } else if (tab === 'yangi-buyurtma') {
    if (!waiterStore.activeTableId) {
      alert(t('select_table_alert'));
      waiterStore.setTab('stollar');
      router.push({ name: 'waiter-tables' });
    } else {
      router.push({ name: 'waiter-order' });
    }
  } else if (tab === 'holatlar') {
    router.push({ name: 'waiter-status' });
  } else if (tab === 'profil') {
    router.push({ name: 'waiter-profile' });
  }
};

const handleLogout = () => {
  waiterStore.stopPolling();
  authStore.logout();
  router.push({ name: 'login' });
};
</script>

<style scoped>
@keyframes slideDown {
  from {
    transform: translateY(-100%);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.animate-slideDown {
  animation: slideDown 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes swing {
  0%, 100% { transform: rotate(0deg); }
  20% { transform: rotate(15deg); }
  40% { transform: rotate(-10deg); }
  60% { transform: rotate(5deg); }
  80% { transform: rotate(-5deg); }
}

.animate-swing {
  animation: swing 1s ease-in-out infinite;
}
</style>

<style scoped>
/* Glassmorphism Navigation & Header styling */
</style>
