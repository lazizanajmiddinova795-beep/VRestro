import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useWaiterCartStore = defineStore('waiterCart', () => {
    // Structure: { [tableId]: [ { food_id, name, price, quantity, notes } ] }
    const carts = ref(JSON.parse(localStorage.getItem('vrestro_waiter_carts') || '{}'));

    const saveToStorage = () => {
        localStorage.setItem('vrestro_waiter_carts', JSON.stringify(carts.value));
    };

    const getCartForTable = (tableId) => {
        return carts.value[tableId] || [];
    };

    const addToCart = (tableId, food, sizeName = null, price = null, extraNotes = '', quantity = 1) => {
        if (!carts.value[tableId]) {
            carts.value[tableId] = [];
        }

        const finalPrice = price !== null ? price : food.price;
        const finalSizeName = sizeName;
        const finalQuantity = quantity > 0 ? quantity : 1;

        const existingItem = carts.value[tableId].find(item => item.food_id === food.id && item.size_name === finalSizeName);
        if (existingItem) {
            existingItem.quantity += finalQuantity;
            if (extraNotes) {
                existingItem.notes = existingItem.notes ? `${existingItem.notes}, ${extraNotes}` : extraNotes;
            }
        } else {
            carts.value[tableId].push({
                food_id: food.id,
                name: food.name,
                price: finalPrice,
                quantity: finalQuantity,
                notes: extraNotes,
                size_name: finalSizeName
            });
        }
        saveToStorage();
    };

    const updateQty = (tableId, foodId, change, sizeName = null) => {
        if (!carts.value[tableId]) return;

        const itemIndex = carts.value[tableId].findIndex(item => item.food_id === foodId && item.size_name === sizeName);
        if (itemIndex > -1) {
            const item = carts.value[tableId][itemIndex];
            item.quantity += change;
            if (item.quantity <= 0) {
                carts.value[tableId].splice(itemIndex, 1);
            }
        }
        saveToStorage();
    };

    const editCartItem = (tableId, foodId, oldSizeName, newSizeName, newPrice, newNotes, newQuantity = null) => {
        if (!carts.value[tableId]) return;

        const itemIndex = carts.value[tableId].findIndex(item => item.food_id === foodId && item.size_name === oldSizeName);
        if (itemIndex > -1) {
            // Check if we already have an item with the new size name to merge, or if size name didn't change
            const targetIndex = carts.value[tableId].findIndex((item, idx) => item.food_id === foodId && item.size_name === newSizeName && idx !== itemIndex);
            const editedQuantity = newQuantity > 0 ? newQuantity : carts.value[tableId][itemIndex].quantity;

            if (targetIndex > -1) {
                // Merge quantity with existing target size item
                carts.value[tableId][targetIndex].quantity += editedQuantity;
                if (newNotes) {
                    const existingNotes = carts.value[tableId][targetIndex].notes;
                    carts.value[tableId][targetIndex].notes = existingNotes ? `${existingNotes}, ${newNotes}` : newNotes;
                }
                // Remove the old item
                carts.value[tableId].splice(itemIndex, 1);
            } else {
                // Update size and other options on current item
                const item = carts.value[tableId][itemIndex];
                item.size_name = newSizeName;
                item.price = newPrice;
                item.notes = newNotes;
                item.quantity = editedQuantity;
            }
            saveToStorage();
        }
    };

    const clearCart = (tableId) => {
        delete carts.value[tableId];
        saveToStorage();
    };

    const getCartTotal = (tableId) => {
        const items = getCartForTable(tableId);
        return items.reduce((sum, item) => sum + (parseFloat(item.price) * item.quantity), 0);
    };

    const getCartCount = (tableId) => {
        const items = getCartForTable(tableId);
        return items.reduce((sum, item) => sum + item.quantity, 0);
    };

    return {
        carts,
        getCartForTable,
        addToCart,
        updateQty,
        editCartItem,
        clearCart,
        getCartTotal,
        getCartCount
    };
});
