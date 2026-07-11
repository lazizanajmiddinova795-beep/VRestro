<template>
  <div class="space-y-6">
    <!-- Quick Status Stats -->
    <div class="grid grid-cols-3 gap-3">
      <div class="backdrop-blur-md bg-white/5 border border-white/5 p-3 rounded-2xl text-center">
        <span class="text-[10px] text-slate-400 font-bold block">{{ t('total_tables') }}</span>
        <span class="text-lg font-bold text-white mt-1 block">{{ waiterStore.tables.length }}</span>
      </div>
      <div class="backdrop-blur-md bg-white/5 border border-white/5 p-3 rounded-2xl text-center">
        <span class="text-[10px] text-slate-400 font-bold block">{{ t('my_tables') }}</span>
        <span class="text-lg font-bold text-violet-400 mt-1 block">{{ myTablesCount }}</span>
      </div>
      <div class="backdrop-blur-md bg-white/5 border border-white/5 p-3 rounded-2xl text-center">
        <span class="text-[10px] text-slate-400 font-bold block">{{ t('empty_tables') }}</span>
        <span class="text-lg font-bold text-emerald-400 mt-1 block">{{ emptyTablesCount }}</span>
      </div>
    </div>

    <!-- Table Grid Loader / Skeleton -->
    <div v-if="waiterStore.loading" class="grid grid-cols-2 gap-3">
      <div v-for="n in 6" :key="n" class="h-28 bg-white/5 animate-pulse rounded-2xl border border-white/5"></div>
    </div>

    <!-- Error state -->
    <div v-else-if="waiterStore.error" class="p-4 rounded-2xl bg-red-500/10 border border-red-500/20 text-center text-xs text-red-400 font-medium">
      {{ waiterStore.error }}
      <button @click="waiterStore.fetchTables" class="mt-2 block w-full py-2 bg-red-500/20 hover:bg-red-500/30 text-red-100 rounded-xl transition duration-200">
        {{ t('try_again') }}
      </button>
    </div>

    <!-- Tables Matrix -->
    <div v-else class="grid grid-cols-2 gap-3 pb-24">
      <div 
        v-for="table in waiterStore.tables" 
        :key="table.id"
        @click="handleTableClick(table)"
        class="relative overflow-hidden backdrop-blur-xl rounded-2xl p-4 flex flex-col justify-between h-28 border transition-all duration-300 cursor-pointer"
        :class="tableCardClasses(table)"
      >
        <!-- Card background glow -->
        <div class="absolute -right-6 -bottom-6 w-20 h-20 rounded-full blur-[25px] opacity-25 pointer-events-none" :class="glowBgClasses(table)"></div>

        <div class="relative z-10 flex items-start justify-between">
          <span class="text-sm font-bold text-white">{{ table.table_number }}</span>
          <span class="text-[10px] px-2 py-0.5 rounded-md bg-white/5 border border-white/10 text-slate-300">
            {{ table.capacity }} {{ t('persons') }}
          </span>
        </div>

        <div class="relative z-10">
          <span class="text-[10px] font-bold tracking-wider uppercase" :class="statusTextClasses(table)">
            {{ statusLabel(table) }}
          </span>
        </div>
      </div>
    </div>

    <!-- Bottom drawer touch modal for new order -->
    <div 
      v-if="showDrawer" 
      class="fixed inset-0 z-50 flex items-end justify-center bg-black/60 backdrop-blur-sm"
      @click.self="closeDrawer"
    >
      <div class="w-full max-w-md bg-slate-900 border-t border-white/10 rounded-t-3xl p-6 space-y-6 shadow-2xl animate-slideUp">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-lg font-bold text-white">{{ selectedTable?.table_number }}</h3>
            <p class="text-xs text-slate-400">{{ t('capacity') }}: {{ selectedTable?.capacity }} {{ t('persons') }}</p>
          </div>
          <button @click="closeDrawer" class="p-2 rounded-xl bg-white/5 text-slate-400 hover:text-white">
            <X class="w-5 h-5" />
          </button>
        </div>

        <div class="space-y-3">
          <button 
            @click="createNewOrder" 
            class="w-full py-4 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 font-bold text-white text-sm shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/50 active:scale-95 transition-all duration-200 flex items-center justify-center space-x-2"
          >
            <PlusCircle class="w-5 h-5" />
            <span>{{ t('open_order') }}</span>
          </button>
          
          <button 
            @click="closeDrawer" 
            class="w-full py-3.5 rounded-xl bg-white/5 border border-white/10 text-xs font-semibold text-slate-300 hover:bg-white/10 transition duration-200"
          >
            {{ t('cancel') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useWaiterStore } from '@/stores/waiter';
import { X, PlusCircle } from 'lucide-vue-next';

const waiterStore = useWaiterStore();
const showDrawer = ref(false);
const selectedTable = ref(null);

onMounted(() => {
  waiterStore.fetchTables();
});

const currentLang = computed(() => localStorage.getItem('waiter_lang') || 'uz');

const dictionary = {
  uz: {
    total_tables: "Jami stollar",
    my_tables: "Mening stollarim",
    empty_tables: "Bo'sh stollar",
    try_again: "Qayta urinib ko'rish",
    persons: "kishi",
    capacity: "Sig'imi",
    open_order: "Yangi Buyurtma ochish",
    cancel: "Bekor qilish",
    empty_status: "Bo'sh",
    my_table_status: "Mening stolim",
    occupied_status: "Band (Boshqa)"
  },
  ru: {
    total_tables: "Всего столов",
    my_tables: "Мои столы",
    empty_tables: "Свободные столы",
    try_again: "Повторить попытку",
    persons: "чел.",
    capacity: "Вместимость",
    open_order: "Открыть новый заказ",
    cancel: "Отмена",
    empty_status: "Свободен",
    my_table_status: "Мой стол",
    occupied_status: "Занят (Другим)"
  }
};

const t = (key) => {
  return dictionary[currentLang.value]?.[key] || key;
};

const myTablesCount = computed(() => {
  return waiterStore.tables.filter(t => t.status === 'occupied_by_me').length;
});

const emptyTablesCount = computed(() => {
  return waiterStore.tables.filter(t => t.status === 'empty').length;
});

const tableCardClasses = (table) => {
  if (table.status === 'empty') {
    return 'bg-slate-900/50 border-white/10 hover:border-slate-700 hover:bg-slate-900/70 shadow-sm';
  } else if (table.status === 'occupied_by_me') {
    return 'bg-violet-950/20 border-violet-500/40 shadow-[0_0_15px_rgba(139,92,246,0.15)] animate-pulseGlow';
  } else {
    // occupied_by_other
    return 'bg-slate-900/30 border-orange-950/30 opacity-40 cursor-not-allowed';
  }
};

const glowBgClasses = (table) => {
  if (table.status === 'empty') return 'bg-slate-400';
  if (table.status === 'occupied_by_me') return 'bg-violet-500';
  return 'bg-orange-500';
};

const statusTextClasses = (table) => {
  if (table.status === 'empty') return 'text-slate-400';
  if (table.status === 'occupied_by_me') return 'text-violet-400';
  return 'text-orange-400';
};

const statusLabel = (table) => {
  if (table.status === 'empty') return t('empty_status');
  if (table.status === 'occupied_by_me') return t('my_table_status');
  return t('occupied_status');
};

const handleTableClick = (table) => {
  if (table.status === 'occupied_by_other') {
    return; // Disabled for clicks
  }
  
  selectedTable.value = table;

  if (table.status === 'empty') {
    showDrawer.value = true;
  } else if (table.status === 'occupied_by_me') {
    waiterStore.selectTable(table.id, table.active_order_id);
    waiterStore.setTab('yangi-buyurtma');
    router.push({ name: 'waiter-order' });
  }
};

const closeDrawer = () => {
  showDrawer.value = false;
  selectedTable.value = null;
};

const createNewOrder = () => {
  closeDrawer();
  waiterStore.selectTable(selectedTable.value.id, null);
  waiterStore.setTab('yangi-buyurtma');
  router.push({ name: 'waiter-order' });
};
</script>

<style scoped>
@keyframes pulseGlow {
  0%, 100% {
    box-shadow: 0 0 15px rgba(139,92,246,0.15);
  }
  50% {
    box-shadow: 0 0 25px rgba(139,92,246,0.3);
  }
}

.animate-pulseGlow {
  animation: pulseGlow 2s infinite ease-in-out;
}

@keyframes slideUp {
  from {
    transform: translateY(100%);
  }
  to {
    transform: translateY(0);
  }
}

.animate-slideUp {
  animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
