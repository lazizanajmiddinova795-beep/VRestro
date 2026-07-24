<template>
  <ChefLayout>
    <div class="max-w-3xl mx-auto space-y-6">
      <div class="text-left space-y-2">
        <h2 class="text-slate-900 font-black text-2xl tracking-tight">Oshxona Ichki Sozlamalari</h2>
        <p class="text-slate-500 font-bold text-sm mt-1">Ishchi terminal displeyi va ovozli xabarnomalarni sozlang.</p>
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
                <span>Navbatchilik Faol</span>
              </div>
            </div>
          </div>

          <!-- Edit button -->
          <button 
            @click="openEditModal" 
            class="bg-slate-900 hover:bg-slate-800 text-white font-black py-2.5 px-4 rounded-xl border border-slate-950 shadow-sm transition duration-200 text-sm flex items-center space-x-2 self-center md:self-start"
          >
            <UserCheck class="w-4.5 h-4.5 text-orange-500" />
            <span>Ma'lumotlarni kiritish</span>
          </button>
        </div>

        <!-- Profile Data Grid UI Structure -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 pt-6 border-t border-slate-200">
          <div>
            <span class="text-slate-500 font-extrabold text-xs tracking-wider uppercase">Telefon Raqami</span>
            <span class="text-slate-900 font-black text-base mt-1 block">{{ chefPhone }}</span>
          </div>
          <div>
            <span class="text-slate-500 font-extrabold text-xs tracking-wider uppercase">Email Manzili</span>
            <span class="text-slate-900 font-black text-base mt-1 block">{{ chefEmail }}</span>
          </div>
          <div>
            <span class="text-slate-500 font-extrabold text-xs tracking-wider uppercase">Pasport Ma'lumotlari</span>
            <span class="text-slate-900 font-black text-base mt-1 block">{{ chefPassport }}</span>
          </div>
          <div>
            <span class="text-slate-500 font-extrabold text-xs tracking-wider uppercase">Tug'ilgan Sanasi</span>
            <span class="text-slate-900 font-black text-base mt-1 block">{{ chefBirthDate }}</span>
          </div>
          <div class="md:col-span-2">
            <span class="text-slate-500 font-extrabold text-xs tracking-wider uppercase">Yashash Manzili</span>
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
            <h3 class="text-slate-900 font-black text-lg flex items-center gap-2">Ovozli Xabarnomalar</h3>
          </div>

          <div class="space-y-6">
            <!-- New Order Sound Toggle -->
            <div class="flex items-center justify-between">
              <div>
                <label class="text-slate-800 font-black text-base block">Yangi Buyurtma Ovozi</label>
                <span class="text-slate-500 font-bold text-xs mt-0.5">Yangi buyurtma kelganda ovoz berish</span>
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
                <label class="text-slate-800 font-black text-base block">Kechikish Ogohlantirish Ovozi</label>
                <span class="text-slate-500 font-bold text-xs mt-0.5">Buyurtma 20 daqiqadan oshganda ogohlantirish</span>
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
                <span class="text-slate-900 font-black text-sm">Ovoz Balandligi</span>
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
              <span>Ovozni sinash (Test Sound)</span>
            </button>
          </div>
        </div>

        <!-- Scale Settings Card -->
        <div class="bg-white border-2 border-slate-200 rounded-3xl p-6 shadow-sm space-y-6">
          <div class="flex items-center space-x-3 border-b border-slate-200 pb-3 mb-6">
            <Maximize2 class="w-6 h-6 text-indigo-600" />
            <h3 class="text-slate-900 font-black text-lg flex items-center gap-2">KDS Displey Masshtabi</h3>
          </div>

          <div class="space-y-4">
            <p class="text-slate-500 font-bold text-xs leading-relaxed mb-4">
              Oshxona terminali ekran o'lchamiga qarab buyurtma kartalarining zichligi va shrift o'lchamlarini tanlang.
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
                    >Zich (Compact)</span>
                    <span 
                      class="text-xs mt-0.5 block"
                      :class="chefStore.kitchenSettings.layoutScale === 'compact' ? 'text-orange-700 font-bold' : 'text-slate-400 font-medium'"
                    >Monitorlar uchun (bir qatorda 5-6 ta karta)</span>
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
                    >Standart (Normal)</span>
                    <span 
                      class="text-xs mt-0.5 block"
                      :class="chefStore.kitchenSettings.layoutScale === 'normal' ? 'text-orange-700 font-bold' : 'text-slate-400 font-medium'"
                    >Klassik ko'rinish (bir qatorda 3-4 ta karta)</span>
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
                    >Yirik (Large / Zoom)</span>
                    <span 
                      class="text-xs mt-0.5 block"
                      :class="chefStore.kitchenSettings.layoutScale === 'large' ? 'text-orange-700 font-bold' : 'text-slate-400 font-medium'"
                    >10 dyumli planshetlar uchun (bir qatorda 2 ta karta)</span>
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
      <!-- Edit Profile Modal -->
      <div v-if="showEditModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div class="bg-white border-2 border-slate-300 rounded-3xl w-full max-w-lg shadow-2xl p-6 space-y-6">
          <div class="flex items-center justify-between border-b pb-3">
            <h3 class="text-slate-900 font-black text-xl">Profil Ma'lumotlarini Tahrirlash</h3>
            <button @click="showEditModal = false" class="text-slate-500 hover:text-slate-700 font-black text-lg">✕</button>
          </div>

          <form @submit.prevent="saveProfile" class="space-y-4 text-left">
            <div>
              <label class="block text-sm font-black text-slate-900 mb-1">To'liq Ism (Full Name)</label>
              <input 
                type="text" 
                v-model="editForm.name" 
                required
                class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl focus:border-orange-500 outline-none transition font-bold"
              />
            </div>

            <div>
              <label class="block text-sm font-black text-slate-900 mb-1">Telefon Raqami</label>
              <input 
                type="text" 
                v-model="editForm.phone" 
                required
                placeholder="+998 (90) 123-45-67"
                class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl focus:border-orange-500 outline-none transition font-bold"
              />
            </div>

            <div>
              <label class="block text-sm font-black text-slate-900 mb-1">Email Manzili</label>
              <input 
                type="email" 
                v-model="editForm.email" 
                class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl focus:border-orange-500 outline-none transition font-bold"
              />
            </div>

            <div>
              <label class="block text-sm font-black text-slate-900 mb-1">Pasport Ma'lumotlari (Seriya va raqam)</label>
              <input 
                type="text" 
                v-model="editForm.passport_number" 
                placeholder="AA1234567"
                class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl focus:border-orange-500 outline-none transition font-bold"
              />
            </div>

            <div>
              <label class="block text-sm font-black text-slate-900 mb-1">Tug'ilgan Sanasi</label>
              <input 
                type="date" 
                v-model="editForm.birth_date" 
                class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl focus:border-orange-500 outline-none transition font-bold"
              />
            </div>

            <div>
              <label class="block text-sm font-black text-slate-900 mb-1">Yashash Manzili</label>
              <input 
                type="text" 
                v-model="editForm.address" 
                class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl focus:border-orange-500 outline-none transition font-bold"
              />
            </div>

            <div class="flex items-center space-x-3 pt-4 border-t">
              <button 
                type="button" 
                @click="showEditModal = false"
                class="flex-1 py-3 border-2 border-slate-200 hover:bg-slate-50 text-slate-800 rounded-xl font-bold transition"
              >
                Bekor qilish
              </button>
              <button 
                type="submit" 
                :disabled="saving"
                class="flex-1 py-3 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-black transition disabled:opacity-50"
              >
                {{ saving ? 'Saqlanmoqda...' : 'Saqlash' }}
              </button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </ChefLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useChefStore } from '@/stores/chef';
import ChefLayout from '@/components/ChefLayout.vue';
import { Volume2, Maximize2, Music, UserCheck } from 'lucide-vue-next';

const authStore = useAuthStore();
const chefStore = useChefStore();

const chefName = computed(() => authStore.user?.name || 'Jasur Oshpaz');
const chefRole = computed(() => authStore.user?.role_label || 'Bosh Oshpaz / Chief Chef');
const chefAvatar = computed(() => authStore.user?.avatar_url || null);
const avatarInitials = computed(() => {
  return chefName.value.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
});

const chefPhone = computed(() => authStore.user?.phone || 'Kiritilmagan');
const chefEmail = computed(() => authStore.user?.email || 'Kiritilmagan');
const chefPassport = computed(() => {
  const raw = authStore.user?.passport_number;
  if (!raw) return 'Kiritilmagan';
  return raw.substring(0, 2) + ' ****' + raw.substring(raw.length - 3);
});
const chefBirthDate = computed(() => {
  const raw = authStore.user?.birth_date;
  if (!raw) return 'Kiritilmagan';
  try {
    const date = new Date(raw);
    return date.toLocaleDateString('uz-UZ');
  } catch {
    return raw;
  }
});
const chefAddress = computed(() => authStore.user?.address || 'Kiritilmagan');

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
        'Authorization': `Bearer ${authStore.token}`
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
    
    showEditModal.value = false;
  } catch (err) {
    alert(err.message);
  } finally {
    saving.value = false;
  }
};

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
