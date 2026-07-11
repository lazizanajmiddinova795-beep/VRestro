<template>
  <div class="h-screen w-screen bg-transparent text-slate-100 flex font-sans overflow-hidden">
    
    <!-- Left Sidebar -->
    <aside class="w-64 backdrop-blur-xl bg-slate-950/50 border-r border-white/5 flex flex-col justify-between shrink-0 sticky top-0 h-screen z-40 overflow-y-auto">
      <div class="p-6 space-y-8">
        <!-- Logo -->
        <router-link to="/" class="flex items-center space-x-3 group">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-violet-600 to-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-500/20 group-hover:scale-105 transition-transform duration-300 overflow-hidden">
            <img v-if="settingStore.settings.restaurant_logo" :src="settingStore.settings.restaurant_logo" class="w-full h-full object-cover" />
            <ChefHat v-else class="w-6 h-6 text-white" />
          </div>
          <span class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-white via-indigo-200 to-indigo-400 tracking-wider truncate max-w-[120px]" :title="settingStore.settings.restaurant_name">
            {{ settingStore.settings.restaurant_name }}
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

          <!-- Analytics (Admin and Cashier only) -->
          <router-link 
            v-if="['Admin', 'Cashier'].includes(authStore.user?.roles?.[0])"
            to="/analytics" 
            class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-medium transition duration-200"
            :class="isActiveRoute('/analytics') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
          >
            <BarChart3 class="w-5 h-5" />
            <span>Tahlillar</span>
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
            <span>Ta'minot</span>
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

          <!-- Chegirmalar / Discounts (Admin and Cashier only) -->
          <router-link 
            v-if="['Admin', 'Cashier'].includes(authStore.user?.roles?.[0])"
            to="/discounts" 
            class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-medium transition duration-200"
            :class="isActiveRoute('/discounts') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
          >
            <Tag class="w-5 h-5" />
            <span>Chegirmalar</span>
          </router-link>

          <!-- Settings (Admin only) -->
          <router-link 
            v-if="authStore.user?.roles?.[0] === 'Admin'"
            to="/settings" 
            class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-medium transition duration-200"
            :class="isActiveRoute('/settings') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
          >
            <Settings class="w-5 h-5" />
            <span>Sozlamalar</span>
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
    <div class="flex-grow flex flex-col h-screen overflow-hidden relative">
      <!-- Top header bar inside Main Content Area -->
      <header class="h-16 border-b border-white/5 backdrop-blur-md bg-slate-950/20 px-6 flex items-center justify-between shrink-0 relative z-30">
        <div></div>
        <!-- Right side: Bell icon dropdown and alerts -->
        <div class="flex items-center space-x-4 relative">
          
          <!-- Bell trigger -->
          <div class="relative">
            <button 
              @click="toggleDropdown" 
              class="p-2 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 hover:text-white transition duration-200 relative"
            >
              <Bell class="w-5 h-5 text-slate-400" />
              <!-- Pulsing unread badge -->
              <span 
                v-if="notificationStore.unreadCount > 0"
                class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-xxs font-extrabold text-white animate-pulse"
              >
                {{ notificationStore.unreadCount }}
              </span>
            </button>

            <!-- Popover flyout dropdown -->
            <div 
              v-if="showDropdown"
              class="absolute right-0 mt-2 w-80 rounded-2xl border border-white/10 bg-slate-950/90 backdrop-blur-xl p-4 shadow-2xl space-y-3 z-50 animate-fadeIn"
            >
              <div class="flex justify-between items-center pb-2 border-b border-white/5">
                <span class="text-xs font-bold text-white uppercase tracking-wider">Bildirishnomalar</span>
                <router-link 
                  to="/notifications" 
                  @click="showDropdown = false"
                  class="text-xxs text-indigo-400 hover:underline"
                >
                  Barchasini ko'rish
                </router-link>
              </div>

              <!-- List -->
              <div class="space-y-2.5 max-h-64 overflow-y-auto">
                <div 
                  v-for="item in latestNotifications" 
                  :key="item.id"
                  class="p-2 rounded-lg bg-white/5 hover:bg-white/10 transition duration-150 border-l-2"
                  :class="getLeftBorderColor(item.type)"
                >
                  <div class="flex justify-between items-start">
                    <span class="text-xs font-semibold text-white truncate block max-w-[180px]">{{ item.title }}</span>
                    <span class="text-xxs text-slate-500">{{ formatTimeAgo(item.created_at) }}</span>
                  </div>
                  <p class="text-xxs text-slate-300 truncate mt-0.5">{{ item.message }}</p>
                </div>
                <div v-if="latestNotifications.length === 0" class="text-center py-4 text-xs text-slate-500">
                  Xabarlar mavjud emas
                </div>
              </div>

              <div class="pt-2 border-t border-white/5 text-center">
                <button 
                  v-if="notificationStore.unreadCount > 0"
                  @click="markAllAsRead"
                  class="text-xxs text-slate-400 hover:text-white"
                >
                  Barchasini o'qildi qilish
                </button>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Router wrapper -->
      <div class="flex-grow overflow-y-auto relative">
        <router-view />
      </div>

      <!-- Real-time Live Toast Overlay -->
      <div class="fixed top-6 right-6 z-50 pointer-events-none space-y-3 max-w-sm w-full">
        <div 
          v-for="toast in activeToasts" 
          :key="toast.id" 
          class="pointer-events-auto w-full p-4 rounded-xl border border-white/10 bg-slate-900/90 backdrop-blur-md shadow-2xl flex gap-3 items-start animate-slideIn relative overflow-hidden"
        >
          <div 
            class="absolute left-0 top-0 bottom-0 w-1" 
            :class="getToastIndicatorColor(toast.type)"
          ></div>
          <div class="p-1 rounded-lg bg-white/5 text-slate-300">
            <Info class="w-4 h-4" />
          </div>
          <div class="space-y-0.5 flex-grow">
            <h5 class="text-xs font-bold text-white">{{ toast.title }}</h5>
            <p class="text-xxs text-slate-300">{{ toast.message }}</p>
          </div>
          <button @click="dismissToast(toast.id)" class="text-slate-500 hover:text-white shrink-0">
            <X class="w-3.5 h-3.5" />
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ChefHat, LayoutDashboard, ShoppingBag, LogOut, BookOpen, Database, Sparkles, Package, Layers, Users, Smile, DollarSign, Tag, BarChart3, Bell, Info, X, Settings } from 'lucide-vue-next';
import { useAuthStore } from '@/stores/auth';
import { useNotificationStore } from '@/stores/notifications';
import { useSettingStore } from '@/stores/settings';

const authStore = useAuthStore();
const notificationStore = useNotificationStore();
const settingStore = useSettingStore();
const router = useRouter();
const route = useRoute();

const showDropdown = ref(false);
const activeToasts = ref([]);

const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value;
  if (showDropdown.value) {
    notificationStore.fetchNotifications();
  }
};

const latestNotifications = computed(() => {
  return notificationStore.notifications.slice(0, 5);
});

const getLeftBorderColor = (type) => {
  const borderColors = {
    'low_stock': 'border-l-red-500',
    'new_order': 'border-l-blue-500',
    'order_cancelled': 'border-l-amber-500',
    'system': 'border-l-slate-500'
  };
  return borderColors[type] || 'border-l-slate-500';
};

const getToastIndicatorColor = (type) => {
  const colors = {
    'low_stock': 'bg-red-500',
    'new_order': 'bg-blue-500',
    'order_cancelled': 'bg-amber-500',
    'system': 'bg-slate-500'
  };
  return colors[type] || 'bg-slate-500';
};

const markAllAsRead = () => {
  notificationStore.markAllAsRead();
};

const dismissToast = (id) => {
  activeToasts.value = activeToasts.value.filter(t => t.id !== id);
};

// Check time diff helper
const formatTimeAgo = (dateStr) => {
  const diffMs = new Date() - new Date(dateStr);
  const diffMins = Math.floor(diffMs / 60000);
  if (diffMins < 1) return 'Hozirgina';
  return `${diffMins} daq. oldin`;
};

// Polling for simulated real-time WebSocket updates
let pollingInterval = null;
let previousUnreadIds = new Set();

const pollNotifications = async () => {
  if (!authStore.token) return;
  
  const oldUnreadCount = notificationStore.unreadCount;
  await notificationStore.fetchNotifications();

  // Find newly added notifications
  notificationStore.notifications.forEach(n => {
    if (!n.is_read && !previousUnreadIds.has(n.id)) {
      // Add as new unread
      previousUnreadIds.add(n.id);
      
      // Trigger toast!
      const toastId = Date.now() + Math.random();
      activeToasts.value.push({
        id: toastId,
        title: n.title,
        message: n.message,
        type: n.type
      });

      // Auto-dismiss after 6 seconds
      setTimeout(() => {
        dismissToast(toastId);
      }, 6000);
    }
  });
};

// Redirect to login if token is cleared (e.g., 401 unauthorized from API)
watch(() => authStore.token, (newToken) => {
  if (!newToken) {
    router.push('/login');
    if (pollingInterval) clearInterval(pollingInterval);
  } else {
    // Start polling
    pollNotifications();
    pollingInterval = setInterval(pollNotifications, 5000);
  }
}, { immediate: true });

onMounted(() => {
  if (authStore.token) {
    pollNotifications();
    settingStore.fetchSettings();
  }
});

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
.animate-fadeIn {
  animation: fadeIn 0.2s ease-out forwards;
}
.animate-slideIn {
  animation: slideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(5px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes slideIn {
  from { opacity: 0; transform: translateX(50px); }
  to { opacity: 1; transform: translateX(0); }
}

/* Custom sleek scrollbar for sidebar */
aside::-webkit-scrollbar {
  width: 4px;
}
aside::-webkit-scrollbar-track {
  background: transparent;
}
aside::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.08);
  border-radius: 2px;
}
aside::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.18);
}
</style>
