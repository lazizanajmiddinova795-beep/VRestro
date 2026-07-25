<template>
  <div class="flex-grow p-6 flex flex-col h-screen overflow-hidden">

    <!-- Top Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 shrink-0 gap-3">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-wide">
          {{ settingsStore.t('warehouse.title') }}
        </h1>
        <p class="text-xs text-slate-500">{{ settingsStore.t('warehouse.subtitle') }}</p>
      </div>

      <!-- Navigation tabs -->
      <div class="flex space-x-1.5 bg-slate-100 p-1 rounded-2xl border border-slate-200 overflow-x-auto shrink-0">
        <button
          v-for="t in ['summary', 'history', 'builder']"
          :key="t"
          @click="activeTab = t"
          class="px-4 py-2 rounded-xl text-xs font-bold transition duration-200 capitalize whitespace-nowrap"
          :class="activeTab === t ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-500 hover:text-slate-900'"
        >
          {{ tabLabel(t) }}
        </button>
      </div>
    </div>

    <!-- TAB 1: Ombor Qoldig'i (Stock Summary) -->
    <div v-if="activeTab === 'summary'" class="flex-grow flex flex-col overflow-hidden">
      <!-- Search row -->
      <div class="bg-white border border-slate-200 shadow-sm rounded-3xl p-5 mb-6 flex flex-col sm:flex-row items-center justify-between gap-4 shrink-0">
        <div class="relative w-full sm:w-80">
          <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
            <Search class="w-4 h-4" />
          </span>
          <input
            v-model="summarySearch"
            type="text"
            :placeholder="settingsStore.t('warehouse.search_placeholder')"
            class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500 text-sm placeholder-slate-400 text-slate-900 focus:outline-none transition"
          />
        </div>
        <div class="flex items-center space-x-2.5 bg-red-50 border border-red-200 rounded-2xl px-4 py-2.5" v-if="lowStockCount > 0">
          <AlertTriangle class="w-4.5 h-4.5 text-red-500 animate-pulse" />
          <span class="text-xs font-bold text-red-500">{{ lowStockCount }} {{ settingsStore.t('warehouse.low_stock_suffix') }}</span>
        </div>
      </div>

      <!-- Inventory summary table -->
      <div class="flex-grow overflow-y-auto pr-1">
        <div class="bg-white border border-slate-200 shadow-sm rounded-3xl overflow-hidden mb-8">
          <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
              <thead>
                <tr class="border-b border-slate-200 text-slate-500 text-3xs font-bold uppercase tracking-wider bg-slate-50">
                  <th class="px-6 py-4">{{ settingsStore.t('warehouse.col_ingredient') }}</th>
                  <th class="px-6 py-4">{{ settingsStore.t('warehouse.col_sku') }}</th>
                  <th class="px-6 py-4">{{ settingsStore.t('quantity') }}</th>
                  <th class="px-6 py-4">{{ settingsStore.t('warehouse.col_unit_price') }}</th>
                  <th class="px-6 py-4">{{ settingsStore.t('warehouse.col_total_value') }}</th>
                  <th class="px-6 py-4">{{ settingsStore.t('status') }}</th>
                  <th class="px-6 py-4 text-right">{{ settingsStore.t('action') }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 text-sm">
                <tr
                  v-for="ing in filteredIngredients"
                  :key="ing.id"
                  class="hover:bg-slate-50 transition duration-200"
                  :class="ing.is_low_stock ? 'bg-red-50/50' : ''"
                >
                  <td class="px-6 py-4 font-bold text-slate-900 tracking-wide">{{ ing.name }}</td>
                  <td class="px-6 py-4 font-mono text-xs text-slate-500">{{ ing.sku }}</td>
                  <td class="px-6 py-4 font-semibold text-slate-900">
                    {{ formatDecimal(ing.quantity) }} <span class="text-xs text-slate-500">{{ ing.unit }}</span>
                  </td>
                  <td class="px-6 py-4 text-slate-600">{{ formatCurrency(ing.cost_price) }}</td>
                  <td class="px-6 py-4 font-bold text-slate-600">{{ formatCurrency(ing.total_value) }}</td>
                  <td class="px-6 py-4">
                    <span
                      class="px-2.5 py-0.5 rounded-full text-3xs font-bold border"
                      :class="ing.is_low_stock ? 'bg-red-50 border-red-200 text-red-600' : 'bg-emerald-50 border-emerald-200 text-emerald-600'"
                    >
                      {{ ing.is_low_stock ? settingsStore.t('warehouse.low_stock_badge') : settingsStore.t('warehouse.sufficient_badge') }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <button
                      @click="openTimelineModal(ing)"
                      class="px-3 py-1.5 rounded-lg bg-indigo-50 border border-indigo-200 text-indigo-600 hover:bg-indigo-600 hover:text-white text-3xs font-bold transition duration-200"
                    >
                      {{ settingsStore.t('warehouse.history_button') }}
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
      <div class="bg-white border border-slate-200 shadow-sm rounded-3xl p-5 mb-6 grid grid-cols-1 sm:grid-cols-3 gap-4 shrink-0">
        <div>
          <label class="text-3xs text-slate-500 font-bold uppercase tracking-wider mb-1.5 block">{{ settingsStore.t('warehouse.doc_type') }}</label>
          <select
            v-model="historyType"
            @change="triggerHistoryFetch"
            class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none transition"
          >
            <option value="">{{ settingsStore.t('warehouse.all') }}</option>
            <option value="kirim">{{ settingsStore.t('warehouse.stock_in') }}</option>
            <option value="chiqim">{{ settingsStore.t('warehouse.stock_out') }}</option>
            <option value="inventarizatsiya">{{ settingsStore.t('warehouse.audit') }}</option>
          </select>
        </div>
        <div>
          <label class="text-3xs text-slate-500 font-bold uppercase tracking-wider mb-1.5 block">{{ settingsStore.t('warehouse.start_date') }}</label>
          <input
            v-model="startDate"
            type="date"
            @change="triggerHistoryFetch"
            class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none transition"
          />
        </div>
        <div>
          <label class="text-3xs text-slate-500 font-bold uppercase tracking-wider mb-1.5 block">{{ settingsStore.t('warehouse.end_date') }}</label>
          <input
            v-model="endDate"
            type="date"
            @change="triggerHistoryFetch"
            class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none transition"
          />
        </div>
      </div>

      <!-- Log loading state -->
      <div v-if="warehouseStore.loading" class="flex-grow flex flex-col items-center justify-center space-y-4">
        <Loader2 class="w-10 h-10 text-indigo-500 animate-spin" />
        <p class="text-slate-500 text-xs font-medium">{{ settingsStore.t('warehouse.loading_docs') }}</p>
      </div>

      <!-- Error state -->
      <div v-else-if="warehouseStore.error" class="flex-grow flex flex-col items-center justify-center p-6 text-center space-y-4">
        <AlertTriangle class="w-12 h-12 text-red-400" />
        <h3 class="text-base font-bold text-slate-900">{{ settingsStore.t('warehouse.history_error_title') }}</h3>
        <p class="text-xs text-red-500">{{ warehouseStore.error }}</p>
        <button @click="triggerHistoryFetch" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold transition">
          {{ settingsStore.t('warehouse.reload') }}
        </button>
      </div>

      <!-- Logs Grid -->
      <div v-else class="flex-grow overflow-y-auto pr-1">
        <div class="space-y-4 pb-12">
          <div
            v-for="tx in warehouseStore.transactions"
            :key="tx.id"
            class="bg-white border border-slate-200 shadow-sm rounded-3xl p-5 space-y-4"
          >
            <!-- Header section -->
            <div class="flex flex-col sm:flex-row justify-between sm:items-center border-b border-slate-100 pb-3 gap-3">
              <div class="flex items-center space-x-3.5">
                <span
                  class="px-3 py-1 rounded-xl text-3xs font-bold uppercase tracking-wider border"
                  :class="transactionTypeClass(tx.type)"
                >
                  {{ tx.type }}
                </span>
                <div>
                  <span class="text-xs text-slate-500 font-medium">{{ settingsStore.t('warehouse.doc_number') }} #{{ tx.id }}</span>
                  <span class="mx-2 text-slate-300">|</span>
                  <span class="text-xs text-slate-500">{{ formatDateTime(tx.created_at) }}</span>
                </div>
              </div>

              <div class="text-xs text-slate-500 flex items-center space-x-2">
                <User class="w-3.5 h-3.5 text-indigo-500" />
                <span class="font-semibold">{{ tx.user?.name }}</span>
                <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200 text-4xs uppercase tracking-wider text-slate-500">
                  {{ tx.user?.roles?.[0] || settingsStore.t('warehouse.staff_fallback') }}
                </span>
              </div>
            </div>

            <!-- Notes -->
            <p v-if="tx.notes" class="text-xs text-slate-600 leading-relaxed italic bg-slate-50 p-3 rounded-xl border border-slate-100">
              "{{ tx.notes }}"
            </p>

            <!-- Line Items table -->
            <div class="overflow-x-auto">
              <table class="w-full border-collapse text-left text-xs">
                <thead>
                  <tr class="border-b border-slate-100 text-slate-400 text-3xs font-bold uppercase tracking-wider">
                    <th class="pb-2">{{ settingsStore.t('warehouse.col_ingredient') }}</th>
                    <th class="pb-2">{{ settingsStore.t('warehouse.col_prev_qty') }}</th>
                    <th class="pb-2">{{ settingsStore.t('warehouse.col_change') }}</th>
                    <th class="pb-2">{{ settingsStore.t('warehouse.col_new_qty') }}</th>
                    <th class="pb-2 text-right" v-if="tx.type === 'kirim'">{{ settingsStore.t('warehouse.col_unit_price_short') }}</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-slate-600">
                  <tr v-for="item in tx.items" :key="item.id" class="hover:bg-slate-50">
                    <td class="py-2.5 font-semibold text-slate-900">{{ item.ingredient?.name }}</td>
                    <td class="py-2.5 text-slate-500">{{ formatDecimal(item.old_quantity) }} {{ item.ingredient?.unit }}</td>
                    <td class="py-2.5 font-bold" :class="deltaClass(item.quantity, tx.type)">
                      {{ deltaSign(item.quantity, tx.type) }} {{ formatDecimal(Math.abs(item.quantity)) }} {{ item.ingredient?.unit }}
                    </td>
                    <td class="py-2.5 text-slate-900 font-medium">{{ formatDecimal(item.new_quantity) }} {{ item.ingredient?.unit }}</td>
                    <td class="py-2.5 text-right text-emerald-600 font-semibold" v-if="tx.type === 'kirim'">
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
          <Database class="w-12 h-12 text-slate-300" />
          <p class="text-slate-500 text-xs font-medium">{{ settingsStore.t('warehouse.docs_not_found') }}</p>
        </div>
      </div>
    </div>

    <!-- TAB 3: Yangi Hujjat Yaratish (Document Builder) -->
    <div v-else-if="activeTab === 'builder'" class="flex-grow flex flex-col overflow-hidden">
      <div class="flex-grow bg-white border border-slate-200 shadow-sm rounded-3xl p-6 flex flex-col h-full overflow-hidden justify-between">

        <div class="space-y-5 flex flex-col h-full overflow-hidden">
          <!-- Document Controls -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 shrink-0 border-b border-slate-100 pb-5">
            <!-- Action type -->
            <div class="space-y-1.5">
              <label class="text-3xs text-slate-500 font-bold uppercase tracking-wider">{{ settingsStore.t('warehouse.doc_type_required') }}</label>
              <select
                v-model="docType"
                @change="clearBuilderRows"
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 transition"
              >
                <option value="kirim">{{ settingsStore.t('warehouse.stock_in') }}</option>
                <option value="chiqim">{{ settingsStore.t('warehouse.stock_out_manual') }}</option>
                <option value="inventarizatsiya">{{ settingsStore.t('warehouse.audit_full') }}</option>
              </select>
            </div>

            <!-- Notes -->
            <div class="sm:col-span-2 space-y-1.5">
              <label class="text-3xs text-slate-500 font-bold uppercase tracking-wider">{{ settingsStore.t('warehouse.notes_label') }}</label>
              <input
                v-model="docNotes"
                type="text"
                :placeholder="settingsStore.t('warehouse.notes_placeholder')"
                class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500 text-xs text-slate-900 focus:outline-none transition"
              />
            </div>
          </div>

          <!-- Repeater Items List -->
          <div class="flex-grow flex flex-col overflow-hidden">
            <div class="flex justify-between items-center mb-3 shrink-0">
              <h3 class="text-xs font-bold text-slate-900">{{ settingsStore.t('warehouse.doc_rows') }}</h3>
              <button
                @click="addBuilderRow"
                class="px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-3xs font-bold transition flex items-center space-x-1"
              >
                <Plus class="w-3.5 h-3.5" />
                <span>{{ settingsStore.t('warehouse.add_row') }}</span>
              </button>
            </div>

            <!-- Rows list -->
            <div class="flex-grow overflow-y-auto pr-1 space-y-3.5 pb-6">
              <div
                v-for="(row, idx) in builderRows"
                :key="idx"
                class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-2xl p-3.5 transition animate-rowIn"
              >
                <!-- Ingredient selector -->
                <div class="sm:col-span-5 space-y-1">
                  <label class="text-4xs text-slate-400 font-bold uppercase tracking-wider block sm:hidden">{{ settingsStore.t('warehouse.col_ingredient') }}</label>
                  <select
                    v-model="row.ingredient_id"
                    @change="handleSelectIngredient($event, idx)"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-xs text-slate-900 focus:outline-none transition appearance-none"
                  >
                    <option value="" disabled>{{ settingsStore.t('warehouse.select_placeholder') }}</option>
                    <option
                      v-for="ing in ingredientsStore.ingredients"
                      :key="ing.id"
                      :value="ing.id"
                    >
                      {{ ing.name }} (SKU: {{ ing.sku }})
                    </option>
                  </select>
                </div>

                <!-- Quantity input -->
                <div class="sm:col-span-3 relative space-y-1">
                  <label class="text-4xs text-slate-400 font-bold uppercase tracking-wider block sm:hidden">{{ settingsStore.t('quantity') }}</label>
                  <input
                    v-model.number="row.quantity"
                    type="number"
                    step="0.001"
                    :placeholder="settingsStore.t('warehouse.quantity_placeholder')"
                    class="w-full pl-3.5 pr-12 py-2.5 rounded-xl bg-white border border-slate-200 text-xs text-slate-900 focus:outline-none transition"
                  />
                  <span class="absolute right-4 bottom-2.5 text-xs text-slate-400 font-semibold uppercase">
                    {{ row.unit || settingsStore.t('warehouse.unit_fallback') }}
                  </span>
                </div>

                <!-- Price input (Only on Stock-In) -->
                <div class="sm:col-span-3 relative space-y-1" v-if="docType === 'kirim'">
                  <label class="text-4xs text-slate-400 font-bold uppercase tracking-wider block sm:hidden">{{ settingsStore.t('warehouse.purchase_price') }}</label>
                  <input
                    v-model.number="row.unit_price"
                    type="number"
                    :placeholder="settingsStore.t('warehouse.price_placeholder')"
                    class="w-full pl-3.5 pr-12 py-2.5 rounded-xl bg-white border border-slate-200 text-xs text-slate-900 focus:outline-none transition"
                  />
                  <span class="absolute right-4 bottom-2.5 text-xs text-slate-400 font-semibold uppercase">
                    UZS
                  </span>
                </div>

                <!-- Remove Row Button -->
                <div class="text-right pt-2 sm:pt-0" :class="docType === 'kirim' ? 'sm:col-span-1' : 'sm:col-span-4'">
                  <button
                    @click="removeBuilderRow(idx)"
                    class="p-2.5 rounded-xl bg-red-50 border border-red-200 text-red-500 hover:bg-red-500 hover:text-white transition duration-200"
                    :title="settingsStore.t('warehouse.remove_row_title')"
                  >
                    <Trash2 class="w-4 h-4 mx-auto sm:mx-0" />
                  </button>
                </div>
              </div>

              <!-- Empty builder state -->
              <div v-if="builderRows.length === 0" class="flex flex-col items-center justify-center py-20 text-slate-400 space-y-2">
                <ChefHat class="w-10 h-10 stroke-[1.2]" />
                <p class="text-xxs font-medium">{{ settingsStore.t('warehouse.empty_builder') }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Submit actions bottom bar -->
        <div class="border-t border-slate-100 pt-4 flex justify-end shrink-0">
          <button
            @click="submitDocument"
            :disabled="warehouseStore.loading"
            class="px-6 py-3 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 font-semibold text-xs text-white shadow-md transition duration-200 flex items-center justify-center space-x-2"
          >
            <Loader2 v-if="warehouseStore.loading" class="w-4 h-4 animate-spin text-white" />
            <Save v-else class="w-4 h-4" />
            <span>{{ settingsStore.t('warehouse.save_document') }}</span>
          </button>
        </div>

      </div>
    </div>

    <!-- MODAL: Historical Audit Timeline Modal -->
    <div
      v-if="showTimelineModal"
      class="fixed inset-0 z-50 backdrop-blur-sm bg-slate-900/30 flex items-center justify-center p-6"
      @click.self="showTimelineModal = false"
    >
      <div class="w-full max-w-lg bg-white border border-slate-200 rounded-3xl p-6 shadow-2xl space-y-5 animate-scaleIn flex flex-col max-h-[500px]">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3 shrink-0">
          <div>
            <h3 class="text-base font-bold text-slate-900">{{ settingsStore.t('warehouse.timeline_title') }}</h3>
            <p class="text-xxs text-slate-500 mt-0.5">{{ selectedIngredient?.name }} ({{ settingsStore.t('warehouse.current_stock_label') }}: {{ selectedIngredient?.quantity }} {{ selectedIngredient?.unit }})</p>
          </div>
          <button @click="showTimelineModal = false" class="p-1 rounded-lg bg-slate-100 text-slate-500 hover:text-slate-900 transition">
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
            class="flex items-start space-x-4 relative before:content-[''] before:absolute before:left-4 before:top-8 before:bottom-0 before:w-[1px] before:bg-slate-100 last:before:hidden"
          >
            <!-- Circular type icon wrapper -->
            <div
              class="w-8 h-8 rounded-full flex items-center justify-center border shrink-0 text-3xs font-bold font-mono"
              :class="timelineDotClass(item.transaction?.type)"
            >
              {{ timelineDotLetter(item.transaction?.type) }}
            </div>

            <!-- Content details -->
            <div class="flex-grow bg-slate-50 border border-slate-100 rounded-2xl p-4 space-y-2">
              <div class="flex justify-between items-center text-2xs">
                <span class="font-bold text-slate-900 uppercase tracking-wider">{{ item.transaction?.type }}</span>
                <span class="text-slate-400 font-medium">{{ formatDateTime(item.created_at) }}</span>
              </div>
              <div class="text-xs">
                <span class="text-slate-500 font-medium">{{ settingsStore.t('warehouse.initial_label') }}</span>
                <span class="text-slate-900 font-bold mx-1">{{ formatDecimal(item.old_quantity) }}</span>
                <span class="text-slate-400 font-medium">&#8594;</span>
                <span class="text-slate-500 font-medium mx-1">{{ settingsStore.t('warehouse.change_label') }}</span>
                <span class="font-bold" :class="deltaClass(item.quantity, item.transaction?.type)">
                  {{ deltaSign(item.quantity, item.transaction?.type) }} {{ formatDecimal(Math.abs(item.quantity)) }}
                </span>
                <span class="text-slate-400 font-medium">&#8594;</span>
                <span class="text-slate-500 font-medium mx-1">{{ settingsStore.t('warehouse.new_label') }}</span>
                <span class="text-slate-900 font-bold">{{ formatDecimal(item.new_quantity) }}</span>
              </div>
              <p v-if="item.transaction?.notes" class="text-xxs text-slate-500 italic">"{{ item.transaction.notes }}"</p>
              <div class="text-4xs text-slate-400 flex items-center space-x-1 pt-1 border-t border-slate-100">
                <User class="w-2.5 h-2.5 text-slate-400" />
                <span>{{ item.transaction?.user?.name }}</span>
              </div>
            </div>

          </div>

          <!-- Empty Timeline -->
          <div v-if="warehouseStore.timeline.length === 0" class="flex flex-col items-center justify-center py-12 text-slate-400 space-y-2 shrink-0">
            <Database class="w-8 h-8 stroke-[1.2]" />
            <p class="text-xxs font-medium">{{ settingsStore.t('warehouse.empty_timeline') }}</p>
          </div>
        </div>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import {
  Search, AlertTriangle, Plus, Trash2, Save, X, Loader2, Database, User, ChefHat
} from 'lucide-vue-next';
import { useIngredientsStore } from '@/stores/ingredients';
import { useWarehouseStore } from '@/stores/warehouse';
import { useSettingsStore } from '@/stores/settings';

const ingredientsStore = useIngredientsStore();
const warehouseStore = useWarehouseStore();
const settingsStore = useSettingsStore();

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
  if (key === 'summary') return settingsStore.t('warehouse.tab_summary');
  if (key === 'history') return settingsStore.t('warehouse.tab_history');
  return settingsStore.t('warehouse.tab_builder');
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
    alert(settingsStore.t('warehouse.alert_add_row'));
    return;
  }

  // Validate duplicate selections
  const ids = builderRows.value.map(r => r.ingredient_id).filter(id => id !== '');
  const duplicates = ids.filter((item, index) => ids.indexOf(item) !== index);
  if (duplicates.length > 0) {
    alert(settingsStore.t('warehouse.alert_duplicate'));
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
    alert(settingsStore.t('warehouse.alert_invalid_rows'));
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

    alert(settingsStore.t('warehouse.alert_doc_saved'));

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
  if (type === 'kirim') return 'bg-emerald-50 border-emerald-200 text-emerald-600';
  if (type === 'chiqim') return 'bg-red-50 border-red-200 text-red-600';
  return 'bg-blue-50 border-blue-200 text-blue-600';
};

const deltaClass = (qty, type) => {
  const q = parseFloat(qty);
  if (type === 'kirim') return 'text-emerald-600';
  if (type === 'chiqim') return 'text-red-600';

  // Audits can adjust up or down
  return q > 0 ? 'text-emerald-600' : (q < 0 ? 'text-red-600' : 'text-slate-500');
};

const deltaSign = (qty, type) => {
  const q = parseFloat(qty);
  if (type === 'kirim') return '+';
  if (type === 'chiqim') return '-';

  return q > 0 ? '+' : '';
};

const timelineDotClass = (type) => {
  if (type === 'kirim') return 'bg-emerald-50 border-emerald-300 text-emerald-600';
  if (type === 'chiqim') return 'bg-red-50 border-red-300 text-red-600';
  return 'bg-blue-50 border-blue-300 text-blue-600';
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
