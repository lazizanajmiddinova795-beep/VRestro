<template>
  <ChefLayout>
    <div class="max-w-3xl mx-auto space-y-6">
      <div class="text-left space-y-2">
        <h2 class="text-2xl font-black text-slate-900 tracking-wide">Oshxona Ichki Sozlamalari</h2>
        <p class="text-sm md:text-base text-slate-700 font-bold">Ishchi terminal displeyi va ovozli xabarnomalarni sozlang.</p>
      </div>

      <!-- Settings Cards Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Audio Settings Card -->
        <div class="bg-white border-2 border-slate-300 rounded-2xl shadow-md p-6 space-y-6">
          <div class="flex items-center space-x-3 border-b border-slate-200 pb-3 mb-6">
            <Volume2 class="w-6 h-6 text-orange-600" />
            <h3 class="text-slate-900 font-black text-xl">Ovozli Xabarnolar</h3>
          </div>

          <div class="space-y-6">
            <!-- New Order Sound Toggle -->
            <div class="flex items-center justify-between">
              <div>
                <label class="text-base font-black text-slate-900 block">Yangi Buyurtma Ovozi</label>
                <span class="text-slate-700 font-bold text-sm md:text-base">Yangi buyurtma kelganda ovoz berish</span>
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
                <label class="text-base font-black text-slate-900 block">Kechikish Ogohlantirish Ovozi</label>
                <span class="text-slate-700 font-bold text-sm md:text-base">Buyurtma 20 daqiqadan oshganda ogohlantirish</span>
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
              <div class="flex items-center justify-between text-sm md:text-base font-bold">
                <span class="text-slate-900 font-black">Ovoz Balandligi</span>
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
              class="bg-slate-900 hover:bg-slate-800 text-white font-bold py-3 px-6 rounded-xl w-full border border-slate-950 shadow-sm transition duration-200 flex items-center justify-center space-x-2 text-base"
            >
              <Music class="w-5 h-5 text-orange-500" />
              <span>Ovozni sinash (Test Sound)</span>
            </button>
          </div>
        </div>

        <!-- Scale Settings Card -->
        <div class="bg-white border-2 border-slate-300 rounded-2xl shadow-md p-6 space-y-6">
          <div class="flex items-center space-x-3 border-b border-slate-200 pb-3 mb-6">
            <Maximize2 class="w-6 h-6 text-orange-600" />
            <h3 class="text-slate-900 font-black text-xl">KDS Displey Masshtabi</h3>
          </div>

          <div class="space-y-4">
            <p class="text-slate-700 font-bold text-sm md:text-base leading-relaxed mb-6">
              Oshxona terminali ekran o'lchamiga qarab buyurtma kartalarining zichligi va shrift o'lchamlarini tanlang.
            </p>

            <div class="flex flex-col space-y-3 pt-2">
              <!-- Compact Scale Option -->
              <button 
                @click="setScale('compact')"
                class="w-full text-left transition duration-200"
                :class="chefStore.kitchenSettings.layoutScale === 'compact' 
                  ? 'bg-orange-50 border-2 border-orange-500 p-4 rounded-xl text-orange-950 font-black block ring-2 ring-orange-500/20' 
                  : 'bg-slate-50 border-2 border-slate-200 p-4 rounded-xl text-slate-800 font-bold block'"
              >
                <div class="flex items-center justify-between">
                  <div>
                    <span class="block text-base md:text-lg">Zich (Compact)</span>
                    <span class="text-xs md:text-sm font-bold text-slate-700 block mt-1">Monitorlar uchun (bir qatorda 5-6 ta karta)</span>
                  </div>
                  <span class="w-4 h-4 rounded-full border-2 border-orange-500 flex items-center justify-center flex-shrink-0">
                    <span v-if="chefStore.kitchenSettings.layoutScale === 'compact'" class="w-2.5 h-2.5 rounded-full bg-orange-500"></span>
                  </span>
                </div>
              </button>

              <!-- Normal Scale Option -->
              <button 
                @click="setScale('normal')"
                class="w-full text-left transition duration-200"
                :class="chefStore.kitchenSettings.layoutScale === 'normal' 
                  ? 'bg-orange-50 border-2 border-orange-500 p-4 rounded-xl text-orange-950 font-black block ring-2 ring-orange-500/20' 
                  : 'bg-slate-50 border-2 border-slate-200 p-4 rounded-xl text-slate-800 font-bold block'"
              >
                <div class="flex items-center justify-between">
                  <div>
                    <span class="block text-base md:text-lg">Standart (Normal)</span>
                    <span class="text-xs md:text-sm font-bold text-slate-700 block mt-1">Klassik ko'rinish (bir qatorda 3-4 ta karta)</span>
                  </div>
                  <span class="w-4 h-4 rounded-full border-2 border-orange-500 flex items-center justify-center flex-shrink-0">
                    <span v-if="chefStore.kitchenSettings.layoutScale === 'normal'" class="w-2.5 h-2.5 rounded-full bg-orange-500"></span>
                  </span>
                </div>
              </button>

              <!-- Large Scale Option -->
              <button 
                @click="setScale('large')"
                class="w-full text-left transition duration-200"
                :class="chefStore.kitchenSettings.layoutScale === 'large' 
                  ? 'bg-orange-50 border-2 border-orange-500 p-4 rounded-xl text-orange-950 font-black block ring-2 ring-orange-500/20' 
                  : 'bg-slate-50 border-2 border-slate-200 p-4 rounded-xl text-slate-800 font-bold block'"
              >
                <div class="flex items-center justify-between">
                  <div>
                    <span class="block text-base md:text-lg">Yirik (Large / Zoom)</span>
                    <span class="text-xs md:text-sm font-bold text-slate-700 block mt-1">10 dyumli planshetlar uchun (bir qatorda 2 ta karta)</span>
                  </div>
                  <span class="w-4 h-4 rounded-full border-2 border-orange-500 flex items-center justify-center flex-shrink-0">
                    <span v-if="chefStore.kitchenSettings.layoutScale === 'large'" class="w-2.5 h-2.5 rounded-full bg-orange-500"></span>
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
