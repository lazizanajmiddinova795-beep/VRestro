import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useWaiterStore = defineStore('waiter', () => {
    const activeTableId = ref(null);
    const activeOrderId = ref(null);
    const currentTab = ref('stollar'); // 'stollar' | 'yangi-buyurtma' | 'holatlar' | 'profil'
    const tables = ref([]);
    const activeOrders = ref([]);
    const loading = ref(false);
    const error = ref(null);
    const toastMessage = ref(null);
    
    let pollingInterval = null;

    const playBeepNotification = () => {
        try {
            const AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (!AudioContextClass) return;
            const ctx = new AudioContextClass();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, ctx.currentTime); // High pitch A5
            gain.gain.setValueAtTime(0.1, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
            
            osc.connect(gain);
            gain.connect(ctx.destination);
            
            osc.start();
            osc.stop(ctx.currentTime + 0.3);
        } catch (e) {
            console.error('Audio synth error: ', e);
        }
    };

    const triggerToast = (msg) => {
        toastMessage.value = msg;
        playBeepNotification();
        setTimeout(() => {
            if (toastMessage.value === msg) {
                toastMessage.value = null;
            }
        }, 5000);
    };

    const fetchTables = async (silent = false) => {
        if (!silent) loading.value = true;
        error.value = null;
        try {
            const token = localStorage.getItem('vrestro_token');
            const response = await fetch('/api/waiter/tables', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            if (!response.ok) {
                throw new Error('Stollar ro\'yxatini yuklashda xatolik yuz berdi');
            }
            const data = await response.json();
            tables.value = data;
        } catch (err) {
            error.value = err.message;
        } finally {
            if (!silent) loading.value = false;
        }
    };

    const fetchActiveStatus = async (silent = false) => {
        if (!silent) loading.value = true;
        try {
            const token = localStorage.getItem('vrestro_token');
            const response = await fetch('/api/waiter/orders/active-status', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            if (!response.ok) {
                throw new Error('Faol buyurtmalarni olishda xatolik.');
            }
            const data = await response.json();

            // Track state updates for completed foods (cooking -> ready status changes)
            if (activeOrders.value.length > 0) {
                data.forEach(newOrder => {
                    const oldOrder = activeOrders.value.find(o => o.id === newOrder.id);
                    if (oldOrder && newOrder.items) {
                        newOrder.items.forEach(newItem => {
                            const oldItem = oldOrder.items?.find(it => it.id === newItem.id);
                            if (oldItem && oldItem.status === 'cooking' && newItem.status === 'ready') {
                                // Trigger completed status notification alert
                                triggerToast(`Stol ${newOrder.table?.table_number || ''}: ${newItem.food?.name || ''} tayyor! Oshxonadan olib keting!`);
                            }
                        });
                    }
                });
            }

            activeOrders.value = data;
        } catch (err) {
            console.error(err.message);
        } finally {
            if (!silent) loading.value = false;
        }
    };

    const startPolling = () => {
        if (pollingInterval) clearInterval(pollingInterval);
        fetchActiveStatus(true);
        pollingInterval = setInterval(() => {
            fetchActiveStatus(true);
            fetchTables(true); // Keep status highlights updated silently without blinking skeletons
        }, 4000);
    };

    const stopPolling = () => {
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }
    };

    const selectTable = (tableId, orderId = null) => {
        activeTableId.value = tableId;
        activeOrderId.value = orderId;
    };

    const setTab = (tab) => {
        currentTab.value = tab;
    };

    return {
        activeTableId,
        activeOrderId,
        currentTab,
        tables,
        activeOrders,
        loading,
        error,
        toastMessage,
        fetchTables,
        fetchActiveStatus,
        startPolling,
        stopPolling,
        selectTable,
        setTab
    };
});
