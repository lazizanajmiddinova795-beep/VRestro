import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';

export const useReportsStore = defineStore('reports', () => {
    const authStore = useAuthStore();
    
    // Default range is the current month
    const defaultStart = () => {
        const d = new Date();
        return new Date(d.getFullYear(), d.getMonth(), 1).toISOString().split('T')[0];
    };
    const defaultEnd = () => {
        const d = new Date();
        return new Date(d.getFullYear(), d.getMonth() + 1, 0).toISOString().split('T')[0];
    };

    const startDate = ref(defaultStart());
    const endDate = ref(defaultEnd());
    const activePeriod = ref('month'); // 'today' | 'week' | 'month' | 'year' | 'custom'

    const salesReport = ref(null);
    const menuReport = ref(null);
    const inventoryReport = ref(null);
    const staffReport = ref(null);

    const loading = ref(false);
    const error = ref('');

    const setPeriod = (period) => {
        activePeriod.value = period;
        const today = new Date();
        
        if (period === 'today') {
            const dateStr = today.toISOString().split('T')[0];
            startDate.value = dateStr;
            endDate.value = dateStr;
        } else if (period === 'week') {
            const first = today.getDate() - today.getDay() + 1; // Monday
            const firstday = new Date(today.setDate(first)).toISOString().split('T')[0];
            const lastday = new Date(today.setDate(first + 6)).toISOString().split('T')[0];
            startDate.value = firstday;
            endDate.value = lastday;
        } else if (period === 'month') {
            startDate.value = defaultStart();
            endDate.value = defaultEnd();
        } else if (period === 'year') {
            const year = today.getFullYear();
            startDate.value = `${year}-01-01`;
            endDate.value = `${year}-12-31`;
        }
    };

    const getHeaders = () => ({
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${authStore.token}`
    });

    const fetchSalesReport = async () => {
        loading.value = true;
        error.value = '';
        try {
            const response = await fetch(`/api/reports/sales?start_date=${startDate.value}&end_date=${endDate.value}`, {
                headers: getHeaders()
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Sotuv hisobotini yuklashda xatolik.');
            salesReport.value = data;
        } catch (err) {
            error.value = err.message;
        } finally {
            loading.value = false;
        }
    };

    const fetchMenuReport = async () => {
        loading.value = true;
        error.value = '';
        try {
            const response = await fetch(`/api/reports/menu?start_date=${startDate.value}&end_date=${endDate.value}`, {
                headers: getHeaders()
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Menyu hisobotini yuklashda xatolik.');
            menuReport.value = data;
        } catch (err) {
            error.value = err.message;
        } finally {
            loading.value = false;
        }
    };

    const fetchInventoryReport = async () => {
        loading.value = true;
        error.value = '';
        try {
            const response = await fetch(`/api/reports/inventory?start_date=${startDate.value}&end_date=${endDate.value}`, {
                headers: getHeaders()
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Ombor hisobotini yuklashda xatolik.');
            inventoryReport.value = data;
        } catch (err) {
            error.value = err.message;
        } finally {
            loading.value = false;
        }
    };

    const fetchStaffReport = async () => {
        loading.value = true;
        error.value = '';
        try {
            const response = await fetch(`/api/reports/staff?start_date=${startDate.value}&end_date=${endDate.value}`, {
                headers: getHeaders()
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Xodimlar hisobotini yuklashda xatolik.');
            staffReport.value = data;
        } catch (err) {
            error.value = err.message;
        } finally {
            loading.value = false;
        }
    };

    const fetchAllReports = async () => {
        loading.value = true;
        await Promise.all([
            fetchSalesReport(),
            fetchMenuReport(),
            fetchInventoryReport(),
            fetchStaffReport()
        ]);
        loading.value = false;
    };

    return {
        startDate,
        endDate,
        activePeriod,
        salesReport,
        menuReport,
        inventoryReport,
        staffReport,
        loading,
        error,
        setPeriod,
        fetchAllReports,
        fetchSalesReport,
        fetchMenuReport,
        fetchInventoryReport,
        fetchStaffReport
    };
});
