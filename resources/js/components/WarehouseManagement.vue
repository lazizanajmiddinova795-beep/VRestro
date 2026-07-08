<template>
  <div class="flex-grow p-6 flex flex-col h-screen overflow-hidden">
    
    <!-- Top Header -->
    <div class="flex items-center justify-between mb-6 shrink-0">
      <div>
        <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-white via-indigo-200 to-indigo-400 tracking-wide">
          Ombor Boshqaruvi
        </h1>
        <p class="text-xs text-slate-400">Kirim, chiqim va inventarizatsiya hujjatlarini rasmiylashtirish hamda tarixiy o'zgarishlar oqimi</p>
      </div>

      <!-- Navigation tabs -->
      <div class="flex space-x-1.5 bg-slate-950/60 p-1 rounded-2xl border border-white/5 backdrop-blur-md">
        <button 
          v-for="t in ['summary', 'history', 'builder']" 
          :key="t"
          @click="activeTab = t"
          class="px-4 py-2 rounded-xl text-xs font-bold transition duration-200 capitalize"
          :class="activeTab === t ? 'bg-indigo-600 text-white shadow-lg' : 'text-slate-400 hover:text-slate-200'"
        >
          {{ tabLabel(t) }}
        </button>
      </div>
    </div>

    <!-- TAB 1: Ombor Qoldig'i (Stock Summary) -->
    <div v-if="activeTab === 'summary'" class="flex-grow flex flex-col overflow-hidden">
      <!-- Search row -->
      <div class="backdrop-blur-md bg-slate-900/40 border border-white/5 rounded-3xl p-5 mb-6 flex flex-col sm:flex-row items-center justify-between gap-4 shrink-0">
        <div class="relative w-full sm:w-80">
          <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
            <Search class="w-4 h-4" />
          </span>
          <input 
            v-model="summarySearch"
            type="text" 
            placeholder="Masalliq nomi yoki SKU..."
            class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm placeholder-slate-500 text-white focus:outline-none transition"
          />
        </div>
        <div class="flex items-center space-x-2.5 bg-red-500/5 border border-red-500/10 rounded-2xl px-4 py-2.5" v-if="lowStockCount > 0">
          <AlertTriangle class="w-4.5 h-4.5 text-red-400 animate-pulse" />
          <span class="text-xs font-bold text-red-400">{{ lowStockCount }} ta masalliq minimal chegaradan kam!</span>
        </div>
      </div>

      <!-- Inventory summary table -->
      <div class="flex-grow overflow-y-auto pr-1">
        <div class="backdrop-blur-md bg-slate-900/40 border border-white/5 rounded-3xl overflow-hidden shadow-2xl mb-8">
          <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
              <thead>
                <tr class="border-b border-white/5 text-slate-400 text-3xs font-bold uppercase tracking-wider bg-slate-950/20">
                  <th class="px-6 py-4">Masalliq</th>
                  <th class="px-6 py-4">SKU</th>
                  <th class="px-6 py-4">Miqdori</th>
                  <th class="px-6 py-4">Birlik narxi (Avg)</th>
                  <th class="px-6 py-4">Umumiy Qiymati</th>
                  <th class="px-6 py-4">Holat</th>
                  <th class="px-6 py-4 text-right">Harakat</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-white/5 text-sm">
                <tr 
                  v-for="ing in filteredIngredients" 
                  :key="ing.id"
                  class="hover:bg-white/2 transition duration-200"
                  :class="ing.is_low_stock ? 'bg-red-500/5' : ''"
                >
                  <td class="px-6 py-4 font-bold text-white tracking-wide">{{ ing.name }}</td>
                  <td class="px-6 py-4 font-mono text-xs text-slate-400">{{ ing.sku }}</td>
                  <td class="px-6 py-4 font-semibold text-white">
                    {{ formatDecimal(ing.quantity) }} <span class="text-xs text-slate-500">{{ ing.unit }}</span>
                  </td>
                  <td class="px-6 py-4 text-slate-300">{{ formatCurrency(ing.cost_price) }}</td>
                  <td class="px-6 py-4 font-bold text-slate-300">{{ formatCurrency(ing.total_value) }}</td>
                  <td class="px-6 py-4">
                    <span 
                      class="px-2.5 py-0.5 rounded-full text-3xs font-bold border"
                      :class="ing.is_low_stock ? 'bg-red-500/10 border-red-500/20 text-red-400' : 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400'"
                    >
                      {{ ing.is_low_stock ? 'Kam qolgan' : 'Yetarli' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <button 
                      @click="openTimelineModal(ing)"
                      class="px-3 py-1.5 rounded-lg bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 hover:bg-indigo-600 hover:text-white text-3xs font-bold transition duration-200"
                    >
                      Tarix
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 2: Kirim/Chiqim Tarixi (Movement Log) -->
    <div v-else-if="activeTab === 'history'" class="flex-grow flex flex-col overflow-hidden">
      <!-- Search parameters -->
      <div class="backdrop-blur-md bg-slate-900/40 border border-white/5 rounded-3xl p-5 mb-6 grid grid-cols-1 sm:grid-cols-3 gap-4 shrink-0">
        <div>
          <label class="text-3xs text-slate-500 font-bold uppercase tracking-wider mb-1.5 block">Hujjat turi</label>
          <select 
            v-model="historyType" 
            @change="triggerHistoryFetch"
            class="w-full px-3.5 py-2 rounded-xl bg-slate-950/40 border border-white/10 text-xs text-white focus:outline-none transition"
          >
            <option value="">Barchasi</option>
            <option value="kirim">Kirim (Stock In)</option>
            <option value="chiqim">Chiqim (Stock Out)</option>
            <option value="inventarizatsiya">Inventarizatsiya</option>
          </select>
        </div>
        <div>
          <label class="text-3xs text-slate-500 font-bold uppercase tracking-wider mb-1.5 block">Boshlanish sanasi</label>
          <input 
            v-model="startDate" 
            type="date" 
            @change="triggerHistoryFetch"
            class="w-full px-3.5 py-2 rounded-xl bg-slate-950/40 border border-white/10 text-xs text-white focus:outline-none transition"
          />
        </div>
        <div>
          <label class="text-3xs text-slate-500 font-bold uppercase tracking-wider mb-1.5 block">Tugash sanasi</label>
          <input 
            v-model="endDate" 
            type="date" 
            @change="triggerHistoryFetch"
            class="w-full px-3.5 py-2 rounded-xl bg-slate-950/40 border border-white/10 text-xs text-white focus:outline-none transition"
          />
        </div>
      </div>

      <!-- Log loading state -->
      <div v-if="warehouseStore.loading" class="flex-grow flex flex-col items-center justify-center space-y-4">
        <Loader2 class="w-10 h-10 text-indigo-500 animate-spin" />
        <p class="text-slate-400 text-xs font-medium">Hujjatlar yuklanmoqda...</p>
      </div>

      <!-- Logs Grid -->
      <div v-else class="flex-grow overflow-y-auto pr-1">
        <div class="space-y-4 pb-12">
          <div 
            v-for="tx in warehouseStore.transactions" 
            :key="tx.id"
            class="backdrop-blur-md bg-slate-900/40 border border-white/5 rounded-3xl p-5 shadow-2xl space-y-4"
          >
            <!-- Header section -->
            <div class="flex flex-col sm:flex-row justify-between sm:items-center border-b border-white/5 pb-3 gap-3">
              <div class="flex items-center space-x-3.5">
                <span 
                  class="px-3 py-1 rounded-xl text-3xs font-bold uppercase tracking-wider border"
                  :class="transactionTypeClass(tx.type)"
                >
                  {{ tx.type }}
                </span>
                <div>
                  <span class="text-xs text-slate-500 font-medium">Hujjat #{{ tx.id }}</span>
                  <span class="mx-2 text-slate-700">|</span>
                  <span class="text-xs text-slate-400">{{ formatDateTime(tx.created_at) }}</span>
                </div>
              </div>

              <div class="text-xs text-slate-400 flex items-center space-x-2">
                <User class="w-3.5 h-3.5 text-indigo-400" />
                <span class="font-semibold">{{ tx.user?.name }}</span>
                <span class="px-2 py-0.5 rounded bg-white/5 border border-white/10 text-4xs uppercase tracking-wider text-slate-500">
                  {{ tx.user?.roles?.[0] || 'Staff' }}
                </span>
              </div>
            </div>

            <!-- Notes -->
            <p v-if="tx.notes" class="text-xs text-slate-400 leading-relaxed italic bg-white/2 p-3 rounded-xl border border-white/5">
              "{{ tx.notes }}"
            </p>

            <!-- Line Items table -->
            <div class="overflow-x-auto">
              <table class="w-full border-collapse text-left text-xs">
                <thead>
                  <tr class="border-b border-white/5 text-slate-500 text-3xs font-bold uppercase tracking-wider">
                    <th class="pb-2">Masalliq</th>
                    <th class="pb-2">Oldingi qoldiq</th>
                    <th class="pb-2">O'zgarish</th>
                    <th class="pb-2">Yangi qoldiq</th>
                    <th class="pb-2 text-right" v-if="tx.type === 'kirim'">Birlik Narxi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-white/2 text-slate-300">
                  <tr v-for="item in tx.items" :key="item.id" class="hover:bg-white/1">
                    <td class="py-2.5 font-semibold text-white">{{ item.ingredient?.name }}</td>
                    <td class="py-2.5 text-slate-400">{{ formatDecimal(item.old_quantity) }} {{ item.ingredient?.unit }}</td>
                    <td class="py-2.5 font-bold" :class="deltaClass(item.quantity, tx.type)">
                      {{ deltaSign(item.quantity, tx.type) }} {{ formatDecimal(Math.abs(item.quantity)) }} {{ item.ingredient?.unit }}
                    </td>
                    <td class="py-2.5 text-white font-medium">{{ formatDecimal(item.new_quantity) }} {{ item.ingredient?.unit }}</td>
                    <td class="py-2.5 text-right text-emerald-400 font-semibold" v-if="tx.type === 'kirim'">
                      {{ formatCurrency(item.unit_price) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Empty history state -->
        <div v-if="warehouseStore.transactions.length === 0" class="flex flex-col items-center justify-center py-24 space-y-3">
          <Database class="w-12 h-12 text-slate-600" />
          <p class="text-slate-400 text-xs font-medium">Hujjatlar topilmadi</p>
        </div>
      </div>
    </div>

    <!-- TAB 3: Yangi Hujjat Yaratish (Document Builder) -->
    <div v-else-if="activeTab === 'builder'" class="flex-grow flex flex-col overflow-hidden">
      <div class="flex-grow backdrop-blur-md bg-slate-900/40 border border-white/5 rounded-3xl p-6 flex flex-col h-full overflow-hidden justify-between">
        
        <div class="space-y-5 flex flex-col h-full overflow-hidden">
          <!-- Document Controls -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 shrink-0 border-b border-white/5 pb-5">
            <!-- Action type -->
            <div class="space-y-1.5">
              <label class="text-3xs text-slate-500 font-bold uppercase tracking-wider">Hujjat Turi *</label>
              <select 
                v-model="docType"
                @change="clearBuilderRows"
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 text-xs text-white focus:outline-none focus:border-indigo-500 transition"
              >
                <option value="kirim">Kirim (Stock In)</option>
                <option value="chiqim">Chiqim (Manual Stock Out/Spoilage)</option>
                <option value="inventarizatsiya">Inventarizatsiya (Audit Stock-take)</option>
              </select>
            </div>

            <!-- Notes -->
            <div class="sm:col-span-2 space-y-1.5">
              <label class="text-3xs text-slate-500 font-bold uppercase tracking-wider">Izoh / Tafsilotlar</label>
              <input 
                v-model="docNotes"
                type="text" 
                placeholder="Hujjat uchun izoh kiriting (masalan: Bozordan go'sht olindi)..."
                class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-xs text-white focus:outline-none transition"
              />
            </div>
          </div>

          <!-- Repeater Items List -->
          <div class="flex-grow flex flex-col overflow-hidden">
            <div class="flex justify-between items-center mb-3 shrink-0">
              <h3 class="text-xs font-bold text-white">Hujjat Satrlari</h3>
              <button 
                @click="addBuilderRow"
                class="px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-3xs font-bold transition flex items-center space-x-1"
              >
                <Plus class="w-3.5 h-3.5" />
                <span>Qator qo'shish</span>
              </button>
            </div>

            <!-- Rows list -->
            <div class="flex-grow overflow-y-auto pr-1 space-y-3.5 pb-6">
              <div 
                v-for="(row, idx) in builderRows" 
                :key="idx"
                class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center bg-white/2 hover:bg-white/4 border border-white/5 rounded-2xl p-3.5 transition animate-rowIn"
              >
                <!-- Ingredient selector -->
                <div class="sm:col-span-5 space-y-1">
                  <label class="text-4xs text-slate-500 font-bold uppercase tracking-wider block sm:hidden">Masalliq</label>
                  <select 
                    v-model="row.ingredient_id"
                    @change="handleSelectIngredient($event, idx)"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 text-xs text-white focus:outline-none transition appearance-none"
                  >
                    <option value="" disabled class="bg-slate-900 text-slate-500">Tanlang...</option>
                    <option 
                      v-for="ing in ingredientsStore.ingredients" 
                      :key="ing.id" 
                      :value="ing.id" 
                      class="bg-slate-900"
                    >
                      {{ ing.name }} (SKU: {{ ing.sku }})
                    </option>
                  </select>
                </div>

                <!-- Quantity input -->
                <div class="sm:col-span-3 relative space-y-1">
                  <label class="text-4xs text-slate-500 font-bold uppercase tracking-wider block sm:hidden">Miqdori</label>
                  <input 
                    v-model.number="row.quantity"
                    type="number" 
                    step="0.001"
                    placeholder="Miqdori..."
                    class="w-full pl-3.5 pr-12 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 text-xs text-white focus:outline-none transition"
                  />
                  <span class="absolute right-4 bottom-2.5 text-xs text-slate-500 font-semibold uppercase">
                    {{ row.unit || 'birlik' }}
                  </span>
                </div>

                <!-- Price input (Only on Stock-In) -->
                <div class="sm:col-span-3 relative space-y-1" v-if="docType === 'kirim'">
                  <label class="text-4xs text-slate-500 font-bold uppercase tracking-wider block sm:hidden">Sotib olish narxi</label>
                  <input 
                    v-model.number="row.unit_price"
                    type="number" 
                    placeholder="Narxi..."
                    class="w-full pl-3.5 pr-12 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 text-xs text-white focus:outline-none transition"
                  />
                  <span class="absolute right-4 bottom-2.5 text-xs text-slate-500 font-semibold uppercase">
                    UZS
                  </span>
                </div>

                <!-- Remove Row Button -->
                <div class="text-right pt-2 sm:pt-0" :class="docType === 'kirim' ? 'sm:col-span-1' : 'sm:col-span-4'">
                  <button 
                    @click="removeBuilderRow(idx)"
                    class="p-2.5 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500 hover:text-white transition duration-200"
                    title="Qatorni o'chirish"
                  >
                    <Trash2 class="w-4 h-4 mx-auto sm:mx-0" />
                  </button>
                </div>
              </div>

              <!-- Empty builder state -->
              <div v-if="builderRows.length === 0" class="flex flex-col items-center justify-center py-20 text-slate-500 space-y-2">
                <ChefHat class="w-10 h-10 stroke-[1.2]" />
                <p class="text-xxs font-medium">Hujjatga hali hech qanday masalliq qatorlari qo'shilmagan.</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Submit actions bottom bar -->
        <div class="border-t border-white/5 pt-4 flex justify-end shrink-0">
          <button 
            @click="submitDocument"
            :disabled="warehouseStore.loading"
            class="px-6 py-3 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 font-semibold text-xs text-white shadow-lg transition duration-200 flex items-center justify-center space-x-2"
          >
            <Loader2 v-if="warehouseStore.loading" class="w-4 h-4 animate-spin text-white" />
            <Save v-else class="w-4 h-4" />
            <span>Hujjatni Saqlash</span>
          </button>
        </div>

      </div>
    </div>

    <!-- MODAL: Historical Audit Timeline Modal -->
    <div 
      v-if="showTimelineModal" 
      class="fixed inset-0 z-50 backdrop-blur-md bg-black/60 flex items-center justify-center p-6"
      @click.self="showTimelineModal = false"
    >
      <div class="w-full max-w-lg backdrop-blur-xl bg-slate-900/80 border border-white/10 rounded-3xl p-6 shadow-2xl space-y-5 animate-scaleIn flex flex-col max-h-[500px]">
        <div class="flex justify-between items-center border-b border-white/5 pb-3 shrink-0">
          <div>
            <h3 class="text-base font-bold text-white">Masalliq Harakati Tarixi</h3>
            <p class="text-xxs text-slate-400 mt-0.5">{{ selectedIngredient?.name }} (Joriy qoldiq: {{ selectedIngredient?.quantity }} {{ selectedIngredient?.unit }})</p>
          </div>
          <button @click="showTimelineModal = false" class="p-1 rounded-lg bg-white/5 text-slate-400 hover:text-white transition">
            <X class="w-4 h-4" />
          </button>
        </div>

        <!-- Timeline container -->
        <div v-if="warehouseStore.loading" class="flex-grow flex items-center justify-center py-12 shrink-0">
          <Loader2 class="w-8 h-8 text-indigo-500 animate-spin" />
        </div>

        <div v-else class="flex-grow overflow-y-auto pr-1 space-y-5 py-2">
          <div 
            v-for="item in warehouseStore.timeline" 
            :key="item.id"
            class="flex items-start space-x-4 relative before:content-[''] before:absolute before:left-4 before:top-8 before:bottom-0 before:w-[1px] before:bg-white/5 last:before:hidden"
          >
            <!-- Circular type icon wrapper -->
            <div 
              class="w-8 h-8 rounded-full flex items-center justify-center border shrink-0 text-3xs font-bold font-mono"
              :class="timelineDotClass(item.transaction?.type)"
            >
              {{ timelineDotLetter(item.transaction?.type) }}
            </div>

            <!-- Content details -->
            <div class="flex-grow bg-white/2 border border-white/5 rounded-2xl p-4 space-y-2">
              <div class="flex justify-between items-center text-2xs">
                <span class="font-bold text-white uppercase tracking-wider">{{ item.transaction?.type }}</span>
                <span class="text-slate-500 font-medium">{{ formatDateTime(item.created_at) }}</span>
              </div>
              <div class="text-xs">
                <span class="text-slate-400 font-medium">Boshlang'ich:</span> 
                <span class="text-white font-bold mx-1">{{ formatDecimal(item.old_quantity) }}</span>
                <span class="text-slate-500 font-medium">&#8594;</span>
                <span class="text-slate-400 font-medium mx-1">O'zgarish:</span>
                <span class="font-bold" :class="deltaClass(item.quantity, item.transaction?.type)">
                  {{ deltaSign(item.quantity, item.transaction?.type) }} {{ formatDecimal(Math.abs(item.quantity)) }}
                </span>
                <span class="text-slate-500 font-medium">&#8594;</span>
                <span class="text-slate-400 font-medium mx-1">Yangi:</span>
                <span class="text-white font-bold">{{ formatDecimal(item.new_quantity) }}</span>
              </div>
              <p v-if="item.transaction?.notes" class="text-xxs text-slate-500 italic">"{{ item.transaction.notes }}"</p>
              <div class="text-4xs text-slate-500 flex items-center space-x-1 pt-1 border-t border-white/2">
                <User class="w-2.5 h-2.5 text-slate-500" />
                <span>{{ item.transaction?.user?.name }}</span>
              </div>
            </div>

          </div>

          <!-- Empty Timeline -->
          <div v-if="warehouseStore.timeline.length === 0" class="flex flex-col items-center justify-center py-12 text-slate-500 space-y-2 shrink-0">
            <Database class="w-8 h-8 stroke-[1.2]" />
            <p class="text-xxs font-medium">Bu masalliq uchun hali hech qanday harakatlar tarixi qayd etilmagan.</p>
          </div>
        </div>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { 
  Search, AlertTriangle, Plus, Trash2, Save, X, Loader2, Database, User
} from 'lucide-vue-next';
import { useIngredientsStore } from '@/stores/ingredients';
import { useWarehouseStore } from '@/stores/warehouse';

const ingredientsStore = useIngredientsStore();
const warehouseStore = useWarehouseStore();

// Navigation tab
const activeTab = ref('summary');

// Tab 1 States
const summarySearch = ref('');
const showTimelineModal = ref(false);
const selectedIngredient = ref(null);

// Tab 2 States
const historyType = ref('');
const startDate = ref('');
const endDate = ref('');

// Tab 3 States
const docType = ref('kirim');
const docNotes = ref('');
const builderRows = ref([]);

// Lifecycle
onMounted(async () => {
  await ingredientsStore.fetchIngredients();
  await triggerHistoryFetch();
});

// Translation labels
const tabLabel = (key) => {
  if (key === 'summary') return 'Ombor Qoldig\'i';
  if (key === 'history') return 'Kirim/Chiqim Tarixi';
  return 'Yangi Hujjat Yaratish';
};

// Summary tab computed properties
const filteredIngredients = computed(() => {
  if (!summarySearch.value.trim()) {
    return ingredientsStore.ingredients;
  }
  const query = summarySearch.value.toLowerCase();
  return ingredientsStore.ingredients.filter(i => 
    i.name.toLowerCase().includes(query) || i.sku.toLowerCase().includes(query)
  );
});

const lowStockCount = computed(() => {
  return ingredientsStore.ingredients.filter(i => i.is_low_stock).length;
});

// History tab triggers
const triggerHistoryFetch = async () => {
  await warehouseStore.fetchTransactions({
    type: historyType.value,
    start_date: startDate.value,
    end_date: endDate.value,
    page: 1
  });
};

// Builder functions
const clearBuilderRows = () => {
  builderRows.value = [];
};

const addBuilderRow = () => {
  builderRows.value.push({
    ingredient_id: '',
    quantity: '',
    unit_price: 0,
    unit: ''
  });
};

const removeBuilderRow = (idx) => {
  builderRows.value.splice(idx, 1);
};

const handleSelectIngredient = (e, index) => {
  const ingId = parseInt(e.target.value);
  const found = ingredientsStore.ingredients.find(i => i.id === ingId);
  if (found) {
    builderRows.value[index].unit = found.unit;
    // Set default unit price from current average cost price to make kirim inputs quicker
    builderRows.value[index].unit_price = parseFloat(found.cost_price);
  }
};

const submitDocument = async () => {
  if (builderRows.value.length === 0) {
    alert('Hujjatga kamida bitta masalliq qatori qo\'shing.');
    return;
  }

  // Validate duplicate selections
  const ids = builderRows.value.map(r => r.ingredient_id).filter(id => id !== '');
  const duplicates = ids.filter((item, index) => ids.indexOf(item) !== index);
  if (duplicates.length > 0) {
    alert('Bir xil masalliq bir necha marta tanlangan. Ularni birlashtiring.');
    return;
  }

  // Row validation check
  const invalid = builderRows.value.some(r => 
    !r.ingredient_id || 
    isNaN(parseFloat(r.quantity)) || 
    parseFloat(r.quantity) < (docType.value === 'inventarizatsiya' ? 0 : 0.001) ||
    (docType.value === 'kirim' && (isNaN(parseFloat(r.unit_price)) || parseFloat(r.unit_price) < 0))
  );

  if (invalid) {
    alert('Barcha qatorlarni to\'g\'ri to\'ldiring. Kirim narxi noldan kichik bo\'lmasligi va miqdorlar ijobiy bo\'lishi kerak.');
    return;
  }

  try {
    const payload = {
      notes: docNotes.value.trim(),
      items: builderRows.value.map(r => ({
        ingredient_id: r.ingredient_id,
        quantity: parseFloat(r.quantity),
        unit_price: docType.value === 'kirim' ? parseFloat(r.unit_price) : undefined
      }))
    };

    if (docType.value === 'kirim') {
      await warehouseStore.submitStockIn(payload);
    } else if (docType.value === 'chiqim') {
      await warehouseStore.submitStockOut(payload);
    } else if (docType.value === 'inventarizatsiya') {
      await warehouseStore.submitAudit(payload);
    }

    alert('Hujjat muvaffaqiyatli saqlandi!');
    
    // Clear builder inputs
    docNotes.value = '';
    builderRows.value = [];
    activeTab.value = 'history'; // Redirect to logs history list
  } catch (err) {
    alert(err.message);
  }
};

// Ingredient Timeline Modal triggers
const openTimelineModal = async (ing) => {
  selectedIngredient.value = ing;
  showTimelineModal.value = true;
  await warehouseStore.fetchIngredientTimeline(ing.id);
};

// Formatting & Stylings Computed Helpers
const formatCurrency = (val) => {
  return new Intl.NumberFormat('uz-UZ').format(val) + ' UZS';
};

const formatDecimal = (val) => {
  return parseFloat(val).toFixed(3);
};

const formatDateTime = (str) => {
  const d = new Date(str);
  return d.toLocaleString('uz-UZ', { 
    year: 'numeric', month: '2-digit', day: '2-digit', 
    hour: '2-digit', minute: '2-digit' 
  });
};

const transactionTypeClass = (type) => {
  if (type === 'kirim') return 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400';
  if (type === 'chiqim') return 'bg-red-500/10 border-red-500/20 text-red-400';
  return 'bg-blue-500/10 border-blue-500/20 text-blue-400';
};

const deltaClass = (qty, type) => {
  const q = parseFloat(qty);
  if (type === 'kirim') return 'text-emerald-400';
  if (type === 'chiqim') return 'text-red-400';
  
  // Audits can adjust up or down
  return q > 0 ? 'text-emerald-400' : (q < 0 ? 'text-red-400' : 'text-slate-400');
};

const deltaSign = (qty, type) => {
  const q = parseFloat(qty);
  if (type === 'kirim') return '+';
  if (type === 'chiqim') return '-';
  
  return q > 0 ? '+' : '';
};

const timelineDotClass = (type) => {
  if (type === 'kirim') return 'bg-emerald-600/20 border-emerald-500/40 text-emerald-400';
  if (type === 'chiqim') return 'bg-red-600/20 border-red-500/40 text-red-400';
  return 'bg-blue-600/20 border-blue-500/40 text-blue-400';
};

const timelineDotLetter = (type) => {
  if (type === 'kirim') return 'K';
  if (type === 'chiqim') return 'C';
  return 'I';
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
.pl-10 {
  padding-left: 2.5rem;
}
.animate-scaleIn {
  animation: scaleIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
.animate-rowIn {
  animation: rowIn 0.2s ease-out forwards;
}

@keyframes scaleIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}

@keyframes rowIn {
  from { opacity: 0; transform: translateY(4px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
