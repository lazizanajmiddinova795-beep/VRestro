<template>
  <div class="min-h-screen bg-radial from-slate-900 via-slate-950 to-black text-slate-100 flex font-sans overflow-x-hidden">
    
    <!-- Left Sidebar -->
    <aside class="w-64 backdrop-blur-xl bg-slate-950/50 border-r border-white/5 flex flex-col justify-between shrink-0 sticky top-0 h-screen z-40">
      <div class="p-6 space-y-8">
        <!-- Logo -->
        <router-link to="/" class="flex items-center space-x-3 group">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-violet-600 to-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-500/20 group-hover:scale-105 transition-transform duration-300">
            <ChefHat class="w-6 h-6 text-white" />
          </div>
          <span class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-white via-indigo-200 to-indigo-400 tracking-wider">
            VRestro
          </span>
        </router-link>

        <!-- Menu Navigation -->
        <nav class="space-y-2">
          <!-- Dashboard -->
          <router-link 
            to="/admin/dashboard" 
            class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-medium transition duration-200"
            :class="isActiveRoute('/admin/dashboard') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
          >
            <LayoutDashboard class="w-5 h-5" />
            <span>Boshqaruv paneli</span>
          </router-link>

          <!-- Orders (All Roles) -->
          <router-link 
            to="/orders" 
            class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-medium transition duration-200"
            :class="isActiveRoute('/orders') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
          >
            <ShoppingBag class="w-5 h-5" />
            <span>Buyurtmalar</span>
          </router-link>

          <!-- Menu (All Roles) -->
          <router-link 
            to="/menu" 
            class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-medium transition duration-200"
            :class="isActiveRoute('/menu') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
          >
            <BookOpen class="w-5 h-5" />
            <span>Menyu</span>
          </router-link>

          <!-- Ingredients (All Roles) -->
          <router-link 
            to="/ingredients" 
            class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-medium transition duration-200"
            :class="isActiveRoute('/ingredients') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
          >
            <Database class="w-5 h-5" />
            <span>Masalliqlar</span>
          </router-link>

          <!-- Recipes (All Roles) -->
          <router-link 
            to="/recipes" 
            class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-medium transition duration-200"
            :class="isActiveRoute('/recipes') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
          >
            <Sparkles class="w-5 h-5" />
            <span>Retseptlar</span>
          </router-link>

          <!-- Warehouse (All Roles) -->
          <router-link 
            to="/warehouse" 
            class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-medium transition duration-200"
            :class="isActiveRoute('/warehouse') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
          >
            <Package class="w-5 h-5" />
            <span>Ombor</span>
          </router-link>

          <!-- Tables (All Roles) -->
          <router-link 
            to="/tables" 
            class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-medium transition duration-200"
            :class="isActiveRoute('/tables') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
          >
            <Layers class="w-5 h-5" />
            <span>Stollar</span>
          </router-link>

          <!-- Staff (Admin only) -->
          <router-link 
            v-if="authStore.user?.roles?.[0] === 'Admin'"
            to="/staff" 
            class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-medium transition duration-200"
            :class="isActiveRoute('/staff') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
          >
            <Users class="w-5 h-5" />
            <span>Xodimlar</span>
          </router-link>

          <!-- Customers (All Roles) -->
          <router-link 
            to="/customers" 
            class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-medium transition duration-200"
            :class="isActiveRoute('/customers') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
          >
            <Smile class="w-5 h-5" />
            <span>Mijozlar</span>
          </router-link>

          <!-- Payments / To'lovlar (Admin and Cashier only) -->
          <router-link 
            v-if="['Admin', 'Cashier'].includes(authStore.user?.roles?.[0])"
            to="/payments" 
            class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-medium transition duration-200"
            :class="isActiveRoute('/payments') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
          >
            <DollarSign class="w-5 h-5" />
            <span>To'lovlar</span>
          </router-link>
        </nav>
      </div>

      <!-- User Profile at bottom -->
      <div class="p-6 border-t border-white/5 space-y-4">
        <div class="flex items-center space-x-3">
          <div class="w-9 h-9 rounded-lg bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 font-bold uppercase text-sm">
            {{ authStore.user?.name ? authStore.user.name.charAt(0) : 'U' }}
          </div>
          <div class="overflow-hidden">
            <h4 class="text-sm font-semibold text-white truncate">{{ authStore.user?.name }}</h4>
            <span class="text-xxs text-slate-400 bg-white/5 px-2 py-0.5 rounded-full inline-block mt-0.5 uppercase tracking-wider font-semibold">
              {{ authStore.user?.roles?.[0] || 'Xodim' }}
            </span>
          </div>
        </div>
        <button 
          @click="handleLogout" 
          class="w-full py-2.5 rounded-xl bg-white/5 border border-white/10 hover:bg-red-500/10 hover:border-red-500/20 hover:text-red-400 text-xs font-semibold transition duration-200 flex items-center justify-center space-x-2"
        >
          <LogOut class="w-4 h-4" />
          <span>Tizimdan chiqish</span>
        </button>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-grow flex flex-col min-h-screen overflow-y-auto relative">
      <router-view />
    </div>

  </div>
</template>

<script setup>
import { useRoute, useRouter } from 'vue-router';
import { ChefHat, LayoutDashboard, ShoppingBag, LogOut, BookOpen, Database, Sparkles, Package, Layers, Users, Smile, DollarSign } from 'lucide-vue-next';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const router = useRouter();
const route = useRoute();

const isActiveRoute = (path) => {
  return route.path === path;
};

const handleLogout = () => {
  authStore.logout();
  router.push('/login');
};
</script>

<style scoped>
.text-xxs {
  font-size: 0.65rem;
}
</style>
