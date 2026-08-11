<template>
  <div class="h-full">
    <div class="space-y-6 bg-[#F1F5F9] no-print">
    <!-- View Title & Actions -->
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-xl md:text-2xl font-black text-slate-900 tracking-wide">{{ settingsStore.t('app_title') }} - {{ cashierStore.t('stollar_xaritasi') }}</h2>
        <p class="text-sm text-slate-700 font-bold mt-1">Stollar holati real vaqt rejimida avtomatik ravishda yangilanadi (har 5s)</p>
      </div>
      <div class="flex items-center space-x-3 shrink-0">
        <router-link 
          to="/cashier/order?type=takeaway"
          class="px-4 py-2.5 rounded-xl bg-indigo-600 font-bold text-sm text-white shadow-md hover:bg-indigo-700 transition-all flex items-center justify-center space-x-2"
        >
          <ShoppingBag class="w-4 h-4" />
          <span>+ Olib ketish</span>
        </router-link>

        <router-link 
          to="/cashier/order?type=delivery"
          class="px-4 py-2.5 rounded-xl bg-emerald-600 font-bold text-sm text-white shadow-md hover:bg-emerald-700 transition-all flex items-center justify-center space-x-2"
        >
          <Truck class="w-4 h-4" />
          <span>+ Dastavka (Yetkazib berish)</span>
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

    <!-- Floor Filters -->
    <div v-if="availableFloors.length > 0" class="flex items-center space-x-2 overflow-x-auto pb-2">
      <button 
        @click="selectedFloor = 'all'"
        class="px-4 py-2 rounded-xl text-sm font-bold whitespace-nowrap transition-colors"
        :class="selectedFloor === 'all' ? 'bg-indigo-600 text-white shadow-md' : 'bg-white border border-slate-300 text-slate-600 hover:bg-slate-50'"
      >
        Barcha Stollar
      </button>
      <button 
        v-for="floor in availableFloors" 
        :key="floor"
        @click="selectedFloor = floor"
        class="px-4 py-2 rounded-xl text-sm font-bold whitespace-nowrap transition-colors"
        :class="selectedFloor === floor ? 'bg-indigo-600 text-white shadow-md' : 'bg-white border border-slate-300 text-slate-600 hover:bg-slate-50'"
      >
        {{ floor }}
      </button>
    </div>

    <!-- Tables Grid -->
    <div 
      v-if="filteredTables.length > 0"
      class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4"
    >
      <div
        v-for="table in filteredTables"
        :key="table.id"
        @click="handleTableClick(table)"
        class="relative group p-5 text-left transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 min-h-[140px] flex flex-col justify-between cursor-pointer"
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
          <div class="flex flex-col space-y-3 w-full">
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
            
            <!-- Actions for Occupied Tables -->
            <div v-if="table.status === 'occupied'" class="flex space-x-2 w-full">
              <button 
                @click.stop="handlePrintPreBill(table)"
                class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg shadow-sm transition flex items-center justify-center"
                title="Chek chiqarish"
              >
                <Printer class="w-4 h-4" />
              </button>
              <button 
                @click.stop="handleCheckout(table)"
                class="flex-1 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg shadow-sm transition"
              >
                To'lovga o'tish
              </button>
              <button 
                @click.stop="handleAddMore(table)"
                class="flex-1 py-1.5 bg-slate-200 hover:bg-slate-300 text-blue-900 text-xs font-bold rounded-lg shadow-sm transition"
              >
                Yana qo'shish
              </button>
            </div>
          </div>
        </div>
      </div>
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
    </div> <!-- end no-print space-y-6 bg-[#F1F5F9] -->

    <!-- Pre-bill Print Section -->
    <div id="physical-thermal-receipt" class="print-only text-black bg-white" v-if="prebillOrder">
      <div class="thermal-ticket">
        <div class="ticket-center mb-1">
          <img :src="'/foodflow_logo.png'" style="width: 1.5cm; height: 1.5cm; display: block; margin: 0 auto; filter: grayscale(100%);" alt="Logo" />
        </div>
        <div class="ticket-center font-bold" style="font-size: 14pt;">{{ settingsStore.branding?.name || 'FoodFlow' }}</div>
        <div class="ticket-center" style="font-size: 9pt;">{{ settingsStore.branding?.address || 'Toshkent, O\'zbekiston' }}</div>
        <div class="ticket-center" style="font-size: 9pt;">Tel: {{ settingsStore.branding?.phone || '+998 90 123 45 67' }}</div>
        
        <div class="ticket-divider"></div>

        <div>{{ cashierStore.t('buyurtma_no') }}: {{ prebillOrder.order_number }}</div>
        <div>{{ cashierStore.t('sana') }}: {{ printFormatDateTime(new Date()) }}</div>
        <div v-if="prebillOrder.table?.table_number">{{ cashierStore.t('stol') }}: {{ prebillOrder.table.table_number }}</div>
        <div>Offitsiant: {{ prebillOrder.waiter?.name || 'Kassir' }}</div>

        <div class="ticket-divider"></div>

        <div class="ticket-center ticket-bold" style="font-size: 14pt; margin: 4px 0;">*** {{ cashierStore.t('navbat_cheki').toUpperCase() }} ***</div>
        <div class="ticket-center ticket-bold" style="font-size: 18pt; margin: 6px 0; letter-spacing: 1px;">№{{ prebillOrder.order_number }}</div>
        
        <div class="ticket-divider"></div>

        <table class="ticket-table">
          <thead>
            <tr style="border-bottom: 1px solid #000;">
              <th align="left" style="padding-bottom: 4px;">{{ cashierStore.t('nomi') }}</th>
              <th align="center" style="padding-bottom: 4px;">{{ cashierStore.t('soni') }}</th>
              <th align="right" style="padding-bottom: 4px;">{{ cashierStore.t('summa') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in prebillOrder.items" :key="item.id">
              <td style="padding: 2px 0;">{{ item.food?.name }}</td>
              <td align="center" style="padding: 2px 0;">x{{ item.quantity }}</td>
              <td align="right" style="padding: 2px 0;">{{ formatCurrency(item.quantity * item.price) }}</td>
            </tr>
          </tbody>
        </table>

        <div class="ticket-divider"></div>

        <div class="ticket-totals">
          <div style="display: flex; justify-content: space-between;">
            <span>{{ cashierStore.t('oraliq_jami') }}:</span>
            <span>{{ formatCurrency(getPrebillSubtotal()) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between; font-style: italic;" v-if="parseFloat(prebillOrder.discount_amount) > 0">
            <span>Chegirma:</span>
            <span>-{{ formatCurrency(prebillOrder.discount_amount) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between;" v-if="getPrebillServiceCharge() > 0">
            <span>Xizmat haqi ({{ settingsStore.settings?.service_charge_rate || 0 }}%):</span>
            <span>{{ formatCurrency(getPrebillServiceCharge()) }}</span>
          </div>

          <div class="ticket-divider"></div>
          <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 14pt; margin-top: 5px;">
            <span>JAMI TO'LOV:</span>
            <span>{{ formatCurrency(getPrebillTotal()) }}</span>
          </div>
        </div>

        <div class="ticket-divider"></div>

        <div class="ticket-center" style="margin: 12px 0;" v-if="printQrCodeUrl">
          <img :src="printQrCodeUrl" style="width: 2.2cm; height: 2.2cm; display: block; margin: 0 auto;" alt="QR Code" @load="onPrebillQrLoaded" @error="onPrebillQrLoaded" />
          <div style="font-size: 7.5pt; font-family: monospace; margin-top: 4px; text-transform: uppercase;">SCAN TO VERIFY</div>
        </div>

        <div class="ticket-divider"></div>

        <div class="ticket-center ticket-footer-text">
          <p>{{ settingsStore.settings?.receipt_header || 'FoodFlow - Xizmatimizdan mamnunmisiz?' }}</p>
          <p class="ticket-bold">{{ settingsStore.settings?.receipt_footer || 'Xaridingiz uchun rahmat! Yana keling!' }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useSettingsStore } from '@/stores/settings';
const settingsStore = useSettingsStore();
import { ref, computed, onMounted, onUnmounted, markRaw, nextTick } from 'vue';
import { RotateCw, Users as UsersIcon, HelpCircle, CheckCircle, Play, Plus, ShoppingBag, Truck, Printer } from 'lucide-vue-next';
import { useCashierTablesStore } from '@/stores/cashierTables';
import { useCashierStore } from '@/stores/cashier';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const cashierTablesStore = useCashierTablesStore();
const cashierStore = useCashierStore();
const router = useRouter();
let pollInterval = null;

const prebillOrder = ref(null);
const authStore = useAuthStore();

const getPrebillSubtotal = () => {
  if (!prebillOrder.value || !prebillOrder.value.items) return 0;
  return prebillOrder.value.items.reduce((acc, item) => acc + (parseFloat(item.price) * item.quantity), 0);
};

const getPrebillServiceCharge = () => {
  if (!prebillOrder.value) return 0;
  const sub = getPrebillSubtotal();
  const disc = parseFloat(prebillOrder.value.discount_amount) || 0;
  const rate = parseFloat(settingsStore.settings?.service_charge_rate) || 0;
  return (sub - disc) * (rate / 100);
};

const getPrebillTotal = () => {
  const sub = getPrebillSubtotal();
  const disc = parseFloat(prebillOrder.value?.discount_amount) || 0;
  const svc = getPrebillServiceCharge();
  return sub - disc + svc;
};

const printQrCodeUrl = computed(() => {
  const name = settingsStore.branding?.name || 'FoodFlow';
  const address = settingsStore.branding?.address || "Toshkent, O'zbekiston";
  return `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(name + ' - ' + address)}`;
});

const formatCurrency = (val) => {
  if (!val) return '0 so\'m';
  return parseFloat(val).toLocaleString('uz-UZ') + ' so\'m';
};

const printFormatDateTime = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleString('uz-UZ', { 
    day: '2-digit', month: '2-digit', year: 'numeric',
    hour: '2-digit', minute: '2-digit', hour12: false
  });
};

const handlePrintPreBill = async (table) => {
  try {
    const res = await fetch(`/api/orders?table_id=${table.id}&status=new,cooking,ready,delivered`, {
      headers: { 'Authorization': `Bearer ${authStore.token}`, 'Accept': 'application/json' }
    });
    if (res.ok) {
      const ordersData = await res.json();
      const ordersList = Array.isArray(ordersData) ? ordersData : (ordersData.data || []);
      const tableOrders = ordersList.filter(o => String(o.table_id) === String(table.id));
      const active = tableOrders.find(o => !o.payments || o.payments.length === 0);
      
      if (active) {
        prebillOrder.value = active;
        await nextTick();
        
        let printTriggered = false;
        const doPrint = () => {
          if (printTriggered) return;
          printTriggered = true;
          window.print();
          setTimeout(() => { prebillOrder.value = null; }, 1000);
        };

        // Assign to a global-like variable or expose it to the template via a ref if needed, 
        // but it's simpler to just set a longer timeout if we don't have a direct hook.
        // Wait up to 1500ms for images to load.
        prebillPrintCallback = doPrint;
        setTimeout(doPrint, 1500);
      }
    }
  } catch (err) {
    console.error('Pre-bill loading error:', err);
  }
};

let prebillPrintCallback = null;
const onPrebillQrLoaded = () => {
  if (prebillPrintCallback) prebillPrintCallback();
};

const selectedFloor = ref('all');

const availableFloors = computed(() => {
  if (!cashierTablesStore.tables) return [];
  const floors = new Set(cashierTablesStore.tables.map(t => t.floor).filter(Boolean));
  return Array.from(floors).sort();
});

const filteredTables = computed(() => {
  if (!cashierTablesStore.tables) return [];
  if (selectedFloor.value === 'all') return cashierTablesStore.tables;
  return cashierTablesStore.tables.filter(t => t.floor === selectedFloor.value);
});

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
  if (table.status !== 'occupied') {
    router.push({ path: '/cashier/order', query: { table_id: table.id } });
  }
};

const handleCheckout = (table) => {
  router.push({ path: '/cashier/order', query: { table_id: table.id, action: 'checkout' } });
};

const handleAddMore = (table) => {
  router.push({ path: '/cashier/order', query: { table_id: table.id } });
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
