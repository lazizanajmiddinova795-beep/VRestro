<template>
  <div class="flex-grow p-6 flex flex-col h-screen overflow-hidden">
    
    <!-- Top Header -->
    <div class="flex items-center justify-between mb-6 shrink-0">
      <div>
        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-wide">
          Filiallar boshqaruvi
        </h1>
        <p class="text-xs text-slate-500 font-bold mt-0.5">Tizimdagi barcha filiallarni boshqarish</p>
      </div>

      <!-- Add Branch button -->
      <button 
        v-if="authStore.user?.is_superadmin"
        @click="openAddEditModal()"
        class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 font-extrabold text-sm text-white shadow-md shadow-indigo-600/30 hover:scale-[1.01] transition-all flex items-center justify-center space-x-2"
      >
        <Plus class="w-4.5 h-4.5" />
        <span>Yangi filial</span>
      </button>
    </div>

    <!-- Alert if not superadmin -->
    <div v-if="!authStore.user?.is_superadmin" class="flex flex-col items-center justify-center h-full space-y-4">
      <AlertTriangle class="w-12 h-12 text-rose-500" />
      <p class="text-slate-900 font-bold text-lg">Sizda bu sahifaga kirish huquqi yo'q</p>
    </div>

    <template v-else>
      <!-- Loading State -->
      <div v-if="branchStore.loading && branchStore.branches.length === 0" class="flex-grow flex flex-col items-center justify-center space-y-4">
        <Loader2 class="w-10 h-10 text-indigo-600 animate-spin" />
        <p class="text-slate-600 text-xs font-bold animate-pulse">Filiallar yuklanmoqda...</p>
      </div>

      <!-- Branches Grid -->
      <div v-else class="flex-grow overflow-y-auto pr-1">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 pb-24">
          <div 
            v-for="branch in branchStore.branches" 
            :key="branch.id"
            class="bg-white border border-slate-200/80 rounded-3xl p-5 flex flex-col justify-between transition-all duration-300 relative group shadow-sm hover:shadow-md hover:border-indigo-100"
            :class="!branch.is_active && branch.is_active !== undefined ? 'opacity-75 grayscale-[0.2]' : ''"
          >
            <div class="space-y-4">
              <!-- Header -->
              <div class="flex justify-between items-start">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shrink-0">
                    <Building2 class="w-5 h-5" />
                  </div>
                  <div>
                    <h3 class="text-lg font-extrabold text-slate-900 leading-tight">{{ branch.name }}</h3>
                    <div class="mt-1">
                      <span v-if="branch.is_active || branch.is_active === undefined" class="px-2 py-0.5 rounded text-4xs font-extrabold uppercase tracking-wider bg-emerald-50 text-emerald-600 border border-emerald-200">Faol</span>
                      <span v-else class="px-2 py-0.5 rounded text-4xs font-extrabold uppercase tracking-wider bg-rose-50 text-rose-600 border border-rose-200">Nofaol</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Details -->
              <div class="space-y-2 text-xs font-bold text-slate-500 pt-2">
                <div class="flex items-start space-x-2">
                  <MapPin class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" />
                  <span class="line-clamp-2">{{ branch.address || 'Manzil kiritilmagan' }}</span>
                </div>
                <div class="flex items-center space-x-2">
                  <Phone class="w-4 h-4 text-slate-400 shrink-0" />
                  <span>{{ branch.phone || 'Telefon kiritilmagan' }}</span>
                </div>
                <div v-if="branch.manager" class="flex items-center space-x-2">
                  <Users class="w-4 h-4 text-indigo-400 shrink-0" />
                  <span class="text-indigo-600 font-bold">{{ branch.manager.name }}</span>
                </div>
                <div v-else class="flex items-center space-x-2">
                  <Users class="w-4 h-4 text-slate-300 shrink-0" />
                  <span class="italic text-slate-400">Menejer biriktirilmagan</span>
                </div>
              </div>

              <!-- Stats -->
              <div class="grid grid-cols-3 gap-2 pt-3 border-t border-slate-100">
                <div class="flex flex-col items-center justify-center p-2 rounded-xl bg-slate-50 border border-slate-100">
                  <Users class="w-4 h-4 text-sky-500 mb-1" />
                  <span class="text-xs font-extrabold text-slate-700">{{ branch.users_count || 0 }}</span>
                  <span class="text-4xs font-bold text-slate-400 uppercase mt-0.5 text-center">Xodimlar</span>
                </div>
                <div class="flex flex-col items-center justify-center p-2 rounded-xl bg-slate-50 border border-slate-100">
                  <Layers class="w-4 h-4 text-amber-500 mb-1" />
                  <span class="text-xs font-extrabold text-slate-700">{{ branch.tables_count || 0 }}</span>
                  <span class="text-4xs font-bold text-slate-400 uppercase mt-0.5 text-center">Stollar</span>
                </div>
                <div class="flex flex-col items-center justify-center p-2 rounded-xl bg-slate-50 border border-slate-100">
                  <ShoppingBag class="w-4 h-4 text-emerald-500 mb-1" />
                  <span class="text-xs font-extrabold text-slate-700">{{ branch.orders_count || 0 }}</span>
                  <span class="text-4xs font-bold text-slate-400 uppercase mt-0.5 text-center">Buyurtmalar</span>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="border-t border-slate-100 pt-3.5 mt-4 flex items-center justify-end space-x-2">
              <button 
                @click="openAddEditModal(branch)"
                class="p-2 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-900 transition flex items-center justify-center"
                title="Tahrirlash"
              >
                <Pencil class="w-4 h-4" />
              </button>
              <button 
                @click="handleDelete(branch)"
                class="p-2 rounded-xl bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition flex items-center justify-center"
                title="O'chirish"
              >
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
        
        <!-- Empty state -->
        <div v-if="branchStore.branches.length === 0" class="flex flex-col items-center justify-center py-24 space-y-3">
          <Building2 class="w-12 h-12 text-slate-300" />
          <p class="text-slate-500 text-xs font-medium">Filiallar topilmadi</p>
        </div>
      </div>
    </template>

    <!-- MODAL: Add / Edit Branch -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 backdrop-blur-sm bg-slate-900/40 flex items-center justify-center p-6"
      @click.self="showModal = false"
    >
      <div class="w-full max-w-md bg-white border border-slate-200 rounded-3xl p-6 shadow-2xl space-y-5 animate-scaleIn">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
          <h3 class="text-lg font-extrabold text-slate-900">
            {{ editingBranch ? 'Filialni Tahrirlash' : 'Yangi Filial' }}
          </h3>
          <button @click="showModal = false" class="p-1.5 rounded-xl bg-slate-100 text-slate-500 hover:text-slate-900 transition">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="submitForm" class="space-y-4">
          <div class="space-y-1.5">
            <label class="text-3xs text-slate-500 font-extrabold uppercase tracking-wider">Filial nomi *</label>
            <input
              v-model="branchForm.name"
              type="text"
              required
              placeholder="Masalan, Chilonzor filiali..."
              class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500 text-sm text-slate-900 font-bold focus:outline-none transition placeholder-slate-400"
            />
          </div>

          <div class="space-y-1.5">
            <label class="text-3xs text-slate-500 font-extrabold uppercase tracking-wider">Manzil (ixtiyoriy)</label>
            <input
              v-model="branchForm.address"
              type="text"
              placeholder="Masalan, Toshkent sh., Chilonzor tumani..."
              class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500 text-sm text-slate-900 font-bold focus:outline-none transition placeholder-slate-400"
            />
          </div>

          <div class="space-y-1.5">
            <label class="text-3xs text-slate-500 font-extrabold uppercase tracking-wider">Telefon (ixtiyoriy)</label>
            <input
              v-model="branchForm.phone"
              type="text"
              placeholder="+998 90 123 45 67"
              class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500 text-sm text-slate-900 font-bold focus:outline-none transition placeholder-slate-400"
            />
          </div>

          <div class="space-y-1.5">
            <label class="text-3xs text-slate-500 font-extrabold uppercase tracking-wider">Menejer (ixtiyoriy)</label>
            <select
              v-model="branchForm.manager_id"
              class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500 text-sm text-slate-900 font-bold focus:outline-none transition"
            >
              <option :value="null">— Menejer tanlanmagan —</option>
              <option 
                v-for="mgr in allManagerOptions" 
                :key="mgr.id" 
                :value="mgr.id"
              >
                {{ mgr.name }} {{ mgr.phone ? '(' + mgr.phone + ')' : '' }}
              </option>
            </select>
          </div>
          
          <div class="space-y-1.5" v-if="editingBranch">
             <label class="text-3xs text-slate-500 font-extrabold uppercase tracking-wider">Holati</label>
             <div class="flex items-center space-x-3 mt-1">
               <button
                 type="button"
                 @click="branchForm.is_active = !branchForm.is_active"
                 class="w-10 h-6 rounded-full p-0.5 transition-colors duration-200 focus:outline-none relative"
                 :class="branchForm.is_active ? 'bg-indigo-600' : 'bg-slate-300'"
               >
                 <span
                   class="block w-5 h-5 rounded-full bg-white transition-transform duration-200 shadow-sm"
                   :class="branchForm.is_active ? 'translate-x-4' : 'translate-x-0'"
                 ></span>
               </button>
               <span class="text-xs font-extrabold" :class="branchForm.is_active ? 'text-indigo-600' : 'text-slate-500'">
                 {{ branchForm.is_active ? 'Faol' : 'Nofaol' }}
               </span>
             </div>
          </div>

          <div class="flex justify-end space-x-2 pt-4 border-t border-slate-100">
            <button type="button" @click="showModal = false" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-extrabold text-slate-700 transition">
              Bekor qilish
            </button>
            <button
              type="submit"
              class="px-5 py-2.5 bg-gradient-to-r from-violet-600 to-indigo-600 hover:opacity-90 text-white rounded-xl text-xs font-extrabold shadow-md shadow-indigo-600/30 transition-all active:scale-95"
            >
              Saqlash
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
  Building2, Plus, Pencil, Trash2, MapPin, Phone, Users, Layers, ShoppingBag, Loader2, AlertTriangle, X
} from 'lucide-vue-next';
import { useBranchStore } from '@/stores/branch';
import { useAuthStore } from '@/stores/auth';
import { useSettingsStore } from '@/stores/settings';

const branchStore = useBranchStore();
const authStore = useAuthStore();
const settingsStore = useSettingsStore();

const showModal = ref(false);
const editingBranch = ref(null);
const branchForm = ref({
  name: '',
  address: '',
  phone: '',
  is_active: true,
  manager_id: null
});

const availableManagers = ref([]);

const fetchAvailableManagers = async () => {
    try {
        const response = await fetch('/api/branches/available-managers', {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${authStore.token}`
            }
        });
        if (response.ok) {
            availableManagers.value = await response.json();
        }
    } catch (err) {
        console.error('Failed to fetch managers:', err);
    }
};

const allManagerOptions = computed(() => {
    const managers = [...availableManagers.value];
    if (editingBranch.value?.manager) {
        const existing = managers.find(m => m.id === editingBranch.value.manager.id);
        if (!existing) {
            managers.unshift(editingBranch.value.manager);
        }
    }
    return managers;
});

onMounted(async () => {
  if (authStore.user?.is_superadmin) {
    await branchStore.fetchBranches();
  }
});

const openAddEditModal = (branch = null) => {
  editingBranch.value = branch;
  if (branch) {
    branchForm.value = {
      name: branch.name,
      address: branch.address || '',
      phone: branch.phone || '',
      is_active: branch.is_active === 1 || branch.is_active === true,
      manager_id: branch.manager?.id || null
    };
  } else {
    branchForm.value = {
      name: '',
      address: '',
      phone: '',
      is_active: true,
      manager_id: null
    };
  }
  showModal.value = true;
  fetchAvailableManagers();
};

const submitForm = async () => {
  if (!branchForm.value.name.trim()) {
    alert("Filial nomini kiritish majburiy.");
    return;
  }
  
  try {
    const payload = {
      name: branchForm.value.name,
      address: branchForm.value.address,
      phone: branchForm.value.phone,
      is_active: branchForm.value.is_active ? 1 : 0,
      manager_id: branchForm.value.manager_id
    };

    if (editingBranch.value) {
      await branchStore.updateBranch(editingBranch.value.id, payload);
    } else {
      await branchStore.createBranch(payload);
    }
    showModal.value = false;
  } catch (err) {
    alert(err.message || "Xatolik yuz berdi");
  }
};

const handleDelete = async (branch) => {
  if (!confirm(`"${branch.name}" filialini o'chirib tashlamoqchimisiz?`)) return;
  try {
    await branchStore.deleteBranch(branch.id);
  } catch (err) {
    alert(err.message || "Xatolik yuz berdi");
  }
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
