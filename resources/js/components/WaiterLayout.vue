<template>
  <div class="min-h-screen bg-[#F8FAFC] text-slate-900 font-sans pb-24 relative overflow-hidden">
    <!-- Live Toast Notification Overlay -->
    <div 
      v-if="waiterStore.toastMessage"
      class="fixed top-4 left-4 right-4 z-50 p-4 rounded-2xl bg-white border-2 border-emerald-500 shadow-2xl flex items-start space-x-3 animate-slideDown pointer-events-auto"
    >
      <div class="w-8 h-8 rounded-full bg-emerald-100 border border-emerald-300 flex items-center justify-center text-emerald-800 shrink-0">
        <BellRing class="w-4 h-4 animate-swing" />
      </div>
      <div class="min-w-0 flex-grow pt-0.5">
        <h4 class="text-sm font-bold text-slate-900 leading-none">{{ t('dish_ready') }}</h4>
        <p class="text-xs text-slate-700 mt-1 leading-snug font-semibold">{{ waiterStore.toastMessage }}</p>
      </div>
      <button @click="waiterStore.toastMessage = null" class="text-slate-500 hover:text-slate-700">
        <X class="w-4 h-4" />
      </button>
    </div>

    <!-- Header Navigation -->
    <header class="sticky top-0 z-40 bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between shadow-sm">
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-violet-600 to-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow-md shadow-indigo-500/20">
          {{ avatarInitials }}
        </div>
        <div>
          <h2 class="text-slate-900 font-black text-lg">{{ waiterName }}</h2>
          <span class="inline-flex items-center text-xs font-bold text-emerald-800 bg-emerald-100 px-2.5 py-1 rounded-full mt-0.5 border border-emerald-250">
            <span class="w-2 h-2 rounded-full bg-emerald-600 mr-1.5 animate-pulse"></span>
            {{ t('active_badge') }}
          </span>
        </div>
      </div>

      <button @click="handleLogout" class="p-2 rounded-xl bg-slate-100 border border-slate-300 text-slate-700 hover:bg-slate-200 transition duration-200">
        <LogOut class="w-5 h-5" />
      </button>
    </header>

    <!-- Main Mobile Content Area -->
    <main class="px-4 pt-4">
      <router-view></router-view>
    </main>

    <!-- Bottom Navigation Bar -->
    <nav class="fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-slate-200 py-2 px-6 flex justify-between items-center shadow-[0_-4px_12px_rgba(0,0,0,0.05)]">
      <!-- Stollar -->
      <button 
        @click="navTo('stollar')" 
        class="flex flex-col items-center space-y-1 transition duration-200" 
        :class="waiterStore.currentTab === 'stollar' ? 'text-indigo-600 font-black' : 'text-slate-400 font-medium'"
      >
        <LayoutGrid class="w-6 h-6" />
        <span class="text-xs">{{ t('tables_tab') }}</span>
      </button>

      <!-- Yangi Buyurtma -->
      <button 
        @click="navTo('yangi-buyurtma')" 
        class="relative -top-5 flex items-center justify-center w-14 h-14 rounded-full bg-indigo-600 text-white shadow-lg hover:scale-105 active:scale-95 transition-all duration-200 z-10 border-4 border-white"
      >
        <Plus class="w-7 h-7" />
      </button>

      <!-- Holatlar -->
      <button 
        @click="navTo('holatlar')" 
        class="flex flex-col items-center space-y-1 transition duration-200"
        :class="waiterStore.currentTab === 'holatlar' ? 'text-indigo-600 font-black' : 'text-slate-400 font-medium'"
      >
        <ClipboardList class="w-6 h-6" />
        <span class="text-xs">{{ t('status_tab') }}</span>
      </button>

      <!-- Profil -->
      <button 
        @click="navTo('profil')" 
        class="flex flex-col items-center space-y-1 transition duration-200"
        :class="waiterStore.currentTab === 'profil' ? 'text-indigo-600 font-black' : 'text-slate-400 font-medium'"
      >
        <User class="w-6 h-6" />
        <span class="text-xs">{{ t('profile_tab') }}</span>
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
