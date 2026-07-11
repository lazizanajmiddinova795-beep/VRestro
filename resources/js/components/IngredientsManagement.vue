<template>
  <div class="flex-grow p-6 flex flex-col h-screen overflow-hidden">
    
    <!-- Top Dashboard Navigation / Header -->
    <div class="flex items-center justify-between mb-6 shrink-0">
      <div>
        <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-white via-indigo-200 to-indigo-400 tracking-wide">
          Ta'minot Zaxirasi
        </h1>
        <p class="text-xs text-slate-400">Xom-ashyolar, ularning miqdori va sotib olish qiymatlarini boshqarish oynasi</p>
      </div>

      <!-- Add Ingredient button -->
      <button 
        @click="openAddEditModal()"
        class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 font-semibold text-sm text-white shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/50 hover:scale-[1.01] transition-all flex items-center justify-center space-x-2"
      >
        <Plus class="w-4.5 h-4.5" />
        <span>Yangi Masalliq</span>
      </button>
    </div>

    <!-- Analytics Mini-widgets -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6 shrink-0">
      <!-- Total Items Widget -->
      <div class="backdrop-blur-md bg-slate-900/40 border border-white/5 rounded-3xl p-5 shadow-2xl flex items-center space-x-4">
        <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
          <Database class="w-6 h-6" />
        </div>
        <div>
          <span class="block text-3xs font-bold uppercase tracking-wider text-slate-500">Umumiy Turlar</span>
          <span class="text-xl font-bold text-white tracking-tight">{{ totalItems }} xil</span>
        </div>
      </div>

      <!-- Low Stock Alerts Widget -->
      <div class="backdrop-blur-md bg-slate-900/40 border border-white/5 rounded-3xl p-5 shadow-2xl flex items-center space-x-4">
        <div 
          class="w-12 h-12 rounded-2xl flex items-center justify-center border transition"
          :class="lowStockAlerts > 0 ? 'bg-red-500/10 border-red-500/20 text-red-400 animate-pulse' : 'bg-slate-800 border-slate-700 text-slate-400'"
        >
          <AlertTriangle class="w-6 h-6" />
        </div>
        <div>
          <span class="block text-3xs font-bold uppercase tracking-wider text-slate-500">Kam Qolgan Masalliqlar</span>
          <span class="text-xl font-bold text-white tracking-tight" :class="lowStockAlerts > 0 ? 'text-red-400' : ''">
            {{ lowStockAlerts }} ta
          </span>
        </div>
      </div>

      <!-- Total Inventory Value Widget -->
      <div class="backdrop-blur-md bg-slate-900/40 border border-white/5 rounded-3xl p-5 shadow-2xl flex items-center space-x-4">
        <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
          <Coins class="w-6 h-6" />
        </div>
        <div>
          <span class="block text-3xs font-bold uppercase tracking-wider text-slate-500">Zaxiradagi Jami Qiymat</span>
          <span class="text-xl font-bold text-white tracking-tight">{{ formatCurrency(totalInventoryValue) }}</span>
        </div>
      </div>
    </div>

    <!-- Filters Row -->
    <div class="backdrop-blur-md bg-slate-900/40 border border-white/5 rounded-3xl p-5 mb-6 flex flex-col sm:flex-row items-center justify-between gap-4 shrink-0">
      <!-- Search input -->
      <div class="relative w-full sm:w-80">
        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
          <Search class="w-4 h-4" />
        </span>
        <input 
          v-model="searchQuery"
          @input="triggerSearch"
          type="text" 
          placeholder="Masalliq nomi yoki SKU..."
          class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm placeholder-slate-500 text-white focus:outline-none transition"
        />
      </div>

      <!-- Filter options -->
      <div class="flex items-center space-x-4 select-none">
        <label class="flex items-center space-x-2.5 cursor-pointer">
          <input 
            type="checkbox" 
            v-model="onlyLowStock" 
            @change="triggerSearch"
            class="rounded bg-slate-950 border-white/10 text-indigo-600 focus:ring-0 focus:ring-offset-0"
          />
          <span class="text-xs text-slate-300 font-semibold">Faqat kam qolganlarni ko'rsatish</span>
        </label>
      </div>
    </div>

    <!-- Loading / Error States -->
    <div v-if="ingredientsStore.loading && ingredientsStore.ingredients.length === 0" class="flex-grow flex flex-col items-center justify-center space-y-4">
      <Loader2 class="w-10 h-10 text-indigo-500 animate-spin" />
      <p class="text-slate-400 text-xs font-medium animate-pulse">Masalliqlar yuklanmoqda...</p>
    </div>

    <div v-else-if="ingredientsStore.error" class="flex-grow flex flex-col items-center justify-center p-6 text-center space-y-4">
      <AlertTriangle class="w-12 h-12 text-red-400" />
      <h3 class="text-base font-bold text-white">Yuklashda xatolik</h3>
      <p class="text-xs text-red-300/80">{{ ingredientsStore.error }}</p>
      <button @click="ingredientsStore.fetchIngredients()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold transition">
        Qayta yuklash
      </button>
    </div>

    <!-- Data Table -->
    <div v-else class="flex-grow overflow-y-auto pr-1">
      <div class="backdrop-blur-md bg-slate-900/40 border border-white/5 rounded-3xl overflow-hidden shadow-2xl mb-8">
        <div class="overflow-x-auto">
          <table class="w-full border-collapse text-left">
            <thead>
              <tr class="border-b border-white/5 text-slate-400 text-3xs font-bold uppercase tracking-wider bg-slate-950/20">
                <th class="px-6 py-4">Nomi</th>
                <th class="px-6 py-4">SKU</th>
                <th class="px-6 py-4">Miqdori / O'lchov</th>
                <th class="px-6 py-4">Minimal Chegara</th>
                <th class="px-6 py-4">Birlik narxi</th>
                <th class="px-6 py-4">Umumiy Qiymati</th>
                <th class="px-6 py-4">Holat</th>
                <th class="px-6 py-4 text-right">Amallar</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5 text-sm">
              <tr 
                v-for="ing in ingredientsStore.ingredients" 
                :key="ing.id"
                class="hover:bg-white/2 transition group"
                :class="ing.is_low_stock ? 'bg-red-500/5 hover:bg-red-500/10' : ''"
              >
                <!-- Name -->
                <td class="px-6 py-4.5 font-bold text-white tracking-wide group-hover:text-indigo-400 transition-colors">
                  {{ ing.name }}
                </td>

                <!-- SKU -->
                <td class="px-6 py-4.5 font-mono text-xs text-slate-400">
                  {{ ing.sku }}
                </td>

                <!-- Quantity -->
                <td class="px-6 py-4.5 font-semibold text-white">
                  {{ formatDecimal(ing.quantity) }} <span class="text-xs text-slate-400 font-normal">{{ ing.unit }}</span>
                </td>

                <!-- Low threshold -->
                <td class="px-6 py-4.5 font-semibold text-slate-400">
                  {{ formatDecimal(ing.low_stock_threshold) }} <span class="text-xs text-slate-500 font-normal">{{ ing.unit }}</span>
                </td>

                <!-- Cost Price -->
                <td class="px-6 py-4.5 text-slate-300">
                  {{ formatCurrency(ing.cost_price) }}
                </td>

                <!-- Total Value -->
                <td class="px-6 py-4.5 font-bold text-slate-300">
                  {{ formatCurrency(ing.total_value) }}
                </td>

                <!-- Alert Badge -->
                <td class="px-6 py-4.5">
                  <span 
                    class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-3xs font-bold border"
                    :class="ing.is_low_stock ? 'bg-red-500/10 border-red-500/20 text-red-400 animate-pulse' : 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400'"
                  >
                    <span class="w-1 h-1 rounded-full" :class="ing.is_low_stock ? 'bg-red-400' : 'bg-emerald-400'"></span>
                    <span>{{ ing.is_low_stock ? 'Kam qolgan' : 'Yetarli' }}</span>
                  </span>
                </td>

                <!-- Actions -->
                <td class="px-6 py-4.5 text-right space-x-1.5 whitespace-nowrap">
                  <!-- Stock Adjustment trigger -->
                  <button 
                    @click="openAdjustModal(ing)"
                    class="p-2 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 hover:bg-indigo-600 hover:text-white transition duration-200"
                    title="Qoldiqni tahrirlash (Adjust)"
                  >
                    <SlidersHorizontal class="w-4 h-4" />
                  </button>

                  <button 
                    @click="openAddEditModal(ing)"
                    class="p-2 rounded-xl bg-white/5 border border-white/10 text-slate-300 hover:bg-white/10 hover:text-white transition duration-200"
                    title="Tahrirlash"
                  >
                    <Edit3 class="w-4 h-4" />
                  </button>

                  <button 
                    @click="handleDelete(ing)"
                    class="p-2 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500 hover:text-white transition duration-200"
                    title="O'chirish"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      
      <!-- Empty state -->
      <div v-if="ingredientsStore.ingredients.length === 0" class="flex flex-col items-center justify-center py-24 space-y-3">
        <Database class="w-12 h-12 text-slate-600" />
        <p class="text-slate-400 text-xs font-medium">Masalliqlar topilmadi</p>
      </div>
    </div>

    <!-- Modal 1: Add / Edit Ingredient -->
    <div 
      v-if="showAddEditModal" 
      class="fixed inset-0 z-50 backdrop-blur-md bg-black/60 flex items-center justify-center p-6"
      @click.self="showAddEditModal = false"
    >
      <div class="w-full max-w-md backdrop-blur-xl bg-slate-900/80 border border-white/10 rounded-3xl p-6 shadow-2xl space-y-5 animate-scaleIn">
        <div class="flex justify-between items-center border-b border-white/5 pb-3">
          <h3 class="text-base font-bold text-white">
            {{ editingIngredient ? 'Masalliqni Tahrirlash' : 'Yangi Masalliq' }}
          </h3>
          <button @click="showAddEditModal = false" class="p-1 rounded-lg bg-white/5 text-slate-400 hover:text-white transition">
            <X class="w-4 h-4" />
          </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="space-y-1.5 sm:col-span-2">
            <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Masalliq nomi *</label>
            <input 
              v-model="addEditForm.name"
              type="text" 
              placeholder="Masalan, Go'sht, Guruch..."
              class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
            />
          </div>

          <div class="space-y-1.5">
            <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">SKU (Kod) *</label>
            <input 
              v-model="addEditForm.sku"
              type="text" 
              placeholder="Avtomatik (ING-XXXXX)"
              :disabled="!!editingIngredient"
              class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none disabled:opacity-50 transition"
            />
          </div>

          <div class="space-y-1.5">
            <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">O'lchov birligi *</label>
            <select 
              v-model="addEditForm.unit"
              class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
            >
              <option value="kg">kilogram (kg)</option>
              <option value="g">gram (g)</option>
              <option value="l">litr (l)</option>
              <option value="ml">millilitr (ml)</option>
              <option value="dona">dona</option>
              <option value="pachka">pachka</option>
            </select>
          </div>

          <div v-if="!editingIngredient" class="space-y-1.5">
            <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Dastlabki miqdori *</label>
            <input 
              v-model.number="addEditForm.quantity"
              type="number" 
              step="0.001"
              placeholder="Qoldiq miqdori..."
              class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
            />
          </div>

          <div class="space-y-1.5">
            <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Birlik narxi (UZS) *</label>
            <input 
              v-model.number="addEditForm.cost_price"
              type="number" 
              placeholder="Birlik narxi..."
              class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
            />
          </div>

          <div class="space-y-1.5" :class="editingIngredient ? 'sm:col-span-2' : ''">
            <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Minimal qoldiq chegarasi *</label>
            <input 
              v-model.number="addEditForm.low_stock_threshold"
              type="number" 
              step="0.001"
              placeholder="Minimal chegara..."
              class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
            />
          </div>
        </div>

        <div class="flex justify-end space-x-2 pt-2">
          <button @click="showAddEditModal = false" class="px-4 py-2 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl text-xs font-semibold text-slate-300 transition">
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

    <!-- Modal 2: Stock Adjustment Modal -->
    <div 
      v-if="showAdjustModal" 
      class="fixed inset-0 z-50 backdrop-blur-md bg-black/60 flex items-center justify-center p-6"
      @click.self="showAdjustModal = false"
    >
      <div class="w-full max-w-sm backdrop-blur-xl bg-slate-900/80 border border-white/10 rounded-3xl p-6 shadow-2xl space-y-5 animate-scaleIn">
        <div class="flex justify-between items-center border-b border-white/5 pb-3">
          <div>
            <h3 class="text-base font-bold text-white">Qoldiqni O'zgartirish</h3>
            <p class="text-xxs text-slate-400 mt-0.5">{{ selectedIngredient?.name }} (Joriy qoldiq: {{ selectedIngredient?.quantity }} {{ selectedIngredient?.unit }})</p>
          </div>
          <button @click="showAdjustModal = false" class="p-1 rounded-lg bg-white/5 text-slate-400 hover:text-white transition">
            <X class="w-4 h-4" />
          </button>
        </div>

        <div class="space-y-4">
          <!-- Adjust type (Add / Subtract) -->
          <div class="grid grid-cols-2 gap-2 bg-slate-950/60 p-1 rounded-xl border border-white/5">
            <button 
              @click="adjustForm.type = 'add'"
              class="py-2 rounded-lg text-xs font-bold transition duration-200 flex items-center justify-center space-x-1"
              :class="adjustForm.type === 'add' ? 'bg-emerald-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-200'"
            >
              <Plus class="w-3.5 h-3.5" />
              <span>Qo'shish</span>
            </button>
            <button 
              @click="adjustForm.type = 'subtract'"
              class="py-2 rounded-lg text-xs font-bold transition duration-200 flex items-center justify-center space-x-1"
              :class="adjustForm.type === 'subtract' ? 'bg-red-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-200'"
            >
              <Minus class="w-3.5 h-3.5" />
              <span>Kamashtirish</span>
            </button>
          </div>

          <!-- Adjust Amount -->
          <div class="space-y-1.5">
            <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">O'zgarish Miqdori *</label>
            <div class="relative">
              <input 
                v-model.number="adjustForm.amount"
                type="number" 
                step="0.001"
                placeholder="Miqdorni kiriting..."
                class="w-full pl-4 pr-12 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
              />
              <span class="absolute right-4 inset-y-0 flex items-center text-xs font-bold text-slate-500 uppercase">
                {{ selectedIngredient?.unit }}
              </span>
            </div>
          </div>
        </div>

        <div class="flex justify-end space-x-2 pt-2">
          <button @click="showAdjustModal = false" class="px-4 py-2 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl text-xs font-semibold text-slate-300 transition">
            Bekor qilish
          </button>
          <button 
            @click="submitAdjustForm"
            class="px-5 py-2.5 text-white rounded-xl text-xs font-semibold transition"
            :class="adjustForm.type === 'add' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-red-600 hover:bg-red-700'"
          >
            Tasdiqlash
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { 
  Plus, Edit3, Trash2, Search, X, Loader2, AlertTriangle, Database, Coins, SlidersHorizontal, Minus
} from 'lucide-vue-next';
import { useIngredientsStore } from '@/stores/ingredients';

const ingredientsStore = useIngredientsStore();

// States
const searchQuery = ref('');
const onlyLowStock = ref(false);
let searchTimeout = null;

const showAddEditModal = ref(false);
const editingIngredient = ref(null);
const addEditForm = ref({
  name: '',
  sku: '',
  quantity: 0,
  unit: 'kg',
  cost_price: 0,
  low_stock_threshold: 1
});

const showAdjustModal = ref(false);
const selectedIngredient = ref(null);
const adjustForm = ref({
  amount: '',
  type: 'add'
});

// Lifecycle
onMounted(async () => {
  await ingredientsStore.fetchIngredients();
});

// Computed Stats
const totalItems = computed(() => {
  return ingredientsStore.ingredients.length;
});

const lowStockAlerts = computed(() => {
  return ingredientsStore.ingredients.filter(i => i.is_low_stock).length;
});

const totalInventoryValue = computed(() => {
  return ingredientsStore.ingredients.reduce((acc, i) => acc + (parseFloat(i.quantity) * parseFloat(i.cost_price)), 0);
});

// Filters trigger
const triggerSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(async () => {
    await ingredientsStore.fetchIngredients({
      search: searchQuery.value,
      low_stock: onlyLowStock.value ? 'true' : ''
    });
  }, 350);
};

// CRUD handlers
const openAddEditModal = (ing = null) => {
  editingIngredient.value = ing;
  addEditForm.value = ing ? {
    name: ing.name,
    sku: ing.sku,
    quantity: parseFloat(ing.quantity),
    unit: ing.unit,
    cost_price: parseFloat(ing.cost_price),
    low_stock_threshold: parseFloat(ing.low_stock_threshold)
  } : {
    name: '',
    sku: '',
    quantity: '',
    unit: 'kg',
    cost_price: '',
    low_stock_threshold: 5
  };
  showAddEditModal.value = true;
};

const submitForm = async () => {
  if (!addEditForm.value.name.trim() || !addEditForm.value.unit || addEditForm.value.cost_price === '' || addEditForm.value.low_stock_threshold === '') {
    alert('Majburiy maydonlarni to\'ldiring.');
    return;
  }

  try {
    if (editingIngredient.value) {
      await ingredientsStore.updateIngredient(editingIngredient.value.id, addEditForm.value);
    } else {
      await ingredientsStore.createIngredient(addEditForm.value);
    }
    showAddEditModal.value = false;
  } catch (err) {
    alert(err.message);
  }
};

const handleDelete = async (ing) => {
  if (!confirm(`"${ing.name}" masallig'ini zaxiradan o'chirmoqchimisiz?`)) return;
  try {
    await ingredientsStore.deleteIngredient(ing.id);
  } catch (err) {
    alert(err.message);
  }
};

// Adjust stock handlers
const openAdjustModal = (ing) => {
  selectedIngredient.value = ing;
  adjustForm.value.amount = '';
  adjustForm.value.type = 'add';
  showAdjustModal.value = true;
};

const submitAdjustForm = async () => {
  const amt = parseFloat(adjustForm.value.amount);
  if (isNaN(amt) || amt <= 0) {
    alert('Musbat o\'zgarish miqdorini kiriting.');
    return;
  }

  try {
    await ingredientsStore.adjustStock(
      selectedIngredient.value.id,
      amt,
      adjustForm.value.type
    );
    showAdjustModal.value = false;
  } catch (err) {
    alert(err.message);
  }
};

// Formatting helpers
const formatCurrency = (val) => {
  return new Intl.NumberFormat('uz-UZ').format(val) + ' UZS';
};

const formatDecimal = (val) => {
  return parseFloat(val).toFixed(3);
};
</script>

<style scoped>
.text-3xs {
  font-size: 0.6rem;
}
.animate-scaleIn {
  animation: scaleIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes scaleIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}
</style>
