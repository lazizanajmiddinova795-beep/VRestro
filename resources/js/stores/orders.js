import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';

export const useOrdersStore = defineStore('orders', () => {
    const authStore = useAuthStore();
    const orders = ref([]);
    const loading = ref(false);
    const error = ref('');
    const activeStatus = ref('new'); // 'new' | 'cooking' | 'ready' | 'delivered' | 'cancelled'

    const fetchOrders = async (status = activeStatus.value) => {
        loading.value = true;
        error.value = '';
        activeStatus.value = status;
        try {
            const response = await fetch(`/api/orders?status=${status}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${authStore.token}`
                }
            });

            const data = await response.json();

            if (!response.ok) {
                if (response.status === 401) {
                    authStore.logout();
                }
                throw new Error(data.message || 'Buyurtmalarni yuklashda xatolik yuz berdi.');
            }

            orders.value = data;
        } catch (err) {
            error.value = err.message;
        } finally {
            loading.value = false;
        }
    };

    const updateOrderStatus = async (orderId, newStatus) => {
        try {
            const response = await fetch(`/api/orders/${orderId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${authStore.token}`
                },
                body: JSON.stringify({
                    status: newStatus
                })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Statusni yangilashda xatolik yuz berdi.');
            }

            // Refresh the current status list
            await fetchOrders(activeStatus.value);
            return data.order;
        } catch (err) {
            error.value = err.message;
            throw err;
        }
    };

    const cancelOrder = async (orderId) => {
        try {
            const response = await fetch(`/api/orders/${orderId}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${authStore.token}`
                }
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Buyurtmani bekor qilishda xatolik yuz berdi.');
            }

            // Refresh the current status list
            await fetchOrders(activeStatus.value);
            return data.order;
        } catch (err) {
            error.value = err.message;
            throw err;
        }
    };

    return {
        orders,
        loading,
        error,
        activeStatus,
        fetchOrders,
        updateOrderStatus,
        cancelOrder
    };
});
