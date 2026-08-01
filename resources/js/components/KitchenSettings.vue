<template>
  <ChefLayout>
    <div class="max-w-3xl mx-auto space-y-6">
      <div class="text-left space-y-2">
        <h2 class="text-slate-900 font-black text-2xl tracking-tight">{{ settingsStore.t('kitchen.settings_title') }}</h2>
        <p class="text-slate-500 font-bold text-sm mt-1">{{ settingsStore.t('kitchen.settings_subtitle') }}</p>
      </div>

      <!-- Chef Profile Card (Oshpaz Shaxsiy Profili) -->
      <div class="bg-white border-2 border-slate-200 rounded-3xl p-6 shadow-sm mb-6">
        <div class="flex flex-col md:flex-row items-center md:items-start space-y-4 md:space-y-0 justify-between">
          <div class="flex flex-col md:flex-row items-center md:items-start space-y-4 md:space-y-0 md:space-x-6">
            <!-- Avatar Slot -->
            <div class="flex-shrink-0">
              <img 
                v-if="chefAvatar" 
                :src="chefAvatar" 
                alt="Chef Avatar" 
                class="w-24 h-24 rounded-full border-4 border-orange-500 shadow-sm mx-auto md:mx-0 object-cover"
              />
              <div 
                v-else 
                class="w-24 h-24 rounded-full border-4 border-orange-500 shadow-sm mx-auto md:mx-0 mb-4 md:mb-0 bg-orange-100 text-orange-600 flex items-center justify-center text-3xl font-black"
              >
                {{ avatarInitials }}
              </div>
            </div>
            
            <!-- Credentials -->
            <div class="text-center md:text-left flex-grow">
              <h3 class="text-slate-900 font-black text-2xl tracking-tight">{{ chefName }}</h3>
              <span class="inline-block bg-slate-100 text-slate-800 text-xs font-black px-3 py-1 rounded-full mt-1 border border-slate-300">
                {{ chefRole }}
              </span>
              <div class="flex items-center justify-center md:justify-start space-x-2 text-emerald-600 font-bold mt-3">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 animate-pulse"></span>
                <span>{{ settingsStore.t('kitchen.shift_active') }}</span>
              </div>
            </div>
          </div>

          <!-- Read-only: this profile is entered by the administrator and cannot be edited here -->
          <div class="flex items-center space-x-2 bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 self-center md:self-start">
            <Lock class="w-3.5 h-3.5 text-slate-400 shrink-0" />
            <span class="text-3xs text-slate-500 font-semibold">{{ settingsStore.t('profile.readonly_notice') }}</span>
          </div>
        </div>

        <!-- Profile Data Grid UI Structure -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 pt-6 border-t border-slate-200">
          <div>
            <span class="text-slate-500 font-extrabold text-xs tracking-wider uppercase">{{ settingsStore.t('kitchen.phone_label') }}</span>
            <span class="text-slate-900 font-black text-base mt-1 block">{{ chefPhone }}</span>
          </div>
          <div>
            <span class="text-slate-500 font-extrabold text-xs tracking-wider uppercase">{{ settingsStore.t('kitchen.email_label') }}</span>
            <span class="text-slate-900 font-black text-base mt-1 block">{{ chefEmail }}</span>
          </div>
          <div>
            <span class="text-slate-500 font-extrabold text-xs tracking-wider uppercase">{{ settingsStore.t('kitchen.passport_label') }}</span>
            <span class="text-slate-900 font-black text-base mt-1 block">{{ chefPassport }}</span>
          </div>
          <div>
            <span class="text-slate-500 font-extrabold text-xs tracking-wider uppercase">{{ settingsStore.t('kitchen.birthdate_label') }}</span>
            <span class="text-slate-900 font-black text-base mt-1 block">{{ chefBirthDate }}</span>
          </div>
          <div class="md:col-span-2">
            <span class="text-slate-500 font-extrabold text-xs tracking-wider uppercase">{{ settingsStore.t('kitchen.address_label') }}</span>
            <span class="text-slate-900 font-black text-base mt-1 block">{{ chefAddress }}</span>
          </div>
        </div>
      </div>

      <!-- Settings Cards Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Audio Settings Card -->
        <div class="bg-white border-2 border-slate-200 rounded-3xl p-6 shadow-sm space-y-6">
          <div class="flex items-center space-x-3 border-b border-slate-200 pb-3 mb-6">
            <Volume2 class="w-6 h-6 text-indigo-600" />
            <h3 class="text-slate-900 font-black text-lg flex items-center gap-2">{{ settingsStore.t('kitchen.sound_notifications') }}</h3>
          </div>

          <div class="space-y-6">
            <!-- New Order Sound Toggle -->
            <div class="flex items-center justify-between">
              <div>
                <label class="text-slate-800 font-black text-base block">{{ settingsStore.t('kitchen.new_order_sound') }}</label>
                <span class="text-slate-500 font-bold text-xs mt-0.5">{{ settingsStore.t('kitchen.new_order_sound_desc') }}</span>
              </div>
              <button 
                @click="toggleSetting('newOrderSound')"
                class="w-12 h-7 rounded-full transition-all duration-300 relative border-2 flex-shrink-0"
                :class="chefStore.kitchenSettings.newOrderSound 
                  ? 'bg-orange-500 border-orange-600' 
                  : 'bg-slate-200 border-slate-350'"
              >
                <span 
                  class="absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white transition-all duration-300 shadow-sm"
                  :class="chefStore.kitchenSettings.newOrderSound ? 'translate-x-5' : 'translate-x-0'"
                ></span>
              </button>
            </div>

            <!-- Overdue Warning Sound Toggle -->
            <div class="flex items-center justify-between">
              <div>
                <label class="text-slate-800 font-black text-base block">{{ settingsStore.t('kitchen.overdue_sound') }}</label>
                <span class="text-slate-500 font-bold text-xs mt-0.5">{{ settingsStore.t('kitchen.overdue_sound_desc') }}</span>
              </div>
              <button 
                @click="toggleSetting('alertSound')"
                class="w-12 h-7 rounded-full transition-all duration-300 relative border-2 flex-shrink-0"
                :class="chefStore.kitchenSettings.alertSound 
                  ? 'bg-orange-500 border-orange-600' 
                  : 'bg-slate-200 border-slate-350'"
              >
                <span 
                  class="absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white transition-all duration-300 shadow-sm"
                  :class="chefStore.kitchenSettings.alertSound ? 'translate-x-5' : 'translate-x-0'"
                ></span>
              </button>
            </div>

            <!-- Volume Slider -->
            <div class="space-y-2 pt-2">
              <div class="flex items-center justify-between text-sm font-bold">
                <span class="text-slate-900 font-black text-sm">{{ settingsStore.t('kitchen.volume_level') }}</span>
                <span class="font-mono text-orange-600 font-extrabold">
                  {{ Math.round(chefStore.kitchenSettings.volume * 100) }}%
                </span>
              </div>
              <input 
                type="range" 
                min="0" 
                max="1" 
                step="0.1" 
                :value="chefStore.kitchenSettings.volume"
                @input="updateVolume"
                class="w-full accent-orange-500 cursor-pointer bg-slate-200 h-2 rounded-lg appearance-none"
              />
            </div>

            <!-- Test Sound Button -->
            <button 
              @click="testSound"
              class="w-full bg-slate-100 hover:bg-slate-200 text-slate-800 font-extrabold py-3.5 rounded-2xl flex items-center justify-center gap-2 border border-slate-200 transition-colors"
            >
              <Music class="w-5 h-5 text-indigo-650" />
              <span>{{ settingsStore.t('kitchen.test_sound') }}</span>
            </button>
          </div>
        </div>

        <!-- Scale Settings Card -->
        <div class="bg-white border-2 border-slate-200 rounded-3xl p-6 shadow-sm space-y-6">
          <div class="flex items-center space-x-3 border-b border-slate-200 pb-3 mb-6">
            <Maximize2 class="w-6 h-6 text-indigo-600" />
            <h3 class="text-slate-900 font-black text-lg flex items-center gap-2">{{ settingsStore.t('kitchen.display_scale') }}</h3>
          </div>

          <div class="space-y-4">
            <p class="text-slate-500 font-bold text-xs leading-relaxed mb-4">
              {{ settingsStore.t('kitchen.display_scale_desc') }}
            </p>

            <div class="flex flex-col space-y-3 pt-2">
              <!-- Compact Scale Option -->
              <button 
                @click="setScale('compact')"
                class="w-full text-left transition duration-200"
                :class="chefStore.kitchenSettings.layoutScale === 'compact' 
                  ? 'border-2 border-orange-500 bg-orange-50/30 rounded-2xl p-4' 
                  : 'border-2 border-slate-200 bg-slate-50/50 rounded-2xl p-4'"
              >
                <div class="flex items-center justify-between">
                  <div>
                    <span 
                      class="block text-sm"
                      :class="chefStore.kitchenSettings.layoutScale === 'compact' ? 'text-orange-950 font-black' : 'text-slate-700 font-extrabold'"
                    >{{ settingsStore.t('kitchen.scale_compact') }}</span>
                    <span
                      class="text-xs mt-0.5 block"
                      :class="chefStore.kitchenSettings.layoutScale === 'compact' ? 'text-orange-700 font-bold' : 'text-slate-400 font-medium'"
                    >{{ settingsStore.t('kitchen.scale_compact_desc') }}</span>
                  </div>
                  <span 
                    class="w-4 h-4 rounded-full border-2 flex items-center justify-center flex-shrink-0"
                    :class="chefStore.kitchenSettings.layoutScale === 'compact' ? 'border-orange-500 bg-orange-500 border-4 border-orange-200' : 'border-slate-350 bg-white'"
                  >
                  </span>
                </div>
              </button>

              <!-- Normal Scale Option -->
              <button 
                @click="setScale('normal')"
                class="w-full text-left transition duration-200"
                :class="chefStore.kitchenSettings.layoutScale === 'normal' 
                  ? 'border-2 border-orange-500 bg-orange-50/30 rounded-2xl p-4' 
                  : 'border-2 border-slate-200 bg-slate-50/50 rounded-2xl p-4'"
              >
                <div class="flex items-center justify-between">
                  <div>
                    <span 
                      class="block text-sm"
                      :class="chefStore.kitchenSettings.layoutScale === 'normal' ? 'text-orange-950 font-black' : 'text-slate-700 font-extrabold'"
                    >{{ settingsStore.t('kitchen.scale_normal') }}</span>
                    <span
                      class="text-xs mt-0.5 block"
                      :class="chefStore.kitchenSettings.layoutScale === 'normal' ? 'text-orange-700 font-bold' : 'text-slate-400 font-medium'"
                    >{{ settingsStore.t('kitchen.scale_normal_desc') }}</span>
                  </div>
                  <span 
                    class="w-4 h-4 rounded-full border-2 flex items-center justify-center flex-shrink-0"
                    :class="chefStore.kitchenSettings.layoutScale === 'normal' ? 'border-orange-500 bg-orange-500 border-4 border-orange-200' : 'border-slate-350 bg-white'"
                  >
                  </span>
                </div>
              </button>

              <!-- Large Scale Option -->
              <button 
                @click="setScale('large')"
                class="w-full text-left transition duration-200"
                :class="chefStore.kitchenSettings.layoutScale === 'large' 
                  ? 'border-2 border-orange-500 bg-orange-50/30 rounded-2xl p-4' 
                  : 'border-2 border-slate-200 bg-slate-50/50 rounded-2xl p-4'"
              >
                <div class="flex items-center justify-between">
                  <div>
                    <span 
                      class="block text-sm"
                      :class="chefStore.kitchenSettings.layoutScale === 'large' ? 'text-orange-950 font-black' : 'text-slate-700 font-extrabold'"
                    >{{ settingsStore.t('kitchen.scale_large') }}</span>
                    <span
                      class="text-xs mt-0.5 block"
                      :class="chefStore.kitchenSettings.layoutScale === 'large' ? 'text-orange-700 font-bold' : 'text-slate-400 font-medium'"
                    >{{ settingsStore.t('kitchen.scale_large_desc') }}</span>
                  </div>
                  <span 
                    class="w-4 h-4 rounded-full border-2 flex items-center justify-center flex-shrink-0"
                    :class="chefStore.kitchenSettings.layoutScale === 'large' ? 'border-orange-500 bg-orange-500 border-4 border-orange-200' : 'border-slate-350 bg-white'"
                  >
                  </span>
                </div>
              </button>
            </div>
          </div>
        </div>

      </div>

    </div>
  </ChefLayout>
</template>

<script setup>
import { useSettingsStore } from '@/stores/settings';
const settingsStore = useSettingsStore();
import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useChefStore } from '@/stores/chef';
import ChefLayout from '@/components/ChefLayout.vue';
import { Volume2, Maximize2, Music, Lock } from 'lucide-vue-next';

const authStore = useAuthStore();
const chefStore = useChefStore();

const chefName = computed(() => authStore.user?.name || 'Jasur Oshpaz');
const chefRole = computed(() => authStore.user?.role_label || 'Bosh Oshpaz / Chief Chef');
const chefAvatar = computed(() => authStore.user?.avatar_url || null);
const avatarInitials = computed(() => {
  return chefName.value.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
});

const chefPhone = computed(() => authStore.user?.phone || settingsStore.t('not_entered'));
const chefEmail = computed(() => authStore.user?.email || settingsStore.t('not_entered'));
const chefPassport = computed(() => {
  const raw = authStore.user?.passport_number;
  if (!raw) return settingsStore.t('not_entered');
  return raw.substring(0, 2) + ' ****' + raw.substring(raw.length - 3);
});
const chefBirthDate = computed(() => {
  const raw = authStore.user?.birth_date;
  if (!raw) return settingsStore.t('not_entered');
  try {
    const date = new Date(raw);
    return date.toLocaleDateString('uz-UZ');
  } catch {
    return raw;
  }
});
const chefAddress = computed(() => authStore.user?.address || settingsStore.t('not_entered'));

const toggleSetting = (key) => {
  const currentValue = chefStore.kitchenSettings[key];
  chefStore.updateSetting(key, !currentValue);
};

const updateVolume = (event) => {
  const val = parseFloat(event.target.value);
  chefStore.updateSetting('volume', val);
};

const setScale = (scale) => {
  chefStore.updateSetting('layoutScale', scale);
};

const testSound = () => {
  chefStore.playChime('newOrder');
};
</script>
