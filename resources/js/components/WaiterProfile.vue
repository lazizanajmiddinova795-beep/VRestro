<template>
  <div class="space-y-6 pb-28">
    
    <!-- Waiter Profile Identity Card -->
    <div class="bg-white border-2 border-slate-200 rounded-2xl p-6 text-center shadow-sm mb-6">
      <div class="w-20 h-20 rounded-full border-4 border-indigo-600 shadow-sm flex items-center justify-center bg-indigo-50 text-indigo-700 text-2xl font-black mx-auto mb-4">
        {{ avatarInitials }}
      </div>
      <div>
        <h3 class="text-slate-900 font-black text-xl text-center">{{ waiterName }}</h3>
        <span class="inline-block bg-slate-100 border border-slate-200 text-slate-700 text-xs font-black px-3 py-1 rounded-full mt-1.5">
          {{ t('waiter_title') }}
        </span>
        <span class="text-emerald-600 font-extrabold text-xs tracking-wider uppercase mt-2 block">
          ● {{ t('shift_active') }}
        </span>
      </div>
    </div>    <!-- Personal Data Grid -->
    <div class="bg-white rounded-2xl border-2 border-slate-200 p-6 shadow-sm">
      <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
        <h4 class="text-slate-900 font-black text-base uppercase tracking-wider">{{ t('personal_info_title') }}</h4>
        <button 
          @click="openEditModal" 
          class="text-xs bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 text-indigo-700 px-3.5 py-1.5 rounded-xl font-bold transition flex items-center space-x-1"
        >
          <span>✏️</span>
          <span>{{ t('edit_btn') }}</span>
        </button>
      </div>
      <div class="grid grid-cols-2 gap-y-5 gap-x-4 text-left">
        <div class="col-span-1">
          <p class="text-[10px] text-slate-500 font-extrabold uppercase tracking-wider">{{ t('phone_label') }}</p>
          <p class="text-slate-900 font-black text-base mt-1 block">{{ waiterPhone }}</p>
        </div>
        <div class="col-span-1">
          <p class="text-[10px] text-slate-500 font-extrabold uppercase tracking-wider">{{ t('email_label') }}</p>
          <p class="text-slate-900 font-black text-base mt-1 block truncate" :title="waiterEmail">{{ waiterEmail }}</p>
        </div>
        <div class="col-span-1">
          <p class="text-[10px] text-slate-500 font-extrabold uppercase tracking-wider">{{ t('passport_label') }}</p>
          <p class="text-slate-900 font-black text-base mt-1 block">{{ waiterPassport }}</p>
        </div>
        <div class="col-span-1">
          <p class="text-[10px] text-slate-500 font-extrabold uppercase tracking-wider">{{ t('birthdate_label') }}</p>
          <p class="text-slate-900 font-black text-base mt-1 block">{{ waiterBirthDate }}</p>
        </div>
        <div class="col-span-2">
          <p class="text-[10px] text-slate-500 font-extrabold uppercase tracking-wider">{{ t('address_label') }}</p>
          <p class="text-slate-900 font-black text-base mt-1 block">{{ waiterAddress }}</p>
        </div>
      </div>
    </div>

    <!-- Daily Performance KPI Widgets (2x2 Grid) -->
    <div class="space-y-3">
      <h4 class="text-xs font-black text-slate-700 uppercase tracking-widest pl-1">{{ t('daily_kpi') }}</h4>
      <div class="grid grid-cols-2 gap-4">
        
        <!-- Box 1: Sales Amount -->
        <div class="bg-white border-2 border-slate-350 p-4 rounded-2xl space-y-1.5 text-left shadow-sm">
          <span class="text-slate-600 font-extrabold text-xs tracking-wider uppercase">{{ t('daily_sales') }}</span>
          <span class="text-indigo-600 font-black text-2xl mt-2 block truncate leading-snug">{{ formatCurrency(stats.total_sales_amount) }}</span>
        </div>

        <!-- Box 2: Closed Tables count -->
        <div class="bg-white border-2 border-slate-355 p-4 rounded-2xl space-y-1.5 text-left shadow-sm">
          <span class="text-slate-600 font-extrabold text-xs tracking-wider uppercase">{{ t('closed_tables') }}</span>
          <span class="text-indigo-600 font-black text-2xl mt-2 block leading-snug">{{ stats.total_orders_count }} {{ t('tables_unit') }}</span>
        </div>

        <!-- Box 3: Pending cashier checkout -->
        <div class="bg-white border-2 border-slate-350 p-4 rounded-2xl space-y-1.5 text-left border-rose-300 shadow-sm">
          <span class="text-slate-600 font-extrabold text-xs tracking-wider uppercase text-rose-700">{{ t('pending_cashier') }}</span>
          <span class="text-rose-750 font-black text-2xl mt-2 block leading-snug">{{ stats.pending_checkout_count }} {{ t('orders_unit') }}</span>
        </div>

        <!-- Box 4: Earned Bonus -->
        <div class="bg-white border-2 border-slate-350 p-4 rounded-2xl space-y-1.5 text-left border-emerald-300 shadow-sm">
          <span class="text-slate-600 font-extrabold text-xs tracking-wider uppercase text-emerald-800">{{ t('my_bonus') }}</span>
          <span class="text-emerald-700 font-black text-2xl mt-2 block truncate leading-snug">{{ formatCurrency(stats.earned_bonus) }}</span>
        </div>

      </div>
    </div>

    <!-- Actions Drawer (Bottom Stack) -->
    <div class="bg-white border-2 border-slate-300 rounded-3xl divide-y divide-slate-200 overflow-hidden shadow-sm">
      <!-- Language Selector -->
      <div class="flex items-center justify-between p-4 text-sm font-bold text-slate-800">
        <span>{{ t('sys_lang') }}</span>
        <div class="flex space-x-2">
          <button 
            @click="setLang('uz')" 
            class="px-3 py-1.5 rounded bg-slate-100 border border-slate-300 text-slate-700 font-bold"
            :class="{'bg-indigo-600 border-indigo-650 text-white font-black': currentLang === 'uz'}"
          >
            UZ
          </button>
          <button 
            @click="setLang('ru')" 
            class="px-3 py-1.5 rounded bg-slate-100 border border-slate-300 text-slate-700 font-bold"
            :class="{'bg-indigo-600 border-indigo-650 text-white font-black': currentLang === 'ru'}"
          >
            RU
          </button>
        </div>
      </div>

      <!-- Logout button trigger -->
      <button 
        @click="triggerLogout"
        class="w-full flex items-center justify-between p-4 text-sm font-bold text-rose-700 hover:bg-rose-50 transition duration-150 text-left"
      >
        <span>{{ t('sys_logout') }}</span>
        <LogOut class="w-4 h-4 text-rose-700" />
      </button>
    </div>

    <!-- Edit Profile Modal Overlay -->
    <div v-if="showEditModal" class="fixed inset-0 z-[999] overflow-y-auto flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
      <div class="bg-white border-2 border-slate-300 rounded-3xl w-full max-w-sm shadow-2xl p-6 space-y-6 animate-slideUp text-slate-900">
        <div class="flex items-center justify-between border-b pb-3">
          <h3 class="text-slate-900 font-black text-lg">Ma'lumotlarni Kiritish</h3>
          <button @click="showEditModal = false" class="text-slate-500 hover:text-slate-700 font-black text-lg">✕</button>
        </div>

        <form @submit.prevent="saveProfile" class="space-y-4 text-left">
          <div>
            <label class="block text-xs font-black text-slate-700 mb-1">To'liq Ism</label>
            <input 
              type="text" 
              v-model="editForm.name" 
              required
              class="w-full px-4 py-2 bg-white text-slate-900 border-2 border-slate-200 rounded-xl focus:border-indigo-600 outline-none transition font-bold"
            />
          </div>

          <div>
            <label class="block text-xs font-black text-slate-700 mb-1">Telefon Raqami</label>
            <input 
              type="text" 
              v-model="editForm.phone" 
              required
              placeholder="+998901234567"
              class="w-full px-4 py-2 bg-white text-slate-900 border-2 border-slate-200 rounded-xl focus:border-indigo-600 outline-none transition font-bold"
            />
          </div>

          <div>
            <label class="block text-xs font-black text-slate-700 mb-1">Email Manzili</label>
            <input 
              type="email" 
              v-model="editForm.email" 
              class="w-full px-4 py-2 bg-white text-slate-900 border-2 border-slate-200 rounded-xl focus:border-indigo-600 outline-none transition font-bold"
            />
          </div>

          <div>
            <label class="block text-xs font-black text-slate-700 mb-1">Pasport Seriyasi va Raqami</label>
            <input 
              type="text" 
              v-model="editForm.passport_number" 
              placeholder="AA1234567"
              class="w-full px-4 py-2 bg-white text-slate-900 border-2 border-slate-200 rounded-xl focus:border-indigo-600 outline-none transition font-bold"
            />
          </div>

          <div>
            <label class="block text-xs font-black text-slate-700 mb-1">Tug'ilgan Sanasi</label>
            <input 
              type="date" 
              v-model="editForm.birth_date" 
              class="w-full px-4 py-2 bg-white text-slate-900 border-2 border-slate-200 rounded-xl focus:border-indigo-600 outline-none transition font-bold"
            />
          </div>

          <div>
            <label class="block text-xs font-black text-slate-700 mb-1">Yashash Manzili</label>
            <input 
              type="text" 
              v-model="editForm.address" 
              class="w-full px-4 py-2 bg-white text-slate-900 border-2 border-slate-200 rounded-xl focus:border-indigo-600 outline-none transition font-bold"
            />
          </div>

          <div class="flex items-center space-x-3 pt-4 border-t">
            <button 
              type="button" 
              @click="showEditModal = false"
              class="flex-1 py-3 border-2 border-slate-200 hover:bg-slate-50 text-slate-800 rounded-xl font-bold transition text-xs"
            >
              Bekor qilish
            </button>
            <button 
              type="submit" 
              :disabled="saving"
              class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-black transition disabled:opacity-50 text-xs shadow-md"
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
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useWaiterStore } from '@/stores/waiter';
import { LogOut } from 'lucide-vue-next';

const authStore = useAuthStore();
const waiterStore = useWaiterStore();
const router = useRouter();

const currentLang = ref(localStorage.getItem('waiter_lang') || 'uz');
const stats = ref({
  total_orders_count: 0,
  total_sales_amount: 0,
  pending_checkout_count: 0,
  earned_bonus: 0
});

const showEditModal = ref(false);
const saving = ref(false);
const editForm = ref({
  name: '',
  phone: '',
  email: '',
  passport_number: '',
  birth_date: '',
  address: ''
});

const openEditModal = () => {
  editForm.value = {
    name: authStore.user?.name || '',
    phone: authStore.user?.phone || '',
    email: authStore.user?.email || '',
    passport_number: authStore.user?.passport_number || '',
    birth_date: authStore.user?.birth_date || '',
    address: authStore.user?.address || ''
  };
  showEditModal.value = true;
};

const saveProfile = async () => {
  saving.value = true;
  try {
    const response = await fetch('/api/user/profile', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('vrestro_token')}`
      },
      body: JSON.stringify(editForm.value)
    });

    const data = await response.json();
    if (!response.ok) {
      throw new Error(data.message || "Profilni saqlashda xatolik yuz berdi.");
    }

    // Update local user in authStore and localStorage
    authStore.user = {
      ...authStore.user,
      ...data.user
    };
    localStorage.setItem('vrestro_user', JSON.stringify(authStore.user));
    waiterStore.triggerToast(t('personal_info_title') + " muvaffaqiyatli saqlandi!");
    showEditModal.value = false;
  } catch (err) {
    waiterStore.triggerToast(err.message);
  } finally {
    saving.value = false;
  }
};

const dictionary = {
  uz: {
    waiter_title: "Ofitsiant / Birinchi toifa",
    shift_active: "Navbatchilik faol",
    daily_kpi: "Kunlik KPI ko'rsatkichlari",
    daily_sales: "Bugungi Savdo",
    closed_tables: "Yopilgan stollar",
    tables_unit: "ta stol",
    pending_cashier: "Kassirda kutilmoqda",
    orders_unit: "ta buyurtma",
    my_bonus: "Mening Bonusim",
    sys_lang: "Tizim tili",
    sys_logout: "Tizimdan chiqish",
    confirm_logout: "Tizimdan chiqishni tasdiqlaysizmi?",
    personal_info_title: "Shaxsiy va Aloqa Ma'lumotlari",
    phone_label: "Telefon Raqami",
    email_label: "Email",
    passport_label: "Pasport",
    birthdate_label: "Tug'ilgan Sana",
    address_label: "Manzil",
    edit_btn: "Kiritish"
  },
  ru: {
    waiter_title: "Официант / Первый класс",
    shift_active: "Смена активна",
    daily_kpi: "Ежедневные показатели KPI",
    daily_sales: "Продажи за сегодня",
    closed_tables: "Закрытые столы",
    tables_unit: "столов",
    pending_cashier: "Ожидает у кассира",
    orders_unit: "заказов",
    my_bonus: "Мой Бонус",
    sys_lang: "Язык системы",
    sys_logout: "Выйти из системы",
    confirm_logout: "Вы действительно хотите выйти из системы?",
    personal_info_title: "Личные и контактные данные",
    phone_label: "Номер телефона",
    email_label: "Эл. адрес",
    passport_label: "Паспорт",
    birthdate_label: "Дата рождения",
    address_label: "Адрес",
    edit_btn: "Ввести данные"
  }
};

const t = (key) => {
  return dictionary[currentLang.value]?.[key] || key;
};

const waiterName = computed(() => authStore.user?.name || 'Ofitsiant');
const avatarInitials = computed(() => {
  return waiterName.value.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
});

// Reactive data bindings with safe fallback strings
const waiterPhone = computed(() => authStore.user?.phone || 'Kiritilmagan');
const waiterEmail = computed(() => authStore.user?.email || 'Kiritilmagan');
const waiterBirthDate = computed(() => authStore.user?.birth_date || 'Kiritilmagan');
const waiterAddress = computed(() => authStore.user?.address || 'Kiritilmagan');

// Secure Passport String Masker (e.g., "AD 1234567" -> "AD ****567")
const waiterPassport = computed(() => {
  const raw = authStore.user?.passport_number;
  if (!raw) return 'Kiritilmagan';
  return raw.substring(0, 2) + ' ****' + raw.substring(raw.length - 3);
});

const formatCurrency = (value) => {
  return new Intl.NumberFormat('uz-UZ', { style: 'currency', currency: 'UZS', maximumFractionDigits: 0 }).format(value);
};

const setLang = (lang) => {
  currentLang.value = lang;
  localStorage.setItem('waiter_lang', lang);
};

const triggerLogout = () => {
  if (confirm(t('confirm_logout'))) {
    waiterStore.stopPolling();
    authStore.logout();
    router.push({ name: 'login' });
  }
};

onMounted(async () => {
  try {
    const token = localStorage.getItem('vrestro_token');
    const response = await fetch('/api/waiter/profile/daily-stats', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    });

    if (response.ok) {
      stats.value = await response.json();
    }
  } catch (error) {
    console.error('Error fetching KPI dailyStats: ', error);
  }
});
</script>
