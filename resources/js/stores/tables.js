import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import router from '@/router';

export const useTablesStore = defineStore('tables', () => {
    const authStore = useAuthStore();
    const tables = ref([]);
    const loading = ref(false);
    const error = ref('');

    const getHeaders = () => {
        const token = authStore.token || localStorage.getItem('vrestro_token');
        return {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': token ? `Bearer ${token}` : ''
        };
    };

    const handleAuthError = (status) => {
        if (status === 401) {
            authStore.logout();
            router.push('/login');
        }
    };

    const fetchTables = async (filters = {}) => {
        loading.value = true;
        error.value = '';
        try {
            let query = '';
            if (filters.status) {
                query = `?status=${filters.status}`;
            }

            const response = await fetch(`/api/tables${query}`, {
                method: 'GET',
                headers: getHeaders()
            });

            const data = await response.json();

            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Stollarni yuklashda xatolik yuz berdi.');
            }

            tables.value = data;
        } catch (err) {
            error.value = err.message;
        } finally {
            loading.value = false;
        }
    };

    const createTable = async (tableData) => {
        try {
            const response = await fetch('/api/tables', {
                method: 'POST',
                headers: getHeaders(),
                body: JSON.stringify(tableData)
            });

            const data = await response.json();

            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Stol qo\'shishda xatolik yuz berdi.');
            }

            await fetchTables();
            return data.table;
        } catch (err) {
            error.value = err.message;
            throw err;
        }
    };

    const updateTable = async (id, tableData) => {
        try {
            const response = await fetch(`/api/tables/${id}`, {
                method: 'PUT',
                headers: getHeaders(),
                body: JSON.stringify(tableData)
            });

            const data = await response.json();

            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Stol tahrirlashda xatolik yuz berdi.');
            }

            await fetchTables();
            return data.table;
        } catch (err) {
            error.value = err.message;
            throw err;
        }
    };

    const updateTableStatus = async (id, status) => {
        try {
            const response = await fetch(`/api/tables/${id}/status`, {
                method: 'PATCH',
                headers: getHeaders(),
                body: JSON.stringify({ status })
            });

            const data = await response.json();

            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Stol holatini o\'zgartirishda xatolik yuz berdi.');
            }

            // Update local table item immediately
            const idx = tables.value.findIndex(t => t.id === id);
            if (idx !== -1) {
                tables.value[idx] = data.table;
            }
            return data.table;
        } catch (err) {
            error.value = err.message;
            throw err;
        }
    };

    const deleteTable = async (id) => {
        try {
            const response = await fetch(`/api/tables/${id}`, {
                method: 'DELETE',
                headers: getHeaders()
            });

            const data = await response.json();

            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Stolni o\'chirishda xatolik yuz berdi.');
            }

            await fetchTables();
            return true;
        } catch (err) {
            error.value = err.message;
            throw err;
        }
    };

    return {
        tables,
        loading,
        error,
        fetchTables,
        createTable,
        updateTable,
        updateTableStatus,
        deleteTable
    };
});
