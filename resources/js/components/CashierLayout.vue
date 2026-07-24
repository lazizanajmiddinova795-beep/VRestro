<template>
  <div class="h-screen w-screen bg-slate-50 text-slate-900 flex flex-col font-sans overflow-hidden relative">

    <!-- Top Adaptive Navbar -->
    <header class="w-full shrink-0 bg-white border-b border-slate-200 relative z-30 px-4 py-3 md:px-8 flex flex-col md:flex-row justify-between items-center gap-4 no-print shadow-sm">
      <!-- Left: Profile and Time -->
      <div class="flex items-center justify-between w-full md:w-auto gap-4">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center text-white font-black text-lg uppercase shrink-0 shadow-sm">
            {{ authStore.user?.name ? authStore.user.name.charAt(0) : 'K' }}
          </div>
          <div>
            <h1 class="text-sm md:text-base font-black text-slate-900 tracking-tight truncate max-w-[150px] sm:max-w-[200px]" :title="authStore.user?.name">
              {{ authStore.user?.name || 'Kassir' }}
            </h1>
            <div class="flex items-center space-x-2 mt-0.5">
              <span class="text-xxs font-black uppercase tracking-widest bg-slate-100 text-slate-900 border border-slate-200 px-2 py-0.5 rounded-md">
                Kassir
              </span>
              <button 
                @click="showShiftModal = true"
                class="text-xxs text-slate-700 font-bold hover:text-slate-900 bg-slate-100 border border-slate-200 hover:bg-slate-200 px-2 py-0.5 rounded-md flex items-center gap-1 transition"
                title="Smena boshqaruvi"
              >
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                <span>{{ cashierStore.t('smena_nazorati') }}</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Clock for Mobile -->
        <div class="md:hidden text-right">
          <span class="text-sm font-black font-mono tracking-widest text-slate-900 block">{{ currentTime }}</span>
          <span class="text-xxs text-slate-500 block font-bold">{{ currentDate }}</span>
        </div>
      </div>

      <!-- Navigation Links -->
      <div class="flex items-center space-x-1 bg-slate-100 border border-slate-200 rounded-xl p-1 shrink-0 w-full md:w-auto justify-center">
        <router-link 
          to="/cashier/tables" 
          class="px-4 py-2 rounded-lg text-xs font-bold transition duration-200 tracking-wide text-center flex-grow md:flex-grow-0"
          :class="isActiveRoute('/cashier/tables') ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
        >
          {{ cashierStore.t('stollar_xaritasi') }}
        </router-link>
        <router-link 
          to="/cashier/order" 
          class="px-4 py-2 rounded-lg text-xs font-bold transition duration-200 tracking-wide text-center flex-grow md:flex-grow-0"
          :class="isActiveRoute('/cashier/order') ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
        >
          {{ cashierStore.t('tezkor_buyurtma') }}
        </router-link>
        <router-link 
          to="/cashier/receipts" 
          class="px-4 py-2 rounded-lg text-xs font-bold transition duration-200 tracking-wide text-center flex-grow md:flex-grow-0"
          :class="isActiveRoute('/cashier/receipts') ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
        >
          {{ cashierStore.t('cheklar_tarixi') }}
        </router-link>
        <router-link 
          to="/cashier/settings" 
          class="px-4 py-2 rounded-xl text-xs font-bold transition duration-255 tracking-wide text-center flex-grow md:flex-grow-0"
          :class="isActiveRoute('/cashier/settings') ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-700 hover:text-slate-950 hover:bg-slate-200'"
        >
          {{ cashierStore.t('sozlamalar') }}
        </router-link>
      </div>

      <!-- Center: Quick-Stats Badge Grid -->
      <div class="grid grid-cols-4 gap-2 w-full md:w-auto max-w-lg md:max-w-none">
        <!-- Jami -->
        <div class="bg-slate-100 border border-slate-300 rounded-xl p-2 text-center flex flex-col justify-center min-w-[70px] sm:min-w-[90px] shadow-sm">
          <span class="text-xxs text-slate-700 uppercase font-black tracking-wider">{{ cashierStore.t('jami') }}</span>
          <span class="text-sm sm:text-base font-extrabold text-slate-950 mt-0.5">{{ totalStats.total }}</span>
        </div>
        <!-- Bo'sh -->
        <div class="bg-emerald-50 border border-emerald-300 rounded-xl p-2 text-center flex flex-col justify-center min-w-[70px] sm:min-w-[90px] shadow-sm">
          <span class="text-xxs text-emerald-800 uppercase font-black tracking-wider">{{ cashierStore.t('bo_sh') }}</span>
          <span class="text-sm sm:text-base font-extrabold text-emerald-800 mt-0.5">{{ totalStats.empty }}</span>
        </div>
        <!-- Band -->
        <div class="bg-rose-50 border border-rose-300 rounded-xl p-2 text-center flex flex-col justify-center min-w-[70px] sm:min-w-[90px] shadow-sm">
          <span class="text-xxs text-rose-800 uppercase font-black tracking-wider">{{ cashierStore.t('band') }}</span>
          <span class="text-sm sm:text-base font-extrabold text-rose-800 mt-0.5">{{ totalStats.occupied }}</span>
        </div>
        <!-- Bron -->
        <div class="bg-amber-50 border border-amber-300 rounded-xl p-2 text-center flex flex-col justify-center min-w-[70px] sm:min-w-[90px] shadow-sm">
          <span class="text-xxs text-amber-800 uppercase font-black tracking-wider">{{ cashierStore.t('bron') }}</span>
          <span class="text-sm sm:text-base font-extrabold text-amber-800 mt-0.5">{{ totalStats.reserved }}</span>
        </div>
      </div>

      <!-- Right: Clock (Desktop) and Exit -->
      <div class="hidden md:flex items-center space-x-6 shrink-0">
        <!-- Real-time clock -->
        <div class="text-right">
          <span class="text-lg font-black font-mono tracking-widest text-indigo-600 block">{{ currentTime }}</span>
          <span class="text-xs text-slate-700 font-bold block mt-0.5">{{ currentDate }}</span>
        </div>

        <button 
          @click="handleLogout" 
          class="px-4 py-2.5 rounded-xl bg-red-50 border border-red-200 hover:bg-red-600 hover:text-white text-red-700 text-xs font-bold tracking-wide transition duration-200 flex items-center space-x-2"
        >
          <LogOut class="w-4 h-4" />
          <span>{{ cashierStore.t('chiqish') }}</span>
        </button>
      </div>

      <!-- Exit Button for Mobile -->
      <div class="flex md:hidden w-full justify-end mt-1">
        <button 
          @click="handleLogout" 
          class="w-full py-2.5 rounded-xl bg-red-50 border border-red-200 hover:bg-red-650 hover:text-white text-red-700 text-xs font-bold tracking-wide transition duration-200 flex items-center justify-center space-x-2"
        >
          <LogOut class="w-4 h-4" />
          <span>{{ cashierStore.t('tizimdan_chiqish') }}</span>
        </button>
      </div>
    </header>

    <!-- Main Viewport Router Container -->
    <main class="flex-grow overflow-y-auto relative p-4 md:p-8 z-10 print-viewport">
      <router-view />
    </main>

    <!-- MODAL: SHIFT SESSION MANAGEMENT -->
    <Transition name="fade">
      <div 
        v-if="showShiftModal"
        class="fixed inset-0 z-50 backdrop-blur-md bg-black/50 flex items-center justify-center p-6 no-print"
        @click.self="showShiftModal = false"
      >
        <div class="w-full max-w-sm bg-white border-2 border-slate-300 rounded-3xl p-6 shadow-2xl space-y-5 animate-scaleIn text-left text-slate-900">
          <div class="flex justify-between items-center border-b border-slate-200 pb-3">
            <h3 class="text-base font-black text-slate-900 flex items-center space-x-2">
              <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 animate-pulse"></span>
              <span>Smena Seansi Nazorati</span>
            </h3>
            <button @click="showShiftModal = false" class="p-1 rounded-lg bg-slate-100 text-slate-500 hover:text-slate-900 transition">
              <X class="w-4 h-4" />
            </button>
          </div>

          <div class="space-y-4">
            <!-- Shift Details -->
            <div class="p-4 rounded-2xl bg-slate-50 border-2 border-slate-200 space-y-2.5 text-xs font-bold text-slate-800">
              <div class="flex justify-between">
                <span class="text-slate-600">Smena ochilgan vaqt:</span>
                <span class="text-indigo-650 font-mono font-black">{{ cashierStore.shiftOpenTime }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-600">Hozirgi vaqt:</span>
                <span class="text-slate-800 font-mono font-black">{{ currentTime }}</span>
              </div>
              <div class="border-t border-slate-250 my-2"></div>
              <div class="flex justify-between">
                <span class="text-slate-600">Kassa (Naqd pul):</span>
                <span class="font-mono text-slate-950 font-black">{{ formatCurrency(shiftStats.cash) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-600">Kassa (Plastik):</span>
                <span class="font-mono text-slate-950 font-black">{{ formatCurrency(shiftStats.card) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-600">Kassa (QR to'lov):</span>
                <span class="font-mono text-slate-950 font-black">{{ formatCurrency(shiftStats.qr) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-600">Jami cheklar soni:</span>
                <span class="font-mono text-emerald-800 font-black">{{ shiftStats.count }} ta chek</span>
              </div>
              <div class="border-t border-dashed border-slate-250 my-2"></div>
              <div class="flex justify-between text-indigo-700 font-black text-sm">
                <span>JAMI TUSHUM:</span>
                <span class="font-mono text-base font-black">{{ formatCurrency(shiftStats.total) }}</span>
              </div>
            </div>
          </div>

          <div class="flex flex-col space-y-2 pt-2">
            <button 
              @click="handleCloseShift"
              class="w-full py-3 rounded-xl bg-gradient-to-r from-red-650 to-rose-650 hover:from-red-600 hover:to-rose-600 font-bold text-xs text-white tracking-wide shadow-lg shadow-red-600/20 transition duration-200"
            >
              {{ cashierStore.t('smena_yakunlash') }}
            </button>
            <button 
              @click="showShiftModal = false"
              class="w-full py-2.5 rounded-xl bg-slate-100 border border-slate-300 hover:bg-slate-200 text-xs font-bold text-slate-700 transition"
            >
              Yopish
            </button>
          </div>
        </div>
      </div>
    </Transition>

  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { LogOut, X } from 'lucide-vue-next';
import { useAuthStore } from '@/stores/auth';
import { useCashierTablesStore } from '@/stores/cashierTables';
import { useCashierStore } from '@/stores/cashier';
import { useReceiptsStore } from '@/stores/receipts';

const authStore = useAuthStore();
const cashierTablesStore = useCashierTablesStore();
const cashierStore = useCashierStore();
const receiptsStore = useReceiptsStore();
const router = useRouter();
const route = useRoute();

// Real-time Clock State
const currentTime = ref('');
const currentDate = ref('');
let clockInterval = null;

const showShiftModal = ref(false);

const updateClock = () => {
  const now = new Date();
  
  // Format Time: HH:MM:SS
  currentTime.value = now.toLocaleTimeString('uz-UZ', { 
    hour: '2-digit', 
    minute: '2-digit', 
    second: '2-digit',
    hour12: false 
  });
  
  // Format Date: DD-MM-YYYY
  currentDate.value = now.toLocaleDateString('uz-UZ', { 
    day: '2-digit', 
    month: 'long', 
    year: 'numeric' 
  });
};

const isActiveRoute = (path) => {
  return route.path === path;
};

// Computed Stats from Pinia tables
const totalStats = computed(() => {
  const list = cashierTablesStore.tables || [];
  return {
    total: list.length,
    empty: list.filter(t => t.status === 'empty').length,
    occupied: list.filter(t => t.status === 'occupied').length,
    reserved: list.filter(t => t.status === 'reserved').length
  };
});

// Shift calculation statistics
const shiftStats = computed(() => {
  const list = receiptsStore.payments || [];
  const cash = list.reduce((acc, p) => acc + (parseFloat(p.cash_amount) || 0), 0);
  const card = list.reduce((acc, p) => acc + (parseFloat(p.card_amount) || 0), 0);
  const qr = list.reduce((acc, p) => acc + (parseFloat(p.qr_amount) || 0), 0);
  const total = list.reduce((acc, p) => acc + (parseFloat(p.total_amount) || 0), 0);
  return {
    cash,
    card,
    qr,
    total,
    count: list.length
  };
});

const formatCurrency = (val) => {
  if (val === undefined || val === null) return '0 UZS';
  return new Intl.NumberFormat('uz-UZ').format(Math.round(val)) + ' UZS';
};

const handleLogout = () => {
  authStore.logout();
  router.push('/login');
};

const handleCloseShift = async () => {
  const list = cashierTablesStore.tables || [];
  const activeTablesCount = list.filter(t => t.status !== 'empty').length;

  if (activeTablesCount > 0) {
    alert("❌ DIQQAT! XATOLIK!\n\nSmenani yopib bo'lmaydi! Tizimda hali to'lovi qilinmagan faol stollar mavjud. Iltimos, barcha faol va band stollarni yopib, keyin smenani yakunlang.");
    return;
  }

  try {
    const token = localStorage.getItem('vrestro_token');
    const response = await fetch('/api/shift/close', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    });
    const result = await response.json();
    if (!response.ok) {
      alert("❌ " + (result.message || "Smenani yopishda xatolik yuz berdi. Hali yopilmagan stollar bor."));
      return;
    }
  } catch (e) {
    console.error(e);
  }

  cashierStore.closeShift();
  showShiftModal.value = false;
  handleLogout();
};

onMounted(() => {
  updateClock();
  clockInterval = setInterval(updateClock, 1000);
  
  // Apply saved cashier local settings (theme, zoom, fonts)
  cashierStore.applyLocalSettings();

  // Load cashier tables immediately
  cashierTablesStore.fetchCashierTables();
  receiptsStore.fetchPayments();
});

onUnmounted(() => {
  if (clockInterval) clearInterval(clockInterval);
});
</script>

<style scoped>
.text-xxs {
  font-size: 0.65rem;
}

@media print {
  .no-print {
    display: none !important;
  }
  .print-viewport {
    padding: 0 !important;
    margin: 0 !important;
  }
}
</style>
