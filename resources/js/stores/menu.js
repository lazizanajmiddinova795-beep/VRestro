import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import router from '@/router';

export const useMenuStore = defineStore('menu', () => {
    const authStore = useAuthStore();
    const categories = ref([]);
    const foods = ref([]);
    const loading = ref(false);
    const error = ref('');
    const selectedCategoryId = ref(null);

    const getHeaders = () => ({
        'Accept': 'application/json',
        'Authorization': `Bearer ${authStore.token}`
    });

    const handleAuthError = (status) => {
        if (status === 401) {
            authStore.logout();
            router.push('/login');
        }
    };

    // --- Category Actions ---

    const fetchCategories = async () => {
        loading.value = true;
        error.value = '';
        try {
            const response = await fetch('/api/menu/categories', {
                method: 'GET',
                headers: getHeaders()
            });

            const data = await response.json();
            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Kategoriyalarni yuklashda xatolik.');
            }

            categories.value = data;
        } catch (err) {
            error.value = err.message;
        } finally {
            loading.value = false;
        }
    };

    const createCategory = async (name) => {
        try {
            const response = await fetch('/api/menu/categories', {
                method: 'POST',
                headers: {
                    ...getHeaders(),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ name })
            });

            const data = await response.json();
            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Kategoriya yaratishda xatolik.');
            }

            await fetchCategories();
            return data.category;
        } catch (err) {
            error.value = err.message;
            throw err;
        }
    };

    const updateCategory = async (id, name) => {
        try {
            const response = await fetch(`/api/menu/categories/${id}`, {
                method: 'PUT',
                headers: {
                    ...getHeaders(),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ name })
            });

            const data = await response.json();
            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Kategoriyani tahrirlashda xatolik.');
            }

            await fetchCategories();
            return data.category;
        } catch (err) {
            error.value = err.message;
            throw err;
        }
    };

    const deleteCategory = async (id) => {
        try {
            const response = await fetch(`/api/menu/categories/${id}`, {
                method: 'DELETE',
                headers: getHeaders()
            });

            const data = await response.json();
            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Kategoriyani o\'chirishda xatolik.');
            }

            await fetchCategories();
            // If selected category was deleted, clear selection
            if (selectedCategoryId.value === id) {
                selectedCategoryId.value = null;
            }
        } catch (err) {
            error.value = err.message;
            throw err;
        }
    };

    // --- Food Actions ---

    const fetchFoods = async (filters = {}) => {
        loading.value = true;
        error.value = '';
        try {
            let query = '';
            const params = [];
            
            if (selectedCategoryId.value) {
                params.push(`category_id=${selectedCategoryId.value}`);
            }
            if (filters.search) {
                params.push(`search=${encodeURIComponent(filters.search)}`);
            }
            if (params.length > 0) {
                query = '?' + params.join('&');
            }

            const response = await fetch(`/api/menu/foods${query}`, {
                method: 'GET',
                headers: getHeaders()
            });

            const data = await response.json();
            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Taomlarni yuklashda xatolik.');
            }

            foods.value = data;
        } catch (err) {
            error.value = err.message;
        } finally {
            loading.value = false;
        }
    };

    const createFood = async (formData) => {
        try {
            // Note: Do NOT add Content-Type header when sending FormData; browser sets it automatically with boundaries
            const response = await fetch('/api/menu/foods', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${authStore.token}`,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();
            if (!response.ok) {
                handleAuthError(response.status);
                const err = new Error(data.message || 'Taom yaratishda xatolik.');
                err.errors = data.errors;
                throw err;
            }

            await fetchFoods();
            await fetchCategories(); // refresh category count badges
            return data.food;
        } catch (err) {
            error.value = err.message;
            throw err;
        }
    };

    const updateFood = async (id, formData) => {
        try {
            // To bypass PHP put limitations with files, we use POST
            const response = await fetch(`/api/menu/foods/${id}`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${authStore.token}`,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();
            if (!response.ok) {
                handleAuthError(response.status);
                const err = new Error(data.message || 'Taomni tahrirlashda xatolik.');
                err.errors = data.errors;
                throw err;
            }

            await fetchFoods();
            await fetchCategories();
            return data.food;
        } catch (err) {
            error.value = err.message;
            throw err;
        }
    };

    const toggleFoodAvailability = async (id) => {
        try {
            const response = await fetch(`/api/menu/foods/${id}/toggle`, {
                method: 'PATCH',
                headers: getHeaders()
            });

            const data = await response.json();
            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Holatni o\'zgartirishda xatolik.');
            }

            // Update item locally in foods state array to preserve UI scroll
            const idx = foods.value.findIndex(f => f.id === id);
            if (idx !== -1) {
                foods.value[idx].is_available = data.food.is_available;
            }
            return data.food;
        } catch (err) {
            error.value = err.message;
            throw err;
        }
    };

    const deleteFood = async (id) => {
        try {
            const response = await fetch(`/api/menu/foods/${id}`, {
                method: 'DELETE',
                headers: getHeaders()
            });

            const data = await response.json();
            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Taomni o\'chirishda xatolik.');
            }

            await fetchFoods();
            await fetchCategories();
            return true;
        } catch (err) {
            error.value = err.message;
            throw err;
        }
    };

    return {
        categories,
        foods,
        loading,
        error,
        selectedCategoryId,
        fetchCategories,
        createCategory,
        updateCategory,
        deleteCategory,
        fetchFoods,
        createFood,
        updateFood,
        toggleFoodAvailability,
        deleteFood
    };
});
