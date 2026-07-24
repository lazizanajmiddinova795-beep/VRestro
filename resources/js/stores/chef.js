import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';

export const useChefStore = defineStore('chef', () => {
    const authStore = useAuthStore();
    const activeItems = ref([]);
    const menu = ref([]);
    const loading = ref(false);
    const stopListLoading = ref(false);
    const error = ref('');
    let pollingInterval = null;

    // Kitchen terminal settings state persisted locally with defaults
    const defaultSettings = {
        volume: 0.8,
        newOrderSound: true,
        alertSound: true,
        layoutScale: 'normal' // compact | normal | large
    };
    const savedSettings = {
        ...defaultSettings,
        ...JSON.parse(localStorage.getItem('vrestro_kitchen_pref') || '{}')
    };
    const kitchenSettings = ref(savedSettings);

    const updateSetting = (key, value) => {
        kitchenSettings.value = {
            ...kitchenSettings.value,
            [key]: value
        };
        localStorage.setItem('vrestro_kitchen_pref', JSON.stringify(kitchenSettings.value));
    };

    /**
     * Web Audio API Synthesizer Chime Engine.
     * Plays local sound effects for touchscreen alerts without loading physical files.
     */
    const playChime = (type = 'newOrder') => {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            
            const ctx = new AudioContext();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            
            osc.connect(gain);
            gain.connect(ctx.destination);
            
            const vol = parseFloat(kitchenSettings.value.volume);
            gain.gain.setValueAtTime(vol, ctx.currentTime);
            
            if (type === 'newOrder') {
                // Ascending bright double-tone chime (C5 -> E5)
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(523.25, ctx.currentTime); // C5
                osc.start(ctx.currentTime);
                
                osc.frequency.setValueAtTime(659.25, ctx.currentTime + 0.12); // E5
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.5);
                osc.stop(ctx.currentTime + 0.5);
            } else if (type === 'alert') {
                // Repeating low-frequency sawtooth warning tone
                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(329.63, ctx.currentTime); // E4
                osc.start(ctx.currentTime);
                
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.7);
                osc.stop(ctx.currentTime + 0.7);
            }
        } catch (e) {
            console.error("Web Audio API Chime error: ", e);
        }
    };

    const fetchActiveItems = async () => {
        if (!authStore.token) return;
        
        // Only set loading to true on the very first load to avoid jarring spinners during short polling
        if (activeItems.value.length === 0) {
            loading.value = true;
        }
        error.value = '';

        try {
            const response = await fetch('/api/chef/items', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${authStore.token}`
                }
            });

            const data = await response.json();

            if (!response.ok) {
                if (response.status === 401) {
                    authStore.logout();
                }
                throw new Error(data.message || 'Oshxona faol buyurtmalarini yuklashda xatolik yuz berdi.');
            }

            activeItems.value = data;
        } catch (err) {
            error.value = err.message;
        } finally {
            loading.value = false;
        }
    };

    const updateItemStatus = async (itemId, newStatus) => {
        error.value = '';
        try {
            const response = await fetch(`/api/chef/items/${itemId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${authStore.token}`
                },
                body: JSON.stringify({
                    status: newStatus
                })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Statusni yangilashda xatolik yuz berdi.');
            }

            // Instantly update the item locally to feel snappy
            const itemIndex = activeItems.value.findIndex(item => item.id === itemId);
            if (itemIndex !== -1) {
                if (newStatus === 'ready') {
                    // Remove from active list if marked ready
                    activeItems.value.splice(itemIndex, 1);
                } else {
                    activeItems.value[itemIndex].status = newStatus;
                }
            }

            // Trigger silent background refresh
            fetchActiveItems();

            return data.item;
        } catch (err) {
            error.value = err.message;
            throw err;
        }
    };

    const fetchMenu = async () => {
        if (!authStore.token) return;
        
        stopListLoading.value = true;
        error.value = '';

        try {
            const response = await fetch('/api/kitchen/foods', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${authStore.token}`
                }
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Oshxona stop-list menyusini yuklashda xatolik yuz berdi.');
            }

            menu.value = data;
        } catch (err) {
            error.value = err.message;
        } finally {
            stopListLoading.value = false;
        }
    };

    const toggleFoodAvailability = async (foodId, isAvailable) => {
        if (!authStore.token) return;

        // --- Optimistic UI Update Pattern ---
        // Find and update status in local state immediately so touch feedback is instant
        let targetCategoryIndex = -1;
        let targetFoodIndex = -1;
        let previousStatus = isAvailable;

        for (let c = 0; c < menu.value.length; c++) {
            const fIndex = menu.value[c].foods.findIndex(f => f.id === foodId);
            if (fIndex !== -1) {
                targetCategoryIndex = c;
                targetFoodIndex = fIndex;
                previousStatus = menu.value[c].foods[fIndex].is_available;
                
                // Set optimistic state
                menu.value[c].foods[fIndex].is_available = isAvailable;
                break;
            }
        }

        try {
            const response = await fetch(`/api/kitchen/foods/${foodId}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${authStore.token}`
                },
                body: JSON.stringify({
                    is_available: isAvailable
                })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || "Taom holatini o'zgartirishda xatolik.");
            }

            // Sync with backend final object representation
            if (targetCategoryIndex !== -1 && targetFoodIndex !== -1) {
                menu.value[targetCategoryIndex].foods[targetFoodIndex].is_available = data.food.is_available;
            }
        } catch (err) {
            // Revert back optimistic update on network/backend failure
            if (targetCategoryIndex !== -1 && targetFoodIndex !== -1) {
                menu.value[targetCategoryIndex].foods[targetFoodIndex].is_available = previousStatus;
            }
            error.value = err.message;
            throw err;
        }
    };

    const startPolling = () => {
        stopPolling();
        fetchActiveItems();
        pollingInterval = setInterval(fetchActiveItems, 4000);
    };

    const stopPolling = () => {
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }
    };

    return {
        activeItems,
        menu,
        loading,
        stopListLoading,
        error,
        kitchenSettings,
        updateSetting,
        playChime,
        fetchActiveItems,
        updateItemStatus,
        fetchMenu,
        toggleFoodAvailability,
        startPolling,
        stopPolling
    };
});
