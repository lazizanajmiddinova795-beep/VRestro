import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { useAuthStore } from '@/stores/auth';

export const useNotificationStore = defineStore('notifications', () => {
    const authStore = useAuthStore();
    const notifications = ref([]);
    const loading = ref(false);
    const error = ref('');

    const unreadCount = computed(() => {
        return notifications.value.filter(n => !n.is_read).length;
    });

    const getHeaders = () => ({
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${authStore.token}`
    });

    const fetchNotifications = async () => {
        loading.value = true;
        error.value = '';
        try {
            const response = await fetch('/api/notifications', {
                headers: getHeaders()
            });
            const data = await response.json();
            if (!response.ok) {
                if (response.status === 401) {
                    authStore.logout();
                }
                throw new Error(data.message || 'Bildirishnomalarni yuklashda xatolik.');
            }
            notifications.value = data;
        } catch (err) {
            error.value = err.message;
        } finally {
            loading.value = false;
        }
    };

    const markAsRead = async (id) => {
        try {
            const response = await fetch(`/api/notifications/${id}/read`, {
                method: 'PATCH',
                headers: getHeaders()
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Xatolik yuz berdi.');
            
            // update locally
            const index = notifications.value.findIndex(n => n.id === id);
            if (index !== -1) {
                notifications.value[index] = data.notification;
            }
        } catch (err) {
            error.value = err.message;
        }
    };

    const markAllAsRead = async () => {
        try {
            const response = await fetch('/api/notifications/read-all', {
                method: 'POST',
                headers: getHeaders()
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Xatolik yuz berdi.');
            
            // update all locally
            notifications.value.forEach(n => {
                n.is_read = true;
            });
        } catch (err) {
            error.value = err.message;
        }
    };

    const deleteNotification = async (id) => {
        try {
            const response = await fetch(`/api/notifications/${id}`, {
                method: 'DELETE',
                headers: getHeaders()
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Xatolik yuz berdi.');

            notifications.value = notifications.value.filter(n => n.id !== id);
        } catch (err) {
            error.value = err.message;
        }
    };

    return {
        notifications,
        unreadCount,
        loading,
        error,
        fetchNotifications,
        markAsRead,
        markAllAsRead,
        deleteNotification
    };
});
