<template>
  <div class="flex-grow overflow-y-auto pb-8">

    <!-- Header / Filters -->
    <div class="bg-white border border-slate-200 rounded-3xl p-5 mb-6 space-y-4">
      <!-- Row 1: search + date range -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <!-- Search -->
        <div class="relative">
          <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
          <input
            v-model="filters.search"
            @input="debouncedFetch"
            type="text"
            placeholder="Izlash..."
            class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:outline-none transition"
          />
        </div>
        <!-- Date from -->
        <input
          v-model="filters.date_from"
          @change="fetchLogs"
          type="date"
          class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 focus:border-indigo-500 focus:outline-none transition"
        />
        <!-- Date to -->
        <input
          v-model="filters.date_to"
          @change="fetchLogs"
          type="date"
          class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 focus:border-indigo-500 focus:outline-none transition"
        />
      </div>

      <!-- Row 2: module filter + action filter + clear button -->
      <div class="flex flex-wrap items-center gap-2">
        <button
          v-for="mod in moduleOptions"
          :key="mod.key"
          @click="toggleModule(mod.key)"
          class="px-3 py-1.5 rounded-xl text-xs font-bold border transition"
          :class="filters.module === mod.key
            ? 'bg-indigo-600 border-indigo-600 text-white'
            : 'bg-slate-50 border-slate-200 text-slate-600 hover:border-indigo-400'"
        >
          <span class="mr-1">{{ mod.icon }}</span>{{ mod.label }}
        </button>

        <button
          v-if="filters.module || filters.search || filters.date_from || filters.date_to"
          @click="clearFilters"
          class="ml-auto flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-rose-500 border border-rose-200 hover:bg-rose-50 transition"
        >
          <X class="w-3.5 h-3.5" /> Filtrni tozalash
        </button>
      </div>
    </div>

    <!-- Stats row -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
      <div v-for="stat in stats" :key="stat.label" class="bg-white border border-slate-200 rounded-2xl p-4 flex flex-col items-center text-center">
        <span class="text-2xl mb-1">{{ stat.icon }}</span>
        <span class="text-lg font-black text-slate-900">{{ stat.value }}</span>
        <span class="text-xs text-slate-500 font-medium">{{ stat.label }}</span>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-20 space-y-4">
      <Loader2 class="w-10 h-10 text-indigo-500 animate-spin" />
      <p class="text-slate-500 text-xs animate-pulse">Jurnal yuklanmoqda...</p>
    </div>

    <!-- Log table -->
    <div v-else class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm">

      <!-- Table head -->
      <div class="hidden sm:grid grid-cols-[140px_1fr_120px_100px] gap-4 px-6 py-3 bg-slate-50 border-b border-slate-200 text-xs font-black text-slate-500 uppercase tracking-wider">
        <span>Sana / Vaqt</span>
        <span>Tavsif</span>
        <span>Xodim</span>
        <span>Modul</span>
      </div>

      <!-- Rows -->
      <div class="divide-y divide-slate-100">
        <div
          v-for="log in logs"
          :key="log.id"
          class="grid grid-cols-1 sm:grid-cols-[140px_1fr_120px_100px] gap-2 sm:gap-4 px-6 py-4 hover:bg-slate-50/70 transition"
        >
          <!-- Date -->
          <div class="flex items-center gap-2">
            <span class="text-lg">{{ actionIcon(log.action_type) }}</span>
            <div>
              <div class="text-xs font-bold text-slate-800">{{ formatDate(log.created_at) }}</div>
              <div class="text-[10px] text-slate-400">{{ formatTime(log.created_at) }}</div>
            </div>
          </div>

          <!-- Description -->
          <div class="text-xs text-slate-700 font-medium self-center">{{ log.description }}</div>

          <!-- User -->
          <div class="self-center">
            <div class="text-xs font-bold text-slate-800 truncate">{{ log.user_name || '—' }}</div>
            <span
              v-if="log.user_role"
              class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide border inline-block"
              :class="roleBadge(log.user_role)"
            >{{ log.user_role }}</span>
          </div>

          <!-- Module badge -->
          <div class="self-center">
            <span
              class="px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase tracking-wider border inline-block"
              :class="moduleBadge(log.module)"
            >{{ moduleLabel(log.module) }}</span>
          </div>
        </div>

        <!-- Empty state -->
        <div v-if="logs.length === 0" class="text-center py-20 space-y-3">
          <ClipboardList class="w-12 h-12 text-slate-300 mx-auto" />
          <p class="text-slate-500 text-xs font-medium">Hech qanday yozuv topilmadi</p>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="px-6 py-4 border-t border-slate-200 flex items-center justify-between gap-3">
        <button
          @click="goPage(pagination.current_page - 1)"
          :disabled="pagination.current_page <= 1"
          class="px-4 py-2 rounded-xl text-xs font-bold border border-slate-200 text-slate-600 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition"
        >← Oldingi</button>

        <span class="text-xs text-slate-500 font-medium">
          {{ pagination.current_page }} / {{ pagination.last_page }} sahifa
          &nbsp;·&nbsp; Jami {{ pagination.total }} ta yozuv
        </span>

        <button
          @click="goPage(pagination.current_page + 1)"
          :disabled="pagination.current_page >= pagination.last_page"
          class="px-4 py-2 rounded-xl text-xs font-bold border border-slate-200 text-slate-600 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition"
        >Keyingi →</button>
      </div>
    </div>

    <!-- Clear all button -->
    <div class="mt-4 flex justify-end">
      <button
        @click="clearAll"
        class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-rose-500 border border-rose-200 hover:bg-rose-50 transition"
      >
        <Trash2 class="w-3.5 h-3.5" /> Barcha jurnalni tozalash
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Search, X, Loader2, ClipboardList, Trash2 } from 'lucide-vue-next';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();

const logs = ref([]);
const loading = ref(false);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });

const filters = ref({
  search: '',
  module: '',
  date_from: '',
  date_to: '',
  page: 1,
});

// ---- Module filter options ----
const moduleOptions = [
  { key: '', label: 'Barchasi', icon: '📋' },
  { key: 'auth', label: 'Kirish', icon: '🔑' },
  { key: 'orders', label: 'Buyurtmalar', icon: '🛒' },
  { key: 'payments', label: "To'lovlar", icon: '💰' },
  { key: 'staff', label: 'Xodimlar', icon: '👤' },
  { key: 'settings', label: 'Sozlamalar', icon: '⚙️' },
];

// ---- Stats ----
const stats = computed(() => [
  { icon: '📋', label: "Jami yozuvlar", value: pagination.value.total },
  { icon: '🔑', label: "Kirish", value: logs.value.filter(l => l.module === 'auth').length },
  { icon: '🛒', label: "Buyurtmalar", value: logs.value.filter(l => l.module === 'orders').length },
  { icon: '💰', label: "To'lovlar", value: logs.value.filter(l => l.module === 'payments').length },
]);

// ---- API ----
const getHeaders = () => ({
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'Authorization': `Bearer ${authStore.token}`,
});

const fetchLogs = async () => {
  loading.value = true;
  try {
    const params = new URLSearchParams();
    if (filters.value.search)    params.append('search', filters.value.search);
    if (filters.value.module)    params.append('module', filters.value.module);
    if (filters.value.date_from) params.append('date_from', filters.value.date_from);
    if (filters.value.date_to)   params.append('date_to', filters.value.date_to);
    params.append('page', filters.value.page);
    params.append('per_page', 30);

    const res = await fetch(`/api/activity-logs?${params}`, { headers: getHeaders() });
    if (!res.ok) throw new Error('Server xatosi');
    const data = await res.json();
    logs.value = data.data || [];
    pagination.value = {
      current_page: data.current_page,
      last_page: data.last_page,
      total: data.total,
    };
  } catch (e) {
    logs.value = [];
  } finally {
    loading.value = false;
  }
};

// Debounce search input
let debounceTimer = null;
const debouncedFetch = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    filters.value.page = 1;
    fetchLogs();
  }, 400);
};

const toggleModule = (key) => {
  filters.value.module = filters.value.module === key ? '' : key;
  filters.value.page = 1;
  fetchLogs();
};

const clearFilters = () => {
  filters.value = { search: '', module: '', date_from: '', date_to: '', page: 1 };
  fetchLogs();
};

const goPage = (page) => {
  filters.value.page = page;
  fetchLogs();
};

const clearAll = async () => {
  if (!confirm("Barcha jurnal yozuvlari o'chiriladimi? Bu amalni qaytarib bo'lmaydi!")) return;
  await fetch('/api/activity-logs/clear', { method: 'DELETE', headers: getHeaders() });
  fetchLogs();
};

onMounted(fetchLogs);

// ---- Formatters ----
const formatDate = (d) => {
  const dt = new Date(d);
  return dt.toLocaleDateString('uz-UZ', { day: '2-digit', month: '2-digit', year: 'numeric' });
};
const formatTime = (d) => {
  const dt = new Date(d);
  return dt.toLocaleTimeString('uz-UZ', { hour: '2-digit', minute: '2-digit' });
};

const actionIcon = (type) => {
  const icons = {
    login: '🔑', logout: '🚪',
    order_created: '🛒', order_status_changed: '🔄', order_cancelled: '❌',
    payment_processed: '💰', payment_refunded: '↩️',
    staff_created: '👤', staff_deleted: '🗑️', staff_toggled: '🔀',
    settings_updated: '⚙️',
  };
  return icons[type] || '📌';
};

const moduleLabel = (module) => {
  const labels = {
    auth: 'Kirish', orders: 'Buyurtmalar', payments: "To'lovlar",
    staff: 'Xodimlar', settings: 'Sozlamalar',
  };
  return labels[module] || (module || '—');
};

const moduleBadge = (module) => {
  const classes = {
    auth:     'bg-indigo-50 border-indigo-200 text-indigo-600',
    orders:   'bg-violet-50 border-violet-200 text-violet-600',
    payments: 'bg-emerald-50 border-emerald-200 text-emerald-600',
    staff:    'bg-sky-50 border-sky-200 text-sky-600',
    settings: 'bg-slate-100 border-slate-200 text-slate-600',
  };
  return classes[module] || 'bg-slate-100 border-slate-200 text-slate-500';
};

const roleBadge = (role) => {
  if (role === 'Admin')   return 'bg-rose-50 border-rose-200 text-rose-500';
  if (role === 'Chef')    return 'bg-amber-50 border-amber-200 text-amber-500';
  if (role === 'Waiter')  return 'bg-sky-50 border-sky-200 text-sky-500';
  return 'bg-purple-50 border-purple-200 text-purple-500';
};
</script>
