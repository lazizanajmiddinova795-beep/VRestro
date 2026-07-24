<template>
  <div class="flex-grow p-6 flex flex-col h-screen overflow-hidden">
    
    <!-- Top Header -->
    <div class="flex items-center justify-between mb-6 shrink-0">
      <div>
        <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-wide">
          Stollar Xaritasi
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 font-bold mt-0.5">Restoranning zallaridagi jismoniy stollar holati, odam sig'imi va holatlarini boshqarish</p>
      </div>

      <!-- Add Table button -->
      <button 
        @click="openAddEditModal()"
        class="px-5 py-2.5 rounded-xl bg-indigo-600 font-extrabold text-sm text-white shadow-md shadow-indigo-600/30 hover:bg-indigo-500 hover:scale-[1.01] transition-all flex items-center justify-center space-x-2"
      >
        <Plus class="w-4.5 h-4.5" />
        <span>Yangi Stol Qo'shish</span>
      </button>
    </div>

    <!-- Status Statistics Widgets -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 shrink-0">
      <!-- Total Tables -->
      <div class="bg-white dark:bg-slate-900/60 border-2 border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 flex items-center space-x-3.5 shadow-sm">
        <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold">
          <Layers class="w-5 h-5" />
        </div>
        <div>
          <span class="block text-4xs uppercase tracking-wider text-slate-500 dark:text-slate-400 font-extrabold">Jami stollar</span>
          <span class="text-lg font-black text-slate-900 dark:text-white tracking-tight">{{ totalTablesCount }} ta</span>
        </div>
      </div>

      <!-- Empty -->
      <div class="bg-white dark:bg-slate-900/60 border-2 border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 flex items-center space-x-3.5 shadow-sm">
        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400 font-bold">
          <CheckCircle2 class="w-5 h-5" />
        </div>
        <div>
          <span class="block text-4xs uppercase tracking-wider text-slate-500 dark:text-slate-400 font-extrabold">Bo'sh</span>
          <span class="text-lg font-black text-emerald-600 dark:text-emerald-400 tracking-tight">{{ emptyTablesCount }} ta</span>
        </div>
      </div>

      <!-- Occupied -->
      <div class="bg-white dark:bg-slate-900/60 border-2 border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 flex items-center space-x-3.5 shadow-sm">
        <div class="w-10 h-10 rounded-xl bg-red-500/10 border border-red-500/20 flex items-center justify-center text-red-600 dark:text-red-400 font-bold">
          <Users class="w-5 h-5" />
        </div>
        <div>
          <span class="block text-4xs uppercase tracking-wider text-slate-500 dark:text-slate-400 font-extrabold">Band</span>
          <span class="text-lg font-black text-red-600 dark:text-red-400 tracking-tight">{{ occupiedTablesCount }} ta</span>
        </div>
      </div>

      <!-- Reserved -->
      <div class="bg-white dark:bg-slate-900/60 border-2 border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 flex items-center space-x-3.5 shadow-sm">
        <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-600 dark:text-amber-400 font-bold">
          <CalendarHeart class="w-5 h-5" />
        </div>
        <div>
          <span class="block text-4xs uppercase tracking-wider text-slate-500 dark:text-slate-400 font-extrabold">Bron qilingan</span>
          <span class="text-lg font-black text-amber-600 dark:text-amber-400 tracking-tight">{{ reservedTablesCount }} ta</span>
        </div>
      </div>
    </div>

    <!-- Tables Grid Container -->
    <div v-if="tablesStore.loading && tablesStore.tables.length === 0" class="flex-grow flex flex-col items-center justify-center space-y-4">
      <Loader2 class="w-10 h-10 text-indigo-600 animate-spin" />
      <p class="text-slate-600 dark:text-slate-400 text-xs font-bold animate-pulse">Zallar yuklanmoqda...</p>
    </div>

    <div v-else class="flex-grow overflow-y-auto pr-1">
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6 pb-24">
        
        <!-- Table Card -->
        <div 
          v-for="table in tablesStore.tables" 
          :key="table.id"
          class="bg-white dark:bg-slate-900/60 border-2 border-slate-200/80 dark:border-slate-800 rounded-3xl p-5 flex flex-col justify-between h-44 transition-all duration-300 relative group shadow-sm"
          :class="tableStatusBorderClass(table.status)"
        >
          <!-- Status Glow/Background -->
          <div class="absolute inset-0 rounded-3xl opacity-[0.03] transition-opacity duration-300 group-hover:opacity-[0.06]" :class="tableStatusBgClass(table.status)"></div>

          <div class="space-y-3 relative z-10">
            <!-- Header Row -->
            <div class="flex justify-between items-start">
              <h3 class="text-sm font-black text-slate-900 dark:text-white tracking-wide truncate pr-2">{{ table.table_number }}</h3>
              
              <!-- Quick State badge -->
              <span 
                class="px-2 py-0.5 rounded-md text-4xs font-extrabold border capitalize"
                :class="tableStatusBadgeClass(table.status)"
              >
                {{ tableStatusLabel(table.status) }}
              </span>
            </div>

            <!-- Capacity details -->
            <div class="flex items-center text-xs text-slate-600 dark:text-slate-400 font-bold space-x-1.5 pt-1">
              <User2 class="w-4 h-4 text-slate-500" />
              <span>Sig'imi: {{ table.capacity }} kishi</span>
            </div>
            
            <div class="text-4xs font-mono text-slate-500 font-bold truncate" v-if="table.qr_code_token">
              QR: {{ table.qr_code_token }}
            </div>
          </div>

          <!-- Bottom Actions (Hover show or quick list) -->
          <div class="border-t border-slate-200/80 dark:border-white/5 pt-3.5 flex items-center justify-between relative z-10">
            <!-- Quick Status Change Select -->
            <div class="relative">
              <select 
                :value="table.status"
                @change="handleStatusChange(table.id, $event.target.value)"
                class="bg-slate-100 dark:bg-slate-950/80 hover:bg-slate-200 dark:hover:bg-slate-950 border border-slate-300 dark:border-white/10 rounded-lg text-4xs font-bold text-slate-800 dark:text-slate-200 px-2 py-1 focus:outline-none transition appearance-none cursor-pointer pr-5"
              >
                <option value="empty" class="bg-white dark:bg-slate-900 text-emerald-600 dark:text-emerald-400">Bo'sh</option>
                <option value="occupied" class="bg-white dark:bg-slate-900 text-red-600 dark:text-red-400">Band</option>
                <option value="reserved" class="bg-white dark:bg-slate-900 text-amber-600 dark:text-amber-400">Bron</option>
              </select>
              <ChevronDown class="w-2.5 h-2.5 text-slate-500 absolute right-1.5 bottom-2 pointer-events-none" />
            </div>

            <!-- Edit/Delete icons -->
            <div class="flex items-center space-x-1">
              <button 
                @click="openAddEditModal(table)"
                class="p-1 rounded bg-white/5 text-slate-400 hover:bg-white/10 hover:text-white transition"
                title="Tahrirlash"
              >
                <Edit3 class="w-3.5 h-3.5" />
              </button>
              <button 
                @click="handleDelete(table)"
                class="p-1 rounded bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-white transition"
                title="O'chirish"
              >
                <Trash2 class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>

        </div>

      </div>

      <!-- Empty state -->
      <div v-if="tablesStore.tables.length === 0" class="flex flex-col items-center justify-center py-24 space-y-3">
        <Layers class="w-12 h-12 text-slate-600" />
        <p class="text-slate-400 text-xs font-medium">Stollar topilmadi</p>
      </div>
    </div>

    <!-- MODAL: Add / Edit Table -->
    <div 
      v-if="showModal" 
      class="fixed inset-0 z-50 backdrop-blur-md bg-black/60 flex items-center justify-center p-6"
      @click.self="showModal = false"
    >
      <div class="w-full max-w-sm backdrop-blur-xl bg-slate-900/80 border border-white/10 rounded-3xl p-6 shadow-2xl space-y-5 animate-scaleIn">
        <div class="flex justify-between items-center border-b border-white/5 pb-3">
          <h3 class="text-base font-bold text-white">
            {{ editingTable ? 'Stolni Tahrirlash' : 'Yangi Stol' }}
          </h3>
          <button @click="showModal = false" class="p-1 rounded-lg bg-white/5 text-slate-400 hover:text-white transition">
            <X class="w-4 h-4" />
          </button>
        </div>

        <div class="space-y-4">
          <!-- Table number/name -->
          <div class="space-y-1.5">
            <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Stol raqami / Nomi *</label>
            <input 
              v-model="tableForm.table_number"
              type="text" 
              placeholder="Masalan, Stol 12, VIP 4..."
              class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
            />
          </div>

          <!-- Capacity -->
          <div class="space-y-1.5">
            <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Maksimal odam sig'imi *</label>
            <input 
              v-model.number="tableForm.capacity"
              type="number" 
              placeholder="Masalan, 4 kishi..."
              class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
            />
          </div>

          <!-- QR Token -->
          <div class="space-y-1.5">
            <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">QR Code Token (Ixtiyoriy)</label>
            <input 
              v-model="tableForm.qr_code_token"
              type="text" 
              placeholder="Avtomatik generatsiya"
              class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
            />
          </div>

          <!-- Initial Status (only on Create) -->
          <div class="space-y-1.5" v-if="!editingTable">
            <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Dastlabki holati *</label>
            <select 
              v-model="tableForm.status"
              class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
            >
              <option value="empty">Bo'sh (Empty)</option>
              <option value="occupied">Band (Occupied)</option>
              <option value="reserved">Bron qilingan (Reserved)</option>
            </select>
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

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { 
  Plus, Edit3, Trash2, X, Loader2, Layers, CheckCircle2, Users, CalendarHeart, User2, ChevronDown
} from 'lucide-vue-next';
import { useTablesStore } from '@/stores/tables';

const tablesStore = useTablesStore();

// States
const showModal = ref(false);
const editingTable = ref(null);
const tableForm = ref({
  table_number: '',
  capacity: 4,
  status: 'empty',
  qr_code_token: ''
});

// Lifecycle
onMounted(async () => {
  await tablesStore.fetchTables();
});

// Stats Computed Calculations
const totalTablesCount = computed(() => {
  return tablesStore.tables.length;
});

const emptyTablesCount = computed(() => {
  return tablesStore.tables.filter(t => t.status === 'empty').length;
});

const occupiedTablesCount = computed(() => {
  return tablesStore.tables.filter(t => t.status === 'occupied').length;
});

const reservedTablesCount = computed(() => {
  return tablesStore.tables.filter(t => t.status === 'reserved').length;
});

// Actions
const openAddEditModal = (table = null) => {
  editingTable.value = table;
  tableForm.value = table ? {
    table_number: table.table_number,
    capacity: parseInt(table.capacity),
    status: table.status,
    qr_code_token: table.qr_code_token || ''
  } : {
    table_number: '',
    capacity: 4,
    status: 'empty',
    qr_code_token: ''
  };
  showModal.value = true;
};

const submitForm = async () => {
  if (!tableForm.value.table_number.trim() || !tableForm.value.capacity) {
    alert('Barcha majburiy maydonlarni to\'g\'ri to\'ldiring.');
    return;
  }

  try {
    if (editingTable.value) {
      await tablesStore.updateTable(editingTable.value.id, tableForm.value);
    } else {
      await tablesStore.createTable(tableForm.value);
    }
    showModal.value = false;
  } catch (err) {
    alert(err.message);
  }
};

const handleStatusChange = async (id, newStatus) => {
  try {
    await tablesStore.updateTableStatus(id, newStatus);
  } catch (err) {
    alert(err.message);
  }
};

const handleDelete = async (table) => {
  if (!confirm(`"${table.table_number}" stolini ro'yxatdan o'chirmoqchimisiz?`)) return;
  try {
    await tablesStore.deleteTable(table.id);
  } catch (err) {
    alert(err.message);
  }
};

// Styling & Label Helper methods
const tableStatusBorderClass = (status) => {
  if (status === 'empty') return 'border-emerald-500/20 hover:border-emerald-500/50 shadow-[0_0_15px_-3px_rgba(16,185,129,0.05)]';
  if (status === 'occupied') return 'border-red-500/20 hover:border-red-500/50 shadow-[0_0_15px_-3px_rgba(239,68,68,0.05)]';
  return 'border-amber-500/20 hover:border-amber-500/50 shadow-[0_0_15px_-3px_rgba(245,158,11,0.05)]';
};

const tableStatusBgClass = (status) => {
  if (status === 'empty') return 'bg-emerald-500';
  if (status === 'occupied') return 'bg-red-500';
  return 'bg-amber-500';
};

const tableStatusBadgeClass = (status) => {
  if (status === 'empty') return 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400';
  if (status === 'occupied') return 'bg-red-500/10 border-red-500/20 text-red-400 animate-pulse';
  return 'bg-amber-500/10 border-amber-500/20 text-amber-400';
};

const tableStatusLabel = (status) => {
  if (status === 'empty') return 'bo\'sh';
  if (status === 'occupied') return 'band';
  return 'bron';
};
</script>

<style scoped>
.text-3xs {
  font-size: 0.6rem;
}
.text-4xs {
  font-size: 0.55rem;
}
.animate-scaleIn {
  animation: scaleIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes scaleIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}
</style>
