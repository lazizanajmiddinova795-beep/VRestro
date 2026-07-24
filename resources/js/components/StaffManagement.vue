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
              <img 
                v-if="member.avatar_url"
                :src="member.avatar_url"
                alt="Avatar"
                class="w-10 h-10 rounded-xl object-cover border-2 border-indigo-500/20 shadow-sm shrink-0"
              />
              <div 
                v-else
                class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm border uppercase shrink-0"
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
      class="fixed inset-0 bg-slate-950/70 backdrop-blur-md z-[9999] flex items-center justify-center p-4 sm:p-6 overflow-y-auto"
      @click.self="showModal = false"
    >
      <div class="bg-slate-900 text-white w-full max-w-xl rounded-3xl p-6 shadow-2xl border border-white/10 my-auto max-h-[90vh] flex flex-col animate-in fade-in zoom-in-95 duration-150">
        <!-- Header -->
        <div class="flex justify-between items-center border-b border-white/10 pb-4 shrink-0">
          <h3 class="text-white font-black text-xl tracking-tight">
            {{ editingStaff ? 'Xodimni Tahrirlash' : 'Yangi Xodim Qo\'shish' }}
          </h3>
          <button @click="showModal = false" class="bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white p-2 rounded-full transition-colors">
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Form Body -->
        <form @submit.prevent="submitForm" class="flex flex-col flex-grow overflow-hidden mt-4">
          <div class="overflow-y-auto pr-2 space-y-4 flex-grow max-h-[65vh]">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-left">
                
                <div class="sm:col-span-2 flex flex-col">
                    <label class="text-slate-400 font-extrabold text-xs tracking-wider uppercase mb-1.5">To'liq Ism-Familiyasi *</label>
                    <input type="text" v-model="staffForm.name" required placeholder="Masalan: Asilbek Povar" 
                           class="bg-slate-950/60 border border-white/10 focus:border-indigo-500 text-white font-bold px-4 py-2.5 rounded-xl outline-none transition-all placeholder-slate-600" />
                </div>

                <div class="flex flex-col">
                    <label class="text-slate-400 font-extrabold text-xs tracking-wider uppercase mb-1.5">Telefon Raqami *</label>
                    <input type="text" v-model="staffForm.phone" required placeholder="+998 90 123 45 67" 
                           class="bg-slate-950/60 border border-white/10 focus:border-indigo-500 text-white font-bold px-4 py-2.5 rounded-xl outline-none transition-all placeholder-slate-600" />
                </div>

                <div class="flex flex-col">
                    <label class="text-slate-400 font-extrabold text-xs tracking-wider uppercase mb-1.5">Tizimga kirish logini *</label>
                    <input type="text" v-model="staffForm.login" required placeholder="Masalan: chef123" 
                           class="bg-slate-950/60 border border-white/10 focus:border-indigo-500 text-white font-bold px-4 py-2.5 rounded-xl outline-none transition-all placeholder-slate-600" />
                </div>

                <div class="flex flex-col">
                    <label class="text-slate-400 font-extrabold text-xs tracking-wider uppercase mb-1.5">Ish vaqti / Smena</label>
                    <input type="text" v-model="staffForm.shift_hours" placeholder="08:00 - 20:00" 
                           class="bg-slate-950/60 border border-white/10 focus:border-indigo-500 text-white font-bold px-4 py-2.5 rounded-xl outline-none transition-all placeholder-slate-600" />
                </div>

                <div class="flex flex-col">
                    <label class="text-slate-400 font-extrabold text-xs tracking-wider uppercase mb-1.5">Tizimdagi Lavozimi (Role) *</label>
                    <div class="relative">
                        <select v-model="staffForm.role" required 
                                class="w-full bg-slate-950/60 border border-white/10 focus:border-indigo-500 text-white font-bold px-4 py-2.5 rounded-xl outline-none transition-all appearance-none">
                            <option value="Admin" class="bg-slate-900 text-white">Administrator</option>
                            <option value="Chef" class="bg-slate-900 text-white">Chef (Oshpaz)</option>
                            <option value="Waiter" class="bg-slate-900 text-white">Waiter (Ofitsiant)</option>
                            <option value="Cashier" class="bg-slate-900 text-white">Cashier (Kassir)</option>
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col" v-if="!editingStaff">
                    <label class="text-slate-400 font-extrabold text-xs tracking-wider uppercase mb-1.5">Faollik holati *</label>
                    <div class="relative">
                        <select v-model="staffForm.status" required 
                                class="w-full bg-slate-950/60 border border-white/10 focus:border-indigo-500 text-white font-bold px-4 py-2.5 rounded-xl outline-none transition-all appearance-none">
                            <option value="active" class="bg-slate-900 text-white">Faol (Active)</option>
                            <option value="inactive" class="bg-slate-900 text-white">Nofaol (Inactive)</option>
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col">
                    <label class="text-slate-400 font-extrabold text-xs tracking-wider uppercase mb-1.5">Email Manzili</label>
                    <input type="email" v-model="staffForm.email" placeholder="example@mail.com" 
                           class="bg-slate-950/60 border border-white/10 focus:border-indigo-500 text-white font-bold px-4 py-2.5 rounded-xl outline-none transition-all placeholder-slate-600" />
                </div>

                <div class="flex flex-col">
                    <label class="text-slate-400 font-extrabold text-xs tracking-wider uppercase mb-1.5">Pasport Ma'lumotlari</label>
                    <input type="text" v-model="staffForm.passport_number" placeholder="AA1234567" 
                           class="bg-slate-950/60 border border-white/10 focus:border-indigo-500 text-white font-bold px-4 py-2.5 rounded-xl outline-none transition-all placeholder-slate-600" />
                </div>

                <div class="flex flex-col">
                    <label class="text-slate-400 font-extrabold text-xs tracking-wider uppercase mb-1.5">Tug'ilgan Sanasi</label>
                    <input type="date" v-model="staffForm.birth_date" 
                           class="bg-slate-950/60 border border-white/10 focus:border-indigo-500 text-white font-bold px-4 py-2.5 rounded-xl outline-none transition-all" />
                </div>

                <div class="sm:col-span-2 flex flex-col">
                    <label class="text-slate-400 font-extrabold text-xs tracking-wider uppercase mb-1.5">Yashash Manzili</label>
                    <input type="text" v-model="staffForm.address" placeholder="Toshkent sh., Chilonzor tumani..." 
                           class="bg-slate-950/60 border border-white/10 focus:border-indigo-500 text-white font-bold px-4 py-2.5 rounded-xl outline-none transition-all placeholder-slate-600" />
                </div>

                <div class="sm:col-span-2 flex flex-col space-y-3">
                    <label class="text-slate-400 font-extrabold text-xs tracking-wider uppercase">Avatar tanlash yoki yuklash</label>
                    <!-- Custom file uploader -->
                    <div class="flex items-center space-x-4">
                      <div class="relative shrink-0">
                        <img v-if="staffForm.avatar_url" :src="staffForm.avatar_url" class="w-14 h-14 rounded-full object-cover border-2 border-indigo-500 shadow-sm animate-in fade-in" />
                        <div v-else class="w-14 h-14 rounded-full bg-slate-800 border-2 border-dashed border-white/10 flex items-center justify-center text-slate-400">
                          <Camera class="w-5 h-5" />
                        </div>
                      </div>
                      <label class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white border border-white/10 rounded-xl cursor-pointer font-bold text-xs transition duration-200">
                        <span>Rasm yuklash</span>
                        <input type="file" @change="handleAvatarUpload" accept="image/*" class="hidden" />
                      </label>
                      <button v-if="staffForm.avatar_url" type="button" @click="staffForm.avatar_url = ''" class="text-xs font-bold text-rose-400 hover:underline">O'chirish</button>
                    </div>

                    <!-- Presets -->
                    <div class="grid grid-cols-6 gap-2 pt-1">
                      <button 
                        type="button"
                        v-for="(preset, i) in avatarPresets" 
                        :key="i"
                        @click="staffForm.avatar_url = preset"
                        class="w-9 h-9 rounded-full overflow-hidden border-2 transition active:scale-90"
                        :class="staffForm.avatar_url === preset ? 'border-indigo-500 ring-2 ring-indigo-500/40' : 'border-white/10 hover:border-white/30'"
                      >
                        <img :src="preset" class="w-full h-full object-cover" />
                      </button>
                    </div>
                </div>

                <div class="sm:col-span-2 flex flex-col">
                    <label class="text-slate-400 font-extrabold text-xs tracking-wider uppercase mb-1.5">Tizim Paroli (Bo'sh qolsa o'zgarmaydi)</label>
                    <input type="password" v-model="staffForm.password" placeholder="Kamida 4 belgili yangi parol..." 
                           class="bg-slate-950/60 border border-white/10 focus:border-indigo-500 text-white font-bold px-4 py-2.5 rounded-xl outline-none transition-all placeholder-slate-600" />
                </div>

            </div>
          </div>

          <!-- Actions Footer (Sticky) -->
          <div class="flex items-center justify-end gap-3 pt-4 mt-2 border-t border-white/10 shrink-0">
              <button type="button" @click="showModal = false" 
                      class="px-5 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-slate-300 font-extrabold text-xs transition-all active:scale-95">
                  Bekor qilish
              </button>
              <button type="submit" 
                      class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:opacity-90 text-white font-black text-xs shadow-lg shadow-indigo-600/30 transition-all active:scale-95">
                  Saqlash
              </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { 
  UserPlus, Search, Phone, Clock, KeyRound, Edit3, Trash2, X, Loader2, Users, Camera
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
  status: 'active',
  email: '',
  passport_number: '',
  birth_date: '',
  address: '',
  avatar_url: ''
});

const avatarPresets = [
  'https://api.dicebear.com/7.x/adventurer/svg?seed=Felix',
  'https://api.dicebear.com/7.x/adventurer/svg?seed=Aneka',
  'https://api.dicebear.com/7.x/adventurer/svg?seed=Liam',
  'https://api.dicebear.com/7.x/adventurer/svg?seed=Sophia',
  'https://api.dicebear.com/7.x/adventurer/svg?seed=Jack',
  'https://api.dicebear.com/7.x/adventurer/svg?seed=Emmy'
];

const handleAvatarUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = (e) => {
      staffForm.value.avatar_url = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

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
    status: member.status,
    email: member.email || '',
    passport_number: member.passport_number || '',
    birth_date: member.birth_date || '',
    address: member.address || '',
    avatar_url: member.avatar_url || ''
  } : {
    name: '',
    phone: '',
    login: '',
    password: '',
    shift_hours: '',
    role: 'Waiter',
    status: 'active',
    email: '',
    passport_number: '',
    birth_date: '',
    address: '',
    avatar_url: ''
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
