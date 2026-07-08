import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';

export const usePaymentStore = defineStore('payment', () => {
    const authStore = useAuthStore();
    const payments = ref([]);
    const todayRevenue = ref({
        total_revenue: 0,
        cash_total: 0,
        card_total: 0,
        qr_total: 0,
        bonus_total: 0
    });
    const loading = ref(false);
    const error = ref('');

    const fetchPayments = async (filters = {}) => {
        loading.value = true;
        error.value = '';
        try {
            const queryParams = new URLSearchParams(filters).toString();
            const response = await fetch(`/api/payments?${queryParams}`, {
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
                throw new Error(data.message || 'To\'lovlar tarixini yuklashda xatolik.');
            }

            payments.value = data;
        } catch (err) {
            error.value = err.message;
        } finally {
            loading.value = false;
        }
    };

    const fetchTodayRevenue = async () => {
        loading.value = true;
        error.value = '';
        try {
            const response = await fetch('/api/payments/revenue', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${authStore.token}`
                }
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Joriy tushum ma\'lumotlarini yuklashda xatolik.');
            }

            todayRevenue.value = data;
        } catch (err) {
            error.value = err.message;
        } finally {
            loading.value = false;
        }
    };

    const processPayment = async (paymentData) => {
        loading.value = true;
        error.value = '';
        try {
            const response = await fetch('/api/payments', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${authStore.token}`
                },
                body: JSON.stringify(paymentData)
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'To\'lovni amalga oshirishda xatolik.');
            }

            // Refresh logs and today's summary
            await fetchTodayRevenue();
            return data.payment;
        } catch (err) {
            error.value = err.message;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const refundPayment = async (paymentId) => {
        loading.value = true;
        error.value = '';
        try {
            const response = await fetch(`/api/payments/${paymentId}/refund`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${authStore.token}`
                }
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'To\'lovni bekor qilishda xatolik.');
            }

            await fetchTodayRevenue();
            return data.payment;
        } catch (err) {
            error.value = err.message;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    return {
        payments,
        todayRevenue,
        loading,
        error,
        fetchPayments,
        fetchTodayRevenue,
        processPayment,
        refundPayment
    };
});
