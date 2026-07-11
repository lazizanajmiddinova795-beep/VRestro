<template>
  <div class="flex-grow p-6 flex flex-col h-screen overflow-hidden">
    
    <!-- Top Dashboard Navigation / Header -->
    <div class="flex items-center justify-between mb-6 shrink-0">
      <div>
        <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-white via-indigo-200 to-indigo-400 tracking-wide">
          Buyurtmalar Boshqaruvi
        </h1>
        <p class="text-xs text-slate-400">Restoran buyurtmalari oqimini real vaqtda nazorat qilish oynasi</p>
      </div>

      <!-- Real-time Indicator -->
      <div class="flex items-center space-x-2 bg-white/5 border border-white/10 px-3 py-1.5 rounded-full text-xs font-semibold text-slate-300">
        <span class="relative flex h-2 w-2">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
        </span>
        <span>Jonli Oqim (8s)</span>
      </div>
    </div>

    <!-- Status Navigation Tabs -->
    <div class="flex items-center space-x-2 border-b border-white/5 pb-px mb-6 shrink-0 overflow-x-auto">
      <button 
        v-for="tab in statusTabs" 
        :key="tab.status"
        @click="activeTab = tab.status"
        class="relative px-5 py-3 text-sm font-semibold tracking-wide transition-all duration-200 focus:outline-none whitespace-nowrap"
        :class="activeTab === tab.status ? 'text-white border-b-2 border-indigo-500' : 'text-slate-400 hover:text-slate-200'"
      >
        <div class="flex items-center space-x-2">
          <span>{{ tab.label }}</span>
          <span 
            class="px-2 py-0.5 rounded-full text-2xs font-bold border"
            :class="tab.badgeClass"
          >
            {{ getCountByStatus(tab.status) }}
          </span>
        </div>
      </button>
    </div>

    <!-- Loading / Error States inside container -->
    <div v-if="loading && allOrders.length === 0" class="flex-grow flex flex-col items-center justify-center space-y-4">
      <Loader2 class="w-10 h-10 text-indigo-500 animate-spin" />
      <p class="text-slate-400 text-xs font-medium animate-pulse">Buyurtmalar yuklanmoqda...</p>
    </div>

    <div v-else-if="error" class="flex-grow flex flex-col items-center justify-center p-6 text-center space-y-4">
      <AlertTriangle class="w-12 h-12 text-red-400" />
      <h3 class="text-base font-bold text-white">Yuklashda xatolik</h3>
      <p class="text-xs text-red-300/80">{{ error }}</p>
      <button @click="loadAllOrders" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold transition">
        Qayta yuklash
      </button>
    </div>

    <!-- Orders Lists Grid -->
    <div v-else class="flex-grow overflow-y-auto pr-1">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 pb-8">
        
        <!-- Order Card -->
        <div 
          v-for="order in activeTabOrders" 
          :key="order.id"
          class="backdrop-blur-md bg-slate-900/40 border border-white/5 rounded-2xl p-5 shadow-2xl flex flex-col justify-between hover:border-white/10 transition-all duration-300 relative group"
        >
          <!-- Elapsed Time Badge -->
          <div class="absolute right-4 top-4 text-xxs font-medium text-slate-500">
            {{ getElapsedTime(order.created_at) }}
          </div>

          <!-- Header -->
          <div class="space-y-1 mb-4 pr-16">
            <div class="flex items-center space-x-2">
              <span class="text-sm font-bold text-white tracking-wider group-hover:text-indigo-400 transition-colors">
                {{ order.order_number }}
              </span>
              <span 
                class="px-2 py-0.5 rounded-full text-3xs font-bold border"
                :class="getStatusBadgeClass(order.status)"
              >
                {{ getStatusLabel(order.status) }}
              </span>
            </div>
            <div class="flex items-center text-xs text-slate-400 font-medium">
              <span class="text-indigo-300 font-semibold">{{ order.table?.number || 'Olib ketish' }}</span>
              <span class="mx-1.5 w-1 h-1 rounded-full bg-slate-600"></span>
              <span>{{ order.waiter?.name || 'Noma\'lum' }}</span>
            </div>
          </div>

          <!-- Content: Brief Items List -->
          <div class="border-t border-b border-white/5 py-3 mb-4 flex-grow space-y-1.5 text-xs text-slate-300">
            <div v-for="item in order.items.slice(0, 3)" :key="item.id" class="flex justify-between">
              <span>
                {{ item.food?.name }}
                <span v-if="item.size_name" class="text-4xs text-indigo-400 bg-indigo-500/5 px-1 py-0.5 rounded ml-1">
                  {{ item.size_name }}
                </span>
              </span>
              <span class="text-slate-400 font-medium">x{{ item.quantity }}</span>
            </div>
            <div v-if="order.items.length > 3" class="text-slate-500 text-xxs italic">
              yana {{ order.items.length - 3 }} ta taom...
            </div>
          </div>

          <!-- Footer: Actions & Pricing -->
          <div class="flex items-center justify-between mt-auto">
            <div>
              <span class="block text-3xs text-slate-500 font-semibold uppercase tracking-wider">Jami summa</span>
              <span class="text-base font-bold text-white tracking-tight">{{ formatCurrency(order.total_amount) }}</span>
            </div>

            <!-- Context Actions -->
            <div class="flex items-center space-x-2">
              <button 
                @click="openDetails(order)"
                class="p-2 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 text-slate-300 transition duration-200"
                title="Batafsil ko'rish"
              >
                <Eye class="w-4 h-4" />
              </button>

              <button 
                v-if="canAdvanceStatus(order.status)"
                @click="handleAdvanceStatus(order)"
                class="px-3.5 py-2 rounded-xl text-xs font-bold text-white shadow-lg transition duration-200"
                :class="getAdvanceButtonClass(order.status)"
              >
                {{ getAdvanceButtonLabel(order.status) }}
              </button>
              
              <button 
                v-if="canCancel(order.status)"
                @click="handleCancel(order)"
                class="p-2 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500 hover:text-white transition duration-200"
                title="Bekor qilish"
              >
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>

      </div>

      <!-- Empty State -->
      <div v-if="activeTabOrders.length === 0" class="flex flex-col items-center justify-center py-24 space-y-3">
        <ShoppingBag class="w-12 h-12 text-slate-600" />
        <p class="text-slate-400 text-xs font-medium">Bu statusda buyurtmalar mavjud emas</p>
      </div>
    </div>

    <!-- Order Detail Modal -->
    <div 
      v-if="selectedOrder" 
      class="fixed inset-0 z-50 backdrop-blur-md bg-black/60 flex items-center justify-center p-6"
      @click.self="closeDetails"
    >
      <div class="w-full max-w-lg backdrop-blur-xl bg-slate-900/80 border border-white/10 rounded-3xl p-6 shadow-2xl space-y-6 animate-scaleIn">
        <!-- Modal Header -->
        <div class="flex justify-between items-start border-b border-white/5 pb-4">
          <div>
            <div class="flex items-center space-x-2">
              <h3 class="text-lg font-bold text-white tracking-wider">{{ selectedOrder.order_number }}</h3>
              <span 
                class="px-2 py-0.5 rounded-full text-xxs font-bold border"
                :class="getStatusBadgeClass(selectedOrder.status)"
              >
                {{ getStatusLabel(selectedOrder.status) }}
              </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">
              Yaratilgan vaqt: {{ formatFullTime(selectedOrder.created_at) }}
            </p>
          </div>
          <button @click="closeDetails" class="p-1 rounded-lg bg-white/5 border border-white/10 hover:bg-white/10 text-slate-400 transition">
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Metadata -->
        <div class="grid grid-cols-2 gap-4 text-xs">
          <div class="p-3 bg-white/5 rounded-xl border border-white/5 space-y-1">
            <span class="text-slate-500 font-semibold block uppercase">Stol</span>
            <span class="text-white font-bold text-sm">{{ selectedOrder.table?.number || 'Olib ketish' }}</span>
          </div>
          <div class="p-3 bg-white/5 rounded-xl border border-white/5 space-y-1">
            <span class="text-slate-500 font-semibold block uppercase">Xizmatchi Ofitsiant</span>
            <span class="text-white font-bold text-sm">{{ selectedOrder.waiter?.name || 'Noma\'lum' }}</span>
          </div>
        </div>

        <!-- Items Breakdown -->
        <div class="space-y-3">
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Buyurtma tarkibi</span>
          <div class="max-h-48 overflow-y-auto divide-y divide-white/5 pr-1">
            <div v-for="item in selectedOrder.items" :key="item.id" class="py-3 flex flex-col space-y-1">
              <div class="flex justify-between text-sm">
                <span class="text-white font-medium">
                  {{ item.food?.name }}
                  <span v-if="item.size_name" class="text-3xs text-indigo-400 font-bold bg-indigo-500/10 border border-indigo-500/20 px-1.5 py-0.5 rounded-md ml-1">
                    {{ item.size_name }}
                  </span>
                </span>
                <span class="text-slate-400 font-medium">x{{ item.quantity }}</span>
              </div>
              <div class="flex justify-between text-xs text-slate-500">
                <span class="font-medium">{{ formatCurrency(item.price) }}</span>
                <span class="font-bold text-slate-400">{{ formatCurrency(item.price * item.quantity) }}</span>
              </div>
              <div v-if="item.notes" class="text-xxs text-amber-400/80 bg-amber-500/5 border border-amber-500/10 px-2.5 py-1 rounded-lg mt-1 inline-block w-fit font-medium">
                Izoh: {{ item.notes }}
              </div>
            </div>
          </div>
        </div>

        <!-- Total Sum -->
        <div class="border-t border-white/5 pt-4 flex justify-between items-center">
          <div>
            <span class="text-xxs text-slate-500 font-bold block uppercase tracking-wider">Umumiy summa</span>
            <span class="text-2xl font-extrabold text-white tracking-tight">
              {{ formatCurrency(selectedOrder.total_amount) }}
            </span>
          </div>

          <!-- Actions inside modal -->
          <div class="flex space-x-2">
            <button 
              v-if="canCancel(selectedOrder.status)"
              @click="handleCancel(selectedOrder); closeDetails()"
              class="px-4 py-2.5 rounded-xl text-xs font-bold bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500 hover:text-white transition duration-200"
            >
              Bekor qilish
            </button>
            <button 
              v-if="canAdvanceStatus(selectedOrder.status)"
              @click="handleAdvanceStatus(selectedOrder); closeDetails()"
              class="px-4 py-2.5 rounded-xl text-xs font-bold text-white shadow-lg transition duration-200"
              :class="getAdvanceButtonClass(selectedOrder.status)"
            >
              {{ getAdvanceButtonLabel(selectedOrder.status) }}
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { 
  Loader2, AlertTriangle, Eye, Trash2, X, ChefHat, ShoppingBag 
} from 'lucide-vue-next';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useOrdersStore } from '@/stores/orders';

const authStore = useAuthStore();
const ordersStore = useOrdersStore();
const router = useRouter();

// State
const activeTab = ref('new');
const allOrders = ref([]);
const loading = ref(false);
const error = ref('');
const selectedOrder = ref(null);
const nowTime = ref(Date.now());

// Polling interval ID
let pollingInterval = null;
let timeTickerInterval = null;

// Tab Configurations
const statusTabs = [
  { status: 'new', label: 'Yangi', badgeClass: 'bg-blue-500/10 border-blue-500/20 text-blue-400' },
  { status: 'cooking', label: 'Tayyorlanmoqda', badgeClass: 'bg-amber-500/10 border-amber-500/20 text-amber-400' },
  { status: 'ready', label: 'Tayyor', badgeClass: 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400' },
  { status: 'delivered', label: 'Yetkazildi', badgeClass: 'bg-slate-500/10 border-slate-500/20 text-slate-400' },
  { status: 'cancelled', label: 'Bekor qilindi', badgeClass: 'bg-red-500/10 border-red-500/20 text-red-400' },
];

// Computed list of filtered orders for active tab
const activeTabOrders = computed(() => {
  return allOrders.value.filter(o => o.status === activeTab.value);
});

// Lifecycle
onMounted(async () => {
  await loadAllOrders();
  
  // Set up polling simulation every 8 seconds
  pollingInterval = setInterval(async () => {
    await fetchLiveOrdersQuietly();
  }, 8000);

  // Time elapsed ticker
  timeTickerInterval = setInterval(() => {
    nowTime.value = Date.now();
  }, 10000);
});

onUnmounted(() => {
  clearInterval(pollingInterval);
  clearInterval(timeTickerInterval);
});

// Loading functions
const loadAllOrders = async () => {
  loading.value = true;
  error.value = '';
  try {
    const response = await fetch('/api/orders', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${authStore.token}`
      }
    });

    if (response.status === 401) {
      authStore.logout();
      router.push('/login');
      return;
    }

    const data = await response.json();
    if (!response.ok) {
      throw new Error(data.message || 'Buyurtmalarni yuklashda xatolik.');
    }
    allOrders.value = data;
  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
};

const fetchLiveOrdersQuietly = async () => {
  try {
    const response = await fetch('/api/orders', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${authStore.token}`
      }
    });

    if (response.status === 401) {
      authStore.logout();
      router.push('/login');
      return;
    }

    if (response.ok) {
      const data = await response.json();
      allOrders.value = data;
      // If modal is open, refresh selected order details
      if (selectedOrder.value) {
        const found = data.find(o => o.id === selectedOrder.value.id);
        if (found) selectedOrder.value = found;
      }
    }
  } catch (err) {
    // Fail silently on background poll
  }
};

// Count helpers
const getCountByStatus = (status) => {
  return allOrders.value.filter(o => o.status === status).length;
};

// Transition helpers
const canAdvanceStatus = (status) => {
  return ['new', 'cooking', 'ready'].includes(status);
};

const canCancel = (status) => {
  return ['new', 'cooking', 'ready'].includes(status);
};

const getAdvanceButtonLabel = (status) => {
  const labels = {
    'new': 'Tayyorlash',
    'cooking': 'Tayyor bo\'ldi',
    'ready': 'Yetkazildi',
  };
  return labels[status] || '';
};

const getAdvanceButtonClass = (status) => {
  const classes = {
    'new': 'bg-gradient-to-r from-blue-600 to-indigo-600 shadow-indigo-600/30 hover:shadow-indigo-600/40',
    'cooking': 'bg-gradient-to-r from-amber-500 to-orange-500 shadow-orange-500/30 hover:shadow-orange-500/40',
    'ready': 'bg-gradient-to-r from-emerald-500 to-teal-500 shadow-emerald-500/30 hover:shadow-emerald-500/40',
  };
  return classes[status] || '';
};

// Mutation handlers
const handleAdvanceStatus = async (order) => {
  const statusMap = {
    'new': 'cooking',
    'cooking': 'ready',
    'ready': 'delivered'
  };
  const nextStatus = statusMap[order.status];
  if (!nextStatus) return;

  try {
    const response = await fetch(`/api/orders/${order.id}/status`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${authStore.token}`
      },
      body: JSON.stringify({
        status: nextStatus
      })
    });
    if (!response.ok) {
      const data = await response.json();
      throw new Error(data.message || 'Xatolik yuz berdi.');
    }
    // Update local list
    await fetchLiveOrdersQuietly();
  } catch (err) {
    alert(err.message);
  }
};

const handleCancel = async (order) => {
  if (!confirm('Ushbu buyurtmani bekor qilmoqchimisiz?')) return;

  try {
    const response = await fetch(`/api/orders/${order.id}/cancel`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${authStore.token}`
      }
    });
    if (!response.ok) {
      const data = await response.json();
      throw new Error(data.message || 'Bekor qilishda xatolik.');
    }
    // Update local list
    await fetchLiveOrdersQuietly();
  } catch (err) {
    alert(err.message);
  }
};

// Modal functions
const openDetails = (order) => {
  selectedOrder.value = order;
};

const closeDetails = () => {
  selectedOrder.value = null;
};

// Formatting helpers
const formatCurrency = (val) => {
  return new Intl.NumberFormat('uz-UZ').format(val) + ' UZS';
};

const getStatusLabel = (status) => {
  const labels = {
    'new': 'Yangi',
    'cooking': 'Tayyorlanmoqda',
    'ready': 'Tayyor',
    'delivered': 'Yetkazildi',
    'cancelled': 'Bekor qilingan'
  };
  return labels[status] || status;
};

const getStatusBadgeClass = (status) => {
  const classes = {
    'new': 'bg-blue-500/10 border-blue-500/20 text-blue-400',
    'cooking': 'bg-amber-500/10 border-amber-500/20 text-amber-400',
    'ready': 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400',
    'delivered': 'bg-slate-500/10 border-slate-500/20 text-slate-400',
    'cancelled': 'bg-red-500/10 border-red-500/20 text-red-400'
  };
  return classes[status] || '';
};

// Elapsed time calculator: ORD relative date difference
const getElapsedTime = (createdAt) => {
  const diffMs = nowTime.value - new Date(createdAt).getTime();
  const diffMins = Math.max(0, Math.floor(diffMs / 60000));
  if (diffMins < 1) return 'Hozirgina';
  if (diffMins < 60) return `${diffMins} daqiqa oldin`;
  const diffHours = Math.floor(diffMins / 60);
  return `${diffHours} soat oldin`;
};

const formatFullTime = (dateStr) => {
  const date = new Date(dateStr);
  return date.toLocaleTimeString('uz-UZ', { hour: '2-digit', minute: '2-digit' }) + ' (' + date.toLocaleDateString('uz-UZ') + ')';
};
</script>

<style scoped>
.text-2xs {
  font-size: 0.7rem;
}
.text-3xs {
  font-size: 0.6rem;
}
.animate-fadeIn {
  animation: fadeIn 0.4s ease-out forwards;
}
.animate-scaleIn {
  animation: scaleIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes scaleIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}
</style>
