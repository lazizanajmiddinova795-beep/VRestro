<template>
  <div class="p-6 space-y-6 flex-grow flex flex-col h-full overflow-y-auto">
    <!-- Top Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Bildirishnomalar tarixi</h1>
        <p class="text-sm text-slate-500">Tizimdagi ombor qoldiqlari, yangi va bekor qilingan buyurtmalar haqidagi real vaqt ogohlantirishlari.</p>
      </div>
      <div>
        <button
          @click="markAllAsRead"
          :disabled="notificationStore.unreadCount === 0"
          class="flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-slate-100 border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-indigo-600 hover:text-white disabled:opacity-50 disabled:hover:bg-slate-100 disabled:hover:text-slate-600 transition duration-200"
        >
          <CheckCheck class="w-4 h-4" />
          <span>Hammasini o'qilgan qilish</span>
        </button>
      </div>
    </div>

    <!-- Alert / Errors -->
    <div v-if="notificationStore.error" class="p-4 rounded-xl border border-red-200 bg-red-50 text-red-600 text-sm">
      {{ notificationStore.error }}
    </div>

    <!-- Notification items list -->
    <div v-if="notificationStore.loading" class="flex justify-center items-center py-12">
      <div class="w-10 h-10 border-4 border-indigo-200 border-t-indigo-500 rounded-full animate-spin"></div>
    </div>

    <div v-else-if="notificationStore.notifications.length === 0" class="flex flex-col items-center justify-center py-16 text-center rounded-2xl border border-dashed border-slate-200 bg-slate-50">
      <BellOff class="w-12 h-12 text-slate-400 mb-3" />
      <h3 class="text-lg font-semibold text-slate-900">Xabarlar mavjud emas</h3>
      <p class="text-sm text-slate-500 mt-1 max-w-sm">Hozirda hech qanday bildirishnomalar kelib tushmagan.</p>
    </div>

    <div v-else class="space-y-4 max-w-4xl">
      <div
        v-for="notification in notificationStore.notifications"
        :key="notification.id"
        class="relative rounded-xl border bg-white shadow-sm p-4 flex gap-4 items-start justify-between hover:bg-slate-50 transition duration-200"
        :class="[
          notification.is_read ? 'border-slate-200 opacity-60' : 'border-slate-300 shadow-md',
          getBorderColor(notification.type)
        ]"
      >
        <!-- Icon and details -->
        <div class="flex gap-3">
          <div
            class="p-2.5 rounded-lg border shrink-0"
            :class="getIconColorClass(notification.type)"
          >
            <component :is="getIconComponent(notification.type)" class="w-5 h-5" />
          </div>

          <div class="space-y-1">
            <div class="flex items-center gap-2">
              <h4 class="font-bold text-slate-900 text-sm" :class="{ 'text-slate-500': notification.is_read }">{{ notification.title }}</h4>
              <span v-if="!notification.is_read" class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed">{{ notification.message }}</p>
            <span class="text-xxs text-slate-400 block mt-1">{{ formatTimeAgo(notification.created_at) }}</span>
          </div>
        </div>

        <!-- Action tools -->
        <div class="flex items-center space-x-2 shrink-0">
          <button
            v-if="!notification.is_read"
            @click="markRead(notification.id)"
            class="p-1.5 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-900 transition duration-200"
            title="O'qilgan deb belgilash"
          >
            <Check class="w-4 h-4" />
          </button>
          <button
            @click="deleteItem(notification.id)"
            class="p-1.5 rounded-lg text-slate-500 hover:bg-red-50 hover:text-red-500 transition duration-200"
            title="O'chirish"
          >
            <Trash2 class="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import {
  CheckCheck, BellOff, Trash2, Check, AlertTriangle, ShoppingBag,
  XOctagon, Info
} from 'lucide-vue-next';
import { useNotificationStore } from '@/stores/notifications';

const notificationStore = useNotificationStore();

const getBorderColor = (type) => {
  const colors = {
    'low_stock': 'border-l-4 border-l-red-500',
    'new_order': 'border-l-4 border-l-blue-500',
    'order_cancelled': 'border-l-4 border-l-amber-500',
    'system': 'border-l-4 border-l-slate-400'
  };
  return colors[type] || 'border-l-4 border-l-slate-400';
};

const getIconColorClass = (type) => {
  const classes = {
    'low_stock': 'bg-red-50 border-red-200 text-red-500',
    'new_order': 'bg-blue-50 border-blue-200 text-blue-500',
    'order_cancelled': 'bg-amber-50 border-amber-200 text-amber-600',
    'system': 'bg-slate-100 border-slate-200 text-slate-500'
  };
  return classes[type] || 'bg-slate-100 border-slate-200 text-slate-500';
};

const getIconComponent = (type) => {
  const icons = {
    'low_stock': AlertTriangle,
    'new_order': ShoppingBag,
    'order_cancelled': XOctagon,
    'system': Info
  };
  return icons[type] || Info;
};

const markRead = (id) => {
  notificationStore.markAsRead(id);
};

const markAllAsRead = () => {
  notificationStore.markAllAsRead();
};

const deleteItem = (id) => {
  if (confirm("Ushbu bildirishnomani o'chirmoqchimisiz?")) {
    notificationStore.deleteNotification(id);
  }
};

const formatTimeAgo = (dateStr) => {
  const date = new Date(dateStr);
  const seconds = Math.floor((new Date() - date) / 1000);

  let interval = Math.floor(seconds / 31536000);
  if (interval >= 1) return interval + ' yil oldin';

  interval = Math.floor(seconds / 2592000);
  if (interval >= 1) return interval + ' oy oldin';

  interval = Math.floor(seconds / 86400);
  if (interval >= 1) return interval + ' kun oldin';

  interval = Math.floor(seconds / 3600);
  if (interval >= 1) return interval + ' soat oldin';

  interval = Math.floor(seconds / 60);
  if (interval >= 1) return interval + ' daqiqa oldin';

  return 'Hozirgina';
};

onMounted(() => {
  notificationStore.fetchNotifications();
});
</script>

<style scoped>
.text-xxs {
  font-size: 0.65rem;
}
</style>
