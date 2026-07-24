import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import router from '@/router';

export const useCashierTablesStore = defineStore('cashierTables', () => {
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

    const fetchCashierTables = async () => {
        loading.value = true;
        error.value = '';
        try {
            const response = await fetch('/api/cashier/tables', {
                method: 'GET',
                headers: getHeaders()
            });

            const data = await response.json();

            if (!response.ok) {
                handleAuthError(response.status);
                throw new Error(data.message || 'Kassir stollarini yuklashda xatolik yuz berdi.');
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

            await fetchCashierTables();
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

            await fetchCashierTables();
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

            await fetchCashierTables();
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
        fetchCashierTables,
        createTable,
        updateTable,
        deleteTable
    };
});
