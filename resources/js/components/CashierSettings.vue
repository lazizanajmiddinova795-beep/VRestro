<template>
  <div class="space-y-6 max-w-4xl mx-auto no-print pb-16">
    
    <!-- MAIN DISPLAY & INTERFACE SETTINGS CARD (Exact Match to User Design) -->
    <div class="backdrop-blur-xl bg-slate-900/60 border border-slate-800/80 rounded-3xl p-6 shadow-2xl space-y-6">
      
      <!-- Card Header -->
      <div class="flex items-center space-x-3.5 pb-4 border-b border-slate-800/60">
        <div class="p-3 rounded-2xl bg-pink-500/10 text-pink-500 border border-pink-500/20 shadow-md">
          <Settings class="w-6 h-6 stroke-[2]" />
        </div>
        <div>
          <h2 class="text-xl font-bold text-white tracking-tight">Sozlamalar</h2>
          <p class="text-xs text-slate-400 mt-0.5">Interfeys tili, ranglar mavzusi, yozuv hajmi va xodimlar huquqlarini boshqarish</p>
        </div>
      </div>

      <!-- Settings Rows List -->
      <div class="divide-y divide-slate-800/60">
        
        <!-- 1. Tizim tili -->
        <div class="py-4 flex items-center justify-between gap-4">
          <div class="flex items-center space-x-3.5">
            <div class="w-11 h-11 rounded-2xl bg-blue-500/10 border border-blue-500/20 text-blue-400 flex items-center justify-center shrink-0">
              <Globe class="w-5.5 h-5.5 stroke-[1.8]" />
            </div>
            <div>
              <h3 class="text-sm font-bold text-white">Tizim tili</h3>
              <p class="text-xs text-slate-400">Dastur interfeysi tilini tanlang</p>
            </div>
          </div>
          <div class="relative">
            <select 
              v-model="cashierStore.localSettings.language"
              @change="cashierStore.playNotificationBeep()"
              class="px-4 py-2.5 bg-slate-950/80 border border-slate-800 hover:border-slate-700 rounded-xl text-xs font-semibold text-white focus:outline-none focus:border-indigo-500 transition cursor-pointer min-w-[150px]"
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
              <h3 class="text-sm font-bold text-white">Ranglar mavzusi</h3>
              <p class="text-xs text-slate-400">Yorug' yoki qorong'i rejimni tanlang</p>
            </div>
          </div>
          <div class="flex items-center p-1 bg-slate-950/80 border border-slate-800 rounded-xl">
            <button 
              @click="setTheme('light')"
              class="px-4 py-2 text-xs font-bold rounded-lg transition duration-200"
              :class="cashierStore.localSettings.theme === 'light'
                ? 'bg-slate-800 text-white shadow-md'
                : 'text-slate-400 hover:text-slate-200'"
            >
              Yorug' (Oq)
            </button>
            <button 
              @click="setTheme('dark')"
              class="px-4 py-2 text-xs font-bold rounded-lg transition duration-200"
              :class="cashierStore.localSettings.theme === 'dark'
                ? 'bg-slate-800 text-white shadow-md'
                : 'text-slate-400 hover:text-slate-200'"
            >
              Qorong'i (Tungi)
            </button>
          </div>
        </div>

        <!-- 3. Ko'z himoyasi (Tungi Filtr) -->
        <div class="py-4 flex items-center justify-between gap-4">
          <div class="flex items-center space-x-3.5">
            <div class="w-11 h-11 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center shrink-0">
              <Eye class="w-5.5 h-5.5 stroke-[1.8]" />
            </div>
            <div>
              <h3 class="text-sm font-bold text-white">Ko'z himoyasi (Tungi Filtr)</h3>
              <p class="text-xs text-slate-400">Moviy nurlarni kamaytirish rejimi</p>
            </div>
          </div>
          <!-- Smooth Toggle Switch -->
          <button 
            @click="toggleNightFilter"
            class="w-13 h-7 rounded-full transition duration-300 relative p-1 focus:outline-none"
            :class="cashierStore.localSettings.nightFilter ? 'bg-amber-500 shadow-lg shadow-amber-500/20' : 'bg-slate-800'"
          >
            <div 
              class="w-5 h-5 rounded-full bg-white transition duration-300 shadow-md transform"
              :class="cashierStore.localSettings.nightFilter ? 'translate-x-6' : 'translate-x-0'"
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
              <h3 class="text-sm font-bold text-white">Matn o'lchami</h3>
              <p class="text-xs text-slate-400">Interfeysdagi harflar hajmini o'zgartirish</p>
            </div>
          </div>
          <div class="flex items-center p-1 bg-slate-950/80 border border-slate-800 rounded-xl">
            <button 
              @click="setFontSize('small')"
              class="px-3.5 py-2 text-xs font-bold rounded-lg transition duration-200"
              :class="cashierStore.localSettings.fontSize === 'small'
                ? 'bg-slate-800 text-white shadow-md'
                : 'text-slate-400 hover:text-slate-200'"
            >
              Kichik
            </button>
            <button 
              @click="setFontSize('normal')"
              class="px-3.5 py-2 text-xs font-bold rounded-lg transition duration-200"
              :class="cashierStore.localSettings.fontSize === 'normal'
                ? 'bg-slate-800 text-white shadow-md'
                : 'text-slate-400 hover:text-slate-200'"
            >
              O'rtacha
            </button>
            <button 
              @click="setFontSize('large')"
              class="px-3.5 py-2 text-xs font-bold rounded-lg transition duration-200"
              :class="cashierStore.localSettings.fontSize === 'large'
                ? 'bg-slate-800 text-white shadow-md'
                : 'text-slate-400 hover:text-slate-200'"
            >
              Katta
            </button>
          </div>
        </div>

      </div>
    </div>

    <!-- ADDITIONAL HARDWARE & HARDWARE SETTINGS -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      
      <!-- Termal Printer Sozlamalari -->
      <div class="backdrop-blur-xl bg-slate-900/60 border border-slate-800/80 rounded-3xl p-6 shadow-2xl space-y-6">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-2xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 flex items-center justify-center shrink-0">
            <Printer class="w-5 h-5" />
          </div>
          <div>
            <h3 class="text-sm font-bold text-white uppercase tracking-wider">Termal Printer Sozlamalari</h3>
            <p class="text-xs text-slate-400">Kassa va oshxona kvitansiya chop etish</p>
          </div>
        </div>

        <div class="space-y-4">
          <div class="space-y-1.5">
            <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Chek qog'oz kengligi</label>
            <select 
              v-model="cashierStore.localSettings.printerWidth"
              class="w-full px-4 py-2.5 rounded-xl bg-slate-950/80 border border-slate-800 focus:border-indigo-500 text-xs text-white focus:outline-none transition cursor-pointer"
            >
              <option value="80mm">80mm (Standart Kassa Prineri)</option>
              <option value="58mm">58mm (Kichik Portativ Printer)</option>
            </select>
          </div>

          <button 
            @click="printTestReceipt"
            class="w-full py-3 rounded-xl bg-indigo-600/10 border border-indigo-500/30 hover:bg-indigo-600/20 text-indigo-300 font-bold text-xs transition duration-200 flex items-center justify-center space-x-2"
          >
            <Printer class="w-4 h-4" />
            <span>Printerni Sinash (Test Chek)</span>
          </button>
        </div>
      </div>

      <!-- Ovozli Signal & Bildirishnomalar -->
      <div class="backdrop-blur-xl bg-slate-900/60 border border-slate-800/80 rounded-3xl p-6 shadow-2xl space-y-6 flex flex-col justify-between">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-2xl bg-violet-500/10 border border-violet-500/20 text-violet-400 flex items-center justify-center shrink-0">
            <Volume2 class="w-5 h-5" />
          </div>
          <div>
            <h3 class="text-sm font-bold text-white uppercase tracking-wider">Ovozli Bildirishnoma</h3>
            <p class="text-xs text-slate-400">Yangi buyurtmalar va hodisalar signallari</p>
          </div>
        </div>

        <div class="flex items-center justify-between p-4 bg-slate-950/80 border border-slate-800 rounded-2xl">
          <div class="space-y-0.5">
            <span class="text-xs font-bold text-white">Audio Signal</span>
            <p class="text-3xs text-slate-400">Ovozsiz rejim yoki tovushli ogohlantirish</p>
          </div>
          <button 
            @click="toggleSound"
            class="px-4 py-2.5 rounded-xl border text-xs font-bold transition duration-200"
            :class="cashierStore.localSettings.soundEnabled
              ? 'bg-indigo-600/20 border-indigo-500/40 text-indigo-300' 
              : 'bg-slate-900 border-slate-800 text-slate-500'"
          >
            {{ cashierStore.localSettings.soundEnabled ? 'Yoqilgan' : "O'chirilgan" }}
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
import { Settings, Globe, Moon, Eye, Type, Printer, Volume2 } from 'lucide-vue-next';
import { useCashierStore } from '@/stores/cashier';

const cashierStore = useCashierStore();

const setTheme = (theme) => {
  cashierStore.localSettings.theme = theme;
  cashierStore.playNotificationBeep();
};

const toggleNightFilter = () => {
  cashierStore.localSettings.nightFilter = !cashierStore.localSettings.nightFilter;
  cashierStore.playNotificationBeep();
};

const setFontSize = (size) => {
  cashierStore.localSettings.fontSize = size;
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
