<template>
  <div class="space-y-6">
    <!-- View Title & Actions -->
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-xl md:text-2xl font-bold text-white tracking-wide">{{ cashierStore.t('stollar_xaritasi') }}</h2>
        <p class="text-xs text-slate-400 mt-1">Stollar holati real vaqt rejimida avtomatik ravishda yangilanadi (har 5s)</p>
      </div>
      <div class="flex items-center space-x-3 shrink-0">
        <router-link 
          to="/cashier/order"
          class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 font-semibold text-xs text-white shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/50 hover:scale-[1.01] transition-all flex items-center justify-center space-x-2"
        >
          <Plus class="w-4 h-4" />
          <span>{{ cashierStore.t('yangi_buyurtma') }}</span>
        </router-link>
        <button 
          @click="refreshTables" 
          :disabled="cashierTablesStore.loading"
          class="p-2.5 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 text-slate-300 hover:text-white transition duration-200 disabled:opacity-50"
          title="Yangilash"
        >
          <RotateCw class="w-5 h-5" :class="{'animate-spin': cashierTablesStore.loading}" />
        </button>
      </div>
    </div>

    <!-- Error state -->
    <div v-if="cashierTablesStore.error" class="p-4 rounded-2xl bg-red-500/10 border border-red-500/20 text-sm text-red-400 font-medium flex items-center justify-between">
      <span>{{ cashierTablesStore.error }}</span>
      <button @click="refreshTables" class="text-xs underline hover:text-red-300 ml-4 shrink-0">Qaytadan urinish</button>
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
        class="relative group rounded-3xl p-5 text-left border backdrop-blur-xl transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 shadow-lg min-h-[140px] flex flex-col justify-between"
        :class="getStatusClasses(table.status)"
      >
        <!-- Table indicator glow accent -->
        <span 
          class="absolute top-4 right-4 w-3.5 h-3.5 rounded-full"
          :class="getStatusIndicatorClass(table.status)"
        ></span>

        <!-- Card Body -->
        <div class="space-y-1 mt-2">
          <span class="text-xs uppercase font-extrabold tracking-widest text-slate-400">
            {{ translateStatus(table.status) }}
          </span>
          <h3 class="text-xl font-black text-white tracking-wide group-hover:scale-[1.02] origin-left transition duration-200">
            {{ table.table_number }}
          </h3>
        </div>

        <!-- Card Footer -->
        <div class="flex items-center justify-between w-full mt-4 border-t border-white/5 pt-3">
          <div class="flex items-center space-x-1.5 text-xs font-semibold text-slate-300">
            <UsersIcon class="w-3.5 h-3.5 text-slate-400" />
            <span>{{ table.capacity }} kishi</span>
          </div>
        </div>
      </button>
    </div>

    <!-- Loading Skeleton -->
    <div v-else-if="cashierTablesStore.loading" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
      <div v-for="n in 12" :key="n" class="bg-white/5 border border-white/5 rounded-3xl p-5 min-h-[140px] animate-pulse flex flex-col justify-between">
        <div class="h-4 bg-white/10 rounded w-1/3"></div>
        <div class="h-6 bg-white/10 rounded w-1/2"></div>
        <div class="h-4 bg-white/10 rounded w-2/3"></div>
      </div>
    </div>

    <!-- Empty state -->
    <div v-else class="text-center py-16 backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-8">
      <p class="text-slate-400 text-sm">Birorta ham stol topilmadi.</p>
    </div>

    <!-- Clean Micro-interaction Modal -->
    <Transition name="fade">
      <div v-if="modal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm" @click="closeModal"></div>
        
        <!-- Modal Card -->
        <div class="relative z-10 w-full max-w-sm backdrop-blur-2xl bg-slate-900/90 border border-white/10 rounded-3xl p-6 shadow-2xl text-center space-y-6 animate-scaleIn">
          <!-- Icon Banner -->
          <div 
            class="w-16 h-16 rounded-full mx-auto flex items-center justify-center"
            :class="getModalIconBg(modal.type)"
          >
            <component :is="modal.icon" class="w-8 h-8" :class="getModalIconColor(modal.type)" />
          </div>

          <!-- Content -->
          <div class="space-y-2">
            <h4 class="text-lg font-bold text-white">{{ modal.title }}</h4>
            <p class="text-xs text-slate-300 leading-relaxed">{{ modal.message }}</p>
          </div>

          <!-- Action Button -->
          <button 
            @click="closeModal" 
            class="w-full py-3 rounded-xl font-bold text-sm bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white shadow-lg shadow-indigo-600/20 hover:shadow-indigo-600/40 transition duration-200"
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
    reserved: cashierStore.t('bron')
  };
  return trans[status] || status;
};

// Colors based on Statuses
const getStatusClasses = (status) => {
  if (status === 'empty') {
    return 'bg-emerald-500/5 hover:bg-emerald-500/10 border-emerald-500/20 hover:border-emerald-500/40 shadow-emerald-500/5';
  } else if (status === 'occupied') {
    return 'bg-rose-500/5 hover:bg-rose-500/10 border-rose-500/20 hover:border-rose-500/40 shadow-rose-500/5 animate-borderGlow';
  } else if (status === 'reserved') {
    return 'bg-amber-500/5 hover:bg-amber-500/10 border-amber-500/20 hover:border-amber-500/40 shadow-amber-500/5';
  }
  return 'bg-white/5 hover:bg-white/10 border-white/10 hover:border-white/20';
};

const getStatusIndicatorClass = (status) => {
  if (status === 'empty') return 'bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.8)]';
  if (status === 'occupied') return 'bg-rose-500 shadow-[0_0_10px_rgba(244,63,94,0.8)] animate-pulse';
  if (status === 'reserved') return 'bg-amber-400 shadow-[0_0_8px_rgba(251,191,36,0.8)]';
  return 'bg-slate-400';
};

// Modal Color styling
const getModalIconBg = (type) => {
  if (type === 'success') return 'bg-emerald-500/10 border border-emerald-500/20';
  if (type === 'warning') return 'bg-amber-500/10 border border-amber-500/20';
  return 'bg-indigo-500/10 border border-indigo-500/20';
};

const getModalIconColor = (type) => {
  if (type === 'success') return 'text-emerald-400';
  if (type === 'warning') return 'text-amber-400';
  return 'text-indigo-400';
};

// Interaction Handler
const handleTableClick = (table) => {
  if (table.status === 'occupied') {
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
/* Glowing crimson border animation for active tables */
@keyframes borderPulse {
  0%, 100% {
    border-color: rgba(244, 63, 94, 0.2);
    box-shadow: 0 4px 20px -2px rgba(244, 63, 94, 0.05);
  }
  50% {
    border-color: rgba(244, 63, 94, 0.45);
    box-shadow: 0 4px 20px 2px rgba(244, 63, 94, 0.15);
  }
}

.animate-borderGlow {
  animation: borderPulse 2.5s infinite ease-in-out;
}

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
