<template>
  <div class="space-y-6 bg-[#F1F5F9]">
    <!-- View Title & Actions -->
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-xl md:text-2xl font-black text-slate-900 tracking-wide">{{ cashierStore.t('stollar_xaritasi') }}</h2>
        <p class="text-sm text-slate-700 font-bold mt-1">Stollar holati real vaqt rejimida avtomatik ravishda yangilanadi (har 5s)</p>
      </div>
      <div class="flex items-center space-x-3 shrink-0">
        <router-link 
          to="/cashier/order"
          class="px-4 py-2.5 rounded-xl bg-indigo-600 font-bold text-sm text-white shadow-md hover:bg-indigo-700 transition-all flex items-center justify-center space-x-2"
        >
          <Plus class="w-4 h-4" />
          <span>{{ cashierStore.t('yangi_buyurtma') }}</span>
        </router-link>
        <button 
          @click="refreshTables" 
          :disabled="cashierTablesStore.loading"
          class="p-2.5 rounded-xl bg-slate-100 border border-slate-300 hover:bg-slate-200 text-slate-700 hover:text-slate-950 transition duration-200 disabled:opacity-50"
          title="Yangilash"
        >
          <RotateCw class="w-5 h-5" :class="{'animate-spin': cashierTablesStore.loading}" />
        </button>
      </div>
    </div>

    <!-- Error state -->
    <div v-if="cashierTablesStore.error" class="p-4 rounded-2xl bg-red-50 border-2 border-red-300 text-sm text-red-800 font-bold flex items-center justify-between shadow-sm">
      <span>{{ cashierTablesStore.error }}</span>
      <button @click="refreshTables" class="text-xs underline hover:text-red-950 ml-4 shrink-0">Qaytadan urinish</button>
    </div>

    <!-- Tables Grid -->
    <div 
      v-if="cashierTablesStore.tables && cashierTablesStore.tables.length > 0"
      class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4"
    >
      <button
        v-for="table in cashierTablesStore.tables"
        :key="table.id"
        @click="handleTableClick(table)"
        class="relative group p-5 text-left transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 min-h-[140px] flex flex-col justify-between"
        :class="getStatusClasses(table.status)"
      >
        <!-- Table indicator glow accent -->
        <span 
          class="absolute top-4 right-4 w-3.5 h-3.5 rounded-full"
          :class="getStatusIndicatorClass(table.status)"
        ></span>

        <!-- Card Body -->
        <div class="space-y-1 mt-2">
          <span 
            class="text-xs uppercase font-extrabold tracking-widest"
            :class="[
              table.status === 'empty' ? 'text-emerald-800' : '',
              table.status === 'occupied' ? 'text-blue-800' : '',
              table.status === 'waiting_checkout' ? 'text-rose-800' : '',
              table.status === 'reserved' ? 'text-amber-800' : 'text-slate-500'
            ]"
          >
            {{ translateStatus(table.status) }}
          </span>
          <h3 
            class="text-xl font-black tracking-wide group-hover:scale-[1.02] origin-left transition duration-200"
            :class="[
              table.status === 'empty' ? 'text-slate-900' : '',
              table.status === 'occupied' ? 'text-blue-950' : '',
              table.status === 'waiting_checkout' ? 'text-rose-950' : '',
              table.status === 'reserved' ? 'text-amber-950' : 'text-slate-900'
            ]"
          >
            {{ table.table_number }}
          </h3>
        </div>

        <!-- Card Footer -->
        <div 
          class="flex items-center justify-between w-full mt-4 border-t pt-3"
          :class="[
            table.status === 'empty' ? 'border-emerald-300' : '',
            table.status === 'occupied' ? 'border-blue-300' : '',
            table.status === 'waiting_checkout' ? 'border-rose-300' : '',
            table.status === 'reserved' ? 'border-amber-300' : 'border-slate-350'
          ]"
        >
          <div 
            class="flex items-center space-x-1.5 text-xs font-bold"
            :class="[
              table.status === 'empty' ? 'text-emerald-800' : '',
              table.status === 'occupied' ? 'text-blue-900' : '',
              table.status === 'waiting_checkout' ? 'text-rose-900' : '',
              table.status === 'reserved' ? 'text-amber-900' : 'text-slate-700'
            ]"
          >
            <UsersIcon class="w-3.5 h-3.5" />
            <span>{{ table.capacity }} kishi</span>
          </div>
        </div>
      </button>
    </div>

    <!-- Loading Skeleton -->
    <div v-else-if="cashierTablesStore.loading" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
      <div v-for="n in 12" :key="n" class="bg-white border border-slate-300 rounded-3xl p-5 min-h-[140px] animate-pulse flex flex-col justify-between">
        <div class="h-4 bg-slate-200 rounded w-1/3"></div>
        <div class="h-6 bg-slate-200 rounded w-1/2"></div>
        <div class="h-4 bg-slate-200 rounded w-2/3"></div>
      </div>
    </div>

    <!-- Empty state -->
    <div v-else class="text-center py-16 bg-white border border-slate-300 rounded-3xl p-8">
      <p class="text-slate-700 font-bold text-sm">Birorta ham stol topilmadi.</p>
    </div>

    <!-- Clean Micro-interaction Modal -->
    <Transition name="fade">
      <div v-if="modal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-950/50 backdrop-blur-sm" @click="closeModal"></div>
        
        <!-- Modal Card -->
        <div class="relative z-10 w-full max-w-sm bg-white border-2 border-slate-300 rounded-3xl p-6 shadow-2xl text-center space-y-6 animate-scaleIn text-slate-900">
          <!-- Icon Banner -->
          <div 
            class="w-16 h-16 rounded-full mx-auto flex items-center justify-center"
            :class="getModalIconBg(modal.type)"
          >
            <component :is="modal.icon" class="w-8 h-8" :class="getModalIconColor(modal.type)" />
          </div>

          <!-- Content -->
          <div class="space-y-2">
            <h4 class="text-lg font-black text-slate-900">{{ modal.title }}</h4>
            <p class="text-sm text-slate-700 font-bold leading-relaxed">{{ modal.message }}</p>
          </div>

          <!-- Action Button -->
          <button 
            @click="closeModal" 
            class="w-full py-3 rounded-xl font-bold text-sm bg-indigo-600 hover:bg-indigo-700 text-white shadow-md transition duration-200"
          >
            Yopish
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, markRaw } from 'vue';
import { RotateCw, Users as UsersIcon, HelpCircle, CheckCircle, Play, Plus } from 'lucide-vue-next';
import { useCashierTablesStore } from '@/stores/cashierTables';
import { useCashierStore } from '@/stores/cashier';
import { useRouter } from 'vue-router';

const cashierTablesStore = useCashierTablesStore();
const cashierStore = useCashierStore();
const router = useRouter();
let pollInterval = null;

// Modal dialog state
const modal = ref({
  show: false,
  type: 'info',
  title: '',
  message: '',
  icon: null
});

// Translation Helper
const translateStatus = (status) => {
  const trans = {
    empty: cashierStore.t('bo_sh'),
    occupied: cashierStore.t('band'),
    reserved: cashierStore.t('bron'),
    waiting_checkout: 'Hisob kutilmoqda'
  };
  return trans[status] || status;
};

// Colors based on Statuses
const getStatusClasses = (status) => {
  if (status === 'empty') {
    return 'bg-white border-2 border-emerald-500 rounded-2xl shadow-sm text-slate-800 font-black';
  } else if (status === 'occupied') {
    return 'bg-blue-50 border-2 border-blue-500 rounded-2xl shadow-sm text-blue-950 font-black';
  } else if (status === 'waiting_checkout') {
    return 'bg-rose-50 border-2 border-rose-500 rounded-2xl text-rose-950 font-black animate-pulse';
  } else if (status === 'reserved') {
    return 'bg-amber-50 border-2 border-amber-500 rounded-2xl shadow-sm text-amber-950 font-black';
  }
  return 'bg-white border-2 border-slate-300 rounded-2xl shadow-sm text-slate-800 font-black';
};

const getStatusIndicatorClass = (status) => {
  if (status === 'empty') return 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.6)]';
  if (status === 'occupied') return 'bg-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.6)]';
  if (status === 'waiting_checkout') return 'bg-rose-500 shadow-[0_0_10px_rgba(244,63,94,0.8)] animate-pulse';
  if (status === 'reserved') return 'bg-amber-500 shadow-[0_0_8px_rgba(245,158,11,0.6)]';
  return 'bg-slate-400';
};

// Modal Color styling
const getModalIconBg = (type) => {
  if (type === 'success') return 'bg-emerald-50 border border-emerald-200';
  if (type === 'warning') return 'bg-amber-50 border border-amber-200';
  return 'bg-indigo-50 border border-indigo-200';
};

const getModalIconColor = (type) => {
  if (type === 'success') return 'text-emerald-700';
  if (type === 'warning') return 'text-amber-700';
  return 'text-indigo-700';
};

// Interaction Handler
const handleTableClick = (table) => {
  if (table.status === 'occupied' || table.status === 'waiting_checkout') {
    console.log(`Active Order ID triggered: ${table.order_id}`);
    
    // Simulate redirection alert to Invoice/Receipt history
    modal.value = {
      show: true,
      type: 'success',
      title: 'To\'lov oynasiga yo\'naltirish',
      message: `Ushbu stolda faol buyurtma mavjud (Order ID: ${table.order_id}). Cheklar tarixi bo'limiga o'tib to'lov qilishingiz mumkin.`,
      icon: markRaw(Play)
    };
  } else {
    // Show empty or reserved modal info
    modal.value = {
      show: true,
      type: 'info',
      title: 'Ma\'lumot',
      message: `Ushbu stolda (${table.table_number}) faol buyurtma mavjud emas. Yangi buyurtma yaratish uchun Tezkor Buyurtma bo'limidan foydalanishingiz mumkin.`,
      icon: markRaw(HelpCircle)
    };
  }
};

const closeModal = () => {
  modal.value.show = false;
};

const refreshTables = () => {
  cashierTablesStore.fetchCashierTables();
};

onMounted(() => {
  refreshTables();
  pollInterval = setInterval(refreshTables, 5000);
});

onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval);
});
</script>

<style>
/* Modal Transitions */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.25s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

@keyframes scaleIn {
  from {
    transform: scale(0.95);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}

.animate-scaleIn {
  animation: scaleIn 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
