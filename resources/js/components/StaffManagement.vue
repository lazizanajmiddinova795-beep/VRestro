<template>
  <div class="flex-grow p-6 flex flex-col h-screen overflow-hidden">
    
    <!-- Top Header -->
    <div class="flex items-center justify-between mb-6 shrink-0">
      <div>
        <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-white via-indigo-200 to-indigo-400 tracking-wide">
          Xodimlar Tizimi
        </h1>
        <p class="text-xs text-slate-400">Tizim xodimlarini ro'yxatga olish, rollarini Spatie orqali boshqarish va kirish ruxsatnomalarini nazorat qilish</p>
      </div>

      <!-- Add Staff button -->
      <button 
        @click="openAddEditModal()"
        class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 font-semibold text-sm text-white shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/50 hover:scale-[1.01] transition-all flex items-center justify-center space-x-2"
      >
        <UserPlus class="w-4.5 h-4.5" />
        <span>Yangi Xodim Qo'shish</span>
      </button>
    </div>

    <!-- Filters Row -->
    <div class="backdrop-blur-md bg-slate-900/40 border border-white/5 rounded-3xl p-5 mb-6 grid grid-cols-1 sm:grid-cols-4 gap-4 shrink-0">
      <!-- Search Input -->
      <div class="relative">
        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
          <Search class="w-4 h-4" />
        </span>
        <input 
          v-model="searchQuery"
          type="text" 
          placeholder="Ism, login yoki tel..."
          @input="triggerFetch"
          class="w-full pl-10 pr-4 py-2 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-xs placeholder-slate-500 text-white focus:outline-none transition"
        />
      </div>

      <!-- Role Filter -->
      <div>
        <select 
          v-model="filterRole" 
          @change="triggerFetch"
          class="w-full px-3.5 py-2 rounded-xl bg-slate-950/40 border border-white/10 text-xs text-white focus:outline-none focus:border-indigo-500 transition"
        >
          <option value="">Barcha Rollar</option>
          <option value="Admin">Admin</option>
          <option value="Chef">Oshpaz (Chef)</option>
          <option value="Waiter">Ofitsiant (Waiter)</option>
          <option value="Cashier">Kassir (Cashier)</option>
        </select>
      </div>

      <!-- Status Filter -->
      <div>
        <select 
          v-model="filterStatus" 
          @change="triggerFetch"
          class="w-full px-3.5 py-2 rounded-xl bg-slate-950/40 border border-white/10 text-xs text-white focus:outline-none focus:border-indigo-500 transition"
        >
          <option value="">Barcha Holatlar</option>
          <option value="active">Faol (Active)</option>
          <option value="inactive">Nofaol (Inactive)</option>
        </select>
      </div>

      <!-- Total Indicator -->
      <div class="flex items-center justify-end px-2">
        <span class="text-xxs font-bold text-slate-500 uppercase tracking-wider">Topildi: {{ staffStore.pagination.total }} ta</span>
      </div>
    </div>

    <!-- Staff Cards Grid -->
    <div v-if="staffStore.loading && staffStore.staffMembers.length === 0" class="flex-grow flex flex-col items-center justify-center space-y-4">
      <Loader2 class="w-10 h-10 text-indigo-500 animate-spin" />
      <p class="text-slate-400 text-xs font-medium animate-pulse">Xodimlar ro'yxati yuklanmoqda...</p>
    </div>

    <div v-else class="flex-grow overflow-y-auto pr-1">
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 pb-24">
        
        <!-- Employee Card -->
        <div 
          v-for="member in staffStore.staffMembers" 
          :key="member.id"
          class="backdrop-blur-md bg-slate-900/30 border rounded-3xl p-5 flex flex-col justify-between h-56 transition-all duration-300 relative group"
          :class="member.status === 'inactive' ? 'border-white/5 opacity-60' : 'border-indigo-500/10 hover:border-indigo-500/30'"
        >
          <div class="space-y-4">
            <!-- Header Row -->
            <div class="flex items-center space-x-3">
              <!-- Avatar Circle -->
              <div 
                class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm border uppercase"
                :class="avatarClass(member.roles?.[0]?.name)"
              >
                {{ member.name.substring(0, 2) }}
              </div>

              <!-- Name & Role -->
              <div class="overflow-hidden">
                <h3 class="text-sm font-bold text-white tracking-wide truncate">{{ member.name }}</h3>
                <span 
                  class="px-2 py-0.5 rounded text-4xs font-bold uppercase tracking-wider border mt-1 inline-block"
                  :class="roleBadgeClass(member.roles?.[0]?.name)"
                >
                  {{ member.roles?.[0]?.name || 'Xodim' }}
                </span>
              </div>
            </div>

            <!-- Details -->
            <div class="space-y-2.5 text-xxs text-slate-400">
              <div class="flex items-center space-x-2">
                <Phone class="w-3.5 h-3.5 text-slate-500" />
                <span>Tel: {{ member.phone || 'Kiritilmagan' }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <Clock class="w-3.5 h-3.5 text-slate-500" />
                <span>Ish vaqti: {{ member.shift_hours || 'Smena kiritilmagan' }}</span>
              </div>
              <div class="flex items-center space-x-2 font-mono">
                <KeyRound class="w-3.5 h-3.5 text-slate-500" />
                <span>Login: {{ member.login }}</span>
              </div>
            </div>
          </div>

          <!-- Footer Settings -->
          <div class="border-t border-white/5 pt-3.5 mt-4 flex items-center justify-between">
            <!-- Active switch -->
            <div class="flex items-center space-x-2">
              <button 
                @click="handleToggleStatus(member)"
                class="w-8 h-4.5 rounded-full p-0.5 transition-colors duration-200 focus:outline-none relative"
                :class="member.status === 'active' ? 'bg-indigo-600' : 'bg-slate-800'"
                :title="member.status === 'active' ? 'Faol (Bloklash)' : 'Nofaol (Aktivlashtirish)'"
              >
                <span 
                  class="block w-3.5 h-3.5 rounded-full bg-white transition-transform duration-200"
                  :class="member.status === 'active' ? 'translate-x-3.5' : 'translate-x-0'"
                ></span>
              </button>
              <span class="text-4xs uppercase tracking-wider font-bold" :class="member.status === 'active' ? 'text-indigo-400' : 'text-slate-500'">
                {{ member.status === 'active' ? 'faol' : 'blok' }}
              </span>
            </div>

            <!-- Actions -->
            <div class="flex items-center space-x-1.5">
              <button 
                @click="openAddEditModal(member)"
                class="p-1.5 rounded bg-white/5 text-slate-400 hover:bg-white/10 hover:text-white transition"
                title="Tahrirlash"
              >
                <Edit3 class="w-3.5 h-3.5" />
              </button>
              <button 
                @click="handleDelete(member)"
                class="p-1.5 rounded bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-white transition"
                title="O'chirish"
              >
                <Trash2 class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>

        </div>

      </div>

      <!-- Empty state -->
      <div v-if="staffStore.staffMembers.length === 0" class="flex flex-col items-center justify-center py-24 space-y-3">
        <Users class="w-12 h-12 text-slate-600" />
        <p class="text-slate-400 text-xs font-medium">Xodimlar topilmadi</p>
      </div>
    </div>

    <!-- MODAL: Add / Edit Employee -->
    <div 
      v-if="showModal" 
      class="fixed inset-0 z-50 backdrop-blur-md bg-black/60 flex items-center justify-center p-6"
      @click.self="showModal = false"
    >
      <div class="w-full max-w-sm backdrop-blur-xl bg-slate-900/80 border border-white/10 rounded-3xl p-6 shadow-2xl space-y-5 animate-scaleIn">
        <div class="flex justify-between items-center border-b border-white/5 pb-3">
          <h3 class="text-base font-bold text-white">
            {{ editingStaff ? 'Xodimni Tahrirlash' : 'Yangi Xodim Qo\'shish' }}
          </h3>
          <button @click="showModal = false" class="p-1 rounded-lg bg-white/5 text-slate-400 hover:text-white transition">
            <X class="w-4 h-4" />
          </button>
        </div>

        <div class="space-y-4">
          <!-- Full name -->
          <div class="space-y-1.5">
            <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">To'liq ism-familiyasi *</label>
            <input 
              v-model="staffForm.name"
              type="text" 
              placeholder="Masalan, Alisher Qodirov..."
              class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
            />
          </div>

          <!-- Phone number -->
          <div class="space-y-1.5">
            <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Telefon raqami *</label>
            <input 
              v-model="staffForm.phone"
              type="text" 
              placeholder="Masalan, +998901234567..."
              class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
            />
          </div>

          <!-- Login/Username -->
          <div class="space-y-1.5">
            <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Tizimga kirish logini *</label>
            <input 
              v-model="staffForm.login"
              type="text" 
              placeholder="Masalan, alisher99..."
              class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
            />
          </div>

          <!-- Password -->
          <div class="space-y-1.5">
            <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">
              Tizim paroli {{ editingStaff ? '(Bo\'sh qolsa o\'zgarmaydi)' : '*' }}
            </label>
            <input 
              v-model="staffForm.password"
              type="password" 
              placeholder="Kamida 4 belgili parol..."
              class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
            />
          </div>

          <!-- Shift Hours -->
          <div class="space-y-1.5">
            <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Ish vaqti / Smena</label>
            <input 
              v-model="staffForm.shift_hours"
              type="text" 
              placeholder="Masalan, 09:00 - 18:00..."
              class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
            />
          </div>

          <!-- Spatie Role selection -->
          <div class="space-y-1.5">
            <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Tizimdagi lavozimi (Role) *</label>
            <select 
              v-model="staffForm.role"
              class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
            >
              <option value="Admin">Administrator</option>
              <option value="Chef">Chef (Oshpaz)</option>
              <option value="Waiter">Waiter (Ofitsiant)</option>
              <option value="Cashier">Cashier (Kassir)</option>
            </select>
          </div>

          <!-- Status selection (only create) -->
          <div class="space-y-1.5" v-if="!editingStaff">
            <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Faollik holati *</label>
            <select 
              v-model="staffForm.status"
              class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
            >
              <option value="active">Faol (Active)</option>
              <option value="inactive">Nofaol (Inactive)</option>
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
import { ref, onMounted } from 'vue';
import { 
  UserPlus, Search, Phone, Clock, KeyRound, Edit3, Trash2, X, Loader2, Users
} from 'lucide-vue-next';
import { useStaffStore } from '@/stores/staff';

const staffStore = useStaffStore();

// Search parameters
const searchQuery = ref('');
const filterRole = ref('');
const filterStatus = ref('');

// Onboarding modal states
const showModal = ref(false);
const editingStaff = ref(null);
const staffForm = ref({
  name: '',
  phone: '',
  login: '',
  password: '',
  shift_hours: '',
  role: 'Waiter',
  status: 'active'
});

// Lifecycle
onMounted(async () => {
  await triggerFetch();
});

// Fetch action
const triggerFetch = async () => {
  await staffStore.fetchStaff({
    status: filterStatus.value,
    role: filterRole.value,
    search: searchQuery.value,
    page: 1
  });
};

const openAddEditModal = (member = null) => {
  editingStaff.value = member;
  staffForm.value = member ? {
    name: member.name,
    phone: member.phone || '',
    login: member.login,
    password: '',
    shift_hours: member.shift_hours || '',
    role: member.roles?.[0]?.name || 'Waiter',
    status: member.status
  } : {
    name: '',
    phone: '',
    login: '',
    password: '',
    shift_hours: '',
    role: 'Waiter',
    status: 'active'
  };
  showModal.value = true;
};

const submitForm = async () => {
  if (!staffForm.value.name.trim() || !staffForm.value.phone.trim() || !staffForm.value.login.trim()) {
    alert('Barcha majburiy maydonlarni to\'ldiring.');
    return;
  }
  if (!editingStaff.value && !staffForm.value.password.trim()) {
    alert('Yangi xodim uchun parol kiritilishi shart.');
    return;
  }

  try {
    if (editingStaff.value) {
      await staffStore.updateStaff(editingStaff.value.id, staffForm.value);
    } else {
      await staffStore.createStaff(staffForm.value);
    }
    showModal.value = false;
  } catch (err) {
    alert(err.message);
  }
};

const handleToggleStatus = async (member) => {
  try {
    await staffStore.toggleStaffStatus(member.id);
  } catch (err) {
    alert(err.message);
  }
};

const handleDelete = async (member) => {
  if (!confirm(`"${member.name}" xodimini tizimdan butunlay o'chirmoqchimisiz?`)) return;
  try {
    await staffStore.deleteStaff(member.id);
  } catch (err) {
    alert(err.message);
  }
};

// Styling helper functions
const avatarClass = (role) => {
  if (role === 'Admin') return 'bg-rose-500/10 border-rose-500/20 text-rose-400';
  if (role === 'Chef') return 'bg-amber-500/10 border-amber-500/20 text-amber-400';
  if (role === 'Waiter') return 'bg-sky-500/10 border-sky-500/20 text-sky-400';
  return 'bg-purple-500/10 border-purple-500/20 text-purple-400';
};

const roleBadgeClass = (role) => {
  if (role === 'Admin') return 'bg-rose-500/10 border-rose-500/20 text-rose-400';
  if (role === 'Chef') return 'bg-amber-500/10 border-amber-500/20 text-amber-400';
  if (role === 'Waiter') return 'bg-sky-500/10 border-sky-500/20 text-sky-400';
  return 'bg-purple-500/10 border-purple-500/20 text-purple-400';
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
