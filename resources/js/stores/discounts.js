import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';

export const useDiscountStore = defineStore('discounts', () => {
    const authStore = useAuthStore();
    const discounts = ref([]);
    const loading = ref(false);
    const error = ref('');

    const fetchDiscounts = async (filters = {}) => {
        loading.value = true;
        error.value = '';
        try {
            const queryParams = new URLSearchParams(filters).toString();
            const response = await fetch(`/api/discounts?${queryParams}`, {
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
                throw new Error(data.message || 'Chegirmalarni yuklashda xatolik.');
            }

            discounts.value = data;
        } catch (err) {
            error.value = err.message;
        } finally {
            loading.value = false;
        }
    };

    const createDiscount = async (discountData) => {
        loading.value = true;
        error.value = '';
        try {
            const response = await fetch('/api/discounts', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${authStore.token}`
                },
                body: JSON.stringify(discountData)
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Chegirmani yaratishda xatolik.');
            }

            await fetchDiscounts();
            return data.discount;
        } catch (err) {
            error.value = err.message;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const updateDiscount = async (id, discountData) => {
        loading.value = true;
        error.value = '';
        try {
            const response = await fetch(`/api/discounts/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${authStore.token}`
                },
                body: JSON.stringify(discountData)
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Chegirmani tahrirlashda xatolik.');
            }

            await fetchDiscounts();
            return data.discount;
        } catch (err) {
            error.value = err.message;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const toggleDiscountStatus = async (id) => {
        loading.value = true;
        error.value = '';
        try {
            const response = await fetch(`/api/discounts/${id}/toggle`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${authStore.token}`
                }
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Statusni o\'zgartirishda xatolik.');
            }

            // Update item in local list directly without full reload
            const index = discounts.value.findIndex(d => d.id === id);
            if (index !== -1) {
                discounts.value[index] = data.discount;
            }
            return data.discount;
        } catch (err) {
            error.value = err.message;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const deleteDiscount = async (id) => {
        loading.value = true;
        error.value = '';
        try {
            const response = await fetch(`/api/discounts/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${authStore.token}`
                }
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Chegirmani o\'chirishda xatolik.');
            }

            discounts.value = discounts.value.filter(d => d.id !== id);
        } catch (err) {
            error.value = err.message;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const validateAndApplyPromocode = async (orderId, code) => {
        loading.value = true;
        error.value = '';
        try {
            const response = await fetch('/api/discounts/validate-code', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${authStore.token}`
                },
                body: JSON.stringify({ order_id: orderId, code })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Promo-kodni qo\'llashda xatolik.');
            }

            return data;
        } catch (err) {
            error.value = err.message;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    return {
        discounts,
        loading,
        error,
        fetchDiscounts,
        createDiscount,
        updateDiscount,
        toggleDiscountStatus,
        deleteDiscount,
        validateAndApplyPromocode
    };
});
