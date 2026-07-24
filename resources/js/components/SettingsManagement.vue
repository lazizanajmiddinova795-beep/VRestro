<template>
  <div class="p-6 space-y-6 flex-grow flex flex-col h-full overflow-y-auto relative">
    <!-- Top Header -->
    <div class="flex items-center justify-between shrink-0">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-white">Tizim Sozlamalari</h1>
        <p class="text-sm text-slate-400">Restoranning global branding, moliyaviy me'yorlari va xavfsizlik konfiguratsiyalari.</p>
      </div>
    </div>

    <!-- Active tabs navigation -->
    <div class="flex space-x-2 border-b border-white/5 pb-px shrink-0">
      <button 
        v-for="tab in tabs" 
        :key="tab.id"
        @click="activeTab = tab.id"
        class="relative px-5 py-3 text-sm font-semibold tracking-wide transition-all duration-200"
        :class="activeTab === tab.id ? 'text-white border-b-2 border-indigo-500' : 'text-slate-400 hover:text-slate-200'"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- Status warnings / alerts -->
    <div v-if="successMsg" class="p-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 text-emerald-200 text-sm">
      {{ successMsg }}
    </div>
    <div v-if="errorMsg" class="p-4 rounded-xl border border-red-500/20 bg-red-500/10 text-red-200 text-sm">
      {{ errorMsg }}
    </div>

    <!-- Forms Area -->
    <div class="max-w-4xl flex-grow pb-24">
      
      <!-- Tab 1: General Branding & Display Interface -->
      <div v-if="activeTab === 'general'" class="space-y-6 animate-fadeIn">
        
        <!-- DISPLAY & INTERFACE SETTINGS CARD (Exact Match to User Screenshot) -->
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
                  <h3 class="text-sm font-bold text-slate-900 dark:text-white">Tizim tili</h3>
                  <p class="text-xs text-slate-500">Dastur interfeysi tilini tanlang</p>
                </div>
              </div>
              <div class="relative">
                <select
                  :value="settingsStore.language"
                  @change="onLanguageChange($event.target.value)"
                  class="px-4 py-2.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-semibold text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500 transition cursor-pointer min-w-[150px]"
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
                  <h3 class="text-sm font-bold text-slate-900 dark:text-white">Ranglar mavzusi</h3>
                  <p class="text-xs text-slate-500">Yorug' yoki qorong'i rejimni tanlang</p>
                </div>
              </div>
              <div class="flex items-center p-1 bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl">
                <button 
                  @click="settingsStore.setTheme('light')"
                  class="px-4 py-2 text-xs font-bold rounded-lg transition duration-200"
                  :class="settingsStore.theme === 'light'
                    ? 'bg-indigo-600 text-white shadow-md'
                    : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'"
                >
                  Yorug' (Oq)
                </button>
                <button 
                  @click="settingsStore.setTheme('dark')"
                  class="px-4 py-2 text-xs font-bold rounded-lg transition duration-200"
                  :class="settingsStore.theme === 'dark'
                    ? 'bg-slate-900 text-white shadow-md'
                    : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'"
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
                  <h3 class="text-sm font-bold text-slate-900 dark:text-white">Ko'z himoyasi (Tungi Filtr)</h3>
                  <p class="text-xs text-slate-500">Moviy nurlarni kamaytirish rejimi</p>
                </div>
              </div>
              <!-- Toggle Switch -->
              <button 
                @click="settingsStore.setNightFilter(!settingsStore.nightFilter)"
                class="w-13 h-7 rounded-full transition duration-300 relative p-1 focus:outline-none"
                :class="settingsStore.nightFilter ? 'bg-amber-500 shadow-lg shadow-amber-500/20' : 'bg-slate-300 dark:bg-slate-800'"
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
                  <h3 class="text-sm font-bold text-slate-900 dark:text-white">Matn o'lchami</h3>
                  <p class="text-xs text-slate-500">Interfeysdagi harflar hajmini o'zgartirish</p>
                </div>
              </div>
              <div class="flex items-center p-1 bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl">
                <button 
                  @click="settingsStore.setFontSize('small')"
                  class="px-3.5 py-2 text-xs font-bold rounded-lg transition duration-200"
                  :class="settingsStore.fontSize === 'small'
                    ? 'bg-indigo-600 text-white shadow-md'
                    : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'"
                >
                  Kichik
                </button>
                <button 
                  @click="settingsStore.setFontSize('medium')"
                  class="px-3.5 py-2 text-xs font-bold rounded-lg transition duration-200"
                  :class="settingsStore.fontSize === 'medium'
                    ? 'bg-indigo-600 text-white shadow-md'
                    : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'"
                >
                  O'rtacha
                </button>
                <button 
                  @click="settingsStore.setFontSize('large')"
                  class="px-3.5 py-2 text-xs font-bold rounded-lg transition duration-200"
                  :class="settingsStore.fontSize === 'large'
                    ? 'bg-indigo-600 text-white shadow-md'
                    : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'"
                >
                  Katta
                </button>
              </div>
            </div>

          </div>
        </div>

        <!-- RESTAURANT BRANDING FORM -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-white/5 pt-6">
        <div class="space-y-1.5">
          <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Restoran nomi</label>
          <input 
            v-model="generalForm.restaurant_name"
            type="text" 
            placeholder="Restoran nomi..."
            class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
          />
        </div>

        <div class="space-y-1.5">
          <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Telefon raqami</label>
          <input 
            v-model="generalForm.restaurant_phone"
            type="text" 
            placeholder="Telefon raqami..."
            class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
          />
        </div>

        <div class="space-y-1.5">
          <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Ish vaqtlari</label>
          <input 
            v-model="generalForm.restaurant_hours"
            type="text" 
            placeholder="09:00 - 23:00"
            class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
          />
        </div>

        <div class="space-y-1.5 md:col-span-2">
          <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Restoran manzili</label>
          <input 
            v-model="generalForm.restaurant_address"
            type="text" 
            placeholder="Manzil..."
            class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
          />
        </div>

        <!-- Logo drag zone -->
        <div class="space-y-1.5 md:col-span-2">
          <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Restoran logotipi</label>
          <div 
            @click="$refs.logoInput.click()"
            class="w-full border-2 border-dashed border-white/10 hover:border-indigo-500/50 rounded-2xl p-6 flex flex-col items-center justify-center cursor-pointer transition bg-white/2 hover:bg-white/5 space-y-2"
          >
            <input 
              ref="logoInput" 
              type="file" 
              class="hidden" 
              accept="image/*" 
              @change="handleLogoChange"
            />
            <div v-if="logoPreview" class="w-32 h-32 rounded-xl overflow-hidden border border-white/10">
              <img :src="logoPreview" class="w-full h-full object-cover" />
            </div>
            <div v-else class="text-slate-500 flex flex-col items-center space-y-1">
              <UploadCloud class="w-10 h-10 stroke-[1.2]" />
              <span class="text-xs font-medium">Logotip yuklash uchun bosing</span>
              <span class="text-3xs text-slate-600">JPEG, PNG, WEBP (Maks: 2MB)</span>
            </div>
          </div>
        </div>
      </div>
      </div>

      <!-- Tab 2: Finance & Localization -->
      <div v-if="activeTab === 'finance'" class="grid grid-cols-1 md:grid-cols-2 gap-6 animate-fadeIn">
        <div class="space-y-1.5">
          <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Soliq stavkasi (%)</label>
          <input 
            v-model.number="financeForm.tax_rate"
            type="number" 
            placeholder="12"
            class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
          />
        </div>

        <div class="space-y-1.5">
          <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Valyuta birligi</label>
          <select 
            v-model="financeForm.currency"
            class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition appearance-none"
          >
            <option value="UZS">UZS (so'm)</option>
            <option value="USD">USD ($)</option>
            <option value="EUR">EUR (€)</option>
            <option value="RUB">RUB (₽)</option>
          </select>
        </div>

        <div class="space-y-1.5">
          <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Xizmat haqi stavkasi (%)</label>
          <input
            v-model.number="financeForm.service_charge_rate"
            type="number" 
            placeholder="10"
            class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
          />
        </div>

        <div class="space-y-1.5 md:col-span-2">
          <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Chek sarlavhasi (Receipt Header)</label>
          <input 
            v-model="financeForm.receipt_header"
            type="text" 
            placeholder="Xizmatimizdan mamnunmisiz?..."
            class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
          />
        </div>

        <div class="space-y-1.5 md:col-span-2">
          <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Chek tagso'zi (Receipt Footer)</label>
          <input 
            v-model="financeForm.receipt_footer"
            type="text" 
            placeholder="Yana kelishingizni kutib qolamiz!..."
            class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
          />
        </div>

        <!-- System Tools inside Tab 2 -->
        <div class="space-y-1.5 md:col-span-2 border-t border-white/5 pt-4 mt-2">
          <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider block">Tizim vositalari (System Tools)</label>
          <button 
            type="button"
            @click="handleClearCache"
            :disabled="clearingCache"
            class="flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500 hover:text-white transition duration-200"
          >
            <Trash2 v-if="!clearingCache" class="w-4 h-4" />
            <Loader2 v-else class="w-4 h-4 animate-spin" />
            <span>Keshni tozalash (Clear Cache)</span>
          </button>
        </div>
      </div>

      <!-- Tab 3: Security Change Password -->
      <div v-if="activeTab === 'security'" class="max-w-md gap-6 space-y-4 animate-fadeIn">
        <div class="space-y-1.5">
          <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Amaldagi parol</label>
          <input 
            v-model="securityForm.old_password"
            type="password" 
            placeholder="Amaldagi parol..."
            class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
          />
        </div>

        <div class="space-y-1.5">
          <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Yangi parol</label>
          <input 
            v-model="securityForm.new_password"
            type="password" 
            placeholder="Yangi parol..."
            class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
          />
        </div>

        <div class="space-y-1.5">
          <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Yangi parolni tasdiqlash</label>
          <input 
            v-model="securityForm.new_password_confirmation"
            type="password" 
            placeholder="Yangi parolni tasdiqlash..."
            class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
          />
        </div>

        <button 
          @click="submitPasswordChange"
          class="flex items-center space-x-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl transition duration-200"
        >
          <KeyRound class="w-4 h-4" />
          <span>Parolni yangilash</span>
        </button>
      </div>

      <!-- Tab 4: Telegram Bot Integration -->
      <div v-if="activeTab === 'telegram'" class="max-w-2xl bg-white/5 backdrop-blur-xl border border-white/10 p-6 md:p-8 rounded-3xl space-y-6 animate-fadeIn">
        <div class="flex items-start space-x-4">
          <div class="p-3 bg-[#229ED9]/10 text-[#229ED9] rounded-2xl shrink-0">
            <Send class="w-6 h-6" />
          </div>
          <div>
            <h3 class="text-base font-bold text-white">Telegram Bildirishnomalari</h3>
            <p class="text-xs text-slate-400 mt-0.5">Tizimdagi yangi buyurtmalar, bekor qilingan buyurtmalar va past ombor qoldig'i haqida real-vaqt rejimida guruh yoki kanalga xabar jo'natish.</p>
          </div>
        </div>

        <div class="flex items-center justify-between bg-slate-950/20 border border-white/5 p-4 rounded-2xl">
          <div>
            <span class="text-xs font-semibold text-white">Bot bildirishnomalari holati</span>
            <span class="text-3xs text-slate-500 block mt-0.5">Bildirishnomalarni Telegram orqali yuborishni faollashtirish.</span>
          </div>
          <label class="flex items-center space-x-2.5 cursor-pointer">
            <input 
              type="checkbox" 
              v-model="telegramForm.telegram_notifications_enabled" 
              class="sr-only peer"
            />
            <div class="w-9 h-5 bg-slate-800 border border-slate-700 rounded-full peer peer-checked:bg-emerald-600 peer-checked:border-emerald-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-slate-400 after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-4 peer-checked:after:bg-white relative"></div>
            <span class="text-xs font-medium text-slate-300">{{ telegramForm.telegram_notifications_enabled ? 'Yoqilgan' : 'O\'chirilgan' }}</span>
          </label>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div class="space-y-1.5 md:col-span-2">
            <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Telegram Bot Token</label>
            <div class="relative">
              <input 
                v-model="telegramForm.telegram_bot_token"
                type="text" 
                placeholder="8846820582:AAEY..."
                class="w-full pl-4 pr-10 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition font-mono"
              />
            </div>
            <span class="text-4xs text-slate-600">@BotFather orqali olingan maxfiy token.</span>
          </div>

          <div class="space-y-1.5 md:col-span-2">
            <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Telegram Chat ID (Guruh yoki Kanal nomi)</label>
            <div class="relative">
              <input 
                v-model="telegramForm.telegram_chat_id"
                type="text" 
                placeholder="-100123456789 yoki @my_channel"
                class="w-full pl-4 pr-10 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition font-mono"
              />
            </div>
            <span class="text-4xs text-slate-600">Mijozlar guruhi yoki jamoaviy kanal havolasi (Masalan: @VRestro_uz).</span>
          </div>

          <div class="md:col-span-2 pt-2 flex items-center justify-between">
            <button 
              type="button"
              @click="testTelegramConnection"
              :disabled="testingConnection"
              class="flex items-center space-x-2 px-5 py-3 rounded-xl bg-indigo-600/10 border border-indigo-500/20 hover:bg-indigo-600 hover:text-white text-xs font-semibold text-indigo-400 transition duration-200"
            >
              <Send v-if="!testingConnection" class="w-4 h-4" />
              <Loader2 v-else class="w-4 h-4 animate-spin" />
              <span>Ulanishni tekshirish (Test Message)</span>
            </button>
            <span class="text-4xs text-slate-500">Kanalga botni administrator qilib qo'shish esdan chiqmasin!</span>
          </div>
        </div>
      </div>

    </div>

    <!-- Floating Global Save Button -->
    <div 
      v-if="activeTab !== 'security'"
      class="fixed bottom-6 right-6 z-40"
    >
      <button 
        @click="saveAllSettings"
        :disabled="settingStore.loading"
        class="flex items-center space-x-2 px-6 py-3.5 bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white text-sm font-bold rounded-2xl shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/40 hover:-translate-y-0.5 transition duration-200 animate-pulse-glow"
      >
        <Save v-if="!settingStore.loading" class="w-5 h-5" />
        <Loader2 v-else class="w-5 h-5 animate-spin" />
        <span>Sozlamalarni saqlash</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Save, UploadCloud, Loader2, KeyRound, Trash2, Send, Settings, Globe, Moon, Eye, Type } from 'lucide-vue-next';
import { useSettingStore, useSettingsStore } from '@/stores/settings';
import { useAuthStore } from '@/stores/auth';

const settingStore = useSettingStore();
const settingsStore = useSettingsStore();
const authStore = useAuthStore();

// Language change: uses settingsStore directly, persists via system_language key
const onLanguageChange = async (lang) => {
  settingsStore.setLanguage(lang);
  // Save to server immediately
  try {
    const formData = new FormData();
    formData.append('system_language', lang);
    await settingsStore.updateSettings(formData);
  } catch (e) {
    // silent – non-critical
  }
};

const activeTab = ref('general');
const successMsg = ref('');
const errorMsg = ref('');

const tabs = [
  { id: 'general', label: 'Asosiy Sozlamalar' },
  { id: 'finance', label: 'Moliya va Lokalizatsiya' },
  { id: 'telegram', label: 'Telegram & Ogohlantirishlar' },
  { id: 'security', label: 'Xavfsizlik' }
];

// Form models
const generalForm = ref({
  restaurant_name: '',
  restaurant_phone: '',
  restaurant_hours: '',
  restaurant_address: ''
});
const logoFile = ref(null);
const logoPreview = ref(null);

const financeForm = ref({
  tax_rate: 12,
  currency: 'UZS',
  service_charge_rate: 10,
  receipt_header: '',
  receipt_footer: ''
});

const telegramForm = ref({
  telegram_bot_token: '',
  telegram_chat_id: '',
  telegram_notifications_enabled: false
});

const clearingCache = ref(false);
const testingConnection = ref(false);

const securityForm = ref({
  old_password: '',
  new_password: '',
  new_password_confirmation: ''
});

onMounted(async () => {
  await settingStore.fetchSettings();
  
  // Bind form values
  generalForm.value.restaurant_name = settingStore.settings.restaurant_name;
  generalForm.value.restaurant_phone = settingStore.settings.restaurant_phone;
  generalForm.value.restaurant_hours = settingStore.settings.restaurant_hours;
  generalForm.value.restaurant_address = settingStore.settings.restaurant_address;
  logoPreview.value = settingStore.settings.restaurant_logo;

  financeForm.value.tax_rate = parseFloat(settingStore.settings.tax_rate) || 0;
  financeForm.value.currency = settingStore.settings.currency || 'UZS';
  financeForm.value.service_charge_rate = parseFloat(settingStore.settings.service_charge_rate) || 0;
  financeForm.value.receipt_header = settingStore.settings.receipt_header || '';
  financeForm.value.receipt_footer = settingStore.settings.receipt_footer || '';

  telegramForm.value.telegram_bot_token = settingStore.settings.telegram_bot_token || '';
  telegramForm.value.telegram_chat_id = settingStore.settings.telegram_chat_id || '';
  telegramForm.value.telegram_notifications_enabled = filterBoolean(settingStore.settings.telegram_notifications_enabled);
});

const filterBoolean = (val) => {
  if (typeof val === 'boolean') return val;
  return val === 'true' || val === '1' || val === 1;
};

const handleLogoChange = (e) => {
  const file = e.target.files[0];
  if (!file) return;

  logoFile.value = file;
  logoPreview.value = URL.createObjectURL(file);
};

const saveAllSettings = async () => {
  successMsg.value = '';
  errorMsg.value = '';

  const formData = new FormData();
  
  // Appends values based on active forms
  formData.append('restaurant_name', generalForm.value.restaurant_name);
  formData.append('restaurant_phone', generalForm.value.restaurant_phone);
  formData.append('restaurant_hours', generalForm.value.restaurant_hours);
  formData.append('restaurant_address', generalForm.value.restaurant_address);

  formData.append('tax_rate', financeForm.value.tax_rate);
  formData.append('currency', financeForm.value.currency);
  formData.append('service_charge_rate', financeForm.value.service_charge_rate);
  formData.append('receipt_header', financeForm.value.receipt_header);
  formData.append('receipt_footer', financeForm.value.receipt_footer);

  formData.append('telegram_bot_token', telegramForm.value.telegram_bot_token);
  formData.append('telegram_chat_id', telegramForm.value.telegram_chat_id || '');
  formData.append('telegram_notifications_enabled', telegramForm.value.telegram_notifications_enabled ? 'true' : 'false');

  if (logoFile.value) {
    formData.append('restaurant_logo', logoFile.value);
  }

  try {
    const msg = await settingStore.updateSettings(formData);
    successMsg.value = msg || 'Sozlamalar muvaffaqiyatli saqlandi.';
    setTimeout(() => { successMsg.value = ''; }, 4000);
  } catch (err) {
    errorMsg.value = err.message;
  }
};

const submitPasswordChange = async () => {
  successMsg.value = '';
  errorMsg.value = '';

  if (!securityForm.value.old_password || !securityForm.value.new_password || !securityForm.value.new_password_confirmation) {
    errorMsg.value = 'Iltimos parollarni to\'liq to\'ldiring.';
    return;
  }

  try {
    const response = await fetch('/api/settings/password', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${authStore.token}`
      },
      body: JSON.stringify({
        old_password: securityForm.value.old_password,
        new_password: securityForm.value.new_password,
        new_password_confirmation: securityForm.value.new_password_confirmation
      })
    });

    const data = await response.json();
    if (!response.ok) throw new Error(data.message || 'Xatolik yuz berdi.');

    successMsg.value = data.message || 'Parol muvaffaqiyatli yangilandi.';
    securityForm.value = { old_password: '', new_password: '', new_password_confirmation: '' };
    setTimeout(() => { successMsg.value = ''; }, 4000);
  } catch (err) {
    errorMsg.value = err.message;
  }
};

const handleClearCache = async () => {
  clearingCache.value = true;
  successMsg.value = '';
  errorMsg.value = '';
  try {
    const response = await fetch('/api/settings/clear-cache', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${authStore.token}`
      }
    });

    const data = await response.json();
    if (!response.ok) throw new Error(data.message || 'Xatolik yuz berdi.');

    successMsg.value = data.message || 'Kesh tozalandi.';
    await settingStore.fetchSettings();
    setTimeout(() => { successMsg.value = ''; }, 4000);
  } catch (err) {
    errorMsg.value = err.message;
  } finally {
    clearingCache.value = false;
  }
};

const testTelegramConnection = async () => {
  testingConnection.value = true;
  successMsg.value = '';
  errorMsg.value = '';
  try {
    const response = await fetch('/api/settings/test-telegram', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${authStore.token}`
      },
      body: JSON.stringify({
        telegram_bot_token: telegramForm.value.telegram_bot_token,
        telegram_chat_id: telegramForm.value.telegram_chat_id
      })
    });

    const data = await response.json();
    if (!response.ok) throw new Error(data.message || 'Xatolik yuz berdi.');

    successMsg.value = data.message || 'Telegram xabari muvaffaqiyatli yuborildi!';
    setTimeout(() => { successMsg.value = ''; }, 4000);
  } catch (err) {
    errorMsg.value = err.message;
  } finally {
    testingConnection.value = false;
  }
};
</script>

<style scoped>
.animate-fadeIn {
  animation: fadeIn 0.3s ease-out forwards;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(5px); }
  to { opacity: 1; transform: translateY(0); }
}

.animate-pulse-glow {
  box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4);
  animation: pulse-glow 2s infinite;
}

@keyframes pulse-glow {
  0% {
    transform: scale(1);
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4);
  }
  70% {
    transform: scale(1);
    box-shadow: 0 0 0 10px rgba(16, 185, 129, 0);
  }
  100% {
    transform: scale(1);
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
  }
}
</style>
