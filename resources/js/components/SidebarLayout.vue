<template>
  <div class="h-screen w-screen bg-slate-50 text-slate-900 flex font-sans overflow-hidden">
    
    <!-- Left Sidebar -->
    <aside class="w-64 bg-white border-r border-slate-200 flex flex-col justify-between shrink-0 sticky top-0 h-screen z-40 overflow-y-auto">
      <div class="p-6 space-y-8">
        <!-- Logo -->
        <router-link to="/" class="flex items-center space-x-3 group">
          <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center text-white shadow-sm overflow-hidden">
            <img v-if="settingStore.settings.restaurant_logo" :src="settingStore.settings.restaurant_logo" class="w-full h-full object-cover" />
            <ChefHat v-else class="w-6 h-6 text-white" />
          </div>
          <span class="text-slate-900 font-black text-xl tracking-tight truncate max-w-[130px]" :title="settingStore.settings.restaurant_name">
            {{ settingStore.settings.restaurant_name }}
          </span>
        </router-link>

        <!-- Menu Navigation -->
        <nav class="space-y-1.5">
          <!-- Dashboard -->
          <router-link 
            to="/admin/dashboard" 
            class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm transition duration-200 group"
            :class="isActiveRoute('/admin/dashboard') ? 'bg-slate-900 text-white font-extrabold shadow-sm' : 'text-slate-600 font-bold hover:text-slate-900 hover:bg-slate-100'"
          >
            <LayoutDashboard class="w-5 h-5" :class="isActiveRoute('/admin/dashboard') ? 'text-white' : 'text-slate-500 group-hover:text-slate-900'" />
            <span>Boshqaruv paneli</span>
          </router-link>

          <!-- Orders (All Roles) -->
          <router-link 
            to="/orders" 
            class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm transition duration-200 group"
            :class="isActiveRoute('/orders') ? 'bg-slate-900 text-white font-extrabold shadow-sm' : 'text-slate-600 font-bold hover:text-slate-900 hover:bg-slate-100'"
          >
            <ShoppingBag class="w-5 h-5" :class="isActiveRoute('/orders') ? 'text-white' : 'text-slate-500 group-hover:text-slate-900'" />
            <span>Buyurtmalar</span>
          </router-link>

          <!-- Menu (All Roles) -->
          <router-link 
            to="/menu" 
            class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm transition duration-200 group"
            :class="isActiveRoute('/menu') ? 'bg-slate-900 text-white font-extrabold shadow-sm' : 'text-slate-600 font-bold hover:text-slate-900 hover:bg-slate-100'"
          >
            <BookOpen class="w-5 h-5" :class="isActiveRoute('/menu') ? 'text-white' : 'text-slate-500 group-hover:text-slate-900'" />
            <span>Menyu</span>
          </router-link>

          <!-- Ingredients (All Roles) -->
          <router-link 
            to="/ingredients" 
            class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm transition duration-200 group"
            :class="isActiveRoute('/ingredients') ? 'bg-slate-900 text-white font-extrabold shadow-sm' : 'text-slate-600 font-bold hover:text-slate-900 hover:bg-slate-100'"
          >
            <Database class="w-5 h-5" :class="isActiveRoute('/ingredients') ? 'text-white' : 'text-slate-500 group-hover:text-slate-900'" />
            <span>Ta'minot</span>
          </router-link>

          <!-- Recipes (All Roles) -->
          <router-link 
            to="/recipes" 
            class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm transition duration-200 group"
            :class="isActiveRoute('/recipes') ? 'bg-slate-900 text-white font-extrabold shadow-sm' : 'text-slate-600 font-bold hover:text-slate-900 hover:bg-slate-100'"
          >
            <Sparkles class="w-5 h-5" :class="isActiveRoute('/recipes') ? 'text-white' : 'text-slate-500 group-hover:text-slate-900'" />
            <span>Retseptlar</span>
          </router-link>

          <!-- Warehouse (All Roles) -->
          <router-link 
            to="/warehouse" 
            class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm transition duration-200 group"
            :class="isActiveRoute('/warehouse') ? 'bg-slate-900 text-white font-extrabold shadow-sm' : 'text-slate-600 font-bold hover:text-slate-900 hover:bg-slate-100'"
          >
            <Package class="w-5 h-5" :class="isActiveRoute('/warehouse') ? 'text-white' : 'text-slate-500 group-hover:text-slate-900'" />
            <span>Ombor</span>
          </router-link>

          <!-- Tables (All Roles) -->
          <router-link 
            to="/tables" 
            class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm transition duration-200 group"
            :class="isActiveRoute('/tables') ? 'bg-slate-900 text-white font-extrabold shadow-sm' : 'text-slate-600 font-bold hover:text-slate-900 hover:bg-slate-100'"
          >
            <Layers class="w-5 h-5" :class="isActiveRoute('/tables') ? 'text-white' : 'text-slate-500 group-hover:text-slate-900'" />
            <span>Stollar</span>
          </router-link>

          <!-- Staff (Admin only) -->
          <router-link 
            v-if="authStore.user?.roles?.[0] === 'Admin'"
            to="/staff" 
            class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm transition duration-200 group"
            :class="isActiveRoute('/staff') ? 'bg-slate-900 text-white font-extrabold shadow-sm' : 'text-slate-600 font-bold hover:text-slate-900 hover:bg-slate-100'"
          >
            <Users class="w-5 h-5" :class="isActiveRoute('/staff') ? 'text-white' : 'text-slate-500 group-hover:text-slate-900'" />
            <span>Xodimlar</span>
          </router-link>

          <!-- Customers (All Roles) -->
          <router-link 
            to="/customers" 
            class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm transition duration-200 group"
            :class="isActiveRoute('/customers') ? 'bg-slate-900 text-white font-extrabold shadow-sm' : 'text-slate-600 font-bold hover:text-slate-900 hover:bg-slate-100'"
          >
            <Smile class="w-5 h-5" :class="isActiveRoute('/customers') ? 'text-white' : 'text-slate-500 group-hover:text-slate-900'" />
            <span>Mijozlar</span>
          </router-link>

          <!-- Payments / To'lovlar (Admin and Cashier only) -->
          <router-link 
            v-if="['Admin', 'Cashier'].includes(authStore.user?.roles?.[0])"
            to="/payments" 
            class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm transition duration-200 group"
            :class="isActiveRoute('/payments') ? 'bg-slate-900 text-white font-extrabold shadow-sm' : 'text-slate-600 font-bold hover:text-slate-900 hover:bg-slate-100'"
          >
            <DollarSign class="w-5 h-5" :class="isActiveRoute('/payments') ? 'text-white' : 'text-slate-500 group-hover:text-slate-900'" />
            <span>To'lovlar</span>
          </router-link>

          <!-- Chegirmalar / Discounts (Admin and Cashier only) -->
          <router-link 
            v-if="['Admin', 'Cashier'].includes(authStore.user?.roles?.[0])"
            to="/discounts" 
            class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm transition duration-200 group"
            :class="isActiveRoute('/discounts') ? 'bg-slate-900 text-white font-extrabold shadow-sm' : 'text-slate-600 font-bold hover:text-slate-900 hover:bg-slate-100'"
          >
            <Tag class="w-5 h-5" :class="isActiveRoute('/discounts') ? 'text-white' : 'text-slate-500 group-hover:text-slate-900'" />
            <span>Chegirmalar</span>
          </router-link>

          <!-- Settings (Admin only) -->
          <router-link 
            v-if="authStore.user?.roles?.[0] === 'Admin'"
            to="/settings" 
            class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm transition duration-200 group"
            :class="isActiveRoute('/settings') ? 'bg-slate-900 text-white font-extrabold shadow-sm' : 'text-slate-600 font-bold hover:text-slate-900 hover:bg-slate-100'"
          >
            <Settings class="w-5 h-5" :class="isActiveRoute('/settings') ? 'text-white' : 'text-slate-500 group-hover:text-slate-900'" />
            <span>Sozlamalar</span>
          </router-link>
        </nav>
      </div>

      <!-- User Profile at bottom -->
      <div class="p-6 border-t border-slate-200 space-y-4">
        <div class="flex items-center space-x-3">
          <div class="w-9 h-9 rounded-lg bg-slate-900 text-white flex items-center justify-center font-extrabold uppercase text-sm shadow-sm">
            {{ authStore.user?.name ? authStore.user.name.charAt(0) : 'U' }}
          </div>
          <div class="overflow-hidden">
            <h4 class="text-sm font-black text-slate-900 truncate">{{ authStore.user?.name }}</h4>
            <span class="text-xxs text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full inline-block mt-0.5 uppercase tracking-wider font-extrabold border border-slate-200">
              {{ authStore.user?.roles?.[0] || 'Xodim' }}
            </span>
          </div>
        </div>
        <button 
          @click="handleLogout" 
          class="w-full py-2.5 rounded-xl bg-slate-100 border border-slate-200 hover:bg-red-50 hover:border-red-200 text-slate-700 hover:text-red-600 text-xs font-bold transition duration-200 flex items-center justify-center space-x-2"
        >
          <LogOut class="w-4 h-4" />
          <span>Tizimdan chiqish</span>
        </button>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-grow flex flex-col h-screen overflow-hidden relative bg-slate-50">
      <!-- Top header bar inside Main Content Area -->
      <header class="h-16 border-b border-slate-200 bg-white px-6 flex items-center justify-between shrink-0 relative z-30">
        <div></div>
        <!-- Right side: Bell icon dropdown and alerts -->
        <div class="flex items-center space-x-4 relative">
          
          <!-- Bell trigger -->
          <div class="relative">
            <button 
              @click="toggleDropdown" 
              class="p-2 rounded-xl bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 hover:bg-slate-200/60 dark:hover:bg-white/10 transition duration-200 relative"
            >
              <Bell class="w-5 h-5 text-slate-600 dark:text-slate-400" />
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
