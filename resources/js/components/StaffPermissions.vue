<template>
  <div class="flex-grow overflow-y-auto pr-1 pb-8">

    <!-- Info banner -->
    <div class="bg-indigo-50 border border-indigo-200 rounded-2xl px-5 py-3.5 mb-6 flex items-start space-x-3">
      <ShieldCheck class="w-5 h-5 text-indigo-600 shrink-0 mt-0.5" />
      <div>
        <p class="text-xs font-black text-indigo-800">Huquqlar va Ruxsatlar boshqaruvi</p>
        <p class="text-xs text-indigo-600 mt-0.5 font-medium">
          Har bir xodim uchun tizim bo'limlariga kirish ruxsatini yoqing yoki o'chiring.
          Faqat <b>Tizim Administratori</b> barcha bo'limlarga kirish huquqiga ega (o'zgartirib bo'lmaydi).
        </p>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="staffStore.loading" class="flex flex-col items-center justify-center py-24 space-y-4">
      <Loader2 class="w-10 h-10 text-indigo-500 animate-spin" />
      <p class="text-slate-500 text-xs font-medium animate-pulse">Xodimlar yuklanmoqda...</p>
    </div>

    <!-- Staff permission table -->
    <div v-else class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm">

      <!-- Table header -->
      <div class="border-b border-slate-200 bg-slate-50 px-6 py-4 grid items-center" :style="gridStyle">
        <div class="flex items-center space-x-2">
          <Users class="w-4 h-4 text-slate-400" />
          <span class="text-xs font-black text-slate-600 uppercase tracking-wider">Xodim</span>
        </div>
        <div
          v-for="mod in modules"
          :key="mod.key"
          class="flex flex-col items-center text-center"
        >
          <component :is="mod.icon" class="w-4 h-4 mb-1" :class="mod.color" />
          <span class="text-[10px] font-black text-slate-500 uppercase tracking-wide leading-tight">{{ mod.label }}</span>
        </div>
      </div>

      <!-- Staff rows -->
      <div class="divide-y divide-slate-100">
        <div
          v-for="member in staffStore.staffMembers"
          :key="member.id"
          class="px-6 py-4 grid items-center hover:bg-slate-50/60 transition duration-150"
          :style="gridStyle"
        >
          <!-- Staff info -->
          <div class="flex items-center space-x-3 min-w-0">
            <img
              v-if="member.avatar_url"
              :src="member.avatar_url"
              class="w-9 h-9 rounded-xl object-cover border border-slate-200 shrink-0"
            />
            <div
              v-else
              class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-sm border uppercase shrink-0"
              :class="avatarClass(member.roles?.[0]?.name)"
            >
              {{ member.name.substring(0, 2) }}
            </div>
            <div class="min-w-0 overflow-hidden">
              <h4 class="text-sm font-bold text-slate-900 truncate">{{ member.name }}</h4>
              <span
                class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider border inline-block mt-0.5"
                :class="roleBadgeClass(member.roles?.[0]?.name)"
              >
                {{ member.roles?.[0]?.name || 'Xodim' }}
              </span>
            </div>
          </div>

          <!-- Module toggles -->
          <div
            v-for="mod in modules"
            :key="mod.key"
            class="flex items-center justify-center"
          >
            <!-- Superadmin always has all access (locked) -->
            <div v-if="member.is_superadmin" class="flex flex-col items-center">
              <div class="w-8 h-8 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center" title="Tizim Administratori — o'zgartirib bo'lmaydi">
                <ShieldCheck class="w-4 h-4 text-amber-600" />
              </div>
            </div>

            <!-- Toggle for everyone else (including regular Admin) -->
            <div v-else class="flex flex-col items-center">
              <button
                @click="togglePermission(member.id, mod.key)"
                class="relative w-11 h-6 rounded-full transition-colors duration-200 focus:outline-none"
                :class="getPermission(member.id, mod.key) ? 'bg-indigo-600' : 'bg-slate-200'"
                :title="getPermission(member.id, mod.key) ? 'Ruxsat berilgan — o\'chirish uchun bosing' : 'Ruxsat yo\'q — yoqish uchun bosing'"
              >
                <span
                  class="absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow-sm transition-transform duration-200"
                  :class="getPermission(member.id, mod.key) ? 'translate-x-5' : 'translate-x-0'"
                ></span>
              </button>
              <span
                class="text-[9px] font-bold mt-1 uppercase tracking-wide"
                :class="getPermission(member.id, mod.key) ? 'text-indigo-600' : 'text-slate-400'"
              >
                {{ getPermission(member.id, mod.key) ? 'Ha' : 'Yo\'q' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Empty state -->
        <div v-if="staffStore.staffMembers.length === 0" class="text-center py-20 space-y-3">
          <Users class="w-12 h-12 text-slate-300 mx-auto" />
          <p class="text-slate-500 text-xs font-medium">Xodimlar topilmadi</p>
        </div>
      </div>
    </div>

    <!-- Save notice -->
    <div class="mt-4 flex items-center justify-between px-1">
      <p class="text-xs text-slate-400 font-medium flex items-center space-x-1.5">
        <Save class="w-3.5 h-3.5" />
        <span>O'zgarishlar avtomatik saqlanadi</span>
      </p>
      <button
        @click="resetAll"
        class="text-xs font-bold text-rose-500 hover:text-rose-700 flex items-center space-x-1 transition"
      >
        <RotateCcw class="w-3.5 h-3.5" />
        <span>Barcha huquqlarni tiklash</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import {
  ShieldCheck, Users, Loader2, Save, RotateCcw,
  LayoutDashboard, ShoppingBag, BookOpen, Database,
  Package, Layers, DollarSign, Tag, BarChart3, Settings,
  Sparkles, Smile
} from 'lucide-vue-next';
import { useStaffStore } from '@/stores/staff';

const staffStore = useStaffStore();

// ---- Module definitions ----
const modules = [
  { key: 'dashboard',    label: 'Dashboard',   icon: LayoutDashboard, color: 'text-indigo-500' },
  { key: 'orders',       label: 'Buyurtmalar',  icon: ShoppingBag,     color: 'text-violet-500' },
  { key: 'menu',         label: 'Menyu',         icon: BookOpen,        color: 'text-sky-500'    },
  { key: 'ingredients',  label: 'Masalliqlar',   icon: Database,        color: 'text-teal-500'   },
  { key: 'warehouse',    label: 'Ombor',         icon: Package,         color: 'text-amber-500'  },
  { key: 'tables',       label: 'Stollar',       icon: Layers,          color: 'text-orange-500' },
  { key: 'customers',    label: 'Mijozlar',      icon: Smile,           color: 'text-pink-500'   },
  { key: 'payments',     label: 'To\'lovlar',    icon: DollarSign,      color: 'text-emerald-500'},
  { key: 'discounts',    label: 'Chegirmalar',   icon: Tag,             color: 'text-rose-500'   },
  { key: 'analytics',    label: 'Tahlillar',     icon: BarChart3,       color: 'text-blue-500'   },
  { key: 'settings',     label: 'Sozlamalar',    icon: Settings,        color: 'text-slate-500'  },
];

// Grid layout: staff column + one column per module
const gridStyle = computed(() => ({
  gridTemplateColumns: `minmax(160px, 200px) repeat(${modules.length}, minmax(56px, 1fr))`
}));

// ---- Permission store (localStorage) ----
const STORAGE_KEY = 'vrestro_staff_permissions';

const permissions = ref({});

const loadPermissions = () => {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    permissions.value = raw ? JSON.parse(raw) : {};
  } catch {
    permissions.value = {};
  }
};

const savePermissions = () => {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(permissions.value));
};

// Default permissions per role (when first accessing the page for that user)
const defaultPermissionsForRole = (role) => {
  const defaults = {
    Manager: modules.reduce((acc, m) => { acc[m.key] = true; return acc; }, {}),
    Chef: {
      dashboard: false, orders: true, menu: true, ingredients: true,
      warehouse: false, tables: false, customers: false,
      payments: false, discounts: false, analytics: false, settings: false
    },
    Waiter: {
      dashboard: false, orders: true, menu: true, ingredients: false,
      warehouse: false, tables: true, customers: true,
      payments: false, discounts: false, analytics: false, settings: false
    },
    Cashier: {
      dashboard: false, orders: true, menu: true, ingredients: false,
      warehouse: false, tables: true, customers: true,
      payments: true, discounts: true, analytics: false, settings: false
    },
  };
  return defaults[role] || modules.reduce((acc, m) => { acc[m.key] = false; return acc; }, {});
};

const getPermission = (userId, moduleKey) => {
  if (!permissions.value[userId]) {
    const member = staffStore.staffMembers.find(m => m.id === userId);
    const role = member?.roles?.[0]?.name;
    const defaults = defaultPermissionsForRole(role);
    return !!defaults[moduleKey];
  }
  return !!permissions.value[userId][moduleKey];
};

const togglePermission = (userId, moduleKey) => {
  if (!permissions.value[userId]) {
    const member = staffStore.staffMembers.find(m => m.id === userId);
    permissions.value[userId] = defaultPermissionsForRole(member?.roles?.[0]?.name);
  }
  permissions.value[userId][moduleKey] = !permissions.value[userId][moduleKey];
  savePermissions();
};

const resetAll = () => {
  if (!confirm('Barcha xodimlar uchun huquqlarni standart holga qaytarilsinmi?')) return;
  permissions.value = {};
  // Reinitialize from roles
  staffStore.staffMembers.forEach(member => {
    const role = member.roles?.[0]?.name;
    if (role) {
      permissions.value[member.id] = defaultPermissionsForRole(role);
    }
  });
  savePermissions();
};

// Initialize defaults for any user not yet in storage
const initDefaults = () => {
  let changed = false;
  staffStore.staffMembers.forEach(member => {
    if (!permissions.value[member.id]) {
      const role = member.roles?.[0]?.name;
      if (role) {
        permissions.value[member.id] = defaultPermissionsForRole(role);
        changed = true;
      }
    }
  });
  if (changed) savePermissions();
};

onMounted(async () => {
  loadPermissions();
  if (staffStore.staffMembers.length === 0) {
    await staffStore.fetchStaff({ page: 1 });
  }
  initDefaults();
});

// ---- Style helpers ----
const avatarClass = (role) => {
  if (role === 'Manager') return 'bg-rose-500/10 border-rose-500/20 text-rose-400';
  if (role === 'Chef') return 'bg-amber-500/10 border-amber-500/20 text-amber-400';
  if (role === 'Waiter') return 'bg-sky-500/10 border-sky-500/20 text-sky-400';
  return 'bg-purple-500/10 border-purple-500/20 text-purple-400';
};

const roleBadgeClass = (role) => {
  if (role === 'Manager') return 'bg-rose-500/10 border-rose-500/20 text-rose-400';
  if (role === 'Chef') return 'bg-amber-500/10 border-amber-500/20 text-amber-400';
  if (role === 'Waiter') return 'bg-sky-500/10 border-sky-500/20 text-sky-400';
  return 'bg-purple-500/10 border-purple-500/20 text-purple-400';
};
</script>
