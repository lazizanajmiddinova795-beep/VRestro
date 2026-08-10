<template>
  <div class="flex-grow p-6 flex flex-col h-screen overflow-hidden bg-slate-50">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 shrink-0 gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-wide">
          Xodimlar boshqaruvi
        </h1>
        <p class="text-xs text-slate-500 font-bold mt-0.5">Tizimdagi barcha xodimlar va menejerlar</p>
      </div>

      <div class="flex items-center gap-3 flex-wrap">
        <!-- Tab switcher -->
        <div class="flex items-center bg-slate-100 p-1 rounded-2xl border border-slate-200">
          <button
            @click="activeTab = 'all'"
            class="px-4 py-2 rounded-xl text-xs font-bold transition duration-200 flex items-center space-x-1.5"
            :class="activeTab === 'all' ? 'bg-white text-indigo-600 shadow-sm border border-slate-200' : 'text-slate-500 hover:text-slate-800'"
          >
            <Users class="w-4 h-4" />
            <span>Barcha Xodimlar</span>
          </button>
          <button
            @click="activeTab = 'managers'"
            class="px-4 py-2 rounded-xl text-xs font-bold transition duration-200 flex items-center space-x-1.5"
            :class="activeTab === 'managers' ? 'bg-white text-indigo-600 shadow-sm border border-slate-200' : 'text-slate-500 hover:text-slate-800'"
          >
            <Shield class="w-4 h-4" />
            <span>Menejerlar</span>
          </button>
        </div>

        <!-- Add Manager button (only on managers tab) -->
        <button
          v-if="activeTab === 'managers'"
          @click="openManagerModal()"
          class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 font-semibold text-sm text-white shadow-md shadow-indigo-600/20 hover:shadow-indigo-600/30 hover:scale-[1.01] transition-all flex items-center justify-center space-x-2"
        >
          <UserPlus class="w-4.5 h-4.5" />
          <span>Yangi Menejer</span>
        </button>
      </div>
    </div>

    <!-- Filters Row -->
    <div class="bg-white border border-slate-200 shadow-sm rounded-3xl p-5 mb-6 grid grid-cols-1 sm:grid-cols-4 gap-4 shrink-0">
      <!-- Search Input -->
      <div class="relative">
        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
          <Search class="w-4 h-4" />
        </span>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Ism bo'yicha qidiruv..."
          class="w-full pl-10 pr-4 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500 text-xs placeholder-slate-400 text-slate-900 focus:outline-none transition"
        />
      </div>

      <!-- Branch Filter -->
      <div>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
            <Building2 class="w-4 h-4" />
          </span>
          <select
            v-model="filterBranch"
            class="w-full pl-10 pr-4 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 transition appearance-none"
          >
            <option value="">Barcha filiallar</option>
            <option v-for="branch in branchStore.branches" :key="branch.id" :value="branch.id">
              {{ branch.name }}
            </option>
          </select>
        </div>
      </div>

      <!-- Role Filter (Only for 'all' tab) -->
      <div v-if="activeTab === 'all'">
        <div class="relative">
          <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
            <Filter class="w-4 h-4" />
          </span>
          <select
            v-model="filterRole"
            class="w-full pl-10 pr-4 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 transition appearance-none"
          >
            <option value="">Barcha rollar</option>
            <option value="Manager">Menejer</option>
            <option value="Chef">Oshpaz</option>
            <option value="Waiter">Ofitsiant</option>
            <option value="Cashier">Kassir</option>
          </select>
        </div>
      </div>
      <div v-else></div> <!-- Placeholder to maintain grid spacing -->

      <!-- Total Indicator -->
      <div class="flex items-center justify-end px-2">
        <span class="text-xxs font-bold text-slate-500 uppercase tracking-wider">Topildi: {{ displayedStaff.length }} ta</span>
      </div>
    </div>

    <!-- Main Content Area -->
    <div v-if="staffStore.loading && staffStore.staffMembers.length === 0" class="flex-grow flex flex-col items-center justify-center space-y-4">
      <Loader2 class="w-10 h-10 text-indigo-500 animate-spin" />
      <p class="text-slate-500 text-xs font-medium animate-pulse">Ma'lumotlar yuklanmoqda...</p>
    </div>

    <div v-else class="flex-grow overflow-y-auto bg-white border border-slate-200 rounded-3xl shadow-sm">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50 border-b border-slate-200 text-xs font-extrabold text-slate-500 uppercase tracking-wider">
            <th class="p-4 rounded-tl-3xl">Xodim</th>
            <th class="p-4" v-if="activeTab === 'all'">Rol</th>
            <th class="p-4">Filial</th>
            <th class="p-4">Telefon</th>
            <th class="p-4">Holat</th>
            <th class="p-4 rounded-tr-3xl text-right">Amallar</th>
          </tr>
        </thead>
        <tbody class="text-sm">
          <tr v-if="displayedStaff.length === 0">
            <td colspan="6" class="p-8 text-center text-slate-500 font-medium">Ma'lumot topilmadi</td>
          </tr>
          <tr 
            v-for="member in displayedStaff" 
            :key="member.id"
            class="border-b border-slate-100 hover:bg-slate-50 transition-colors group"
          >
            <td class="p-4">
              <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 font-bold shrink-0 uppercase">
                  {{ member.name.substring(0, 2) }}
                </div>
                <div>
                  <div class="font-bold text-slate-900">{{ member.name }}</div>
                  <div class="text-xs text-slate-500">{{ member.login }}</div>
                </div>
              </div>
            </td>
            <td class="p-4" v-if="activeTab === 'all'">
              <span 
                class="px-2.5 py-1 rounded-lg text-xs font-bold border inline-block"
                :class="roleBadgeClass(member.roles?.[0]?.name)"
              >
                {{ member.roles?.[0]?.name || 'Noma\'lum' }}
              </span>
            </td>
            <td class="p-4 text-slate-600 font-medium text-xs">
              {{ getBranchName(member.branch_id) }}
            </td>
            <td class="p-4 text-slate-600 font-medium text-xs">
              {{ member.phone || 'Kiritilmagan' }}
            </td>
            <td class="p-4">
              <span 
                class="px-2.5 py-1 rounded-lg text-xs font-bold border inline-block"
                :class="member.status === 'active' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-rose-50 text-rose-600 border-rose-200'"
              >
                {{ member.status === 'active' ? 'Faol' : 'Nofaol' }}
              </span>
            </td>
            <td class="p-4 text-right">
              <div class="flex justify-end space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                <!-- Action for Tab 1 -->
                <button 
                  v-if="activeTab === 'all'"
                  @click="openDetailModal(member)"
                  class="p-2 rounded-xl bg-slate-100 text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 transition"
                  title="Ko'rish"
                >
                  <Eye class="w-4 h-4" />
                </button>
                
                <!-- Actions for Tab 2 -->
                <template v-if="activeTab === 'managers'">
                  <button 
                    @click="openManagerModal(member)"
                    class="p-2 rounded-xl bg-slate-100 text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 transition"
                    title="Tahrirlash"
                  >
                    <Pencil class="w-4 h-4" />
                  </button>
                  <button 
                    @click="deleteManager(member)"
                    class="p-2 rounded-xl bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition"
                    title="O'chirish"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </template>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Detail Modal (Tab 1) -->
    <div
      v-if="showDetailModal && selectedStaff"
      class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[9999] flex items-center justify-center p-4"
      @click.self="showDetailModal = false"
    >
      <div class="bg-white text-slate-900 w-full max-w-lg rounded-3xl p-6 shadow-2xl border border-slate-200 animate-in fade-in zoom-in-95 duration-200">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-xl font-bold">Xodim Ma'lumotlari</h3>
          <button @click="showDetailModal = false" class="bg-slate-100 hover:bg-slate-200 text-slate-500 p-2 rounded-full transition-colors">
            <X class="w-5 h-5" />
          </button>
        </div>

        <div class="space-y-4">
          <div class="flex items-center space-x-4 pb-4 border-b border-slate-100">
            <div class="w-16 h-16 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xl uppercase">
              {{ selectedStaff.name.substring(0, 2) }}
            </div>
            <div>
              <div class="font-bold text-lg text-slate-900">{{ selectedStaff.name }}</div>
              <span 
                class="px-2 py-0.5 rounded text-xs font-bold border inline-block mt-1"
                :class="roleBadgeClass(selectedStaff.roles?.[0]?.name)"
              >
                {{ selectedStaff.roles?.[0]?.name || 'Noma\'lum' }}
              </span>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-y-4 gap-x-6 text-sm">
            <div>
              <div class="text-xs font-bold text-slate-400 mb-1">Login</div>
              <div class="font-medium text-slate-700">{{ selectedStaff.login }}</div>
            </div>
            <div>
              <div class="text-xs font-bold text-slate-400 mb-1">Telefon</div>
              <div class="font-medium text-slate-700">{{ selectedStaff.phone || '-' }}</div>
            </div>
            <div>
              <div class="text-xs font-bold text-slate-400 mb-1">Email</div>
              <div class="font-medium text-slate-700">{{ selectedStaff.email || '-' }}</div>
            </div>
            <div>
              <div class="text-xs font-bold text-slate-400 mb-1">Passport</div>
              <div class="font-medium text-slate-700">{{ selectedStaff.passport_number || '-' }}</div>
            </div>
            <div>
              <div class="text-xs font-bold text-slate-400 mb-1">Tug'ilgan sana</div>
              <div class="font-medium text-slate-700">{{ selectedStaff.birth_date || '-' }}</div>
            </div>
            <div>
              <div class="text-xs font-bold text-slate-400 mb-1">Smena vaqti</div>
              <div class="font-medium text-slate-700">{{ selectedStaff.shift_hours || '-' }}</div>
            </div>
            <div class="col-span-2">
              <div class="text-xs font-bold text-slate-400 mb-1">Manzil</div>
              <div class="font-medium text-slate-700">{{ selectedStaff.address || '-' }}</div>
            </div>
            <div class="col-span-2">
              <div class="text-xs font-bold text-slate-400 mb-1">Filial</div>
              <div class="font-medium text-slate-700">{{ getBranchName(selectedStaff.branch_id) }}</div>
            </div>
          </div>
        </div>
        
        <div class="mt-6 flex justify-end">
          <button @click="showDetailModal = false" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 font-bold text-sm text-slate-700 transition">
            Yopish
          </button>
        </div>
      </div>
    </div>

    <!-- Manager Form Modal (Tab 2) -->
    <div
      v-if="showManagerModal"
      class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[9999] flex items-center justify-center p-4"
      @click.self="showManagerModal = false"
    >
      <div class="bg-white text-slate-900 w-full max-w-lg rounded-3xl p-6 shadow-2xl border border-slate-200 animate-in fade-in zoom-in-95 duration-200">
        <div class="flex justify-between items-center border-b border-slate-100 pb-4 mb-4">
          <h3 class="font-bold text-xl">
            {{ editingManager ? 'Menejerni Tahrirlash' : 'Yangi Menejer' }}
          </h3>
          <button @click="showManagerModal = false" class="bg-slate-100 hover:bg-slate-200 text-slate-500 p-2 rounded-full transition-colors">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitManagerForm" class="space-y-4 max-h-[70vh] overflow-y-auto pr-2">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            
            <div class="sm:col-span-2">
              <label class="text-xs font-extrabold text-slate-500 uppercase tracking-wider mb-1.5 block">F.I.SH *</label>
              <input type="text" v-model="managerForm.name" required placeholder="Ism familiya..."
                     class="w-full bg-slate-50 border border-slate-200 focus:border-indigo-500 text-slate-900 font-bold px-4 py-2.5 rounded-xl outline-none transition placeholder-slate-400" />
            </div>

            <div>
              <label class="text-xs font-extrabold text-slate-500 uppercase tracking-wider mb-1.5 block">Login *</label>
              <input type="text" v-model="managerForm.login" required placeholder="Login..."
                     class="w-full bg-slate-50 border border-slate-200 focus:border-indigo-500 text-slate-900 font-bold px-4 py-2.5 rounded-xl outline-none transition placeholder-slate-400" />
            </div>

            <div>
              <label class="text-xs font-extrabold text-slate-500 uppercase tracking-wider mb-1.5 block">Parol {{ !editingManager ? '*' : '(Ixtiyoriy)' }}</label>
              <input type="password" v-model="managerForm.password" :required="!editingManager" placeholder="Parol..."
                     class="w-full bg-slate-50 border border-slate-200 focus:border-indigo-500 text-slate-900 font-bold px-4 py-2.5 rounded-xl outline-none transition placeholder-slate-400" />
            </div>

            <div>
              <label class="text-xs font-extrabold text-slate-500 uppercase tracking-wider mb-1.5 block">Telefon</label>
              <input type="text" v-model="managerForm.phone" placeholder="+998..."
                     class="w-full bg-slate-50 border border-slate-200 focus:border-indigo-500 text-slate-900 font-bold px-4 py-2.5 rounded-xl outline-none transition placeholder-slate-400" />
            </div>

            <div>
              <label class="text-xs font-extrabold text-slate-500 uppercase tracking-wider mb-1.5 block">Email</label>
              <input type="email" v-model="managerForm.email" placeholder="email@..."
                     class="w-full bg-slate-50 border border-slate-200 focus:border-indigo-500 text-slate-900 font-bold px-4 py-2.5 rounded-xl outline-none transition placeholder-slate-400" />
            </div>

            <div class="sm:col-span-2">
              <label class="text-xs font-extrabold text-slate-500 uppercase tracking-wider mb-1.5 block">Xodim Roli *</label>
              <div class="relative">
                <select v-model="managerForm.role" required
                        class="w-full bg-slate-50 border border-slate-200 focus:border-indigo-500 text-slate-900 font-bold px-4 py-2.5 rounded-xl outline-none transition appearance-none">
                  <option value="Admin">Tizim administratori (Admin - Barcha filiallar nazorati)</option>
                  <option value="Manager">Filial menejeri (Manager - Muayyan filial)</option>
                </select>
                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                </div>
              </div>
            </div>

            <div class="sm:col-span-2">
              <label class="text-xs font-extrabold text-slate-500 uppercase tracking-wider mb-1.5 block">Biriktirilgan Filial {{ managerForm.role === 'Admin' ? '(Ixtiyoriy)' : '*' }}</label>
              <div class="relative">
                <select v-model="managerForm.branch_id"
                        class="w-full bg-slate-50 border border-slate-200 focus:border-indigo-500 text-slate-900 font-bold px-4 py-2.5 rounded-xl outline-none transition appearance-none">
                  <option value="">Filialni tanlang...</option>
                  <option v-for="branch in branchStore.branches" :key="branch.id" :value="branch.id">
                    {{ branch.name }}
                  </option>
                </select>
                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                </div>
              </div>
            </div>

            <div class="sm:col-span-2 flex items-center justify-between bg-slate-50 p-4 rounded-xl border border-slate-200 mt-2">
              <span class="text-sm font-extrabold text-slate-700">Holati (Faol/Nofaol)</span>
              <button
                type="button"
                @click="managerForm.status = managerForm.status === 'active' ? 'inactive' : 'active'"
                class="w-12 h-6 rounded-full p-1 transition-colors duration-200 focus:outline-none relative"
                :class="managerForm.status === 'active' ? 'bg-indigo-600' : 'bg-slate-300'"
              >
                <span
                  class="block w-4 h-4 rounded-full bg-white shadow-sm transition-transform duration-200"
                  :class="managerForm.status === 'active' ? 'translate-x-6' : 'translate-x-0'"
                ></span>
              </button>
            </div>

          </div>

          <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100 mt-6">
            <button type="button" @click="showManagerModal = false" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 font-extrabold text-xs text-slate-700 transition">
              Bekor qilish
            </button>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 text-white font-black text-xs shadow-md shadow-indigo-600/30 hover:opacity-90 transition active:scale-95">
              Saqlash
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { 
  Users, UserPlus, Building2, Search, Eye, Pencil, Trash2, X, Loader2, Shield, Filter 
} from 'lucide-vue-next';
import { useStaffStore } from '@/stores/staff';
import { useBranchStore } from '@/stores/branch';

const staffStore = useStaffStore();
const branchStore = useBranchStore();

const activeTab = ref('all');

const searchQuery = ref('');
const filterBranch = ref('');
const filterRole = ref('');

onMounted(async () => {
  await branchStore.fetchBranches();
  await loadStaff();
});

const loadStaff = async () => {
  // We fetch a wide array of staff since we want to filter them locally
  // In a real huge app we'd triggerFetch on every input change, 
  // but we'll fetch once and filter with computed for immediate responsiveness
  await staffStore.fetchStaff({ page: 1, limit: 1000 }); // Assuming limit can get all, or just fetch
};

// Filtering logic
const displayedStaff = computed(() => {
  let staff = staffStore.staffMembers || [];
  
  // Filter out superadmins as requested
  staff = staff.filter(s => !s.is_superadmin);

  // Tab filter
  if (activeTab.value === 'managers') {
    staff = staff.filter(s => s.roles?.[0]?.name === 'Manager');
  } else if (filterRole.value) {
    staff = staff.filter(s => s.roles?.[0]?.name === filterRole.value);
  }

  // Branch filter
  if (filterBranch.value) {
    staff = staff.filter(s => s.branch_id === filterBranch.value);
  }

  // Search filter
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    staff = staff.filter(s => s.name.toLowerCase().includes(q));
  }

  return staff;
});

const getBranchName = (branchId) => {
  const branch = branchStore.branches.find(b => b.id === branchId);
  return branch ? branch.name : 'Biriktirilmagan';
};

const roleBadgeClass = (role) => {
  if (role === 'Manager') return 'bg-rose-50 border-rose-200 text-rose-600';
  if (role === 'Chef') return 'bg-amber-50 border-amber-200 text-amber-600';
  if (role === 'Waiter') return 'bg-sky-50 border-sky-200 text-sky-600';
  return 'bg-purple-50 border-purple-200 text-purple-600';
};

// Detail Modal (All Tab)
const showDetailModal = ref(false);
const selectedStaff = ref(null);

const openDetailModal = (member) => {
  selectedStaff.value = member;
  showDetailModal.value = true;
};

// Manager Modal (Managers Tab)
const showManagerModal = ref(false);
const editingManager = ref(null);
const managerForm = ref({
  name: '',
  login: '',
  password: '',
  phone: '',
  email: '',
  role: 'Manager',
  branch_id: '',
  status: 'active'
});

const openManagerModal = (manager = null) => {
  editingManager.value = manager;
  if (manager) {
    const isAdmin = manager.is_superadmin || manager.roles?.[0]?.name === 'Admin';
    managerForm.value = {
      name: manager.name,
      login: manager.login,
      password: '',
      phone: manager.phone || '',
      email: manager.email || '',
      role: isAdmin ? 'Admin' : 'Manager',
      branch_id: manager.branch_id || '',
      status: manager.status || 'active'
    };
  } else {
    managerForm.value = {
      name: '',
      login: '',
      password: '',
      phone: '',
      email: '',
      role: 'Manager',
      branch_id: '',
      status: 'active'
    };
  }
  showManagerModal.value = true;
};

const submitManagerForm = async () => {
  try {
    const formData = new FormData();
    formData.append('name', managerForm.value.name);
    formData.append('login', managerForm.value.login);
    if (managerForm.value.password) {
      formData.append('password', managerForm.value.password);
    }
    formData.append('phone', managerForm.value.phone || '');
    formData.append('email', managerForm.value.email || '');
    formData.append('role', managerForm.value.role || 'Manager');
    formData.append('branch_id', managerForm.value.branch_id || '');
    formData.append('status', managerForm.value.status);

    if (editingManager.value) {
      formData.append('_method', 'PUT');
      await staffStore.updateStaff(editingManager.value.id, formData);
    } else {
      await staffStore.createStaff(formData);
    }
    
    showManagerModal.value = false;
    await loadStaff();
  } catch (err) {
    if (err.errors) {
      alert(Object.values(err.errors).flat().join('\n'));
    } else {
      alert(err.message || 'Xatolik yuz berdi');
    }
  }
};

const deleteManager = async (manager) => {
  if (confirm(`Rostdan ham ${manager.name} o'chirilsinmi?`)) {
    try {
      await staffStore.deleteStaff(manager.id);
      await loadStaff();
    } catch (err) {
      alert(err.message || 'Xatolik yuz berdi');
    }
  }
};
</script>
