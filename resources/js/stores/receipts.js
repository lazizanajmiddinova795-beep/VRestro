import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import router from '@/router';

export const useReceiptsStore = defineStore('receipts', () => {
    const authStore = useAuthStore();
    const payments = ref([]);
    const loading = ref(false);
    const error = ref('');

    const getHeaders = () => ({
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${authStore.token}`
    });

    const handleAuthError = (status) => {
        if (status === 401) {
            authStore.logout();
            router.push('/login');
        }
    };

    const fetchPayments = async () => {
        loading.value = true;
        error.value = '';
        try {
            const response = await fetch('/api/payments', {
                method: 'GET',
                headers: getHeaders()
            });

            const data = await response.json();

            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Cheklar tarixini yuklashda xatolik yuz berdi.');
            }

            payments.value = data;
        } catch (err) {
            error.value = err.message;
        } finally {
            loading.value = false;
        }
    };

    const markOrderAsPrinted = async (orderId) => {
        try {
            const response = await fetch(`/api/orders/${orderId}/print-status`, {
                method: 'POST',
                headers: getHeaders()
            });

            const data = await response.json();
            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Buyurtma chop etilish holatini yangilashda xatolik.');
            }
            return data.order;
        } catch (err) {
            console.error(err.message);
        }
    };

    const markPaymentAsPrinted = async (paymentId) => {
        try {
            const response = await fetch(`/api/payments/${paymentId}/print-status`, {
                method: 'POST',
                headers: getHeaders()
            });

            const data = await response.json();
            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'To\'lov chop etilish holatini yangilashda xatolik.');
            }
            await fetchPayments();
            return data.payment;
        } catch (err) {
            console.error(err.message);
        }
    };

    const fetchOrderPrintData = async (orderId) => {
        try {
            const response = await fetch(`/api/orders/${orderId}/print-data`, {
                method: 'GET',
                headers: getHeaders()
            });

            const data = await response.json();
            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Chop etish ma\'lumotlarini yuklashda xatolik.');
            }
            return data;
        } catch (err) {
            error.value = err.message;
            throw err;
        }
    };

    const processPayment = async (paymentData) => {
        try {
            const response = await fetch('/api/payments', {
                method: 'POST',
                headers: getHeaders(),
                body: JSON.stringify(paymentData)
            });

            const data = await response.json();

            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'To\'lovni amalga oshirishda xatolik yuz berdi.');
            }

            await fetchPayments();
            return data.payment;
        } catch (err) {
            error.value = err.message;
            throw err;
        }
    };

    return {
        payments,
        loading,
        error,
        fetchPayments,
        markOrderAsPrinted,
        markPaymentAsPrinted,
        fetchOrderPrintData,
        processPayment
    };
});
