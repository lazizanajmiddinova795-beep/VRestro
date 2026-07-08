import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useIngredientsStore } from '@/stores/ingredients';
import router from '@/router';

export const useWarehouseStore = defineStore('warehouse', () => {
    const authStore = useAuthStore();
    const ingredientsStore = useIngredientsStore();

    const transactions = ref([]);
    const pagination = ref({
        current_page: 1,
        last_page: 1,
        total: 0
    });
    const timeline = ref([]);
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

    const fetchTransactions = async (filters = {}) => {
        loading.value = true;
        error.value = '';
        try {
            const params = [];
            if (filters.type) params.push(`type=${filters.type}`);
            if (filters.ingredient_id) params.push(`ingredient_id=${filters.ingredient_id}`);
            if (filters.start_date) params.push(`start_date=${filters.start_date}`);
            if (filters.end_date) params.push(`end_date=${filters.end_date}`);
            if (filters.page) params.push(`page=${filters.page}`);

            const query = params.length > 0 ? '?' + params.join('&') : '';

            const response = await fetch(`/api/warehouse/transactions${query}`, {
                method: 'GET',
                headers: getHeaders()
            });

            const data = await response.json();

            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Hujjatlarni yuklashda xatolik yuz berdi.');
            }

            transactions.value = data.data;
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

    const fetchIngredientTimeline = async (ingredientId) => {
        loading.value = true;
        error.value = '';
        try {
            const response = await fetch(`/api/warehouse/ingredients/${ingredientId}/timeline`, {
                method: 'GET',
                headers: getHeaders()
            });

            const data = await response.json();

            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Tarixni yuklashda xatolik yuz berdi.');
            }

            timeline.value = data;
        } catch (err) {
            error.value = err.message;
        } finally {
            loading.value = false;
        }
    };

    const submitStockIn = async (payload) => {
        loading.value = true;
        error.value = '';
        try {
            const response = await fetch('/api/warehouse/kirim', {
                method: 'POST',
                headers: getHeaders(),
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Kirim hujjatini saqlashda xatolik yuz berdi.');
            }

            // Sync ingredient global list to display recalculated costs & volumes
            await ingredientsStore.fetchIngredients();
            await fetchTransactions({ page: 1 });
            return data.transaction;
        } catch (err) {
            error.value = err.message;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const submitStockOut = async (payload) => {
        loading.value = true;
        error.value = '';
        try {
            const response = await fetch('/api/warehouse/chiqim', {
                method: 'POST',
                headers: getHeaders(),
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Chiqim hujjatini saqlashda xatolik yuz berdi.');
            }

            await ingredientsStore.fetchIngredients();
            await fetchTransactions({ page: 1 });
            return data.transaction;
        } catch (err) {
            error.value = err.message;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const submitAudit = async (payload) => {
        loading.value = true;
        error.value = '';
        try {
            const response = await fetch('/api/warehouse/inventarizatsiya', {
                method: 'POST',
                headers: getHeaders(),
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Inventarizatsiya hujjatini saqlashda xatolik yuz berdi.');
            }

            await ingredientsStore.fetchIngredients();
            await fetchTransactions({ page: 1 });
            return data.transaction;
        } catch (err) {
            error.value = err.message;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    return {
        transactions,
        pagination,
        timeline,
        loading,
        error,
        fetchTransactions,
        fetchIngredientTimeline,
        submitStockIn,
        submitStockOut,
        submitAudit
    };
});
