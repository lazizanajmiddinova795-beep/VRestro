<template>
  <div class="space-y-6 max-w-4xl mx-auto no-print pb-16">
    
    <!-- MAIN DISPLAY & INTERFACE SETTINGS CARD (Exact Match to User Design) -->
    <div class="backdrop-blur-xl bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-6">

      <!-- Card Header -->
      <div class="flex items-center space-x-3.5 pb-4 border-b border-slate-200">
        <div class="p-3 rounded-2xl bg-pink-500/10 text-pink-500 border border-pink-500/20 shadow-md">
          <Settings class="w-6 h-6 stroke-[2]" />
        </div>
        <div>
          <h2 class="text-xl font-bold text-slate-900 tracking-tight">{{ settingsStore.t('settings.title') }}</h2>
          <p class="text-xs text-slate-500 mt-0.5">{{ settingsStore.t('settings.interface_desc') }}</p>
        </div>
      </div>

      <!-- Settings Rows List -->
      <div class="divide-y divide-slate-200">

        <!-- 1. Tizim tili -->
        <div class="py-4 flex items-center justify-between gap-4">
          <div class="flex items-center space-x-3.5">
            <div class="w-11 h-11 rounded-2xl bg-blue-500/10 border border-blue-500/20 text-blue-400 flex items-center justify-center shrink-0">
              <Globe class="w-5.5 h-5.5 stroke-[1.8]" />
            </div>
            <div>
              <h3 class="text-sm font-bold text-slate-900">{{ settingsStore.t('settings.sys_lang') }}</h3>
              <p class="text-xs text-slate-500">{{ settingsStore.t('settings.lang_desc') }}</p>
            </div>
          </div>
          <div class="relative">
            <select
              :value="settingsStore.language"
              @change="settingsStore.setLanguage($event.target.value)"
              class="px-4 py-2.5 bg-white border border-slate-200 hover:border-slate-300 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:border-indigo-500 transition cursor-pointer min-w-[150px]"
            >
              <option value="uz">O'zbek (UZ)</option>
              <option value="ru">Русский (RU)</option>
              <option value="en">English (EN)</option>
            </select>
          </div>
        </div>

        <!-- 2. Ranglar mavzusi -->
        <div class="py-4 flex items-center justify-between gap-4">
          <div class="flex items-center space-x-3.5">
            <div class="w-11 h-11 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center shrink-0">
              <Moon class="w-5.5 h-5.5 stroke-[1.8]" />
            </div>
            <div>
              <h3 class="text-sm font-bold text-slate-900">{{ settingsStore.t('settings.theme_mode') }}</h3>
              <p class="text-xs text-slate-500">{{ settingsStore.t('settings.theme_desc') }}</p>
            </div>
          </div>
          <span class="px-4 py-2 text-xs font-bold rounded-lg bg-indigo-600 text-white shadow-md">
            {{ settingsStore.t('settings.light_theme') }}
          </span>
        </div>

        <!-- 3. Ko'z himoyasi (Tungi Filtr) -->
        <div class="py-4 flex items-center justify-between gap-4">
          <div class="flex items-center space-x-3.5">
            <div class="w-11 h-11 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center shrink-0">
              <Eye class="w-5.5 h-5.5 stroke-[1.8]" />
            </div>
            <div>
              <h3 class="text-sm font-bold text-slate-900">{{ settingsStore.t('settings.night_filter') }}</h3>
              <p class="text-xs text-slate-500">{{ settingsStore.t('settings.night_filter_desc') }}</p>
            </div>
          </div>
          <!-- Smooth Toggle Switch -->
          <button
            @click="toggleNightFilter"
            class="w-13 h-7 rounded-full transition duration-300 relative p-1 focus:outline-none"
            :class="settingsStore.nightFilter ? 'bg-amber-500 shadow-lg shadow-amber-500/20' : 'bg-slate-300'"
          >
            <div
              class="w-5 h-5 rounded-full bg-white transition duration-300 shadow-md transform"
              :class="settingsStore.nightFilter ? 'translate-x-6' : 'translate-x-0'"
            ></div>
          </button>
        </div>

        <!-- 4. Matn o'lchami -->
        <div class="py-4 flex items-center justify-between gap-4">
          <div class="flex items-center space-x-3.5">
            <div class="w-11 h-11 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
              <Type class="w-5.5 h-5.5 stroke-[1.8]" />
            </div>
            <div>
              <h3 class="text-sm font-bold text-slate-900">{{ settingsStore.t('settings.font_size') }}</h3>
              <p class="text-xs text-slate-500">{{ settingsStore.t('settings.font_size_desc') }}</p>
            </div>
          </div>
          <div class="flex items-center p-1 bg-slate-100 border border-slate-200 rounded-xl">
            <button
              @click="setFontSize('small')"
              class="px-3.5 py-2 text-xs font-bold rounded-lg transition duration-200"
              :class="settingsStore.fontSize === 'small'
                ? 'bg-indigo-600 text-white shadow-md'
                : 'text-slate-500 hover:text-slate-900'"
            >
              {{ settingsStore.t('settings.small') }}
            </button>
            <button
              @click="setFontSize('medium')"
              class="px-3.5 py-2 text-xs font-bold rounded-lg transition duration-200"
              :class="settingsStore.fontSize === 'medium' || settingsStore.fontSize === 'normal'
                ? 'bg-indigo-600 text-white shadow-md'
                : 'text-slate-500 hover:text-slate-900'"
            >
              {{ settingsStore.t('settings.medium') }}
            </button>
            <button
              @click="setFontSize('large')"
              class="px-3.5 py-2 text-xs font-bold rounded-lg transition duration-200"
              :class="settingsStore.fontSize === 'large'
                ? 'bg-indigo-600 text-white shadow-md'
                : 'text-slate-500 hover:text-slate-900'"
            >
              {{ settingsStore.t('settings.large') }}
            </button>
          </div>
        </div>

      </div>
    </div>

    <!-- STAFF PROFILE CARD (read-only - admin-entered data, cannot be edited by the staff member) -->
    <div class="backdrop-blur-xl bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-6">
      <div class="flex items-center space-x-3.5 pb-4 border-b border-slate-200">
        <div class="p-3 rounded-2xl bg-indigo-500/10 text-indigo-500 border border-indigo-500/20 shadow-md">
          <UserCircle class="w-6 h-6 stroke-[2]" />
        </div>
        <div>
          <h2 class="text-xl font-bold text-slate-900 tracking-tight">{{ settingsStore.t('profile.title') }}</h2>
          <p class="text-xs text-slate-500 mt-0.5">{{ settingsStore.t('profile.desc') }}</p>
        </div>
      </div>

      <div class="flex items-center space-x-4">
        <div class="w-16 h-16 rounded-full border-2 border-indigo-200 bg-indigo-50 text-indigo-700 text-xl font-black flex items-center justify-center overflow-hidden shrink-0">
          <img v-if="authStore.user?.avatar_url" :src="authStore.user.avatar_url" class="w-full h-full object-cover" />
          <span v-else>{{ profileInitials }}</span>
        </div>
        <div>
          <h3 class="text-base font-black text-slate-900">{{ authStore.user?.name || '—' }}</h3>
          <span class="inline-block bg-slate-100 border border-slate-200 text-slate-700 text-xs font-black px-3 py-1 rounded-full mt-1">
            {{ settingsStore.t('staff.cashier') }}
          </span>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-5 gap-x-4">
        <div>
          <p class="text-3xs text-slate-500 font-extrabold uppercase tracking-wider">{{ settingsStore.t('profile.login') }}</p>
          <p class="text-slate-900 font-bold text-sm mt-1">{{ authStore.user?.login || settingsStore.t('not_entered') }}</p>
        </div>
        <div>
          <p class="text-3xs text-slate-500 font-extrabold uppercase tracking-wider">{{ settingsStore.t('phone') }}</p>
          <p class="text-slate-900 font-bold text-sm mt-1">{{ authStore.user?.phone || settingsStore.t('not_entered') }}</p>
        </div>
        <div>
          <p class="text-3xs text-slate-500 font-extrabold uppercase tracking-wider">{{ settingsStore.t('profile.email') }}</p>
          <p class="text-slate-900 font-bold text-sm mt-1 truncate">{{ authStore.user?.email || settingsStore.t('not_entered') }}</p>
        </div>
        <div>
          <p class="text-3xs text-slate-500 font-extrabold uppercase tracking-wider">{{ settingsStore.t('staff.passport') }}</p>
          <p class="text-slate-900 font-bold text-sm mt-1">{{ maskedPassport }}</p>
        </div>
        <div>
          <p class="text-3xs text-slate-500 font-extrabold uppercase tracking-wider">{{ settingsStore.t('staff.birth_date') }}</p>
          <p class="text-slate-900 font-bold text-sm mt-1">{{ authStore.user?.birth_date || settingsStore.t('not_entered') }}</p>
        </div>
        <div class="sm:col-span-2">
          <p class="text-3xs text-slate-500 font-extrabold uppercase tracking-wider">{{ settingsStore.t('staff.address') }}</p>
          <p class="text-slate-900 font-bold text-sm mt-1">{{ authStore.user?.address || settingsStore.t('not_entered') }}</p>
        </div>
      </div>

      <div class="flex items-center space-x-2 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3">
        <Lock class="w-3.5 h-3.5 text-slate-400 shrink-0" />
        <span class="text-3xs text-slate-500 font-semibold">{{ settingsStore.t('profile.readonly_notice') }}</span>
      </div>
    </div>

    <!-- ADDITIONAL HARDWARE & HARDWARE SETTINGS -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      
      <!-- Termal Printer Sozlamalari -->
      <div class="backdrop-blur-xl bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-6">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-2xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 flex items-center justify-center shrink-0">
            <Printer class="w-5 h-5" />
          </div>
          <div>
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">{{ settingsStore.t('cashier.printer_settings') }}</h3>
            <p class="text-xs text-slate-500">{{ settingsStore.t('cashier.printer_settings_desc') }}</p>
          </div>
        </div>

        <div class="space-y-4">
          <div class="space-y-1.5">
            <label class="text-3xs text-slate-500 font-bold uppercase tracking-wider">{{ settingsStore.t('cashier.receipt_width') }}</label>
            <select
              v-model="cashierStore.localSettings.printerWidth"
              class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 focus:border-indigo-500 text-xs text-slate-900 focus:outline-none transition cursor-pointer"
            >
              <option value="80mm">{{ settingsStore.t('cashier.width_80mm') }}</option>
              <option value="58mm">{{ settingsStore.t('cashier.width_58mm') }}</option>
            </select>
          </div>

          <button
            @click="printTestReceipt"
            class="w-full py-3 rounded-xl bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 text-indigo-600 font-bold text-xs transition duration-200 flex items-center justify-center space-x-2"
          >
            <Printer class="w-4 h-4" />
            <span>{{ settingsStore.t('cashier.test_printer') }}</span>
          </button>
        </div>
      </div>

      <!-- Ovozli Signal & Bildirishnomalar -->
      <div class="backdrop-blur-xl bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-6 flex flex-col justify-between">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-2xl bg-violet-500/10 border border-violet-500/20 text-violet-400 flex items-center justify-center shrink-0">
            <Volume2 class="w-5 h-5" />
          </div>
          <div>
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">{{ settingsStore.t('cashier.sound_notification') }}</h3>
            <p class="text-xs text-slate-500">{{ settingsStore.t('cashier.sound_notification_desc') }}</p>
          </div>
        </div>

        <div class="flex items-center justify-between p-4 bg-slate-50 border border-slate-200 rounded-2xl">
          <div class="space-y-0.5">
            <span class="text-xs font-bold text-slate-900">{{ settingsStore.t('cashier.audio_signal') }}</span>
            <p class="text-3xs text-slate-500">{{ settingsStore.t('cashier.audio_signal_desc') }}</p>
          </div>
          <button
            @click="toggleSound"
            class="px-4 py-2.5 rounded-xl border text-xs font-bold transition duration-200"
            :class="cashierStore.localSettings.soundEnabled
              ? 'bg-indigo-50 border-indigo-200 text-indigo-600'
              : 'bg-slate-100 border-slate-200 text-slate-500'"
          >
            {{ cashierStore.localSettings.soundEnabled ? settingsStore.t('settings.enabled') : settingsStore.t('settings.disabled') }}
          </button>
        </div>
      </div>

    </div>

  </div>

  <!-- PRINT ONLY: TEST RECEIPT TICKET -->
  <div id="physical-thermal-receipt" class="print-only">
    <div class="thermal-ticket" :class="{'small-roll': cashierStore.localSettings.printerWidth === '58mm'}">
      <div class="ticket-center font-bold font-large">*** {{ settingsStore.t('cashier.test_receipt_title') }} ***</div>
      <div class="ticket-center font-bold">{{ settingsStore.t('cashier.system_name') }}</div>
      <div class="ticket-center">{{ settingsStore.t('cashier.printer_test') }}</div>
      <div class="ticket-divider"></div>

      <div>{{ settingsStore.t('date') }}: {{ new Date().toLocaleString('uz-UZ') }}</div>
      <div>{{ settingsStore.t('cashier.paper_size') }}: {{ cashierStore.localSettings.printerWidth }}</div>
      <div>{{ settingsStore.t('status') }}: {{ settingsStore.t('success') }}</div>

      <div class="ticket-divider"></div>
      <table class="ticket-table">
        <thead>
          <tr>
            <th align="left">{{ settingsStore.t('cashier.dish_col') }}</th>
            <th align="right">{{ settingsStore.t('status') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>{{ settingsStore.t('cashier.test_row') }} 1</td>
            <td align="right">OK</td>
          </tr>
          <tr>
            <td>{{ settingsStore.t('cashier.test_row') }} 2</td>
            <td align="right">OK</td>
          </tr>
        </tbody>
      </table>
      <div class="ticket-divider"></div>
      <div class="ticket-center ticket-footer-text">
        <p>{{ settingsStore.t('cashier.test_complete') }}</p>
        <p class="ticket-bold">{{ settingsStore.t('cashier.tagline') }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Settings, Globe, Moon, Eye, Type, Printer, Volume2, UserCircle, Lock } from 'lucide-vue-next';
import { useCashierStore } from '@/stores/cashier';
import { useSettingsStore } from '@/stores/settings';
import { useAuthStore } from '@/stores/auth';

const cashierStore = useCashierStore();
const settingsStore = useSettingsStore();
const authStore = useAuthStore();

const profileInitials = computed(() => {
  const name = authStore.user?.name || '';
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() || '?';
});

// Mask passport like "AD 1234567" -> "AD ****567" so it isn't shown in full on-screen
const maskedPassport = computed(() => {
  const raw = authStore.user?.passport_number;
  if (!raw) return settingsStore.t('not_entered');
  if (raw.length < 5) return raw;
  return raw.substring(0, 2) + ' ****' + raw.substring(raw.length - 3);
});

const setTheme = (theme) => {
  settingsStore.setTheme(theme);
};

const toggleNightFilter = () => {
  settingsStore.setNightFilter(!settingsStore.nightFilter);
};

const setFontSize = (size) => {
  settingsStore.setFontSize(size);
};

const toggleSound = () => {
  cashierStore.localSettings.soundEnabled = !cashierStore.localSettings.soundEnabled;
  if (cashierStore.localSettings.soundEnabled) {
    setTimeout(() => {
      cashierStore.playNotificationBeep();
    }, 100);
  }
};

const printTestReceipt = () => {
  window.print();
};
</script>
