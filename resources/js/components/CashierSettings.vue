<template>
  <div class="space-y-6 max-w-4xl mx-auto no-print">
    <div>
      <h2 class="text-xl md:text-2xl font-bold text-white tracking-wide">{{ cashierStore.t('sozlamalar') }}</h2>
      <p class="text-xs text-slate-400 mt-1">{{ cashierStore.t('lokal_sozlamalar_desc') }}</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      
      <!-- 1. Tizim Tili (Language) -->
      <div class="backdrop-blur-xl bg-slate-900/40 border border-white/10 rounded-3xl p-6 shadow-2xl flex flex-col justify-between">
        <div class="space-y-2">
          <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center space-x-2">
            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
            <span>{{ cashierStore.t('tizim_tili') }}</span>
          </h3>
          <p class="text-xxs text-slate-400">{{ cashierStore.t('tizim_tili_desc') }}</p>
        </div>

        <div class="grid grid-cols-3 gap-2.5 mt-6">
          <button 
            @click="setLanguage('uz')"
            class="py-3 rounded-2xl border font-bold text-xs transition duration-200 flex flex-col items-center justify-center space-y-1"
            :class="cashierStore.localSettings.language === 'uz' 
              ? 'bg-indigo-600 border-indigo-500 text-white shadow-lg' 
              : 'bg-white/5 border-white/10 text-slate-300 hover:bg-white/10'"
          >
            <span class="text-base">🇺🇿</span>
            <span>O'zbekcha</span>
          </button>
          <button 
            @click="setLanguage('ru')"
            class="py-3 rounded-2xl border font-bold text-xs transition duration-200 flex flex-col items-center justify-center space-y-1"
            :class="cashierStore.localSettings.language === 'ru' 
              ? 'bg-indigo-600 border-indigo-500 text-white shadow-lg' 
              : 'bg-white/5 border-white/10 text-slate-300 hover:bg-white/10'"
          >
            <span class="text-base">🇷🇺</span>
            <span>Русский</span>
          </button>
          <button 
            @click="setLanguage('en')"
            class="py-3 rounded-2xl border font-bold text-xs transition duration-200 flex flex-col items-center justify-center space-y-1"
            :class="cashierStore.localSettings.language === 'en' 
              ? 'bg-indigo-600 border-indigo-500 text-white shadow-lg' 
              : 'bg-white/5 border-white/10 text-slate-300 hover:bg-white/10'"
          >
            <span class="text-base">🇬🇧</span>
            <span>English</span>
          </button>
        </div>
      </div>

      <!-- 2. Ekran Rejimi (Theme) -->
      <div class="backdrop-blur-xl bg-slate-900/40 border border-white/10 rounded-3xl p-6 shadow-2xl flex flex-col justify-between">
        <div class="space-y-2">
          <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center space-x-2">
            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
            <span>{{ cashierStore.t('ekran_rejimi') }}</span>
          </h3>
          <p class="text-xxs text-slate-400">{{ cashierStore.t('ekran_rejimi_desc') }}</p>
        </div>

        <div class="grid grid-cols-2 gap-4 mt-6">
          <button 
            @click="setTheme('light')"
            class="py-4 rounded-2xl border font-bold text-xs transition duration-200 flex flex-col items-center justify-center space-y-2"
            :class="cashierStore.localSettings.theme === 'light' 
              ? 'bg-indigo-600 border-indigo-500 text-white shadow-lg shadow-indigo-600/20' 
              : 'bg-white/5 border-white/10 text-slate-300 hover:bg-white/10'"
          >
            <span class="text-lg">☀️</span>
            <span>Light Mode</span>
          </button>
          <button 
            @click="setTheme('dark')"
            class="py-4 rounded-2xl border font-bold text-xs transition duration-200 flex flex-col items-center justify-center space-y-2"
            :class="cashierStore.localSettings.theme === 'dark' 
              ? 'bg-indigo-600 border-indigo-500 text-white shadow-lg shadow-indigo-600/20' 
              : 'bg-white/5 border-white/10 text-slate-300 hover:bg-white/10'"
          >
            <span class="text-lg">🌙</span>
            <span>Dark Mode</span>
          </button>
        </div>
      </div>

      <!-- 3. Shrift va Masshtab (Fonts & Zoom) -->
      <div class="backdrop-blur-xl bg-slate-900/40 border border-white/10 rounded-3xl p-6 shadow-2xl space-y-6">
        <div class="space-y-2">
          <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center space-x-2">
            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
            <span>{{ cashierStore.t('shrift_olchami') }} & {{ cashierStore.t('lupa_masshtab') }}</span>
          </h3>
          <p class="text-xxs text-slate-400">{{ cashierStore.t('shrift_olchami_desc') }}</p>
        </div>

        <!-- Font size choices -->
        <div class="space-y-2">
          <label class="text-[10px] text-slate-400 font-bold uppercase">{{ cashierStore.t('shrift_olchami') }}</label>
          <div class="grid grid-cols-3 gap-2">
            <button 
              v-for="sz in ['small', 'normal', 'large']"
              :key="sz"
              @click="setFontSize(sz)"
              class="py-2.5 rounded-xl border text-xxs font-bold uppercase transition duration-150"
              :class="cashierStore.localSettings.fontSize === sz
                ? 'bg-indigo-600 border-indigo-500 text-white' 
                : 'bg-white/5 border-white/10 text-slate-300 hover:bg-white/10'"
            >
              {{ sz === 'small' ? cashierStore.t('kichik') : sz === 'large' ? cashierStore.t('katta') : cashierStore.t('normal') }}
            </button>
          </div>
        </div>

        <!-- Zoom Scale -->
        <div class="space-y-2">
          <label class="text-[10px] text-slate-400 font-bold uppercase">{{ cashierStore.t('lupa_masshtab') }}</label>
          <div class="grid grid-cols-3 gap-2">
            <button 
              v-for="z in [100, 108, 115]"
              :key="z"
              @click="setZoomScale(z)"
              class="py-2.5 rounded-xl border text-xxs font-bold font-mono transition duration-150"
              :class="cashierStore.localSettings.zoomScale === z
                ? 'bg-indigo-600 border-indigo-500 text-white' 
                : 'bg-white/5 border-white/10 text-slate-300 hover:bg-white/10'"
            >
              {{ z }}%
            </button>
          </div>
        </div>
      </div>

      <!-- 4. Printer Interfeysi (Printer size and test) -->
      <div class="backdrop-blur-xl bg-slate-900/40 border border-white/10 rounded-3xl p-6 shadow-2xl space-y-6">
        <div class="space-y-2">
          <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center space-x-2">
            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
            <span>{{ cashierStore.t('printer_sozlamalari') }}</span>
          </h3>
          <p class="text-xxs text-slate-400">{{ cashierStore.t('printer_sozlamalari_desc') }}</p>
        </div>

        <div class="space-y-4">
          <div class="space-y-1.5">
            <label class="text-[10px] text-slate-400 font-bold uppercase">{{ cashierStore.t('chek_kengligi') }}</label>
            <select 
              v-model="cashierStore.localSettings.printerWidth"
              class="w-full px-4 py-2.5 rounded-xl bg-slate-950/60 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
            >
              <option value="80mm">{{ cashierStore.t('standart_qogoz') }}</option>
              <option value="58mm">{{ cashierStore.t('kichik_qogoz') }}</option>
            </select>
          </div>

          <button 
            @click="printTestReceipt"
            class="w-full py-3 rounded-xl bg-white/5 border border-white/10 hover:bg-indigo-600/10 hover:border-indigo-500/20 text-indigo-400 font-bold text-xs transition duration-200"
          >
            {{ cashierStore.t('printerni_sinash') }}
          </button>
        </div>
      </div>

      <!-- 5. Ovozli Bildirishnomalar (Audio Alerts) -->
      <div class="backdrop-blur-xl bg-slate-900/40 border border-white/10 rounded-3xl p-6 shadow-2xl flex flex-col justify-between">
        <div class="space-y-2">
          <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center space-x-2">
            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
            <span>{{ cashierStore.t('ovozli_bildirishnoma') }}</span>
          </h3>
          <p class="text-xxs text-slate-400">{{ cashierStore.t('ovozli_signal_desc') }}</p>
        </div>

        <div class="mt-6 flex items-center justify-between p-4 bg-white/5 border border-white/5 rounded-2xl">
          <div class="space-y-0.5">
            <span class="text-xs font-bold text-white">{{ cashierStore.t('ovozli_bildirishnoma') }}</span>
            <p class="text-[10px] text-slate-400">{{ cashierStore.t('savat_signal_desc') }}</p>
          </div>
          <button 
            @click="toggleSound"
            class="px-4 py-2.5 rounded-xl border text-xxs font-bold transition duration-200"
            :class="cashierStore.localSettings.soundEnabled
              ? 'bg-indigo-600/10 border-indigo-500/30 text-indigo-400' 
              : 'bg-slate-950 border-white/5 text-slate-500'"
          >
            {{ cashierStore.localSettings.soundEnabled ? cashierStore.t('yoqilgan') : cashierStore.t('ochirilgan') }}
          </button>
        </div>
      </div>

    </div>
  </div>

  <!-- PRINT ONLY: TEST RECEIPT TICKET -->
  <div id="physical-thermal-receipt" class="print-only">
    <div class="thermal-ticket" :class="{'small-roll': cashierStore.localSettings.printerWidth === '58mm'}">
      <div class="ticket-center font-bold font-large">*** TEST CHEKI ***</div>
      <div class="ticket-center font-bold">VRestro ERP Tizimi</div>
      <div class="ticket-center">Termal Printer Sinovi</div>
      <div class="ticket-divider"></div>
      
      <div>Sana: {{ new Date().toLocaleString('uz-UZ') }}</div>
      <div>Qog'oz o'lchami: {{ cashierStore.localSettings.printerWidth }}</div>
      <div>Holat: Muvaffaqiyatli</div>
      
      <div class="ticket-divider"></div>
      <table class="ticket-table">
        <thead>
          <tr>
            <th align="left">Taom</th>
            <th align="right">Holat</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Printer test satri 1</td>
            <td align="right">OK</td>
          </tr>
          <tr>
            <td>Printer test satri 2</td>
            <td align="right">OK</td>
          </tr>
        </tbody>
      </table>
      <div class="ticket-divider"></div>
      <div class="ticket-center ticket-footer-text">
        <p>Printer sinovi muvaffaqiyatli yakunlandi!</p>
        <p class="ticket-bold">VRestro - Biz bilan yuksalishda davom eting</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useCashierStore } from '@/stores/cashier';

const cashierStore = useCashierStore();

const setTheme = (theme) => {
  cashierStore.localSettings.theme = theme;
  cashierStore.playNotificationBeep();
};

const setFontSize = (size) => {
  cashierStore.localSettings.fontSize = size;
  cashierStore.playNotificationBeep();
};

const setZoomScale = (zoom) => {
  cashierStore.localSettings.zoomScale = zoom;
  cashierStore.playNotificationBeep();
};

const setLanguage = (lang) => {
  cashierStore.localSettings.language = lang;
  cashierStore.playNotificationBeep();
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

<style>
/* Style adjustments for light-theme customization */
.light-theme {
  background-color: #f8fafc !important;
  color: #0f172a !important;
}

.light-theme .text-white {
  color: #0f172a !important;
}

.light-theme .text-slate-300 {
  color: #334155 !important;
}

.light-theme .text-slate-400 {
  color: #475569 !important;
}

.light-theme .bg-slate-900\/40 {
  background-color: rgba(255, 255, 255, 0.7) !important;
  border-color: rgba(15, 23, 42, 0.08) !important;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05) !important;
}

.light-theme .bg-white\/5 {
  background-color: rgba(15, 23, 42, 0.03) !important;
  border-color: rgba(15, 23, 42, 0.06) !important;
}

.light-theme .bg-slate-950\/60,
.light-theme .bg-slate-950 {
  background-color: #ffffff !important;
  border-color: rgba(15, 23, 42, 0.1) !important;
  color: #0f172a !important;
}

.light-theme select,
.light-theme option {
  color: #0f172a !important;
  background-color: #ffffff !important;
}

.light-theme .border-white\/10 {
  border-color: rgba(15, 23, 42, 0.08) !important;
}

/* Katta / Kichik font classes */
.text-sm {
  font-size: 0.825rem !important;
}
.text-lg {
  font-size: 1.125rem !important;
}

/* 58mm small roll printer adjustments */
@media print {
  .small-roll {
    width: 58mm !important;
    font-size: 9pt !important;
  }
  .small-roll .ticket-table {
    font-size: 8pt !important;
  }
}
</style>
