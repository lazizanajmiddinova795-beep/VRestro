import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';

export const useDashboardStore = defineStore('dashboard', () => {
    const authStore = useAuthStore();
    const metrics = ref(null);
    const loading = ref(false);
    const error = ref('');

    const fetchAnalytics = async () => {
        loading.value = true;
        error.value = '';
        try {
            const response = await fetch('/api/admin/dashboard', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${authStore.token}`
                }
            });

            const text = await response.text();
            let data;
            try { data = JSON.parse(text); } catch { throw new Error('Server noto\'g\'ri javob qaytardi.'); }

            if (!response.ok) {
                if (response.status === 401 || response.status === 403) {
                    authStore.logout();
                }
                throw new Error(data.message || 'Tahliliy ma\'lumotlarni yuklashda xatolik yuz berdi.');
            }

            // Ensure data has expected shape before setting metrics
            if (data && data.widgets) {
                metrics.value = data;
            } else {
                throw new Error('Server noto\'g\'ri format qaytardi. Qayta urinib ko\'ring.');
            }
        } catch (err) {
            error.value = err.message;
        } finally {
            loading.value = false;
        }
    };

    return {
        metrics,
        loading,
        error,
        fetchAnalytics
    };
});
