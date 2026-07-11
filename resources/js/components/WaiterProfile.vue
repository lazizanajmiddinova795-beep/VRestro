<template>
  <div class="space-y-6 pb-28">
    
    <!-- Waiter Profile Identity Card -->
    <div class="bg-white border-2 border-slate-300 rounded-3xl p-6 text-center space-y-4 shadow-sm">
      <div class="w-20 h-20 rounded-full mx-auto bg-gradient-to-tr from-violet-600 to-indigo-600 flex items-center justify-center text-white text-3xl font-extrabold shadow-lg shadow-indigo-600/30">
        {{ avatarInitials }}
      </div>
      <div class="space-y-1">
        <h3 class="text-slate-900 font-black text-xl leading-tight">{{ waiterName }}</h3>
        <p class="text-sm text-slate-700 font-bold">{{ t('waiter_title') }}</p>
      </div>

      <!-- Shift status toggle badge -->
      <div class="inline-flex items-center space-x-2 bg-emerald-100 border border-emerald-300 px-3 py-1 rounded-full">
        <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 animate-pulse"></span>
        <span class="text-xs uppercase font-black tracking-wider text-emerald-800">{{ t('shift_active') }}</span>
      </div>
    </div>

    <!-- Daily Performance KPI Widgets (2x2 Grid) -->
    <div class="space-y-3">
      <h4 class="text-xs font-black text-slate-700 uppercase tracking-widest pl-1">{{ t('daily_kpi') }}</h4>
      <div class="grid grid-cols-2 gap-4">
        
        <!-- Box 1: Sales Amount -->
        <div class="bg-white border-2 border-slate-300 p-4 rounded-2xl space-y-1.5 text-left shadow-sm">
          <span class="text-slate-600 font-extrabold text-xs tracking-wider uppercase">{{ t('daily_sales') }}</span>
          <span class="text-indigo-600 font-black text-2xl mt-2 block truncate leading-snug">{{ formatCurrency(stats.total_sales_amount) }}</span>
        </div>

        <!-- Box 2: Closed Tables count -->
        <div class="bg-white border-2 border-slate-300 p-4 rounded-2xl space-y-1.5 text-left shadow-sm">
          <span class="text-slate-600 font-extrabold text-xs tracking-wider uppercase">{{ t('closed_tables') }}</span>
          <span class="text-indigo-600 font-black text-2xl mt-2 block leading-snug">{{ stats.total_orders_count }} {{ t('tables_unit') }}</span>
        </div>

        <!-- Box 3: Pending cashier checkout -->
        <div class="bg-white border-2 border-slate-300 p-4 rounded-2xl space-y-1.5 text-left border-rose-300 shadow-sm">
          <span class="text-slate-600 font-extrabold text-xs tracking-wider uppercase text-rose-700">{{ t('pending_cashier') }}</span>
          <span class="text-rose-750 font-black text-2xl mt-2 block leading-snug">{{ stats.pending_checkout_count }} {{ t('orders_unit') }}</span>
        </div>

        <!-- Box 4: Earned Bonus -->
        <div class="bg-white border-2 border-slate-300 p-4 rounded-2xl space-y-1.5 text-left border-emerald-300 shadow-sm">
          <span class="text-slate-600 font-extrabold text-xs tracking-wider uppercase text-emerald-800">{{ t('my_bonus') }}</span>
          <span class="text-emerald-700 font-black text-2xl mt-2 block truncate leading-snug">{{ formatCurrency(stats.earned_bonus) }}</span>
        </div>

      </div>
    </div>

    <!-- Actions Drawer (Bottom Stack) -->
    <div class="bg-white border-2 border-slate-300 rounded-3xl divide-y divide-slate-200 overflow-hidden shadow-sm">
      <!-- Language Selector -->
      <div class="flex items-center justify-between p-4 text-sm font-bold text-slate-800">
        <span>{{ t('sys_lang') }}</span>
        <div class="flex space-x-2">
          <button 
            @click="setLang('uz')" 
            class="px-3 py-1.5 rounded bg-slate-100 border border-slate-300 text-slate-700 font-bold"
            :class="{'bg-indigo-600 border-indigo-650 text-white font-black': currentLang === 'uz'}"
          >
            UZ
          </button>
          <button 
            @click="setLang('ru')" 
            class="px-3 py-1.5 rounded bg-slate-100 border border-slate-300 text-slate-700 font-bold"
            :class="{'bg-indigo-600 border-indigo-650 text-white font-black': currentLang === 'ru'}"
          >
            RU
          </button>
        </div>
      </div>

      <!-- Logout button trigger -->
      <button 
        @click="triggerLogout"
        class="w-full flex items-center justify-between p-4 text-sm font-bold text-rose-700 hover:bg-rose-50 transition duration-150 text-left"
      >
        <span>{{ t('sys_logout') }}</span>
        <LogOut class="w-4 h-4 text-rose-700" />
      </button>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useWaiterStore } from '@/stores/waiter';
import { LogOut } from 'lucide-vue-next';

const authStore = useAuthStore();
const waiterStore = useWaiterStore();
const router = useRouter();

const currentLang = ref(localStorage.getItem('waiter_lang') || 'uz');
const stats = ref({
  total_orders_count: 0,
  total_sales_amount: 0,
  pending_checkout_count: 0,
  earned_bonus: 0
});

const dictionary = {
  uz: {
    waiter_title: "Ofitsiant / Birinchi toifa",
    shift_active: "Navbatchilik faol",
    daily_kpi: "Kunlik KPI ko'rsatkichlari",
    daily_sales: "Bugungi Savdo",
    closed_tables: "Yopilgan stollar",
    tables_unit: "ta stol",
    pending_cashier: "Kassirda kutilmoqda",
    orders_unit: "ta buyurtma",
    my_bonus: "Mening Bonusim",
    sys_lang: "Tizim tili",
    sys_logout: "Tizimdan chiqish",
    confirm_logout: "Tizimdan chiqishni tasdiqlaysizmi?"
  },
  ru: {
    waiter_title: "Официант / Первый класс",
    shift_active: "Смена активна",
    daily_kpi: "Ежедневные показатели KPI",
    daily_sales: "Продажи за сегодня",
    closed_tables: "Закрытые столы",
    tables_unit: "столов",
    pending_cashier: "Ожидает у кассира",
    orders_unit: "заказов",
    my_bonus: "Мой Бонус",
    sys_lang: "Язык системы",
    sys_logout: "Выйти из системы",
    confirm_logout: "Вы действительно хотите выйти из системы?"
  }
};

const t = (key) => {
  return dictionary[currentLang.value]?.[key] || key;
};

const waiterName = computed(() => authStore.user?.name || 'Ofitsiant');
const avatarInitials = computed(() => {
  return waiterName.value.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
});

const formatCurrency = (value) => {
  return new Intl.NumberFormat('uz-UZ', { style: 'currency', currency: 'UZS', maximumFractionDigits: 0 }).format(value);
};

const setLang = (lang) => {
  currentLang.value = lang;
  localStorage.setItem('waiter_lang', lang);
};

const triggerLogout = () => {
  if (confirm(t('confirm_logout'))) {
    waiterStore.stopPolling();
    authStore.logout();
    router.push({ name: 'login' });
  }
};

onMounted(async () => {
  try {
    const token = localStorage.getItem('vrestro_token');
    const response = await fetch('/api/waiter/profile/daily-stats', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    });

    if (response.ok) {
      stats.value = await response.json();
    }
  } catch (error) {
    console.error('Error fetching KPI dailyStats: ', error);
  }
});
</script>
