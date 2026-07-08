import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import router from '@/router';

export const useRecipesStore = defineStore('recipes', () => {
    const authStore = useAuthStore();
    const recipe = ref([]);
    const portionCapacity = ref(null);
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

    const fetchRecipeForFood = async (foodId) => {
        loading.value = true;
        error.value = '';
        try {
            const response = await fetch(`/api/menu/foods/${foodId}/recipe`, {
                method: 'GET',
                headers: getHeaders()
            });

            const data = await response.json();

            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Retseptni yuklashda xatolik yuz berdi.');
            }

            recipe.value = data.recipe;
            portionCapacity.value = data.portion_capacity;
        } catch (err) {
            error.value = err.message;
        } finally {
            loading.value = false;
        }
    };

    const saveRecipe = async (foodId, ingredientsList) => {
        loading.value = true;
        error.value = '';
        try {
            const response = await fetch(`/api/menu/foods/${foodId}/recipe`, {
                method: 'POST',
                headers: getHeaders(),
                body: JSON.stringify({ ingredients: ingredientsList })
            });

            const data = await response.json();

            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Retseptni saqlashda xatolik yuz berdi.');
            }

            // Refetch recipe to refresh capacity metrics
            await fetchRecipeForFood(foodId);
            return true;
        } catch (err) {
            error.value = err.message;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    return {
        recipe,
        portionCapacity,
        loading,
        error,
        fetchRecipeForFood,
        saveRecipe
    };
});
