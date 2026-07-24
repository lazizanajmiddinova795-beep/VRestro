<template>
  <div class="space-y-6">
    <!-- Quick Status Stats -->
    <div class="grid grid-cols-3 gap-3">
      <div class="bg-white border-2 border-slate-300 p-3 rounded-2xl text-center shadow-sm">
        <span class="text-xs text-slate-600 font-extrabold block">{{ t('total_tables') }}</span>
        <span class="text-xl font-black text-slate-900 mt-1 block">{{ waiterStore.tables.length }}</span>
      </div>
      <div class="bg-white border-2 border-slate-300 p-3 rounded-2xl text-center shadow-sm">
        <span class="text-xs text-indigo-700 font-extrabold block">{{ t('my_tables') }}</span>
        <span class="text-xl font-black text-indigo-700 mt-1 block">{{ myTablesCount }}</span>
      </div>
      <div class="bg-white border-2 border-slate-300 p-3 rounded-2xl text-center shadow-sm">
        <span class="text-xs text-emerald-700 font-extrabold block">{{ t('empty_tables') }}</span>
        <span class="text-xl font-black text-emerald-700 mt-1 block">{{ emptyTablesCount }}</span>
      </div>
    </div>

    <!-- Table Grid Loader / Skeleton -->
    <div v-if="waiterStore.loading" class="grid grid-cols-2 gap-3">
      <div v-for="n in 6" :key="n" class="h-28 bg-white animate-pulse rounded-2xl border-2 border-slate-200"></div>
    </div>

    <!-- Error state -->
    <div v-else-if="waiterStore.error" class="p-4 rounded-2xl bg-red-50 border-2 border-red-300 text-center text-xs text-red-800 font-bold shadow-sm">
      {{ waiterStore.error }}
      <button @click="waiterStore.fetchTables" class="mt-2 block w-full py-2 bg-red-100 hover:bg-red-200 text-red-800 rounded-xl transition duration-200 border border-red-300">
        {{ t('try_again') }}
      </button>
    </div>

    <!-- Tables Matrix -->
    <div v-else class="grid grid-cols-2 gap-3 pb-24">
      <div 
        v-for="table in waiterStore.tables" 
        :key="table.id"
        @click="handleTableClick(table)"
        class="relative overflow-hidden rounded-2xl p-5 flex flex-col justify-between h-28 border transition-all duration-300 cursor-pointer text-center"
        :class="tableCardClasses(table)"
      >
        <div class="relative z-10 flex items-start justify-between w-full">
          <span class="text-base font-black text-slate-900" :class="[table.status === 'occupied_by_me' ? 'text-indigo-950' : '']">{{ table.table_number }}</span>
          <span class="text-xs px-2 py-0.5 rounded-md bg-slate-100 border border-slate-300 text-slate-800 font-black">
            {{ table.capacity }} {{ t('persons') }}
          </span>
        </div>

        <div class="relative z-10 w-full mt-2">
          <span class="text-xs font-black tracking-wider uppercase" :class="statusTextClasses(table)">
            {{ statusLabel(table) }}
          </span>
        </div>
      </div>
    </div>

    <!-- Bottom drawer touch modal for new order -->
    <div 
      v-if="showDrawer" 
      class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 backdrop-blur-sm"
      @click.self="closeDrawer"
    >
      <div class="w-full max-w-md bg-white border-t-2 border-slate-300 rounded-t-3xl p-6 space-y-6 shadow-2xl animate-slideUp text-slate-900">
        <div class="flex items-center justify-between border-b border-slate-200 pb-3">
          <div>
            <h3 class="text-lg font-black text-slate-900">{{ selectedTable?.table_number }}</h3>
            <p class="text-xs text-slate-650 font-bold">{{ t('capacity') }}: {{ selectedTable?.capacity }} {{ t('persons') }}</p>
          </div>
          <button @click="closeDrawer" class="p-2 rounded-xl bg-slate-100 text-slate-500 hover:text-slate-900 transition">
            <X class="w-5 h-5" />
          </button>
        </div>

        <div class="space-y-3">
          <button 
            @click="createNewOrder" 
            class="w-full py-4 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 font-black text-white text-sm shadow-md hover:from-indigo-700 hover:to-violet-700 transition duration-200 flex items-center justify-center space-x-2"
          >
            <PlusCircle class="w-5 h-5" />
            <span>{{ t('open_order') }}</span>
          </button>
          
          <button 
            @click="closeDrawer" 
            class="w-full py-3.5 rounded-xl bg-slate-100 border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-200 transition duration-200"
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
import { useRouter } from 'vue-router';
import { useWaiterStore } from '@/stores/waiter';
import { X, PlusCircle } from 'lucide-vue-next';

const waiterStore = useWaiterStore();
const router = useRouter();
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
    return 'bg-white border-2 border-emerald-500 rounded-2xl p-5 text-slate-900 font-black shadow-sm text-center';
  } else if (table.status === 'occupied_by_me') {
    return 'bg-indigo-50 border-2 border-indigo-500 rounded-2xl p-5 text-indigo-950 font-black shadow-sm text-center';
  } else {
    // occupied_by_other
    return 'bg-slate-100 border-2 border-slate-300 opacity-60 text-slate-500 rounded-2xl p-5 text-center';
  }
};

const statusTextClasses = (table) => {
  if (table.status === 'empty') return 'text-emerald-800 font-black';
  if (table.status === 'occupied_by_me') return 'text-indigo-900 font-black';
  return 'text-slate-650 font-bold';
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
    waiterStore.selectTable(table.id, null);
    waiterStore.setTab('yangi-buyurtma');
    router.push({ name: 'waiter-order' });
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
