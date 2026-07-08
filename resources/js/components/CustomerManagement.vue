<template>
  <div class="flex-grow p-6 flex flex-col h-screen overflow-hidden">
    
    <!-- Top Header -->
    <div class="flex items-center justify-between mb-6 shrink-0">
      <div>
        <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-white via-indigo-200 to-indigo-400 tracking-wide">
          Mijozlar CRM & Sodiqlik Tizimi
        </h1>
        <p class="text-xs text-slate-400">Doimiy mijozlar bazasi, buyurtmalar statistikasi va Cashback/Bonus hisoblarini boshqarish</p>
      </div>

      <!-- Add Customer button -->
      <button 
        @click="openAddEditModal()"
        class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 font-semibold text-sm text-white shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/50 hover:scale-[1.01] transition-all flex items-center justify-center space-x-2"
      >
        <UserPlus class="w-4.5 h-4.5" />
        <span>Yangi Mijoz Qo'shish</span>
      </button>
    </div>

    <!-- CRM Insights Dashboard Header Widgets -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 shrink-0">
      <!-- Total Customers -->
      <div class="backdrop-blur-md bg-slate-900/40 border border-white/5 rounded-2xl p-4.5 flex items-center space-x-4">
        <div class="w-11 h-11 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
          <Users class="w-5.5 h-5.5" />
        </div>
        <div>
          <span class="block text-4xs uppercase tracking-wider text-slate-500 font-bold">Jami Mijozlar</span>
          <span class="text-lg font-bold text-white tracking-tight">{{ customerStore.analytics.total_customers }} ta</span>
        </div>
      </div>

      <!-- Total Outstanding Bonus Wallet -->
      <div class="backdrop-blur-md bg-slate-900/40 border border-white/5 rounded-2xl p-4.5 flex items-center space-x-4">
        <div class="w-11 h-11 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
          <Coins class="w-5.5 h-5.5" />
        </div>
        <div>
          <span class="block text-4xs uppercase tracking-wider text-slate-500 font-bold">Yig'ilgan Bonuslar</span>
          <span class="text-lg font-bold text-emerald-400 tracking-tight">{{ formatCurrency(customerStore.analytics.total_bonus_balance) }}</span>
        </div>
      </div>

      <!-- Top Spender Customer -->
      <div class="backdrop-blur-md bg-slate-900/40 border border-white/5 rounded-2xl p-4.5 flex items-center space-x-4">
        <div class="w-11 h-11 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400">
          <Crown class="w-5.5 h-5.5" />
        </div>
        <div class="overflow-hidden">
          <span class="block text-4xs uppercase tracking-wider text-slate-500 font-bold">Eng faol mijoz (Top)</span>
          <span class="text-xs font-bold text-white block truncate" v-if="customerStore.analytics.top_customer">
            {{ customerStore.analytics.top_customer.name }} ({{ formatCurrency(customerStore.analytics.top_customer.total_spent) }})
          </span>
          <span class="text-xs font-bold text-slate-500" v-else>Topilmagan</span>
        </div>
      </div>
    </div>

    <!-- Search Row -->
    <div class="backdrop-blur-md bg-slate-900/40 border border-white/5 rounded-3xl p-5 mb-6 flex flex-col sm:flex-row items-center justify-between gap-4 shrink-0">
      <div class="relative w-full sm:w-80">
        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
          <Search class="w-4 h-4" />
        </span>
        <input 
          v-model="searchQuery"
          type="text" 
          placeholder="Ism yoki telefon bo'yicha qidirish..."
          @input="triggerFetch"
          class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm placeholder-slate-500 text-white focus:outline-none transition"
        />
      </div>

      <div class="text-3xs text-slate-500 font-bold uppercase tracking-wider">
        Qidiruv natijasi: {{ customerStore.pagination.total }} ta
      </div>
    </div>

    <!-- Main CRM Table Grid -->
    <div v-if="customerStore.loading && customerStore.customers.length === 0" class="flex-grow flex flex-col items-center justify-center space-y-4">
      <Loader2 class="w-10 h-10 text-indigo-500 animate-spin" />
      <p class="text-slate-400 text-xs font-medium">Mijozlar yuklanmoqda...</p>
    </div>

    <div v-else class="flex-grow overflow-y-auto pr-1">
      <div class="backdrop-blur-md bg-slate-900/40 border border-white/5 rounded-3xl overflow-hidden shadow-2xl mb-8">
        <div class="overflow-x-auto">
          <table class="w-full border-collapse text-left">
            <thead>
              <tr class="border-b border-white/5 text-slate-400 text-3xs font-bold uppercase tracking-wider bg-slate-950/20">
                <th class="px-6 py-4">Mijoz</th>
                <th class="px-6 py-4">Telefon</th>
                <th class="px-6 py-4">Bonus Balansi</th>
                <th class="px-6 py-4">Tashriflar (Buyurtmalar)</th>
                <th class="px-6 py-4">Sarflagan Summasi</th>
                <th class="px-6 py-4 text-right">Amallar</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5 text-sm">
              <tr 
                v-for="customer in customerStore.customers" 
                :key="customer.id"
                class="hover:bg-white/2 transition duration-200"
              >
                <!-- Name with avatar placeholder -->
                <td class="px-6 py-4">
                  <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 font-bold text-xs uppercase flex items-center justify-center">
                      {{ customer.name.substring(0,2) }}
                    </div>
                    <span class="font-bold text-white tracking-wide">{{ customer.name }}</span>
                  </div>
                </td>

                <!-- Phone -->
                <td class="px-6 py-4 text-slate-300 font-mono text-xs">{{ customer.phone }}</td>

                <!-- Bonus Balance -->
                <td class="px-6 py-4">
                  <span class="font-bold text-emerald-400 bg-emerald-500/5 px-2.5 py-1 rounded-lg border border-emerald-500/10 text-xs">
                    {{ formatCurrency(customer.bonus_balance) }}
                  </span>
                </td>

                <!-- Visits count -->
                <td class="px-6 py-4 text-slate-400 font-semibold">{{ customer.total_orders_count }} marta</td>

                <!-- Total Spent amount -->
                <td class="px-6 py-4 font-bold text-slate-300">{{ formatCurrency(customer.total_spent_amount) }}</td>

                <!-- Actions -->
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end space-x-2">
                    <!-- Adjust balance -->
                    <button 
                      @click="openAdjustModal(customer)"
                      class="px-2.5 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 hover:bg-emerald-600 hover:text-white text-3xs font-bold transition duration-200"
                      title="Bonus tuzatish"
                    >
                      Bonus +/-
                    </button>
                    <!-- Edit -->
                    <button 
                      @click="openAddEditModal(customer)"
                      class="p-1.5 rounded-lg bg-white/5 border border-white/10 text-slate-400 hover:text-white transition"
                      title="Tahrirlash"
                    >
                      <Edit3 class="w-3.5 h-3.5" />
                    </button>
                    <!-- Delete -->
                    <button 
                      @click="handleDelete(customer)"
                      class="p-1.5 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500 hover:text-white transition"
                      title="O'chirish"
                    >
                      <Trash2 class="w-3.5 h-3.5" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- MODAL: Add / Edit Customer -->
    <div 
      v-if="showModal" 
      class="fixed inset-0 z-50 backdrop-blur-md bg-black/60 flex items-center justify-center p-6"
      @click.self="showModal = false"
    >
      <div class="w-full max-w-sm backdrop-blur-xl bg-slate-900/80 border border-white/10 rounded-3xl p-6 shadow-2xl space-y-5 animate-scaleIn">
        <div class="flex justify-between items-center border-b border-white/5 pb-3">
          <h3 class="text-base font-bold text-white">
            {{ editingCustomer ? 'Mijoz Ma\'lumotlarini Tahrirlash' : 'Yangi Mijoz Ro\'yxatdan O\'tkazish' }}
          </h3>
          <button @click="showModal = false" class="p-1 rounded-lg bg-white/5 text-slate-400 hover:text-white transition">
            <X class="w-4 h-4" />
          </button>
        </div>

        <div class="space-y-4">
          <!-- Customer Name -->
          <div class="space-y-1.5">
            <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Mijoz Ismi-Familiyasi *</label>
            <input 
              v-model="customerForm.name"
              type="text" 
              placeholder="Masalan, Jasur Aliyev..."
              class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
            />
          </div>

          <!-- Customer Phone -->
          <div class="space-y-1.5">
            <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Telefon raqami *</label>
            <input 
              v-model="customerForm.phone"
              type="text" 
              placeholder="Masalan, +998901234567..."
              class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
            />
          </div>
        </div>

        <div class="flex justify-end space-x-2 pt-2">
          <button @click="showModal = false" class="px-4 py-2 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl text-xs font-semibold text-slate-300 transition">
            Bekor qilish
          </button>
          <button 
            @click="submitForm"
            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold transition"
          >
            Saqlash
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL: Adjust Loyalty Balance -->
    <div 
      v-if="showAdjustModal" 
      class="fixed inset-0 z-50 backdrop-blur-md bg-black/60 flex items-center justify-center p-6"
      @click.self="showAdjustModal = false"
    >
      <div class="w-full max-w-sm backdrop-blur-xl bg-slate-900/80 border border-white/10 rounded-3xl p-6 shadow-2xl space-y-5 animate-scaleIn">
        <div class="flex justify-between items-center border-b border-white/5 pb-3">
          <div>
            <h3 class="text-base font-bold text-white">Bonus Balansini Tuzatish</h3>
            <p class="text-xxs text-slate-400 mt-0.5">{{ selectedCustomer?.name }}</p>
          </div>
          <button @click="showAdjustModal = false" class="p-1 rounded-lg bg-white/5 text-slate-400 hover:text-white transition">
            <X class="w-4 h-4" />
          </button>
        </div>

        <div class="space-y-4">
          <!-- Current Balance Info -->
          <div class="bg-white/2 rounded-2xl p-4 border border-white/5 flex justify-between items-center text-xs">
            <span class="text-slate-400">Joriy hisob:</span>
            <span class="font-bold text-emerald-400">{{ formatCurrency(selectedCustomer?.bonus_balance) }}</span>
          </div>

          <!-- Adjust balance input -->
          <div class="space-y-1.5">
            <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Yangi bonus miqdori (UZS) *</label>
            <input 
              v-model.number="adjustForm.bonus_balance"
              type="number" 
              placeholder="Masalan, 50000..."
              class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
            />
          </div>
        </div>

        <div class="flex justify-end space-x-2 pt-2">
          <button @click="showAdjustModal = false" class="px-4 py-2 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl text-xs font-semibold text-slate-300 transition">
            Bekor qilish
          </button>
          <button 
            @click="submitAdjustment"
            class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold transition"
          >
            Hisobni Yangilash
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { 
  UserPlus, Search, Edit3, Trash2, X, Loader2, Users, Coins, Crown
} from 'lucide-vue-next';
import { useCustomerStore } from '@/stores/customers';

const customerStore = useCustomerStore();

// Search and filters
const searchQuery = ref('');

// Modals states
const showModal = ref(false);
const showAdjustModal = ref(false);
const editingCustomer = ref(null);
const selectedCustomer = ref(null);

const customerForm = ref({
  name: '',
  phone: ''
});

const adjustForm = ref({
  bonus_balance: 0
});

// Lifecycle
onMounted(async () => {
  await triggerFetch();
  await customerStore.fetchAnalytics();
});

const triggerFetch = async () => {
  await customerStore.fetchCustomers({
    search: searchQuery.value,
    page: 1
  });
};

// Form modal actions
const openAddEditModal = (customer = null) => {
  editingCustomer.value = customer;
  customerForm.value = customer ? {
    name: customer.name,
    phone: customer.phone
  } : {
    name: '',
    phone: ''
  };
  showModal.value = true;
};

const submitForm = async () => {
  if (!customerForm.value.name.trim() || !customerForm.value.phone.trim()) {
    alert('Barcha majburiy maydonlarni to\'g\'ri to\'ldiring.');
    return;
  }

  try {
    if (editingCustomer.value) {
      await customerStore.updateCustomer(editingCustomer.value.id, customerForm.value);
    } else {
      await customerStore.createCustomer(customerForm.value);
    }
    showModal.value = false;
  } catch (err) {
    alert(err.message);
  }
};

// Adjust modal actions
const openAdjustModal = (customer) => {
  selectedCustomer.value = customer;
  adjustForm.value.bonus_balance = parseFloat(customer.bonus_balance);
  showAdjustModal.value = true;
};

const submitAdjustment = async () => {
  if (isNaN(adjustForm.value.bonus_balance) || adjustForm.value.bonus_balance < 0) {
    alert('Bonus balansi noldan kam bo\'la olmaydi.');
    return;
  }

  try {
    await customerStore.adjustCustomerBalance(selectedCustomer.value.id, adjustForm.value.bonus_balance);
    showAdjustModal.value = false;
  } catch (err) {
    alert(err.message);
  }
};

const handleDelete = async (customer) => {
  if (!confirm(`"${customer.name}" mijozini tizimdan butunlay o'chirmoqchimisiz?`)) return;
  try {
    await customerStore.deleteCustomer(customer.id);
  } catch (err) {
    alert(err.message);
  }
};

// Formatting helpers
const formatCurrency = (val) => {
  return new Intl.NumberFormat('uz-UZ').format(val) + ' UZS';
};
</script>

<style scoped>
.text-3xs {
  font-size: 0.6rem;
}
.text-4xs {
  font-size: 0.55rem;
}
.text-2xs {
  font-size: 0.65rem;
}
.animate-scaleIn {
  animation: scaleIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes scaleIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}
</style>
