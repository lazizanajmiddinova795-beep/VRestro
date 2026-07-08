import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import router from '@/router';

export const useIngredientsStore = defineStore('ingredients', () => {
    const authStore = useAuthStore();
    const ingredients = ref([]);
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

    const fetchIngredients = async (filters = {}) => {
        loading.value = true;
        error.value = '';
        try {
            let query = '';
            const params = [];
            if (filters.search) {
                params.push(`search=${encodeURIComponent(filters.search)}`);
            }
            if (filters.low_stock) {
                params.push(`low_stock=${filters.low_stock}`);
            }
            if (params.length > 0) {
                query = '?' + params.join('&');
            }

            const response = await fetch(`/api/ingredients${query}`, {
                method: 'GET',
                headers: getHeaders()
            });

            const data = await response.json();

            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Masalliqlarni yuklashda xatolik yuz berdi.');
            }

            ingredients.value = data;
        } catch (err) {
            error.value = err.message;
        } finally {
            loading.value = false;
        }
    };

    const createIngredient = async (ingredientData) => {
        try {
            const response = await fetch('/api/ingredients', {
                method: 'POST',
                headers: getHeaders(),
                body: JSON.stringify(ingredientData)
            });

            const data = await response.json();

            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Masalliq yaratishda xatolik yuz berdi.');
            }

            await fetchIngredients();
            return data.ingredient;
        } catch (err) {
            error.value = err.message;
            throw err;
        }
    };

    const updateIngredient = async (id, ingredientData) => {
        try {
            const response = await fetch(`/api/ingredients/${id}`, {
                method: 'PUT',
                headers: getHeaders(),
                body: JSON.stringify(ingredientData)
            });

            const data = await response.json();

            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Masalliqni tahrirlashda xatolik yuz berdi.');
            }

            await fetchIngredients();
            return data.ingredient;
        } catch (err) {
            error.value = err.message;
            throw err;
        }
    };

    const adjustStock = async (id, amount, type) => {
        try {
            const response = await fetch(`/api/ingredients/${id}/adjust`, {
                method: 'POST',
                headers: getHeaders(),
                body: JSON.stringify({ amount, type })
            });

            const data = await response.json();

            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Zaxira miqdorini o\'zgartirishda xatolik yuz berdi.');
            }

            // Update item locally in foods state array to preserve UI scroll
            const idx = ingredients.value.findIndex(i => i.id === id);
            if (idx !== -1) {
                ingredients.value[idx] = data.ingredient;
            }
            return data.ingredient;
        } catch (err) {
            error.value = err.message;
            throw err;
        }
    };

    const deleteIngredient = async (id) => {
        try {
            const response = await fetch(`/api/ingredients/${id}`, {
                method: 'DELETE',
                headers: getHeaders()
            });

            const data = await response.json();

            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Masalliqni o\'chirishda xatolik yuz berdi.');
            }

            await fetchIngredients();
            return true;
        } catch (err) {
            error.value = err.message;
            throw err;
        }
    };

    return {
        ingredients,
        loading,
        error,
        fetchIngredients,
        createIngredient,
        updateIngredient,
        adjustStock,
        deleteIngredient
    };
});
