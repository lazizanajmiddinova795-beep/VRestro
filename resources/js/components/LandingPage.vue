<template>
  <div class="min-h-screen bg-transparent text-slate-900 flex flex-col font-sans overflow-x-hidden selection:bg-indigo-500 selection:text-white">
    <!-- Header -->
    <header class="sticky top-0 z-50 backdrop-blur-md border-b border-slate-200 bg-white/80 px-6 py-4 transition-all duration-300">
      <div class="max-w-7xl mx-auto flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <img src="/foodflow_logo.png" alt="FoodFlow Logo" class="h-10 md:h-12 object-contain drop-shadow-sm hover:scale-105 transition-transform duration-300" />
        </div>
        <div class="flex items-center space-x-4">
          <template v-if="authStore.isAuthenticated()">
            <div class="flex items-center space-x-3 mr-4">
              <span class="text-xs bg-indigo-50 text-indigo-700 border border-indigo-200 px-2.5 py-1 rounded-full font-medium">
                {{ authStore.user?.roles?.[0] || 'Xodim' }}
              </span>
              <span class="text-sm font-medium text-slate-600">{{ authStore.user?.name }}</span>
            </div>
            <router-link v-if="authStore.user?.roles?.includes('Manager')" to="/admin/dashboard" class="px-4 py-2 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-600/20 mr-2 transition duration-300">
              Boshqaruv paneli
            </router-link>
            <router-link v-else-if="authStore.user?.roles?.includes('Cashier')" to="/cashier/tables" class="px-4 py-2 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-600/20 mr-2 transition duration-300">
              Kassa paneli
            </router-link>
            <router-link v-else-if="authStore.user?.roles?.includes('Chef')" to="/kitchen" class="px-4 py-2 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-600/20 mr-2 transition duration-300">
              Oshpaz paneli
            </router-link>
            <router-link v-else to="/orders" class="px-4 py-2 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-600/20 mr-2 transition duration-300">
              Buyurtmalar
            </router-link>
            <button @click="handleLogout" class="px-4 py-2 text-xs font-semibold rounded-lg bg-slate-50 border border-slate-200 hover:bg-red-50 hover:border-red-200 hover:text-red-600 transition duration-300">
              Chiqish
            </button>
          </template>
          <template v-else>
            <router-link to="/login" class="relative group px-5 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/50 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 overflow-hidden">
              <span class="relative z-10">Tizimga kirish</span>
              <div class="absolute inset-0 bg-white/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            </router-link>
          </template>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow flex flex-col justify-center items-center px-6 py-20 relative">
      <!-- Decorative Background Glows -->
      <div class="absolute top-1/4 left-1/4 -translate-x-1/2 -translate-y-1/2 w-96 h-96 rounded-full bg-violet-600/10 blur-[120px] pointer-events-none"></div>
      <div class="absolute bottom-1/4 right-1/4 translate-x-1/2 translate-y-1/2 w-96 h-96 rounded-full bg-indigo-600/10 blur-[120px] pointer-events-none"></div>

      <div class="max-w-5xl text-center space-y-12 relative z-10">
        <!-- Badge -->
        <div class="inline-flex items-center space-x-2 px-3 py-1.5 rounded-full bg-white border border-slate-200 shadow-sm animate-pulse">
          <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
          <span class="text-xs font-medium text-slate-600 tracking-wide">Phase 1: Ecosystem Infrastructure initialized</span>
        </div>

        <!-- Title -->
        <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight text-slate-900 leading-tight max-w-4xl mx-auto">
          Restoran Boshqaruvida
          <span class="block bg-clip-text text-transparent bg-gradient-to-r from-violet-600 via-indigo-600 to-cyan-600">
            Yangi Davr Texnologiyasi
          </span>
        </h1>

        <!-- Subtitle -->
        <p class="text-lg md:text-xl text-slate-500 max-w-2xl mx-auto leading-relaxed">
          Moliya, buyurtmalar, oshxona texnologiyalari va xizmat ko'rsatishni yagona oynada boshqaruvchi restoran va oshxonalar uchun keyingi avlod ekotizimi.
        </p>

        <!-- CTAs -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
          <router-link v-if="!authStore.isAuthenticated()" to="/login" class="px-8 py-4 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 font-bold text-white shadow-lg shadow-indigo-600/40 hover:shadow-indigo-600/60 hover:scale-[1.03] active:scale-[0.97] transition-all duration-300 w-full sm:w-auto">
            Tizimga kirish
          </router-link>
          <div v-else class="flex flex-col sm:flex-row items-center justify-center gap-4 w-full sm:w-auto">
            <router-link v-if="authStore.user?.roles?.includes('Manager')" to="/admin/dashboard" class="px-8 py-4 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 font-bold text-white shadow-lg shadow-indigo-600/40 hover:shadow-indigo-600/60 hover:scale-[1.03] active:scale-[0.97] transition-all duration-300 w-full sm:w-auto">
              Boshqaruv paneliga o'tish
            </router-link>
            <router-link v-else-if="authStore.user?.roles?.includes('Cashier')" to="/cashier/tables" class="px-8 py-4 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 font-bold text-white shadow-lg shadow-indigo-600/40 hover:shadow-indigo-600/60 hover:scale-[1.03] active:scale-[0.97] transition-all duration-300 w-full sm:w-auto">
              Kassa paneliga o'tish
            </router-link>
            <router-link v-else-if="authStore.user?.roles?.includes('Chef')" to="/kitchen" class="px-8 py-4 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 font-bold text-white shadow-lg shadow-indigo-600/40 hover:shadow-indigo-600/60 hover:scale-[1.03] active:scale-[0.97] transition-all duration-300 w-full sm:w-auto">
              Oshpaz paneliga o'tish
            </router-link>
            <router-link v-else to="/orders" class="px-8 py-4 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 font-bold text-white shadow-lg shadow-indigo-600/40 hover:shadow-indigo-600/60 hover:scale-[1.03] active:scale-[0.97] transition-all duration-300 w-full sm:w-auto">
              Tizimga o'tish
            </router-link>
            <div class="px-8 py-4 rounded-xl bg-indigo-50 border border-indigo-200 font-bold text-indigo-700 w-full sm:w-auto">
              Tizimga muvaffaqiyatli kirildi!
            </div>
          </div>
        </div>

        <!-- Features Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 pt-16 text-left">
          <!-- Card 1 -->
          <div class="group relative rounded-2xl border border-slate-200 bg-white p-6 shadow-md transition-all duration-300 hover:border-violet-300 hover:shadow-lg hover:-translate-y-1">
            <div class="w-12 h-12 rounded-xl bg-violet-50 flex items-center justify-center border border-violet-200 text-violet-600 mb-5 group-hover:bg-violet-500 group-hover:text-white transition-all duration-300">
              <Layers class="w-6 h-6" />
            </div>
            <h3 class="text-lg font-bold text-slate-900 mb-2">{{ settingsStore.t('app_title') }} - Barchasi Bir Oynada</h3>
            <p class="text-sm text-slate-500 leading-relaxed">Buyurtmalar, stollar joylashuvi va xizmatchilar navbati hammasi real vaqtda yangilanadi.</p>
          </div>

          <!-- Card 2 -->
          <div class="group relative rounded-2xl border border-slate-200 bg-white p-6 shadow-md transition-all duration-300 hover:border-indigo-300 hover:shadow-lg hover:-translate-y-1">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center border border-indigo-200 text-indigo-600 mb-5 group-hover:bg-indigo-500 group-hover:text-white transition-all duration-300">
              <DollarSign class="w-6 h-6" />
            </div>
            <h3 class="text-lg font-bold text-slate-900 mb-2">Moliya & Hisobotlar</h3>
            <p class="text-sm text-slate-500 leading-relaxed">Kassa operatsiyalari, kundalik tushumlar va foyda-zarar tahlilini tezkor hisoblash.</p>
          </div>

          <!-- Card 3 -->
          <div class="group relative rounded-2xl border border-slate-200 bg-white p-6 shadow-md transition-all duration-300 hover:border-cyan-300 hover:shadow-lg hover:-translate-y-1">
            <div class="w-12 h-12 rounded-xl bg-cyan-50 flex items-center justify-center border border-cyan-200 text-cyan-600 mb-5 group-hover:bg-cyan-500 group-hover:text-white transition-all duration-300">
              <ChefHat class="w-6 h-6" />
            </div>
            <h3 class="text-lg font-bold text-slate-900 mb-2">Oshxona Nazorati</h3>
            <p class="text-sm text-slate-500 leading-relaxed">Shef-povar va oshxona xodimlari uchun tayyorlanayotgan taomlar monitori integratsiyasi.</p>
          </div>

        </div>
      </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-200 py-8 text-center text-xs text-slate-400">
      <div class="container mx-auto px-6 text-center text-slate-500 text-sm">
        &copy; 2026 FoodFlow. Barcha huquqlar himoyalangan. Next-Gen Restoran Ekotizimi.
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ChefHat, Layers, DollarSign, Lock } from 'lucide-vue-next';
import { useAuthStore } from '@/stores/auth';
import { useSettingsStore } from '@/stores/settings';

const authStore = useAuthStore();
const settingsStore = useSettingsStore();

const handleLogout = () => {
  authStore.logout();
};
</script>
