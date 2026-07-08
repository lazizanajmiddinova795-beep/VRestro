<template>
  <div class="p-6 space-y-6 flex-grow flex flex-col h-full overflow-y-auto">
    <!-- Top Header & Breadcrumbs -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-white">To'lovlar va Kassirlik</h1>
        <p class="text-sm text-slate-400">Buyurtmalar hisob-kitobi, kassa operatsiyalari va mijozlar keshbeki tizimi.</p>
      </div>
    </div>

    <!-- 1. Financial Overview Bar (Top Mini-Widgets) -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <!-- Bugungi jami tushum -->
      <div class="relative overflow-hidden rounded-2xl border border-white/5 bg-slate-950/40 p-6 backdrop-blur-xl shadow-xl">
        <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-indigo-500/10 blur-xl"></div>
        <div class="flex items-center space-x-4">
          <div class="rounded-lg bg-indigo-500/10 p-3 text-indigo-400 border border-indigo-500/20">
            <DollarSign class="h-6 w-6" />
          </div>
          <div>
            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Bugungi jami tushum</p>
            <h3 class="text-xl font-bold text-white mt-1">{{ formatCurrency(paymentStore.todayRevenue.total_revenue) }}</h3>
          </div>
        </div>
      </div>

      <!-- Naqd ulushi -->
      <div class="relative overflow-hidden rounded-2xl border border-white/5 bg-slate-950/40 p-6 backdrop-blur-xl shadow-xl">
        <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-emerald-500/10 blur-xl"></div>
        <div class="flex flex-col justify-between h-full">
          <div class="flex items-center space-x-4">
            <div class="rounded-lg bg-emerald-500/10 p-3 text-emerald-400 border border-emerald-500/20">
              <Banknote class="h-6 w-6" />
            </div>
            <div>
              <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Naqd to'lov</p>
              <h3 class="text-lg font-bold text-white mt-1">{{ formatCurrency(paymentStore.todayRevenue.cash_total) }}</h3>
            </div>
          </div>
          <div class="mt-4">
            <div class="w-full bg-white/5 rounded-full h-1.5 overflow-hidden">
              <div class="bg-emerald-500 h-1.5 rounded-full transition-all duration-500" :style="{ width: getPercentage(paymentStore.todayRevenue.cash_total) + '%' }"></div>
            </div>
            <div class="flex justify-between items-center text-xxs text-slate-400 mt-1">
              <span>Ulush</span>
              <span>{{ getPercentage(paymentStore.todayRevenue.cash_total) }}%</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Karta ulushi -->
      <div class="relative overflow-hidden rounded-2xl border border-white/5 bg-slate-950/40 p-6 backdrop-blur-xl shadow-xl">
        <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-cyan-500/10 blur-xl"></div>
        <div class="flex flex-col justify-between h-full">
          <div class="flex items-center space-x-4">
            <div class="rounded-lg bg-cyan-500/10 p-3 text-cyan-400 border border-cyan-500/20">
              <CreditCard class="h-6 w-6" />
            </div>
            <div>
              <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Karta to'lov</p>
              <h3 class="text-lg font-bold text-white mt-1">{{ formatCurrency(paymentStore.todayRevenue.card_total) }}</h3>
            </div>
          </div>
          <div class="mt-4">
            <div class="w-full bg-white/5 rounded-full h-1.5 overflow-hidden">
              <div class="bg-cyan-500 h-1.5 rounded-full transition-all duration-500" :style="{ width: getPercentage(paymentStore.todayRevenue.card_total) + '%' }"></div>
            </div>
            <div class="flex justify-between items-center text-xxs text-slate-400 mt-1">
              <span>Ulush</span>
              <span>{{ getPercentage(paymentStore.todayRevenue.card_total) }}%</span>
            </div>
          </div>
        </div>
      </div>

      <!-- QR / Payme / Click ulushi -->
      <div class="relative overflow-hidden rounded-2xl border border-white/5 bg-slate-950/40 p-6 backdrop-blur-xl shadow-xl">
        <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-purple-500/10 blur-xl"></div>
        <div class="flex flex-col justify-between h-full">
          <div class="flex items-center space-x-4">
            <div class="rounded-lg bg-purple-500/10 p-3 text-purple-400 border border-purple-500/20">
              <QrCode class="h-6 w-6" />
            </div>
            <div>
              <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">QR/Click/Payme</p>
              <h3 class="text-lg font-bold text-white mt-1">{{ formatCurrency(paymentStore.todayRevenue.qr_total) }}</h3>
            </div>
          </div>
          <div class="mt-4">
            <div class="w-full bg-white/5 rounded-full h-1.5 overflow-hidden">
              <div class="bg-purple-500 h-1.5 rounded-full transition-all duration-500" :style="{ width: getPercentage(paymentStore.todayRevenue.qr_total) + '%' }"></div>
            </div>
            <div class="flex justify-between items-center text-xxs text-slate-400 mt-1">
              <span>Ulush</span>
              <span>{{ getPercentage(paymentStore.todayRevenue.qr_total) }}%</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Workspace (Split Grid) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start flex-grow">
      
      <!-- 2. Active Unpaid Orders Sidebar (Left) -->
      <div class="lg:col-span-4 flex flex-col h-full space-y-4">
        <div class="rounded-2xl border border-white/5 bg-slate-950/40 p-4 backdrop-blur-xl flex flex-col flex-grow min-h-[500px]">
          <div class="flex items-center justify-between pb-3 border-b border-white/5 mb-3">
            <h2 class="text-base font-semibold text-white flex items-center gap-2">
              <Clock class="w-4 h-4 text-amber-500" />
              Faol buyurtmalar
            </h2>
            <span class="px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 text-xs font-bold">
              {{ unpaidOrders.length }} ta
            </span>
          </div>

          <!-- Loading state -->
          <div v-if="ordersLoading" class="flex flex-col items-center justify-center py-12 flex-grow">
            <div class="w-10 h-10 border-4 border-t-indigo-500 border-white/5 rounded-full animate-spin"></div>
            <span class="text-xs text-slate-400 mt-3">Buyurtmalar yuklanmoqda...</span>
          </div>

          <!-- Empty state -->
          <div v-else-if="unpaidOrders.length === 0" class="flex flex-col items-center justify-center py-12 flex-grow text-center">
            <CheckCircle class="w-12 h-12 text-slate-600 mb-2" />
            <span class="text-sm font-semibold text-slate-300">To'lanmagan buyurtma yo'q</span>
            <span class="text-xs text-slate-500 mt-1">Barcha buyurtmalar muvaffaqiyatli yopilgan.</span>
          </div>

          <!-- Active list -->
          <div v-else class="space-y-2 overflow-y-auto max-h-[500px] flex-grow pr-1">
            <button
              v-for="order in unpaidOrders"
              :key="order.id"
              @click="selectOrder(order)"
              class="w-full text-left p-3 rounded-xl border transition-all duration-200 flex flex-col gap-2"
              :class="selectedOrder?.id === order.id 
                ? 'bg-indigo-600/20 border-indigo-500 shadow-lg shadow-indigo-500/10' 
                : 'bg-white/5 border-white/5 hover:border-white/10 hover:bg-white/10'"
            >
              <div class="flex justify-between items-center">
                <span class="text-sm font-bold text-white">{{ order.order_number }}</span>
                <span class="px-2 py-0.5 rounded-full text-xxs uppercase tracking-wider font-bold" 
                      :class="getStatusClass(order.status)">
                  {{ getStatusText(order.status) }}
                </span>
              </div>
              <div class="flex justify-between items-center text-xs text-slate-400">
                <span class="flex items-center gap-1">
                  <User class="w-3.5 h-3.5" />
                  {{ order.waiter?.name || 'Waitstaff' }}
                </span>
                <span class="font-semibold text-slate-200">
                  Stol: {{ order.table?.table_number || 'Olib ketish' }}
                </span>
              </div>
              <div class="flex justify-between items-center border-t border-white/5 pt-2 mt-1">
                <span class="text-xs text-slate-400">Jami summa:</span>
                <span class="text-sm font-extrabold text-indigo-300">{{ formatCurrency(order.total_amount) }}</span>
              </div>
            </button>
          </div>
        </div>
      </div>

      <!-- 3. Interactive Checkout Workspace (Right) -->
      <div class="lg:col-span-8">
        <div v-if="!selectedOrder" class="rounded-2xl border border-white/5 bg-slate-950/40 p-12 backdrop-blur-xl flex flex-col items-center justify-center text-center h-[500px]">
          <ShoppingBag class="w-16 h-16 text-indigo-500/20 border border-indigo-500/10 rounded-2xl p-3 mb-4" />
          <h3 class="text-lg font-bold text-white">To'lov ish maydoni</h3>
          <p class="text-sm text-slate-400 mt-2 max-w-sm">Hisob-kitob qilish va to'lovni yakunlash uchun chap tomondagi faol buyurtmalardan birini tanlang.</p>
        </div>

        <div v-else class="rounded-2xl border border-white/5 bg-slate-950/40 p-6 backdrop-blur-xl flex flex-col space-y-6">
          <!-- Order Title -->
          <div class="flex justify-between items-start border-b border-white/5 pb-4">
            <div>
              <div class="flex items-center gap-2">
                <h3 class="text-lg font-bold text-white">{{ selectedOrder.order_number }}</h3>
                <span class="px-2 py-0.5 rounded-full text-xxs font-bold bg-white/10 text-white">
                  Stol: {{ selectedOrder.table?.table_number || 'Noma\'lum' }}
                </span>
              </div>
              <p class="text-xs text-slate-400 mt-1">Ofitsiant: {{ selectedOrder.waiter?.name || 'Tizim' }} | Sana: {{ formatDate(selectedOrder.created_at) }}</p>
            </div>
            <button @click="selectedOrder = null" class="p-1 rounded-lg bg-white/5 text-slate-400 hover:text-white border border-white/5">
              <X class="w-4 h-4" />
            </button>
          </div>

          <!-- Items list -->
          <div class="space-y-3">
            <h4 class="text-xs font-semibold uppercase text-slate-400 tracking-wider">Buyurtma tarkibi</h4>
            <div class="rounded-xl border border-white/5 bg-white/5 p-4 max-h-[180px] overflow-y-auto space-y-2">
              <div v-for="item in selectedOrder.items" :key="item.id" class="flex justify-between items-center text-sm text-slate-200">
                <div class="flex flex-col">
                  <span>{{ item.food?.name }}</span>
                  <span class="text-xxs text-slate-400" v-if="item.notes">Izoh: {{ item.notes }}</span>
                </div>
                <div class="flex items-center space-x-8 text-right font-medium">
                  <span class="text-slate-400 text-xs">{{ item.quantity }} x {{ formatCurrency(item.price) }}</span>
                  <span class="text-white font-semibold">{{ formatCurrency(item.quantity * item.price) }}</span>
                </div>
              </div>
            </div>
            <div class="flex justify-between items-center pt-2">
              <span class="text-sm font-semibold text-slate-400">Hisob jami:</span>
              <span class="text-lg font-black text-white">{{ formatCurrency(selectedOrder.total_amount) }}</span>
            </div>
          </div>

          <!-- Loyalty Integration Checkbox / Customer selector -->
          <div class="border-t border-white/5 pt-4 space-y-4">
            <h4 class="text-xs font-semibold uppercase text-slate-400 tracking-wider">Mijoz va Sodiqlik kartasi</h4>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Customer select -->
              <div class="relative">
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Mijozni tanlang (Keshbek uchun)</label>
                <div class="relative flex items-center">
                  <Search class="absolute left-3 w-4 h-4 text-slate-400" />
                  <input 
                    type="text" 
                    placeholder="Mijoz ismi yoki telefoni..." 
                    v-model="customerSearchQuery"
                    @input="searchCustomers"
                    class="w-full bg-slate-900 border border-white/5 rounded-xl py-2.5 pl-10 pr-4 text-sm text-white focus:outline-none focus:border-indigo-500 placeholder-slate-500"
                  />
                </div>

                <!-- Dropdown -->
                <div v-if="showCustomerDropdown && filteredCustomers.length > 0" class="absolute z-50 w-full mt-1 bg-slate-900 border border-white/10 rounded-xl max-h-48 overflow-y-auto shadow-2xl p-1">
                  <button 
                    v-for="c in filteredCustomers" 
                    :key="c.id"
                    @click="selectCustomer(c)"
                    class="w-full text-left px-3 py-2 text-xs text-slate-200 hover:bg-indigo-600 hover:text-white rounded-lg flex justify-between items-center"
                  >
                    <span>{{ c.name }} ({{ c.phone }})</span>
                    <span class="bg-indigo-500/20 text-indigo-400 font-bold px-1.5 py-0.5 rounded text-xxs">Balans: {{ formatCurrency(c.bonus_balance) }}</span>
                  </button>
                </div>
              </div>

              <!-- Selected Customer details & redeem -->
              <div class="flex flex-col justify-end">
                <div v-if="linkedCustomer" class="rounded-xl border border-white/5 bg-indigo-500/5 p-3 flex flex-col justify-between">
                  <div class="flex justify-between items-center text-xs">
                    <span class="font-bold text-white">{{ linkedCustomer.name }}</span>
                    <button @click="unlinkCustomer" class="text-red-400 hover:underline text-xxs">O'chirish</button>
                  </div>
                  <div class="flex justify-between items-center text-xxs text-slate-400 mt-1">
                    <span>Mavjud bonus: {{ formatCurrency(linkedCustomer.bonus_balance) }}</span>
                  </div>
                  <!-- Use bonus field -->
                  <div class="mt-2 flex items-center gap-2">
                    <input 
                      type="number" 
                      placeholder="Bonus ishlatish..." 
                      v-model.number="bonusUsed"
                      @input="validateBonus"
                      class="w-full bg-slate-950 border border-white/5 rounded-lg py-1.5 px-3 text-xs text-white focus:outline-none focus:border-indigo-500"
                    />
                    <button 
                      @click="useMaxBonus"
                      class="px-2.5 py-1.5 rounded-lg bg-indigo-600/30 border border-indigo-500/20 text-indigo-300 hover:bg-indigo-600 hover:text-white text-xs font-semibold shrink-0 transition"
                    >
                      MAX
                    </button>
                  </div>
                </div>
                <div v-else class="rounded-xl border border-dashed border-white/10 p-4 flex items-center justify-center text-slate-500 text-xs">
                  Mehmon check-outi (loyalliksiz)
                </div>
              </div>
            </div>
          </div>

          <!-- Payment Method Selectors -->
          <div class="border-t border-white/5 pt-4 space-y-3">
            <h4 class="text-xs font-semibold uppercase text-slate-400 tracking-wider">To'lov turi</h4>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <button 
                v-for="method in ['cash', 'card', 'qr', 'mixed']" 
                :key="method"
                @click="paymentMethod = method"
                class="py-3 px-4 rounded-xl border font-bold text-xs uppercase tracking-wider flex flex-col items-center justify-center gap-2 transition"
                :class="paymentMethod === method 
                  ? 'bg-indigo-600/20 border-indigo-500 text-indigo-300 shadow-md shadow-indigo-600/10' 
                  : 'bg-white/5 border-white/5 text-slate-300 hover:bg-white/10 hover:border-white/10'"
              >
                <Banknote v-if="method === 'cash'" class="w-5 h-5" />
                <CreditCard v-if="method === 'card'" class="w-5 h-5" />
                <QrCode v-if="method === 'qr'" class="w-5 h-5" />
                <Layers v-if="method === 'mixed'" class="w-5 h-5" />
                <span>{{ getMethodLabel(method) }}</span>
              </button>
            </div>
          </div>

          <!-- Mixed Payment inputs -->
          <div v-if="paymentMethod === 'mixed'" class="rounded-xl border border-white/5 bg-slate-900/60 p-4 space-y-4 animate-fadeIn">
            <h4 class="text-xs font-semibold uppercase text-slate-400 tracking-wider">Aralash to'lov summalarini kiritish</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-xxs text-slate-400 mb-1">Naqd pul summasi</label>
                <input 
                  type="number" 
                  v-model.number="mixedCash"
                  class="w-full bg-slate-950 border border-white/5 rounded-xl py-2 px-3 text-sm text-white focus:outline-none focus:border-indigo-500"
                />
              </div>
              <div>
                <label class="block text-xxs text-slate-400 mb-1">Karta summasi</label>
                <input 
                  type="number" 
                  v-model.number="mixedCard"
                  class="w-full bg-slate-950 border border-white/5 rounded-xl py-2 px-3 text-sm text-white focus:outline-none focus:border-indigo-500"
                />
              </div>
              <div>
                <label class="block text-xxs text-slate-400 mb-1">QR (Payme/Click) summasi</label>
                <input 
                  type="number" 
                  v-model.number="mixedQr"
                  class="w-full bg-slate-950 border border-white/5 rounded-xl py-2 px-3 text-sm text-white focus:outline-none focus:border-indigo-500"
                />
              </div>
            </div>
            
            <div class="flex justify-between items-center text-xs text-slate-400 border-t border-white/5 pt-2">
              <span>Loyallik bonus ishlatildi: <strong>{{ formatCurrency(bonusUsed) }}</strong></span>
              <span>Kiritildi: <strong :class="mixedTotalEqualsAmount ? 'text-emerald-400' : 'text-red-400'">{{ formatCurrency(mixedCash + mixedCard + mixedQr + bonusUsed) }}</strong> / {{ formatCurrency(selectedOrder.total_amount) }}</span>
            </div>
          </div>

          <!-- Checkout summary & action button -->
          <div class="border-t border-white/5 pt-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="text-xs text-slate-400">
              <div v-if="bonusUsed > 0">Bonus chegirmasi: <strong class="text-emerald-400">{{ formatCurrency(bonusUsed) }}</strong></div>
              <div v-if="linkedCustomer">Mijozga keshbek qo'shiladi: <strong class="text-indigo-400">{{ formatCurrency((selectedOrder.total_amount - bonusUsed) * 0.05) }}</strong></div>
              <div class="mt-1 text-sm text-white font-bold">To'lanadigan yakuniy summa: <strong class="text-lg text-indigo-300 font-extrabold">{{ formatCurrency(selectedOrder.total_amount - bonusUsed) }}</strong></div>
            </div>

            <button 
              @click="submitPayment"
              :disabled="loading || (paymentMethod === 'mixed' && !mixedTotalEqualsAmount)"
              class="px-6 py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 disabled:opacity-50 text-white font-bold text-sm tracking-wide shadow-lg shadow-emerald-500/20 hover:scale-102 transition flex items-center justify-center gap-2 cursor-pointer"
            >
              <Loader2 v-if="loading" class="w-4 h-4 animate-spin" />
              <CheckCircle v-else class="w-4 h-4" />
              <span>TO'LOVNI YAKUNLASH</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Alert Dialog -->
    <div v-if="alertMessage" class="fixed bottom-6 right-6 z-50 rounded-2xl bg-slate-900 border border-emerald-500/30 p-4 shadow-2xl flex items-center gap-3 backdrop-blur-xl animate-slideIn">
      <div class="rounded-lg bg-emerald-500/20 text-emerald-400 p-2 border border-emerald-500/30">
        <CheckCircle class="w-5 h-5" />
      </div>
      <div>
        <h4 class="text-sm font-bold text-white">{{ alertTitle }}</h4>
        <p class="text-xs text-slate-400 mt-0.5">{{ alertMessage }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { 
  DollarSign, Banknote, CreditCard, QrCode, Layers, 
  Clock, CheckCircle, User, ShoppingBag, X, Search, Loader2 
} from 'lucide-vue-next';
import { usePaymentStore } from '@/stores/payment';
import { useOrdersStore } from '@/stores/orders';
import { useCustomerStore } from '@/stores/customers';

const paymentStore = usePaymentStore();
const ordersStore = useOrdersStore();
const customerStore = useCustomerStore();

// UI States
const selectedOrder = ref(null);
const paymentMethod = ref('cash');
const loading = ref(false);

// Customer loyalty
const customerSearchQuery = ref('');
const showCustomerDropdown = ref(false);
const linkedCustomer = ref(null);
const bonusUsed = ref(0);

// Mixed payments state
const mixedCash = ref(0);
const mixedCard = ref(0);
const mixedQr = ref(0);

// Alert Notification
const alertTitle = ref('');
const alertMessage = ref('');

// Load initial data
onMounted(async () => {
  await paymentStore.fetchTodayRevenue();
  await ordersStore.fetchOrders();
  await customerStore.fetchCustomers();
});

// Computed properties
const ordersLoading = computed(() => ordersStore.loading);
const unpaidOrders = computed(() => {
  // Show active status orders: 'ready' or 'cooking' or 'new'
  return ordersStore.orders.filter(o => o.status !== 'delivered' && o.status !== 'cancelled');
});

const filteredCustomers = computed(() => {
  if (!customerSearchQuery.value) return [];
  const q = customerSearchQuery.value.toLowerCase();
  return customerStore.customers.filter(c => 
    c.name.toLowerCase().includes(q) || c.phone.includes(q)
  );
});

const mixedTotalEqualsAmount = computed(() => {
  if (!selectedOrder.value) return false;
  const sum = mixedCash.value + mixedCard.value + mixedQr.value + bonusUsed.value;
  return Math.abs(sum - parseFloat(selectedOrder.value.total_amount)) < 0.01;
});

// Methods
const formatCurrency = (val) => {
  if (val === undefined || val === null) return '0 UZS';
  return new Intl.NumberFormat('uz-UZ').format(val) + ' UZS';
};

const getPercentage = (val) => {
  const total = parseFloat(paymentStore.todayRevenue.total_revenue) || 1;
  return Math.round((parseFloat(val) / total) * 100);
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleString('uz-UZ', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit' });
};

const getStatusClass = (status) => {
  switch (status) {
    case 'new': return 'bg-blue-500/10 text-blue-400 border border-blue-500/20';
    case 'cooking': return 'bg-amber-500/10 text-amber-400 border border-amber-500/20';
    case 'ready': return 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
    default: return 'bg-slate-500/10 text-slate-400 border border-slate-500/20';
  }
};

const getStatusText = (status) => {
  switch (status) {
    case 'new': return 'Yangi';
    case 'cooking': return 'Tayyorlanmoqda';
    case 'ready': return 'Tayyor';
    default: return status;
  }
};

const getMethodLabel = (method) => {
  switch (method) {
    case 'cash': return 'Naqd (Naqd)';
    case 'card': return 'Karta (Karta)';
    case 'qr': return 'QR (Payme/Click)';
    case 'mixed': return 'Aralash';
    default: return method;
  }
};

const selectOrder = (order) => {
  selectedOrder.value = order;
  paymentMethod.value = 'cash';
  linkedCustomer.value = null;
  customerSearchQuery.value = '';
  bonusUsed.value = 0;
  
  // Pre-fill mixed amounts
  mixedCash.value = parseFloat(order.total_amount);
  mixedCard.value = 0;
  mixedQr.value = 0;
};

// Customer Selection
const searchCustomers = () => {
  showCustomerDropdown.value = customerSearchQuery.value.length > 0;
};

const selectCustomer = (c) => {
  linkedCustomer.value = c;
  customerSearchQuery.value = c.name;
  showCustomerDropdown.value = false;
  bonusUsed.value = 0;
};

const unlinkCustomer = () => {
  linkedCustomer.value = null;
  customerSearchQuery.value = '';
  bonusUsed.value = 0;
};

const validateBonus = () => {
  if (!linkedCustomer.value) {
    bonusUsed.value = 0;
    return;
  }
  const maxAvailable = parseFloat(linkedCustomer.value.bonus_balance);
  const maxPossible = parseFloat(selectedOrder.value.total_amount);
  if (bonusUsed.value < 0) bonusUsed.value = 0;
  if (bonusUsed.value > maxAvailable) bonusUsed.value = maxAvailable;
  if (bonusUsed.value > maxPossible) bonusUsed.value = maxPossible;
};

const useMaxBonus = () => {
  if (!linkedCustomer.value) return;
  const maxAvailable = parseFloat(linkedCustomer.value.bonus_balance);
  const maxPossible = parseFloat(selectedOrder.value.total_amount);
  bonusUsed.value = Math.min(maxAvailable, maxPossible);
};

// Submit Payment
const submitPayment = async () => {
  if (!selectedOrder.value) return;
  loading.value = true;
  
  const payload = {
    order_id: selectedOrder.value.id,
    customer_id: linkedCustomer.value ? linkedCustomer.value.id : null,
    payment_method: paymentMethod.value,
    bonus_used: bonusUsed.value
  };

  if (paymentMethod.value === 'mixed') {
    payload.cash_amount = mixedCash.value;
    payload.card_amount = mixedCard.value;
    payload.qr_amount = mixedQr.value;
  }

  try {
    const payment = await paymentStore.processPayment(payload);
    
    // Refresh lists
    await ordersStore.fetchOrders();
    selectedOrder.value = null;

    // Show Success message
    triggerAlert('Muvaffaqiyatli to\'lov!', `Buyurtma yopildi, jami tushum: ${formatCurrency(payment.total_amount)}`);
  } catch (err) {
    console.error(err);
    alert(err.message || 'To\'lovni yakunlashda xatolik yuz berdi.');
  } finally {
    loading.value = false;
  }
};

const triggerAlert = (title, message) => {
  alertTitle.value = title;
  alertMessage.value = message;
  setTimeout(() => {
    alertMessage.value = '';
  }, 4000);
};
</script>

<style scoped>
.text-xxs {
  font-size: 0.65rem;
}
.animate-fadeIn {
  animation: fadeIn 0.3s ease-out;
}
.animate-slideIn {
  animation: slideIn 0.3s ease-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-4px); }
  to { opacity: 1; transform: translateY(0); }
}
@keyframes slideIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
