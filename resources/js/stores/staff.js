import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import router from '@/router';

export const useStaffStore = defineStore('staff', () => {
    const authStore = useAuthStore();
    const staffMembers = ref([]);
    const pagination = ref({
        current_page: 1,
        last_page: 1,
        total: 0
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

    const fetchStaff = async (filters = {}) => {
        loading.value = true;
        error.value = '';
        try {
            const params = [];
            if (filters.status) params.push(`status=${filters.status}`);
            if (filters.role) params.push(`role=${filters.role}`);
            if (filters.search) params.push(`search=${filters.search}`);
            if (filters.page) params.push(`page=${filters.page}`);

            const query = params.length > 0 ? '?' + params.join('&') : '';

            const response = await fetch(`/api/staff${query}`, {
                method: 'GET',
                headers: getHeaders()
            });

            const data = await response.json();

            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Xodimlarni yuklashda xatolik yuz berdi.');
            }

            staffMembers.value = data.data;
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

    const createStaff = async (payload) => {
        try {
            const response = await fetch('/api/staff', {
                method: 'POST',
                headers: getHeaders(),
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (!response.ok) {
                handleAuthError(response.status);
                const err = new Error(data.message || 'Xodim qo\'shishda xatolik yuz berdi.');
                err.errors = data.errors;
                throw err;
            }

            await fetchStaff({ page: 1 });
            return data.user;
        } catch (err) {
            error.value = err.message;
            throw err;
        }
    };

    const updateStaff = async (id, payload) => {
        try {
            const response = await fetch(`/api/staff/${id}`, {
                method: 'PUT',
                headers: getHeaders(),
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (!response.ok) {
                handleAuthError(response.status);
                const err = new Error(data.message || 'Xodim ma\'lumotlarini yangilashda xatolik yuz berdi.');
                err.errors = data.errors;
                throw err;
            }

            await fetchStaff({ page: pagination.value.current_page });
            return data.user;
        } catch (err) {
            error.value = err.message;
            throw err;
        }
    };

    const toggleStaffStatus = async (id) => {
        try {
            const response = await fetch(`/api/staff/${id}/toggle`, {
                method: 'PATCH',
                headers: getHeaders()
            });

            const data = await response.json();

            if (!response.ok) {
                handleAuthError(response.status);
                const err = new Error(data.message || 'Xodim holatini o\'zgartirishda xatolik yuz berdi.');
                err.errors = data.errors;
                throw err;
            }

            // Sync immediately on local list item
            const idx = staffMembers.value.findIndex(u => u.id === id);
            if (idx !== -1) {
                staffMembers.value[idx] = data.user;
            }
            return data.user;
        } catch (err) {
            error.value = err.message;
            throw err;
        }
    };

    const deleteStaff = async (id) => {
        try {
            const response = await fetch(`/api/staff/${id}`, {
                method: 'DELETE',
                headers: getHeaders()
            });

            const data = await response.json();

            if (!response.ok) {
                handleAuthError(response.status);
                const err = new Error(data.message || 'Xodimni o\'chirishda xatolik yuz berdi.');
                err.errors = data.errors;
                throw err;
            }

            await fetchStaff({ page: pagination.value.current_page });
            return true;
        } catch (err) {
            error.value = err.message;
            throw err;
        }
    };

    return {
        staffMembers,
        pagination,
        loading,
        error,
        fetchStaff,
        createStaff,
        updateStaff,
        toggleStaffStatus,
        deleteStaff
    };
});
