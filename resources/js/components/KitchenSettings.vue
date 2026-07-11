<template>
  <ChefLayout>
    <div class="max-w-3xl mx-auto space-y-6">
      <div class="text-left space-y-1">
        <h2 class="text-xl font-bold text-white tracking-wide">Oshxona Ichki Sozlamalari</h2>
        <p class="text-xs text-slate-400">Ishchi terminal displeyi va ovozli xabarnomalarni sozlang.</p>
      </div>

      <!-- Settings Cards Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Audio Settings Card -->
        <div class="rounded-2xl border border-white/5 bg-slate-900/40 backdrop-blur-md p-6 space-y-6">
          <div class="flex items-center space-x-3 border-b border-white/5 pb-3">
            <Volume2 class="w-5 h-5 text-orange-400" />
            <h3 class="text-sm font-bold text-slate-200">Ovozli Xabarnomalar</h3>
          </div>

          <div class="space-y-4">
            <!-- New Order Sound Toggle -->
            <div class="flex items-center justify-between">
              <div>
                <label class="text-xs font-bold text-slate-300 block">Yangi Buyurtma Ovozi</label>
                <span class="text-[10px] text-slate-500">Yangi buyurtma kelganda ovoz berish</span>
              </div>
              <button 
                @click="toggleSetting('newOrderSound')"
                class="w-12 h-6 rounded-full transition-all duration-300 relative border"
                :class="chefStore.kitchenSettings.newOrderSound 
                  ? 'bg-orange-500 border-orange-500' 
                  : 'bg-white/5 border-white/10'"
              >
                <span 
                  class="absolute top-0.5 w-4.5 h-4.5 rounded-full bg-white transition-all duration-300"
                  :class="chefStore.kitchenSettings.newOrderSound ? 'left-6.5' : 'left-0.5'"
                ></span>
              </button>
            </div>

            <!-- Overdue Warning Sound Toggle -->
            <div class="flex items-center justify-between">
              <div>
                <label class="text-xs font-bold text-slate-300 block">Kechikish Ogohlantirish Ovozi</label>
                <span class="text-[10px] text-slate-500">Buyurtma 20 daqiqadan oshganda ogohlantirish</span>
              </div>
              <button 
                @click="toggleSetting('alertSound')"
                class="w-12 h-6 rounded-full transition-all duration-300 relative border"
                :class="chefStore.kitchenSettings.alertSound 
                  ? 'bg-orange-500 border-orange-500' 
                  : 'bg-white/5 border-white/10'"
              >
                <span 
                  class="absolute top-0.5 w-4.5 h-4.5 rounded-full bg-white transition-all duration-300"
                  :class="chefStore.kitchenSettings.alertSound ? 'left-6.5' : 'left-0.5'"
                ></span>
              </button>
            </div>

            <!-- Volume Slider -->
            <div class="space-y-2 pt-2">
              <div class="flex items-center justify-between text-xs">
                <span class="font-bold text-slate-300">Ovoz Balandligi</span>
                <span class="font-mono text-orange-400 font-extrabold">
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
                class="w-full accent-orange-500 cursor-pointer bg-white/10 h-2 rounded-lg appearance-none"
              />
            </div>

            <!-- Test Sound Button -->
            <button 
              @click="testSound"
              class="w-full py-3 px-4 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 text-slate-200 hover:text-white font-bold text-xs transition duration-200 flex items-center justify-center space-x-2"
            >
              <Music class="w-4 h-4 text-orange-400" />
              <span>Ovozni sinash (Test Sound)</span>
            </button>
          </div>
        </div>

        <!-- Scale Settings Card -->
        <div class="rounded-2xl border border-white/5 bg-slate-900/40 backdrop-blur-md p-6 space-y-6">
          <div class="flex items-center space-x-3 border-b border-white/5 pb-3">
            <Maximize2 class="w-5 h-5 text-orange-400" />
            <h3 class="text-sm font-bold text-slate-200">KDS Displey Masshtabi</h3>
          </div>

          <div class="space-y-4">
            <p class="text-xs text-slate-400 leading-relaxed">
              Oshxona terminali ekran o'lchamiga qarab buyurtma kartalarining zichligi va shrift o'lchamlarini tanlang.
            </p>

            <div class="flex flex-col space-y-2.5">
              <!-- Compact Scale Option -->
              <button 
                @click="setScale('compact')"
                class="w-full py-3.5 px-4 rounded-xl border flex items-center justify-between font-bold text-xs transition duration-200"
                :class="chefStore.kitchenSettings.layoutScale === 'compact' 
                  ? 'bg-orange-500/10 border-orange-500/40 text-orange-300' 
                  : 'bg-white/5 border-white/5 text-slate-400 hover:bg-white/10'"
              >
                <div class="text-left">
                  <span class="block">Zich (Compact)</span>
                  <span class="text-[9px] font-normal text-slate-500">Monitorlar uchun (bir qatorda 5-6 ta karta)</span>
                </div>
                <span class="w-2.5 h-2.5 rounded-full border border-orange-400/50 flex items-center justify-center">
                  <span v-if="chefStore.kitchenSettings.layoutScale === 'compact'" class="w-1.5 h-1.5 rounded-full bg-orange-400"></span>
                </span>
              </button>

              <!-- Normal Scale Option -->
              <button 
                @click="setScale('normal')"
                class="w-full py-3.5 px-4 rounded-xl border flex items-center justify-between font-bold text-xs transition duration-200"
                :class="chefStore.kitchenSettings.layoutScale === 'normal' 
                  ? 'bg-orange-500/10 border-orange-500/40 text-orange-300' 
                  : 'bg-white/5 border-white/5 text-slate-400 hover:bg-white/10'"
              >
                <div class="text-left">
                  <span class="block">Standart (Normal)</span>
                  <span class="text-[9px] font-normal text-slate-500">Klassik ko'rinish (bir qatorda 3-4 ta karta)</span>
                </div>
                <span class="w-2.5 h-2.5 rounded-full border border-orange-400/50 flex items-center justify-center">
                  <span v-if="chefStore.kitchenSettings.layoutScale === 'normal'" class="w-1.5 h-1.5 rounded-full bg-orange-400"></span>
                </span>
              </button>

              <!-- Large Scale Option -->
              <button 
                @click="setScale('large')"
                class="w-full py-3.5 px-4 rounded-xl border flex items-center justify-between font-bold text-xs transition duration-200"
                :class="chefStore.kitchenSettings.layoutScale === 'large' 
                  ? 'bg-orange-500/10 border-orange-500/40 text-orange-300' 
                  : 'bg-white/5 border-white/5 text-slate-400 hover:bg-white/10'"
              >
                <div class="text-left">
                  <span class="block">Yirik (Large / Zoom)</span>
                  <span class="text-[9px] font-normal text-slate-500">10 dyumli planshetlar uchun (bir qatorda 2 ta karta)</span>
                </div>
                <span class="w-2.5 h-2.5 rounded-full border border-orange-400/50 flex items-center justify-center">
                  <span v-if="chefStore.kitchenSettings.layoutScale === 'large'" class="w-1.5 h-1.5 rounded-full bg-orange-400"></span>
                </span>
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </ChefLayout>
</template>

<script setup>
import { useChefStore } from '@/stores/chef';
import ChefLayout from '@/components/ChefLayout.vue';
import { Volume2, Maximize2, Music } from 'lucide-vue-next';

const chefStore = useChefStore();

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
