import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { useAuthStore } from '@/stores/auth';

export const useBranchStore = defineStore('branch', () => {
    const authStore = useAuthStore();

    const branches = ref([]);
    const activeBranch = ref(JSON.parse(localStorage.getItem('vrestro_active_branch') || 'null'));
    const loading = ref(false);
    const error = ref('');

    const getHeaders = () => ({
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${authStore.token}`
    });

    const fetchBranches = async () => {
        loading.value = true;
        error.value = '';
        try {
            const response = await fetch('/api/branches', { headers: getHeaders() });
            const text = await response.text();
            let data;
            try { data = JSON.parse(text); } catch { throw new Error('Server noto\'g\'ri javob qaytardi.'); }
            if (!response.ok) throw new Error(data.message || 'Filiallarni yuklashda xatolik.');
            branches.value = data;
        } catch (err) {
            error.value = err.message;
        } finally {
            loading.value = false;
        }
    };

    const createBranch = async (branchData) => {
        loading.value = true;
        error.value = '';
        try {
            const response = await fetch('/api/branches', {
                method: 'POST',
                headers: getHeaders(),
                body: JSON.stringify(branchData)
            });
            const text = await response.text();
            let data;
            try { data = JSON.parse(text); } catch { throw new Error('Server noto\'g\'ri javob qaytardi.'); }
            if (!response.ok) throw new Error(data.message || 'Filial yaratishda xatolik.');
            await fetchBranches();
            return data;
        } catch (err) {
            error.value = err.message;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const updateBranch = async (id, branchData) => {
        loading.value = true;
        error.value = '';
        try {
            const response = await fetch(`/api/branches/${id}`, {
                method: 'PUT',
                headers: getHeaders(),
                body: JSON.stringify(branchData)
            });
            const text = await response.text();
            let data;
            try { data = JSON.parse(text); } catch { throw new Error('Server noto\'g\'ri javob qaytardi.'); }
            if (!response.ok) throw new Error(data.message || 'Filalni yangilashda xatolik.');
            await fetchBranches();
            return data;
        } catch (err) {
            error.value = err.message;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const deleteBranch = async (id) => {
        loading.value = true;
        error.value = '';
        try {
            const response = await fetch(`/api/branches/${id}`, {
                method: 'DELETE',
                headers: getHeaders()
            });
            const text = await response.text();
            let data;
            try { data = JSON.parse(text); } catch { throw new Error('Server noto\'g\'ri javob qaytardi.'); }
            if (!response.ok) throw new Error(data.message || 'Filalni o\'chirishda xatolik.');
            await fetchBranches();
        } catch (err) {
            error.value = err.message;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const switchBranch = async (id) => {
        loading.value = true;
        error.value = '';
        try {
            const response = await fetch(`/api/branches/switch/${id}`, {
                method: 'POST',
                headers: getHeaders()
            });
            const text = await response.text();
            let data;
            try { data = JSON.parse(text); } catch { throw new Error('Server noto\'g\'ri javob qaytardi.'); }
            if (!response.ok) throw new Error(data.message || 'Filalga o\'tishda xatolik.');
            activeBranch.value = data.branch;
            localStorage.setItem('vrestro_active_branch', JSON.stringify(data.branch));
            return data;
        } catch (err) {
            error.value = err.message;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const clearContext = async () => {
        loading.value = true;
        try {
            const response = await fetch('/api/branches/clear-context', {
                method: 'POST',
                headers: getHeaders()
            });
            if (response.ok) {
                activeBranch.value = null;
                localStorage.removeItem('vrestro_active_branch');
            }
        } catch (err) {
            error.value = err.message;
        } finally {
            loading.value = false;
        }
    };

    return {
        branches,
        activeBranch,
        loading,
        error,
        fetchBranches,
        createBranch,
        updateBranch,
        deleteBranch,
        switchBranch,
        clearContext
    };
});
