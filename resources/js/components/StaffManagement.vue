<template>
  <div class="flex-grow p-6 flex flex-col h-screen overflow-hidden">
    
    <!-- Top Header -->
    <div class="flex items-center justify-between mb-6 shrink-0">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-wide">
          {{ settingsStore.t('staff.title') }}
        </h1>
        <p class="text-xs text-slate-500">{{ settingsStore.t('staff.subtitle') }}</p>
      </div>

      <!-- Add Staff button -->
      <button
        @click="openAddEditModal()"
        class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 font-semibold text-sm text-white shadow-md shadow-indigo-600/20 hover:shadow-indigo-600/30 hover:scale-[1.01] transition-all flex items-center justify-center space-x-2"
      >
        <UserPlus class="w-4.5 h-4.5" />
        <span>{{ settingsStore.t('staff.add_button') }}</span>
      </button>
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
          :placeholder="settingsStore.t('staff.search_placeholder')"
          @input="triggerFetch"
          class="w-full pl-10 pr-4 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500 text-xs placeholder-slate-400 text-slate-900 focus:outline-none transition"
        />
      </div>

      <!-- Role Filter -->
      <div>
        <select
          v-model="filterRole"
          @change="triggerFetch"
          class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 transition"
        >
          <option value="">{{ settingsStore.t('staff.all_roles') }}</option>
          <option value="Admin">{{ settingsStore.t('staff.admin') }}</option>
          <option value="Chef">{{ settingsStore.t('staff.role_chef_opt') }}</option>
          <option value="Waiter">{{ settingsStore.t('staff.role_waiter_opt') }}</option>
          <option value="Cashier">{{ settingsStore.t('staff.role_cashier_opt') }}</option>
        </select>
      </div>

      <!-- Status Filter -->
      <div>
        <select
          v-model="filterStatus"
          @change="triggerFetch"
          class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 transition"
        >
          <option value="">{{ settingsStore.t('staff.all_statuses') }}</option>
          <option value="active">{{ settingsStore.t('staff.active_opt') }}</option>
          <option value="inactive">{{ settingsStore.t('staff.inactive_opt') }}</option>
        </select>
      </div>

      <!-- Total Indicator -->
      <div class="flex items-center justify-end px-2">
        <span class="text-xxs font-bold text-slate-500 uppercase tracking-wider">{{ settingsStore.t('staff.found_count') }} {{ staffStore.pagination.total }} ta</span>
      </div>
    </div>

    <!-- Staff Cards Grid -->
    <div v-if="staffStore.loading && staffStore.staffMembers.length === 0" class="flex-grow flex flex-col items-center justify-center space-y-4">
      <Loader2 class="w-10 h-10 text-indigo-500 animate-spin" />
      <p class="text-slate-500 text-xs font-medium animate-pulse">{{ settingsStore.t('staff.loading') }}</p>
    </div>

    <div v-else class="flex-grow overflow-y-auto pr-1">
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 pb-24">
        
        <!-- Employee Card -->
        <div
          v-for="member in staffStore.staffMembers"
          :key="member.id"
          class="bg-white border shadow-sm rounded-3xl p-5 flex flex-col justify-between min-h-[240px] transition-all duration-300 relative group overflow-hidden"
          :class="member.status === 'inactive' ? 'border-slate-200 opacity-60' : 'border-indigo-100 hover:border-indigo-300'"
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
                <h3 class="text-sm font-bold text-slate-900 tracking-wide truncate flex items-center gap-1.5">
                  {{ member.name }}
                  <ShieldCheck v-if="member.is_superadmin" class="w-3.5 h-3.5 text-amber-500 shrink-0" :title="settingsStore.t('staff.superadmin_tooltip')" />
                </h3>
                <span class="flex items-center gap-1 mt-1">
                  <span
                    class="px-2 py-0.5 rounded text-4xs font-bold uppercase tracking-wider border inline-block"
                    :class="roleBadgeClass(member.roles?.[0]?.name)"
                  >
                    {{ member.roles?.[0]?.name || settingsStore.t('staff.employee_fallback') }}
                  </span>
                  <span v-if="member.is_superadmin" class="px-2 py-0.5 rounded text-4xs font-bold uppercase tracking-wider border bg-amber-50 border-amber-200 text-amber-600 inline-block">
                    {{ settingsStore.t('staff.superadmin_badge') }}
                  </span>
                </span>
              </div>
            </div>

            <!-- Details -->
            <div class="space-y-2.5 text-xxs text-slate-500">
              <div class="flex items-center space-x-2">
                <Phone class="w-3.5 h-3.5 text-slate-400" />
                <span>{{ settingsStore.t('staff.phone_prefix') }} {{ member.phone || settingsStore.t('not_entered') }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <Clock class="w-3.5 h-3.5 text-slate-400" />
                <span>{{ settingsStore.t('staff.shift_prefix') }} {{ member.shift_hours || settingsStore.t('staff.shift_not_set') }}</span>
              </div>
              <div class="flex items-center space-x-2 font-mono">
                <KeyRound class="w-3.5 h-3.5 text-slate-400" />
                <span>{{ settingsStore.t('staff.login_prefix') }} {{ member.login }}</span>
              </div>
            </div>
          </div>

          <!-- Footer Settings -->
          <div class="border-t border-slate-100 pt-3.5 mt-4 flex items-center justify-between">
            <!-- Active switch -->
            <div class="flex items-center space-x-2">
              <button
                @click="handleToggleStatus(member)"
                :disabled="!canManage(member)"
                class="w-8 h-4.5 rounded-full p-0.5 transition-colors duration-200 focus:outline-none relative disabled:opacity-40 disabled:cursor-not-allowed"
                :class="member.status === 'active' ? 'bg-indigo-600' : 'bg-slate-300'"
                :title="!canManage(member) ? settingsStore.t('staff.only_superadmin_toggle') : (member.status === 'active' ? settingsStore.t('staff.active_block_tooltip') : settingsStore.t('staff.inactive_activate_tooltip'))"
              >
                <span
                  class="block w-3.5 h-3.5 rounded-full bg-white transition-transform duration-200"
                  :class="member.status === 'active' ? 'translate-x-3.5' : 'translate-x-0'"
                ></span>
              </button>
              <span class="text-4xs uppercase tracking-wider font-bold" :class="member.status === 'active' ? 'text-indigo-600' : 'text-slate-400'">
                {{ member.status === 'active' ? settingsStore.t('staff.status_active_short') : settingsStore.t('staff.status_blocked_short') }}
              </span>
            </div>

            <!-- Actions -->
            <div class="flex items-center space-x-1.5">
              <button
                @click="openAddEditModal(member)"
                :disabled="!canManage(member)"
                class="p-1.5 rounded bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-900 transition disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-slate-100 disabled:hover:text-slate-500"
                :title="!canManage(member) ? settingsStore.t('staff.only_superadmin_edit') : settingsStore.t('edit')"
              >
                <Edit3 class="w-3.5 h-3.5" />
              </button>
              <button
                @click="handleDelete(member)"
                :disabled="!canManage(member)"
                class="p-1.5 rounded bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-red-50 disabled:hover:text-red-500"
                :title="!canManage(member) ? settingsStore.t('staff.only_superadmin_delete') : settingsStore.t('delete')"
              >
                <Trash2 class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>

        </div>

      </div>

      <!-- Empty state -->
      <div v-if="staffStore.staffMembers.length === 0" class="flex flex-col items-center justify-center py-24 space-y-3">
        <Users class="w-12 h-12 text-slate-300" />
        <p class="text-slate-500 text-xs font-medium">{{ settingsStore.t('staff.not_found') }}</p>
      </div>
    </div>

    <!-- MODAL: Add / Edit Employee -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm z-[9999] flex items-center justify-center p-4 sm:p-6 overflow-y-auto"
      @click.self="showModal = false"
    >
      <div class="bg-white text-slate-900 w-full max-w-xl rounded-3xl p-6 shadow-2xl border border-slate-200 my-auto max-h-[90vh] flex flex-col animate-in fade-in zoom-in-95 duration-150">
        <!-- Header -->
        <div class="flex justify-between items-center border-b border-slate-100 pb-4 shrink-0">
          <h3 class="text-slate-900 font-black text-xl tracking-tight">
            {{ editingStaff ? settingsStore.t('staff.edit_title') : settingsStore.t('staff.add_button') }}
          </h3>
          <button @click="showModal = false" class="bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-900 p-2 rounded-full transition-colors">
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Form Body -->
        <form @submit.prevent="submitForm" class="flex flex-col flex-grow overflow-hidden mt-4">
          <div class="overflow-y-auto pr-2 space-y-4 flex-grow max-h-[65vh]">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-left">
                
                <div class="sm:col-span-2 flex flex-col">
                    <label class="text-slate-500 font-extrabold text-xs tracking-wider uppercase mb-1.5">{{ settingsStore.t('staff.full_name_label') }}</label>
                    <input type="text" v-model="staffForm.name" required :placeholder="settingsStore.t('staff.full_name_placeholder')"
                           class="bg-slate-50 border border-slate-200 focus:border-indigo-500 text-slate-900 font-bold px-4 py-2.5 rounded-xl outline-none transition-all placeholder-slate-400" />
                </div>

                <div class="flex flex-col">
                    <label class="text-slate-500 font-extrabold text-xs tracking-wider uppercase mb-1.5">{{ settingsStore.t('staff.phone_label') }}</label>
                    <input type="text" v-model="staffForm.phone" required placeholder="+998 90 123 45 67"
                           class="bg-slate-50 border border-slate-200 focus:border-indigo-500 text-slate-900 font-bold px-4 py-2.5 rounded-xl outline-none transition-all placeholder-slate-400" />
                </div>

                <div class="flex flex-col">
                    <label class="text-slate-500 font-extrabold text-xs tracking-wider uppercase mb-1.5">{{ settingsStore.t('staff.login_label') }}</label>
                    <input type="text" v-model="staffForm.login" required :placeholder="settingsStore.t('staff.login_placeholder')"
                           class="bg-slate-50 border border-slate-200 focus:border-indigo-500 text-slate-900 font-bold px-4 py-2.5 rounded-xl outline-none transition-all placeholder-slate-400" />
                </div>

                <div class="flex flex-col">
                    <label class="text-slate-500 font-extrabold text-xs tracking-wider uppercase mb-1.5">{{ settingsStore.t('staff.shift_label') }}</label>
                    <input type="text" v-model="staffForm.shift_hours" placeholder="08:00 - 20:00"
                           class="bg-slate-50 border border-slate-200 focus:border-indigo-500 text-slate-900 font-bold px-4 py-2.5 rounded-xl outline-none transition-all placeholder-slate-400" />
                </div>

                <div class="flex flex-col">
                    <label class="text-slate-500 font-extrabold text-xs tracking-wider uppercase mb-1.5">{{ settingsStore.t('staff.role_label') }}</label>
                    <div class="relative">
                        <select v-model="staffForm.role" required
                                class="w-full bg-slate-50 border border-slate-200 focus:border-indigo-500 text-slate-900 font-bold px-4 py-2.5 rounded-xl outline-none transition-all appearance-none">
                            <option value="Admin" class="bg-white text-slate-900">{{ settingsStore.t('staff.admin') }}</option>
                            <option value="Chef" class="bg-white text-slate-900">{{ settingsStore.t('staff.role_chef_opt') }}</option>
                            <option value="Waiter" class="bg-white text-slate-900">{{ settingsStore.t('staff.role_waiter_opt') }}</option>
                            <option value="Cashier" class="bg-white text-slate-900">{{ settingsStore.t('staff.role_cashier_opt') }}</option>
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col" v-if="!editingStaff">
                    <label class="text-slate-500 font-extrabold text-xs tracking-wider uppercase mb-1.5">{{ settingsStore.t('staff.status_label') }}</label>
                    <div class="relative">
                        <select v-model="staffForm.status" required
                                class="w-full bg-slate-50 border border-slate-200 focus:border-indigo-500 text-slate-900 font-bold px-4 py-2.5 rounded-xl outline-none transition-all appearance-none">
                            <option value="active" class="bg-white text-slate-900">{{ settingsStore.t('staff.active_opt') }}</option>
                            <option value="inactive" class="bg-white text-slate-900">{{ settingsStore.t('staff.inactive_opt') }}</option>
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col">
                    <label class="text-slate-500 font-extrabold text-xs tracking-wider uppercase mb-1.5">{{ settingsStore.t('staff.email_label') }}</label>
                    <input type="email" v-model="staffForm.email" placeholder="example@mail.com"
                           class="bg-slate-50 border border-slate-200 focus:border-indigo-500 text-slate-900 font-bold px-4 py-2.5 rounded-xl outline-none transition-all placeholder-slate-400" />
                </div>

                <div class="flex flex-col">
                    <label class="text-slate-500 font-extrabold text-xs tracking-wider uppercase mb-1.5">{{ settingsStore.t('staff.passport') }}</label>
                    <input type="text" v-model="staffForm.passport_number" placeholder="AA1234567"
                           class="bg-slate-50 border border-slate-200 focus:border-indigo-500 text-slate-900 font-bold px-4 py-2.5 rounded-xl outline-none transition-all placeholder-slate-400" />
                </div>

                <div class="flex flex-col">
                    <label class="text-slate-500 font-extrabold text-xs tracking-wider uppercase mb-1.5">{{ settingsStore.t('staff.birthdate_label') }}</label>
                    <input type="date" v-model="staffForm.birth_date"
                           class="bg-slate-50 border border-slate-200 focus:border-indigo-500 text-slate-900 font-bold px-4 py-2.5 rounded-xl outline-none transition-all" />
                </div>

                <div class="sm:col-span-2 flex flex-col">
                    <label class="text-slate-500 font-extrabold text-xs tracking-wider uppercase mb-1.5">{{ settingsStore.t('staff.address_label') }}</label>
                    <input type="text" v-model="staffForm.address" placeholder="Toshkent sh., Chilonzor tumani..."
                           class="bg-slate-50 border border-slate-200 focus:border-indigo-500 text-slate-900 font-bold px-4 py-2.5 rounded-xl outline-none transition-all placeholder-slate-400" />
                </div>

                <div class="sm:col-span-2 flex flex-col space-y-3">
                    <label class="text-slate-500 font-extrabold text-xs tracking-wider uppercase">{{ settingsStore.t('staff.avatar_label') }}</label>
                    <!-- Custom file uploader -->
                    <div class="flex items-center space-x-4">
                      <div class="relative shrink-0">
                        <img v-if="staffForm.avatar_url" :src="staffForm.avatar_url" class="w-14 h-14 rounded-full object-cover border-2 border-indigo-500 shadow-sm animate-in fade-in" />
                        <div v-else class="w-14 h-14 rounded-full bg-slate-100 border-2 border-dashed border-slate-200 flex items-center justify-center text-slate-400">
                          <Camera class="w-5 h-5" />
                        </div>
                      </div>
                      <label class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 rounded-xl cursor-pointer font-bold text-xs transition duration-200">
                        <span>{{ settingsStore.t('staff.upload_photo') }}</span>
                        <input type="file" @change="handleAvatarUpload" accept="image/*" class="hidden" />
                      </label>
                      <button v-if="staffForm.avatar_url" type="button" @click="staffForm.avatar_url = ''; avatarFile.value = null" class="text-xs font-bold text-rose-500 hover:underline">{{ settingsStore.t('delete') }}</button>
                    </div>

                    <!-- Presets -->
                    <div class="grid grid-cols-6 gap-2 pt-1">
                      <button
                        type="button"
                        v-for="(preset, i) in avatarPresets"
                        :key="i"
                        @click="staffForm.avatar_url = preset; avatarFile.value = null"
                        class="w-9 h-9 rounded-full overflow-hidden border-2 transition active:scale-90"
                        :class="staffForm.avatar_url === preset ? 'border-indigo-500 ring-2 ring-indigo-500/40' : 'border-slate-200 hover:border-slate-400'"
                      >
                        <img :src="preset" class="w-full h-full object-cover" />
                      </button>
                    </div>
                </div>

                <div class="sm:col-span-2 flex flex-col">
                    <label class="text-slate-500 font-extrabold text-xs tracking-wider uppercase mb-1.5">{{ settingsStore.t('staff.password_full_label') }}</label>
                    <input type="password" v-model="staffForm.password" :placeholder="settingsStore.t('staff.password_placeholder')"
                           class="bg-slate-50 border border-slate-200 focus:border-indigo-500 text-slate-900 font-bold px-4 py-2.5 rounded-xl outline-none transition-all placeholder-slate-400" />
                </div>

            </div>
          </div>

          <!-- Actions Footer (Sticky) -->
          <div class="flex items-center justify-end gap-3 pt-4 mt-2 border-t border-slate-100 shrink-0">
              <button type="button" @click="showModal = false"
                      class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold text-xs transition-all active:scale-95">
                  {{ settingsStore.t('cancel') }}
              </button>
              <button type="submit"
                      class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:opacity-90 text-white font-black text-xs shadow-lg shadow-indigo-600/30 transition-all active:scale-95">
                  {{ settingsStore.t('save') }}
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
  UserPlus, Search, Phone, Clock, KeyRound, Edit3, Trash2, X, Loader2, Users, Camera, ShieldCheck
} from 'lucide-vue-next';
import { useStaffStore } from '@/stores/staff';
import { useAuthStore } from '@/stores/auth';
import { useSettingsStore } from '@/stores/settings';

const staffStore = useStaffStore();
const authStore = useAuthStore();
const settingsStore = useSettingsStore();

// Whether the currently logged-in user is allowed to edit/deactivate/delete a
// given staff member. Only a super-admin may touch another super-admin's account.
const canManage = (member) => {
  if (!member.is_superadmin) return true;
  return !!authStore.user?.is_superadmin;
};

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

// The actual uploaded file is kept separate from staffForm.avatar_url and
// sent as a real multipart upload — sending it as a base64 data URL in JSON
// blew past the backend's string length limit and produced a raw 500 error.
const avatarFile = ref(null);

const handleAvatarUpload = (event) => {
  const file = event.target.files[0];
  if (!file) return;

  avatarFile.value = file;
  staffForm.value.avatar_url = URL.createObjectURL(file);
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
  avatarFile.value = null;
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
    alert(settingsStore.t('staff.alert_required'));
    return;
  }
  if (!editingStaff.value && !staffForm.value.password.trim()) {
    alert(settingsStore.t('staff.alert_password_required'));
    return;
  }

  const formData = new FormData();
  formData.append('name', staffForm.value.name);
  formData.append('phone', staffForm.value.phone);
  formData.append('login', staffForm.value.login);
  if (staffForm.value.password) formData.append('password', staffForm.value.password);
  formData.append('role', staffForm.value.role);
  formData.append('shift_hours', staffForm.value.shift_hours || '');
  formData.append('status', staffForm.value.status);
  formData.append('email', staffForm.value.email || '');
  formData.append('passport_number', staffForm.value.passport_number || '');
  formData.append('birth_date', staffForm.value.birth_date || '');
  formData.append('address', staffForm.value.address || '');

  if (avatarFile.value) {
    // A real file upload takes precedence; the backend derives avatar_url from it.
    formData.append('avatar', avatarFile.value);
  } else {
    formData.append('avatar_url', staffForm.value.avatar_url || '');
  }

  try {
    if (editingStaff.value) {
      formData.append('_method', 'PUT');
      await staffStore.updateStaff(editingStaff.value.id, formData);
    } else {
      await staffStore.createStaff(formData);
    }
    showModal.value = false;
  } catch (err) {
    alertValidationError(err);
  }
};

const handleToggleStatus = async (member) => {
  try {
    await staffStore.toggleStaffStatus(member.id);
  } catch (err) {
    alertValidationError(err);
  }
};

const handleDelete = async (member) => {
  if (!confirm(settingsStore.t('staff.confirm_delete').replace('{name}', member.name))) return;
  try {
    await staffStore.deleteStaff(member.id);
  } catch (err) {
    alertValidationError(err);
  }
};

// Laravel validation errors (422) carry the real reason under err.errors,
// while err.message is just the generic "The given data was invalid."
const alertValidationError = (err) => {
  if (err.errors) {
    alert(Object.values(err.errors).flat().join('\n'));
  } else {
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
