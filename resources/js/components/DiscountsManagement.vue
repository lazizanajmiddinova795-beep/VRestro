<template>
  <div class="p-6 space-y-6 flex-grow flex flex-col h-full overflow-y-auto">
    <!-- Top Header & Breadcrumbs -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-white">Chegirmalar va Promo-kodlar</h1>
        <p class="text-sm text-slate-400">Promokampaniyalar, foizli va summali vaucherlar hamda sotuv chegirmalari boshqaruvi.</p>
      </div>
      <div>
        <button 
          @click="openCreateModal"
          class="flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/50 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200"
        >
          <Plus class="w-4 h-4" />
          <span>Yangi chegirma qo'shish</span>
        </button>
      </div>
    </div>

    <!-- Alert Message for Pinia Errors -->
    <div v-if="discountStore.error" class="p-4 rounded-xl border border-red-500/20 bg-red-500/10 text-red-200 text-sm flex items-start space-x-3 backdrop-blur-md">
      <AlertCircle class="w-5 h-5 shrink-0 mt-0.5" />
      <span>{{ discountStore.error }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
      <!-- Left 2 Cols: Coupons Grid -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Search and Filters -->
        <div class="relative rounded-2xl border border-white/5 bg-slate-950/40 p-4 backdrop-blur-xl flex flex-col sm:flex-row gap-4 items-center justify-between">
          <div class="relative w-full sm:w-72">
            <Search class="absolute left-3.5 top-3 w-4 h-4 text-slate-400" />
            <input 
              v-model="filters.search"
              @input="handleSearch"
              type="text" 
              placeholder="Nomi yoki kod bo'yicha qidirish..." 
              class="w-full pl-10 pr-4 py-2 text-sm bg-white/5 border border-white/10 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 transition duration-200"
            />
          </div>
          <div class="flex items-center space-x-2 shrink-0">
            <button 
              @click="setFilterActive('')"
              class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition duration-200"
              :class="filters.is_active === '' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white bg-white/5 hover:bg-white/10'"
            >
              Barchasi
            </button>
            <button 
              @click="setFilterActive('1')"
              class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition duration-200"
              :class="filters.is_active === '1' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white bg-white/5 hover:bg-white/10'"
            >
              Faol
            </button>
            <button 
              @click="setFilterActive('0')"
              class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition duration-200"
              :class="filters.is_active === '0' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white bg-white/5 hover:bg-white/10'"
            >
              Nofaol
            </button>
          </div>
        </div>

        <!-- Coupons Grid -->
        <div v-if="discountStore.loading" class="flex justify-center items-center py-12">
          <div class="w-10 h-10 border-4 border-indigo-500/30 border-t-indigo-500 rounded-full animate-spin"></div>
        </div>

        <div v-else-if="discountStore.discounts.length === 0" class="flex flex-col items-center justify-center py-16 text-center rounded-2xl border border-dashed border-white/10 bg-slate-950/20">
          <Tag class="w-12 h-12 text-slate-500 mb-3" />
          <h3 class="text-lg font-semibold text-white">Hech qanday chegirma topilmadi</h3>
          <p class="text-sm text-slate-400 mt-1 max-w-sm">Hozircha hech qanday promo-kod yoki chegirma kampaniyalari qo'shilmagan.</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- coupon physical-like card -->
          <div 
            v-for="discount in discountStore.discounts" 
            :key="discount.id"
            class="relative rounded-2xl border border-white/5 bg-slate-950/40 backdrop-blur-xl p-6 shadow-xl flex flex-col justify-between overflow-hidden group hover:border-white/10 transition-all duration-300"
          >
            <!-- Glowing light background based on active status -->
            <div 
              class="absolute -right-10 -top-10 h-32 w-32 rounded-full blur-3xl transition-colors duration-500"
              :class="discount.is_active ? 'bg-indigo-500/10 group-hover:bg-indigo-500/20' : 'bg-slate-500/5'"
            ></div>

            <!-- Coupon main content -->
            <div class="space-y-4 relative z-10">
              <div class="flex items-start justify-between">
                <div>
                  <h3 class="font-bold text-white text-lg tracking-wide group-hover:text-indigo-300 transition-colors duration-200">{{ discount.name }}</h3>
                  <span class="text-xxs text-slate-400 mt-0.5 inline-block">ID: #{{ discount.id }}</span>
                </div>
                <!-- Status Toggle -->
                <button 
                  @click="handleToggleStatus(discount.id)"
                  class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                  :class="discount.is_active ? 'bg-indigo-600' : 'bg-slate-800'"
                >
                  <span 
                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-lg ring-0 transition duration-200"
                    :class="discount.is_active ? 'translate-x-5' : 'translate-x-0'"
                  ></span>
                </button>
              </div>

              <!-- Type & Value Badges -->
              <div class="flex items-center space-x-3">
                <span 
                  class="px-2.5 py-1 text-xs font-semibold rounded-lg flex items-center space-x-1.5"
                  :class="discount.type === 'percentage' ? 'bg-violet-500/10 text-violet-300 border border-violet-500/20' : 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/20'"
                >
                  <Percent v-if="discount.type === 'percentage'" class="w-3.5 h-3.5" />
                  <DollarSign v-else class="w-3.5 h-3.5" />
                  <span>{{ discount.type === 'percentage' ? 'Foizli' : 'Summali' }}</span>
                </span>
                
                <span class="text-xl font-extrabold text-white">
                  {{ discount.type === 'percentage' ? discount.value + '%' : formatCurrency(discount.value) }}
                </span>
              </div>

              <!-- Promocode badge if exists -->
              <div class="flex flex-col gap-1.5">
                <div class="flex items-center space-x-2 text-xs">
                  <span class="text-slate-400">Promo-kod:</span>
                  <span 
                    v-if="discount.code"
                    class="font-mono px-2 py-0.5 bg-white/5 border border-white/10 rounded text-indigo-300 font-bold uppercase tracking-wider select-all"
                  >
                    {{ discount.code }}
                  </span>
                  <span v-else class="text-slate-500 italic">Avtomatik menyu chegirmasi</span>
                </div>

                <!-- Minimum Order Limit -->
                <div class="text-xs text-slate-400 flex items-center space-x-1">
                  <span>Minimal buyurtma:</span>
                  <span class="text-white font-medium">{{ formatCurrency(discount.min_order_amount) }}</span>
                </div>
              </div>
            </div>

            <!-- Coupon footer details -->
            <div class="mt-6 pt-4 border-t border-white/5 flex items-center justify-between relative z-10 text-xs text-slate-400">
              <div class="flex items-center space-x-1">
                <Calendar class="w-3.5 h-3.5 text-slate-500" />
                <span v-if="discount.expires_at">
                  {{ formatDate(discount.starts_at) }} - {{ formatDate(discount.expires_at) }}
                </span>
                <span v-else class="italic text-slate-500">Muddatsiz</span>
              </div>

              <div class="flex items-center space-x-2">
                <button 
                  @click="openEditModal(discount)"
                  class="p-1.5 rounded-lg hover:bg-white/5 hover:text-white transition duration-200"
                  title="Tahrirlash"
                >
                  <Edit3 class="w-4 h-4 text-slate-400" />
                </button>
                <button 
                  @click="handleDelete(discount.id)"
                  class="p-1.5 rounded-lg hover:bg-red-500/10 hover:text-red-400 transition duration-200"
                  title="O'chirish"
                >
                  <Trash2 class="w-4 h-4 text-slate-400" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Col: POS Checkout Promo-Code Hook Panel -->
      <div class="space-y-6">
        <div class="rounded-2xl border border-white/5 bg-slate-950/40 p-6 backdrop-blur-xl shadow-xl space-y-6 relative overflow-hidden">
          <div class="absolute -right-6 -top-6 h-20 w-20 rounded-full bg-violet-600/10 blur-xl"></div>
          <div>
            <h2 class="text-lg font-bold text-white flex items-center space-x-2">
              <ShoppingBag class="w-5 h-5 text-indigo-400" />
              <span>POS Chekout Promo-kod</span>
            </h2>
            <p class="text-xs text-slate-400 mt-1">Haqiqiy buyurtmalarni tanlab chegirma yoki promo-kodlarni tekshirib ko'rish integratsiya paneli.</p>
          </div>

          <!-- Select Order -->
          <div class="space-y-2">
            <label class="text-xs text-slate-400 uppercase tracking-wider block font-semibold">Faol buyurtmani tanlang</label>
            <div v-if="ordersLoading" class="flex items-center space-x-2 text-xs text-slate-400">
              <div class="w-4 h-4 border-2 border-indigo-500/30 border-t-indigo-500 rounded-full animate-spin"></div>
              <span>Buyurtmalar yuklanmoqda...</span>
            </div>
            <select 
              v-else-if="activeOrders.length > 0"
              v-model="selectedOrderId" 
              @change="handleOrderChange"
              class="w-full bg-slate-900 border border-white/10 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500/50 transition duration-200"
            >
              <option value="" disabled>Buyurtmani tanlang</option>
              <option v-for="order in activeOrders" :key="order.id" :value="order.id">
                {{ order.order_number }} - Stol: {{ order.table?.table_number || 'Olib ketish' }} ({{ formatCurrency(order.total_amount) }})
              </option>
            </select>
            <div v-else class="text-xs text-amber-400 italic">
              Ayni paytda faol (delivered bo'lmagan) buyurtmalar topilmadi. Avval yangi buyurtma yarating.
            </div>
          </div>

          <!-- Current Order Summary Card -->
          <div v-if="currentOrder" class="p-4 rounded-xl bg-white/5 border border-white/10 space-y-3">
            <div class="flex justify-between items-center text-xs text-slate-400">
              <span>Buyurtma:</span>
              <span class="text-white font-bold">{{ currentOrder.order_number }}</span>
            </div>
            
            <div class="text-xs text-slate-400">
              <span class="block mb-1.5 font-semibold">Tarkibi:</span>
              <ul class="space-y-1 pl-2">
                <li v-for="item in currentOrder.items" :key="item.id" class="flex justify-between text-slate-300">
                  <span>{{ item.food?.name }} ({{ item.quantity }}x)</span>
                  <span>{{ formatCurrency(item.price * item.quantity) }}</span>
                </li>
              </ul>
            </div>

            <!-- Discount Applied Snapshot -->
            <div v-if="currentOrder.discount" class="pt-2.5 border-t border-white/5 space-y-1.5 text-xs">
              <div class="flex justify-between text-slate-400">
                <span>Qo'llangan Chegirma:</span>
                <span class="text-indigo-400 font-bold uppercase">{{ currentOrder.discount.name }}</span>
              </div>
              <div class="flex justify-between text-slate-400">
                <span>Chegirma Summasi:</span>
                <span class="text-red-400 font-bold">- {{ formatCurrency(currentOrder.discount_amount) }}</span>
              </div>
            </div>

            <div class="pt-2.5 border-t border-white/5 flex justify-between items-center">
              <span class="text-sm font-semibold text-slate-300">Jami summasi:</span>
              <span class="text-base font-extrabold text-white">
                {{ formatCurrency(currentOrder.total_amount) }}
              </span>
            </div>
          </div>

          <!-- Test Promo Input -->
          <div class="space-y-3">
            <label class="text-xs text-slate-400 uppercase tracking-wider block font-semibold">Promo-kod yozing</label>
            <div class="flex space-x-2">
              <input 
                v-model="promocodeString"
                type="text" 
                placeholder="Masalan: YANGIYIL2026" 
                class="w-full px-3.5 py-2.5 text-sm bg-white/5 border border-white/10 rounded-xl text-white uppercase placeholder-slate-500 focus:outline-none focus:border-indigo-500/50 transition duration-200"
              />
              <button 
                @click="testApplyPromocode"
                :disabled="!selectedOrderId || !promocodeString"
                class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white font-semibold text-sm transition duration-200 shrink-0"
              >
                Tekshirish
              </button>
            </div>
          </div>

          <!-- Promo Feedback Alert -->
          <div v-if="verificationSuccess" class="p-3 rounded-xl border border-emerald-500/20 bg-emerald-500/10 text-emerald-300 text-xs flex items-center space-x-2 transition-all duration-300">
            <CheckCircle class="w-4 h-4 shrink-0" />
            <span>{{ verificationSuccess }}</span>
          </div>
          <div v-if="verificationError" class="p-3 rounded-xl border border-red-500/20 bg-red-500/10 text-red-300 text-xs flex items-center space-x-2 transition-all duration-300">
            <XCircle class="w-4 h-4 shrink-0" />
            <span>{{ verificationError }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- 2. New/Edit Discount Setup Modal -->
    <div 
      v-if="showModal" 
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-md transition-opacity duration-300"
    >
      <div class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-white/10 bg-slate-900/90 p-6 shadow-2xl space-y-6">
        <div class="flex justify-between items-center">
          <h3 class="text-xl font-bold text-white">{{ isEditing ? 'Chegirma kampaniyasini tahrirlash' : 'Yangi chegirma yaratish' }}</h3>
          <button @click="closeModal" class="p-1 rounded-lg text-slate-400 hover:bg-white/5 hover:text-white transition duration-200">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="saveDiscount" class="space-y-4">
          <!-- Name of discount -->
          <div class="space-y-1">
            <label class="text-xs text-slate-400 uppercase tracking-wider block font-semibold">Kampaniya nomi</label>
            <input 
              v-model="form.name"
              type="text" 
              required
              placeholder="Masalan: Yangi yil chegirmasi" 
              class="w-full px-3.5 py-2 bg-white/5 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500/50 transition duration-200"
            />
          </div>

          <!-- Row: Type Select and Numeric Value -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1">
              <label class="text-xs text-slate-400 uppercase tracking-wider block font-semibold">Chegirma turi</label>
              <select 
                v-model="form.type"
                class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-indigo-500/50 transition duration-200"
              >
                <option value="percentage">Percentage (Foizli %)</option>
                <option value="fixed">Fixed (Summali UZS)</option>
              </select>
            </div>
            
            <div class="space-y-1">
              <label class="text-xs text-slate-400 uppercase tracking-wider block font-semibold">Chegirma miqdori</label>
              <input 
                v-model.number="form.value"
                type="number" 
                step="0.01"
                required
                placeholder="Masalan: 15 yoki 50000" 
                class="w-full px-3.5 py-2 bg-white/5 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500/50 transition duration-200"
              />
            </div>
          </div>

          <!-- Row: Promo Code & Min Spend -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1">
              <label class="text-xs text-slate-400 uppercase tracking-wider block font-semibold">Promo-kod (Ixtiyoriy)</label>
              <input 
                v-model="form.code"
                type="text" 
                placeholder="Masalan: YANGIYIL2026" 
                class="w-full px-3.5 py-2 bg-white/5 border border-white/10 rounded-xl text-white uppercase placeholder-slate-500 focus:outline-none focus:border-indigo-500/50 transition duration-200"
              />
              <span class="text-xxs text-slate-500">Bo'sh qoldirilsa, avtomatik chegirma bo'ladi.</span>
            </div>

            <div class="space-y-1">
              <label class="text-xs text-slate-400 uppercase tracking-wider block font-semibold">Minimal buyurtma summasi</label>
              <input 
                v-model.number="form.min_order_amount"
                type="number" 
                step="0.01"
                placeholder="0.00" 
                class="w-full px-3.5 py-2 bg-white/5 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500/50 transition duration-200"
              />
            </div>
          </div>

          <!-- Date Pickers for Start and Expiration -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1">
              <label class="text-xs text-slate-400 uppercase tracking-wider block font-semibold">Boshlanish vaqti</label>
              <input 
                v-model="form.starts_at"
                type="datetime-local" 
                class="w-full px-3.5 py-2 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-indigo-500/50 transition duration-200"
              />
            </div>

            <div class="space-y-1">
              <label class="text-xs text-slate-400 uppercase tracking-wider block font-semibold">Tugash vaqti</label>
              <input 
                v-model="form.expires_at"
                type="datetime-local" 
                class="w-full px-3.5 py-2 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-indigo-500/50 transition duration-200"
              />
            </div>
          </div>

          <!-- Status Toggle inside modal -->
          <div class="flex items-center space-x-3 pt-2">
            <input 
              v-model="form.is_active"
              id="is_active_form"
              type="checkbox" 
              class="w-4 h-4 rounded border-slate-700 bg-slate-900 text-indigo-600 focus:ring-0"
            />
            <label for="is_active_form" class="text-xs font-semibold text-slate-300">Faol kampaniya sifatida saqlash</label>
          </div>

          <div class="pt-4 border-t border-white/5 flex justify-end space-x-2">
            <button 
              type="button" 
              @click="closeModal"
              class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-300 hover:bg-white/5 transition duration-200"
            >
              Bekor qilish
            </button>
            <button 
              type="submit" 
              :disabled="discountStore.loading"
              class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm transition duration-200 flex items-center space-x-2"
            >
              <span v-if="discountStore.loading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
              <span>Saqlash</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { 
  Plus, Search, Tag, Percent, DollarSign, Calendar, Edit3, Trash2, 
  AlertCircle, CheckCircle, XCircle, X, ShoppingBag
} from 'lucide-vue-next';
import { useDiscountStore } from '@/stores/discounts';
import { useAuthStore } from '@/stores/auth';

const discountStore = useDiscountStore();
const authStore = useAuthStore();

// Filters state
const filters = ref({
  search: '',
  is_active: ''
});

// Search timeout
let searchTimeout = null;

const handleSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    loadDiscounts();
  }, 300);
};

const setFilterActive = (val) => {
  filters.value.is_active = val;
  loadDiscounts();
};

const loadDiscounts = () => {
  discountStore.fetchDiscounts(filters.value);
};

// Modal & Form State
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = ref({
  name: '',
  type: 'percentage',
  value: null,
  code: '',
  min_order_amount: 0,
  starts_at: '',
  expires_at: '',
  is_active: true
});

const openCreateModal = () => {
  isEditing.value = false;
  editingId.value = null;
  form.value = {
    name: '',
    type: 'percentage',
    value: null,
    code: '',
    min_order_amount: 0,
    starts_at: '',
    expires_at: '',
    is_active: true
  };
  showModal.value = true;
};

const openEditModal = (discount) => {
  isEditing.value = true;
  editingId.value = discount.id;
  form.value = {
    name: discount.name,
    type: discount.type,
    value: parseFloat(discount.value),
    code: discount.code || '',
    min_order_amount: parseFloat(discount.min_order_amount) || 0,
    starts_at: discount.starts_at ? formatDatetimeForInput(discount.starts_at) : '',
    expires_at: discount.expires_at ? formatDatetimeForInput(discount.expires_at) : '',
    is_active: discount.is_active
  };
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const saveDiscount = async () => {
  try {
    const payload = { ...form.value };
    if (!payload.code) {
      payload.code = null;
    } else {
      payload.code = payload.code.toUpperCase();
    }
    
    // Normalize dates
    if (!payload.starts_at) payload.starts_at = null;
    if (!payload.expires_at) payload.expires_at = null;

    if (isEditing.value) {
      await discountStore.updateDiscount(editingId.value, payload);
    } else {
      await discountStore.createDiscount(payload);
    }
    showModal.value = false;
  } catch (err) {
    // Keep modal open to display validation messages
  }
};

const handleToggleStatus = async (id) => {
  try {
    await discountStore.toggleDiscountStatus(id);
  } catch (err) {
    // Error is handled in store
  }
};

const handleDelete = async (id) => {
  if (confirm("Ushbu chegirma kampaniyasini o'chirib tashlamoqchimisiz?")) {
    try {
      await discountStore.deleteDiscount(id);
    } catch (err) {
      // Error handled in store
    }
  }
};

// --- POS Hook / Preview integration ---
const activeOrders = ref([]);
const ordersLoading = ref(false);
const selectedOrderId = ref('');
const currentOrder = ref(null);
const promocodeString = ref('');
const verificationSuccess = ref('');
const verificationError = ref('');

const loadActiveOrders = async () => {
  ordersLoading.value = true;
  try {
    // Fetch active/unpaid orders.
    // OrderController has index, let's fetch orders with new status
    const response = await fetch('/api/orders?status=new', {
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${authStore.token}`
      }
    });
    if (response.ok) {
      activeOrders.value = await response.json();
    }
  } catch (e) {
    console.error("Faol buyurtmalarni yuklashda xatolik:", e);
  } finally {
    ordersLoading.value = false;
  }
};

const handleOrderChange = () => {
  const found = activeOrders.value.find(o => o.id === selectedOrderId.value);
  currentOrder.value = found || null;
  verificationSuccess.value = '';
  verificationError.value = '';
};

const testApplyPromocode = async () => {
  if (!selectedOrderId.value || !promocodeString.value) return;
  verificationSuccess.value = '';
  verificationError.value = '';
  try {
    const res = await discountStore.validateAndApplyPromocode(
      selectedOrderId.value,
      promocodeString.value.toUpperCase()
    );
    verificationSuccess.value = res.message || 'Promo-kod muvaffaqiyatli qo\'llanildi.';
    // Update local order state
    currentOrder.value = res.order;
    // Update it in the list too
    const idx = activeOrders.value.findIndex(o => o.id === res.order.id);
    if (idx !== -1) {
      activeOrders.value[idx] = res.order;
    }
  } catch (err) {
    verificationError.value = err.message || 'Promo-kod noto\'g\'ri yoki muddati o\'tgan.';
  }
};

// Utilities
const formatCurrency = (value) => {
  return new Intl.NumberFormat('uz-UZ', { style: 'currency', currency: 'UZS', maximumFractionDigits: 0 }).format(value || 0);
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleDateString('uz-UZ', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const formatDatetimeForInput = (dateStr) => {
  const date = new Date(dateStr);
  const tzOffset = date.getTimezoneOffset() * 60000; // offset in milliseconds
  const localISOTime = (new Date(date.getTime() - tzOffset)).toISOString().slice(0, 16);
  return localISOTime;
};

onMounted(() => {
  loadDiscounts();
  loadActiveOrders();
});
</script>

<style scoped>
.text-xxs {
  font-size: 0.65rem;
}
</style>
