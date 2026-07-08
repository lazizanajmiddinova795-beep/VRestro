<template>
  <div class="min-h-screen bg-radial from-slate-900 via-slate-950 to-black text-slate-100 flex items-center justify-center p-6 relative font-sans">
    <!-- Background glow decoration -->
    <div class="absolute w-96 h-96 rounded-full bg-violet-600/10 blur-[100px] -top-12 -left-12 pointer-events-none"></div>
    <div class="absolute w-96 h-96 rounded-full bg-indigo-600/10 blur-[100px] -bottom-12 -right-12 pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10">
      <!-- Logo header -->
      <div class="text-center mb-8">
        <router-link to="/" class="inline-flex items-center space-x-3 group">
          <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-violet-600 to-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-500/20 group-hover:scale-105 transition-transform duration-300">
            <ChefHat class="w-7 h-7 text-white" />
          </div>
          <span class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-white via-indigo-200 to-indigo-400 tracking-wider">
            VRestro
          </span>
        </router-link>
        <p class="text-slate-400 text-sm mt-2">Tizimga kirish va autentifikatsiya</p>
      </div>

      <!-- Glassmorphic Login Card -->
      <div class="backdrop-blur-xl bg-slate-900/65 border border-white/10 rounded-3xl p-8 shadow-2xl shadow-black/50 transition-all duration-500 overflow-hidden relative">
        
        <!-- STEP 1: Credentials Form -->
        <div v-if="authStore.loginStep === 'credentials'" class="space-y-6">
          <div class="space-y-1">
            <h2 class="text-xl font-bold text-white">Xush kelibsiz</h2>
            <p class="text-xs text-slate-400">Davom etish uchun login va parolingizni kiriting</p>
          </div>

          <form @submit.prevent="handleCredentialsSubmit" class="space-y-5">
            <!-- Login Field -->
            <div class="space-y-2">
              <label for="login" class="text-xs font-semibold tracking-wider text-slate-400 uppercase">Login</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                  <User class="w-5 h-5" />
                </span>
                <input 
                  type="text" 
                  id="login" 
                  v-model="login"
                  required
                  placeholder="admin" 
                  class="w-full bg-slate-950/50 border border-white/10 rounded-xl py-3 pl-11 pr-4 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500/60 transition duration-200"
                />
              </div>
            </div>

            <!-- Password Field -->
            <div class="space-y-2">
              <label for="password" class="text-xs font-semibold tracking-wider text-slate-400 uppercase">Parol</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                  <Lock class="w-5 h-5" />
                </span>
                <input 
                  type="password" 
                  id="password" 
                  v-model="password"
                  required
                  placeholder="••••••••" 
                  class="w-full bg-slate-950/50 border border-white/10 rounded-xl py-3 pl-11 pr-4 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500/60 transition duration-200"
                />
              </div>
            </div>

            <!-- Display Errors -->
            <div v-if="error" class="p-3 rounded-lg bg-red-500/10 border border-red-500/20 text-xs text-red-400 font-medium">
              {{ error }}
            </div>

            <!-- Submit Button -->
            <button 
              type="submit" 
              :disabled="loading"
              class="w-full relative group py-3.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 font-bold text-white text-sm shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/50 transition-all duration-200 flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="loading">Yuklanmoqda...</span>
              <span v-else class="flex items-center space-x-2">
                <span>Keyingi qadam</span>
                <ArrowRight class="w-4 h-4" />
              </span>
            </button>
          </form>

          <div class="text-center pt-2">
            <router-link to="/" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium transition duration-200">
              Bosh sahifaga qaytish
            </router-link>
          </div>
        </div>

        <!-- STEP 2: Biometric / Face ID Simulation -->
        <div v-else-if="authStore.loginStep === 'biometrics'" class="space-y-6 flex flex-col items-center">
          <div class="text-center space-y-1 w-full">
            <h2 class="text-xl font-bold text-white">Face ID Tekshiruvi</h2>
            <p class="text-xs text-slate-400">Identifikatsiyani yakunlash uchun biometrik tekshiruvdan o'ting</p>
          </div>

          <!-- Face Scanner Box -->
          <div class="relative w-48 h-48 rounded-3xl border-2 border-dashed border-indigo-500/30 bg-slate-950/60 flex items-center justify-center overflow-hidden my-4">
            <!-- Scan grid overlay -->
            <div class="absolute inset-0 bg-radial from-indigo-500/5 to-transparent pointer-events-none"></div>

            <!-- Scanner Face Silhouette -->
            <ScanFace class="w-24 h-24 text-indigo-500/20 transition-all duration-300" :class="{'text-indigo-400 animate-pulseScale': scanning}" />

            <!-- Scanner Laser Animation -->
            <div 
              v-if="scanning" 
              class="absolute left-0 right-0 h-1 bg-gradient-to-r from-transparent via-cyan-400 to-transparent shadow-[0_0_12px_2px_rgba(34,211,238,0.5)] animate-scanLine"
            ></div>

            <div v-if="success" class="absolute inset-0 bg-slate-900/90 flex flex-col items-center justify-center text-emerald-400 animate-fadeIn">
              <CheckCircle class="w-16 h-16 animate-bounce" />
              <span class="text-sm font-bold mt-2">Tasdiqlandi!</span>
            </div>
          </div>

          <div class="w-full text-center text-xs px-4">
            <span class="text-slate-400 font-medium">Tizimga kirayotgan xodim:</span>
            <span class="block text-indigo-300 font-bold mt-1 text-sm">{{ authStore.tempUser?.name }}</span>
          </div>

          <!-- Display Errors -->
          <div v-if="error" class="w-full p-3 rounded-lg bg-red-500/10 border border-red-500/20 text-xs text-red-400 font-medium text-center">
            {{ error }}
          </div>

          <!-- Actions -->
          <div class="w-full space-y-3">
            <button 
              @click="handleFaceScan" 
              :disabled="scanning || success"
              class="w-full py-3.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 font-bold text-white text-sm shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/50 transition-all duration-200 flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="scanning">Biometriya tekshirilmoqda...</span>
              <span v-else-if="success">Muvaffaqiyatli!</span>
              <span v-else class="flex items-center space-x-2">
                <Scan class="w-4 h-4" />
                <span>Face ID simulyatsiyasini boshlash</span>
              </span>
            </button>

            <button 
              @click="handleBackToCredentials" 
              :disabled="scanning || success"
              class="w-full py-2.5 rounded-xl bg-white/5 border border-white/10 text-xs font-semibold text-slate-300 hover:bg-white/10 transition duration-200 disabled:opacity-50"
            >
              Orqaga
            </button>
          </div>
        </div>

        <!-- STEP 3: SUCCESS STATE -->
        <div v-else-if="authStore.loginStep === 'success'" class="space-y-6 text-center py-6">
          <div class="w-16 h-16 rounded-full bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 mx-auto animate-bounce">
            <CheckCircle class="w-10 h-10" />
          </div>
          <div class="space-y-2">
            <h2 class="text-2xl font-bold text-white">Xush kelibsiz!</h2>
            <p class="text-sm text-slate-400">Tizimga kirish muvaffaqiyatli yakunlandi.</p>
          </div>
          <p class="text-xs text-slate-500 animate-pulse">Siz avtomatik tarzda boshqaruv paneliga yo'naltirilmoqdasiz...</p>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { ChefHat, User, Lock, ArrowRight, ScanFace, Scan, CheckCircle } from 'lucide-vue-next';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const router = useRouter();

// State
const login = ref('');
const password = ref('');
const error = ref('');
const loading = ref(false);
const scanning = ref(false);
const success = ref(false);

const handleCredentialsSubmit = async () => {
  error.value = '';
  loading.value = true;
  try {
    const response = await fetch('/api/auth/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        login: login.value,
        password: password.value,
      }),
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Tizimga kirishda xatolik yuz berdi.');
    }

    // Step 1 success: Set temporary user data
    authStore.setTempUser(data.user);
    
    // Automatically trigger face scan simulation to bypass manual action
    setTimeout(() => {
      handleFaceScan();
    }, 400);
  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
};

const handleFaceScan = async () => {
  error.value = '';
  scanning.value = true;

  // Simulate scanning duration (1.5 seconds)
  setTimeout(async () => {
    try {
      const response = await fetch('/api/auth/verify-face', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          user_id: authStore.tempUser.id,
          face_verified: true
        }),
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Face ID tekshiruvi bajarilmadi.');
      }

      // Success
      scanning.value = false;
      success.value = true;

      // Complete login in Pinia
      authStore.setAuth(data.user, data.token);

      // Redirect to dashboard (Admin) or orders (Staff) directly
      setTimeout(() => {
        if (data.user?.roles?.includes('Admin')) {
          router.push('/admin/dashboard');
        } else {
          router.push('/orders');
        }
      }, 1000);

    } catch (err) {
      scanning.value = false;
      error.value = err.message;
    }
  }, 1800);
};

const handleBackToCredentials = () => {
  error.value = '';
  authStore.resetLoginFlow();
};
</script>

<style>
@keyframes scan {
  0% {
    top: 0%;
  }
  50% {
    top: 100%;
  }
  100% {
    top: 0%;
  }
}

@keyframes pulseScale {
  0%, 100% {
    transform: scale(1);
    opacity: 0.2;
  }
  50% {
    transform: scale(1.05);
    opacity: 0.6;
  }
}

.animate-scanLine {
  animation: scan 2s infinite ease-in-out;
}

.animate-pulseScale {
  animation: pulseScale 1.5s infinite ease-in-out;
}

.animate-fadeIn {
  animation: fadeIn 0.4s ease-out forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}
</style>
