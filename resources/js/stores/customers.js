import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import router from '@/router';

export const useCustomerStore = defineStore('customers', () => {
    const authStore = useAuthStore();
    const customers = ref([]);
    const pagination = ref({
        current_page: 1,
        last_page: 1,
        total: 0
    });
    const analytics = ref({
        total_customers: 0,
        total_bonus_balance: 0.00,
        top_customer: null
    });
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

    const fetchCustomers = async (filters = {}) => {
        loading.value = true;
        error.value = '';
        try {
            const params = [];
            if (filters.search) params.push(`search=${filters.search}`);
            if (filters.page) params.push(`page=${filters.page}`);

            const query = params.length > 0 ? '?' + params.join('&') : '';

            const response = await fetch(`/api/customers${query}`, {
                method: 'GET',
                headers: getHeaders()
            });

            const data = await response.json();

            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Mijozlarni yuklashda xatolik yuz berdi.');
            }

            customers.value = data.data;
            pagination.value = {
                current_page: data.current_page,
                last_page: data.last_page,
                total: data.total
            };
        } catch (err) {
            error.value = err.message;
        } finally {
            loading.value = false;
        }
    };

    const fetchAnalytics = async () => {
        try {
            const response = await fetch('/api/customers/analytics', {
                method: 'GET',
                headers: getHeaders()
            });

            const data = await response.json();

            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Analitikani yuklashda xatolik yuz berdi.');
            }

            analytics.value = data;
        } catch (err) {
            console.error(err.message);
        }
    };

    const createCustomer = async (payload) => {
        try {
            const response = await fetch('/api/customers', {
                method: 'POST',
                headers: getHeaders(),
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Mijoz qo\'shishda xatolik yuz berdi.');
            }

            await fetchCustomers({ page: 1 });
            await fetchAnalytics();
            return data.customer;
        } catch (err) {
            error.value = err.message;
            throw err;
        }
    };

    const updateCustomer = async (id, payload) => {
        try {
            const response = await fetch(`/api/customers/${id}`, {
                method: 'PUT',
                headers: getHeaders(),
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Mijoz ma\'lumotlarini yangilashda xatolik yuz berdi.');
            }

            await fetchCustomers({ page: pagination.value.current_page });
            await fetchAnalytics();
            return data.customer;
        } catch (err) {
            error.value = err.message;
            throw err;
        }
    };

    const adjustCustomerBalance = async (id, bonusBalance) => {
        try {
            const response = await fetch(`/api/customers/${id}/adjust`, {
                method: 'POST',
                headers: getHeaders(),
                body: JSON.stringify({ bonus_balance: bonusBalance })
            });

            const data = await response.json();

            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Bonus balansini moslashtirishda xatolik yuz berdi.');
            }

            // Sync single item in array
            const idx = customers.value.findIndex(c => c.id === id);
            if (idx !== -1) {
                customers.value[idx] = data.customer;
            }
            await fetchAnalytics();
            return data.customer;
        } catch (err) {
            error.value = err.message;
            throw err;
        }
    };

    const deleteCustomer = async (id) => {
        try {
            const response = await fetch(`/api/customers/${id}`, {
                method: 'DELETE',
                headers: getHeaders()
            });

            const data = await response.json();

            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Mijozni o\'chirishda xatolik yuz berdi.');
            }

            await fetchCustomers({ page: pagination.value.current_page });
            await fetchAnalytics();
            return true;
        } catch (err) {
            error.value = err.message;
            throw err;
        }
    };

    return {
        customers,
        pagination,
        analytics,
        loading,
        error,
        fetchCustomers,
        fetchAnalytics,
        createCustomer,
        updateCustomer,
        adjustCustomerBalance,
        deleteCustomer
    };
});
