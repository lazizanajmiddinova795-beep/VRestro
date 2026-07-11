import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { useAuthStore } from '@/stores/auth';

export const useSettingStore = defineStore('settings', () => {
    const authStore = useAuthStore();
    const settings = ref({
        restaurant_name: 'VRestro Restaurant',
        restaurant_address: 'Toshkent, O\'zbekiston',
        restaurant_phone: '+998901234567',
        restaurant_hours: '09:00 - 23:00',
        restaurant_logo: null,
        tax_rate: 12,
        currency: 'UZS',
        language: 'uz'
    });
    const loading = ref(false);
    const error = ref('');

    const fetchSettings = async () => {
        loading.value = true;
        error.value = '';
        try {
            const headers = {
                'Accept': 'application/json'
            };
            if (authStore.token) {
                headers['Authorization'] = `Bearer ${authStore.token}`;
            }

            const response = await fetch('/api/settings', { headers });
            const data = await response.json();
            if (response.ok) {
                settings.value = { ...settings.value, ...data };
            }
        } catch (err) {
            error.value = err.message;
        } finally {
            loading.value = false;
        }
    };

    const updateSettings = async (formData) => {
        loading.value = true;
        error.value = '';
        try {
            const response = await fetch('/api/settings', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${authStore.token}`,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || 'Sozlamalarni saqlashda xatolik.');
            }
            settings.value = { ...settings.value, ...data.settings };
            return data.message;
        } catch (err) {
            error.value = err.message;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    return {
        settings,
        loading,
        error,
        fetchSettings,
        updateSettings
    };
});
