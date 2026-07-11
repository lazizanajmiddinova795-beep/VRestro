<template>
  <div class="space-y-4 pb-28">
    <!-- Back to Tables Map / Selected Table Info -->
    <div class="flex items-center justify-between">
      <button @click="backToTables" class="flex items-center space-x-2 text-sm text-slate-600 hover:text-slate-900 font-bold transition duration-200">
        <ArrowLeft class="w-4 h-4" />
        <span>{{ t('back_to_tables') }}</span>
      </button>
      
      <!-- Table dropdown selector container -->
      <div class="relative">
        <button 
          @click="toggleTableSelector"
          class="flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 border border-indigo-200 text-sm font-bold text-indigo-700 cursor-pointer"
        >
          <span>{{ t('table_label') }}: {{ tableNumber }}</span>
          <ChevronDown class="w-3.5 h-3.5" />
        </button>
        <div v-if="showTableDropdown" class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 max-h-60 overflow-y-auto">
          <button 
            v-for="table in waiterStore.tables" 
            :key="table.id"
            @click="switchTable(table)"
            class="w-full text-left px-4 py-2.5 text-xs text-slate-700 hover:bg-slate-50 hover:text-slate-955 transition-colors"
            :class="table.id === tableId ? 'text-indigo-600 font-black bg-indigo-50' : ''"
          >
            {{ table.table_number }} ({{ table.status === 'empty' ? t('empty_tag') : t('my_tag') }})
          </button>
        </div>
      </div>
    </div>

    <!-- Search input -->
    <div class="relative w-full">
      <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
        <Search class="w-4 h-4" />
      </span>
      <input 
        v-model="searchQuery"
        type="text" 
        :placeholder="t('search_placeholder')"
        class="w-full pl-10 pr-4 py-2.5 bg-white border-2 border-slate-300 text-slate-900 placeholder-slate-500 rounded-xl focus:outline-none focus:border-indigo-600 transition duration-200 font-bold"
      />
    </div>

    <!-- Horizontal Category Switcher -->
    <div class="flex space-x-2 overflow-x-auto pb-2 scrollbar-none">
      <button 
        @click="selectCategory(null)"
        class="px-4 py-2.5 rounded-xl text-xs whitespace-nowrap transition duration-200"
        :class="!selectedCategoryId ? 'bg-indigo-600 text-white font-black shadow-md' : 'bg-slate-200/70 text-slate-700 font-bold border border-slate-300'"
      >
        🍲 {{ t('all_cats') }}
      </button>

      <button 
        v-for="cat in menuStore.categories" 
        :key="cat.id"
        @click="selectCategory(cat.id)"
        class="px-4 py-2.5 rounded-xl text-xs whitespace-nowrap transition duration-200"
        :class="selectedCategoryId === cat.id ? 'bg-indigo-600 text-white font-black shadow-md' : 'bg-slate-200/70 text-slate-700 font-bold border border-slate-300'"
      >
        🍽️ {{ translateCategory(cat.name) }}
      </button>
    </div>

    <!-- Loading Feed state -->
    <div v-if="menuStore.loading" class="space-y-3">
      <div v-for="n in 5" :key="n" class="h-20 bg-slate-200 animate-pulse rounded-2xl border border-slate-300 shadow-sm"></div>
    </div>

    <!-- Foods List Feed -->
    <div v-else class="space-y-3">
      <div 
        v-for="food in filteredFoods" 
        :key="food.id"
        class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm mb-3 flex items-center justify-between transition duration-200"
        :class="!food.is_available ? 'opacity-50 bg-slate-100 border-slate-300' : 'hover:border-slate-350'"
      >
        <div class="flex items-center space-x-3 min-w-0">
          <div class="w-14 h-14 rounded-xl bg-slate-100 flex items-center justify-center overflow-hidden border border-slate-200 shrink-0">
            <img v-if="food.image_url" :src="food.image_url" :alt="food.name" class="w-full h-full object-cover" />
            <ChefHat v-else class="w-6 h-6 text-slate-500 stroke-[1.2]" />
          </div>
          <div class="min-w-0">
            <h4 class="text-slate-900 font-black text-base leading-snug truncate" :title="food.name">{{ food.name }}</h4>
            <span class="text-indigo-600 font-bold text-sm block mt-0.5">{{ formatCurrency(food.price) }}</span>
          </div>
        </div>

        <div>
          <!-- Stop list check -->
          <span 
            v-if="!food.is_available" 
            class="bg-rose-100 text-rose-700 font-black border border-rose-300 px-3 py-1.5 rounded-xl text-xs uppercase tracking-wider"
          >
            {{ t('out_of_stock') }}
          </span>
          <button 
            v-else
            @click="triggerAddFlow(food)"
            class="bg-indigo-600 hover:bg-indigo-700 text-white font-black py-2 px-4 rounded-xl shadow-sm text-sm active:scale-95 transition-all"
          >
            {{ t('add_btn') }}
          </button>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="filteredFoods.length === 0" class="text-center py-12 text-slate-700 font-bold text-sm">
        {{ t('no_foods_found') }}
      </div>
    </div>

    <!-- Product Options Sheet Modal -->
    <ProductOptionsSheet 
      v-if="activeCustomFood" 
      :food="activeCustomFood"
      :initialSizeName="editingCartItem ? editingCartItem.size_name : null"
      :initialNotes="editingCartItem ? editingCartItem.notes : ''"
      @close="activeCustomFood = null"
      @add="handleCustomAdd"
    />

    <!-- Slide-Up Floating Cart Bar -->
    <div 
      v-if="cartItems.length > 0"
      class="fixed bottom-20 left-4 right-4 z-40 bg-indigo-600 text-white py-3.5 px-5 rounded-2xl flex items-center justify-between shadow-xl cursor-pointer animate-slideUp"
      @click="openCartDrawer"
    >
      <div class="flex items-center space-x-2 text-white font-black text-sm">
        <ShoppingBag class="w-5 h-5" />
        <span>{{ t('cart_items_count') }}: {{ cartCount }} {{ t('qty_unit') }}</span>
      </div>
      <div class="flex items-center space-x-1.5">
        <span class="text-base font-black text-white">{{ formatCurrency(cartTotal) }}</span>
        <ChevronUp class="w-4 h-4 text-white" />
      </div>
    </div>

    <!-- Slide-Up Bottom Cart Drawer -->
    <div 
      v-if="showCartDrawer"
      class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 backdrop-blur-sm"
      @click.self="closeCartDrawer"
    >
      <div class="w-full max-w-md bg-white border-t-2 border-slate-300 rounded-t-3xl p-6 flex flex-col max-h-[85vh] shadow-2xl animate-slideUp">
        <div class="flex items-center justify-between border-b border-slate-200 pb-4 shrink-0">
          <div>
            <h3 class="text-lg font-black text-slate-900">{{ t('cart_title') }}</h3>
            <p class="text-xs text-slate-700 font-bold">{{ t('table_label') }}: {{ tableNumber }}</p>
          </div>
          <button @click="closeCartDrawer" class="p-2 rounded-xl bg-slate-100 text-slate-600 hover:text-slate-900 border border-slate-200">
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Cart items list -->
        <div class="flex-grow overflow-y-auto py-4 space-y-4 pr-1">
          <div 
            v-for="item in cartItems" 
            :key="item.food_id + '-' + (item.size_name || 'default')"
            class="p-4 rounded-2xl bg-slate-50 border-2 border-slate-200 space-y-3"
          >
            <div class="flex justify-between items-start">
              <div @click="triggerEditFlow(item)" class="cursor-pointer group flex-grow pr-4">
                <h4 class="text-base font-black text-slate-900 group-hover:text-indigo-600 transition-colors flex items-center">
                  {{ item.name }}
                  <span v-if="item.size_name" class="text-[10px] px-2 py-0.5 rounded bg-indigo-50 border border-indigo-200 text-indigo-700 ml-1.5 font-bold">
                    {{ item.size_name }}
                  </span>
                  <span class="text-xs text-slate-500 ml-2 opacity-0 group-hover:opacity-100 transition-opacity">({{ t('edit_tag') }})</span>
                </h4>
                <span class="text-sm font-bold text-slate-600 block mt-0.5">{{ formatCurrency(item.price) }}</span>
              </div>

              <!-- Count Adjustments -->
              <div class="flex items-center space-x-3 bg-white border-2 border-slate-350 px-2 py-1 rounded-xl shrink-0">
                <button @click="updateQty(item.food_id, -1, item.size_name)" class="text-slate-700 hover:text-slate-955 px-2 py-0.5 text-base font-extrabold">-</button>
                <span class="text-slate-950 text-sm font-black">{{ item.quantity }}</span>
                <button @click="updateQty(item.food_id, 1, item.size_name)" class="text-slate-700 hover:text-slate-955 px-2 py-0.5 text-base font-extrabold">+</button>
              </div>
            </div>

            <!-- Notes Modification -->
            <div class="relative">
              <input 
                type="text" 
                v-model="item.notes"
                :placeholder="t('modifier_placeholder')"
                @input="updateNote(item.food_id, item.notes, item.size_name)"
                class="w-full px-3 py-2 rounded-xl bg-white border-2 border-slate-300 text-xs text-slate-900 placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition duration-150 font-bold"
              />
            </div>
          </div>
        </div>

        <!-- Action Drawer Section -->
        <div class="border-t border-slate-200 pt-4 space-y-4 shrink-0">
          <div class="flex justify-between items-center text-sm font-bold text-slate-900">
            <span>{{ t('subtotal_cart') }}:</span>
            <span class="text-lg text-indigo-600 font-black">{{ formatCurrency(cartTotal) }}</span>
          </div>

          <button 
            @click="submitOrder"
            :disabled="submitting"
            class="w-full py-4 rounded-xl bg-indigo-600 font-black text-white text-base shadow-md hover:bg-indigo-750 active:scale-95 transition-all duration-200 flex items-center justify-center space-x-2 disabled:opacity-50"
          >
            <span v-if="submitting" class="flex items-center space-x-2">
              <Loader2 class="w-5 h-5 animate-spin" />
              <span>{{ t('sending_tag') }}...</span>
            </span>
            <span v-else class="flex items-center space-x-2">
              <Send class="w-5 h-5" />
              <span>{{ t('send_kitchen') }}</span>
            </span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useMenuStore } from '@/stores/menu';
import { useWaiterStore } from '@/stores/waiter';
import { useWaiterCartStore } from '@/stores/waiterCart';
import { ArrowLeft, Search, ChefHat, ShoppingBag, ChevronUp, ChevronDown, X, Loader2, Send } from 'lucide-vue-next';
import ProductOptionsSheet from './ProductOptionsSheet.vue';

const menuStore = useMenuStore();
const waiterStore = useWaiterStore();
const waiterCartStore = useWaiterCartStore();
const router = useRouter();

// State properties
const searchQuery = ref('');
const selectedCategoryId = ref(null);
const showCartDrawer = ref(false);
const showTableDropdown = ref(false);
const submitting = ref(false);
const activeCustomFood = ref(null);
const editingCartItem = ref(null);

const currentLang = computed(() => localStorage.getItem('waiter_lang') || 'uz');

const dictionary = {
  uz: {
    back_to_tables: "Stollar xaritasiga qaytish",
    table_label: "Stol",
    empty_tag: "Bo'sh",
    my_tag: "Mening",
    search_placeholder: "Taomlarni qidirish...",
    all_cats: "Barchasi",
    out_of_stock: "Tugagan",
    add_btn: "+ Qo'shish",
    no_foods_found: "Hech qanday mos keladigan taom topilmadi.",
    cart_items_count: "Savatchada",
    qty_unit: "ta taom",
    cart_title: "Buyurtma Savatchasi",
    edit_tag: "Tahrirlash",
    modifier_placeholder: "Modifikator (masalan: Piyozi bo'lmasin)",
    subtotal_cart: "Umumiy miqdor",
    sending_tag: "Oshxonaga yuborilmoqda",
    send_kitchen: "Oshxonaga yuborish",
    occupied_table_alert: "Ushbu stol band.",
    submit_success: "Buyurtma oshxonaga muvaffaqiyatli yuborildi!",
    submit_error: "Buyurtmani topshirishda xatolik."
  },
  ru: {
    back_to_tables: "Вернуться к карте столов",
    table_label: "Стол",
    empty_tag: "Свободен",
    my_tag: "Мой",
    search_placeholder: "Поиск блюд...",
    all_cats: "Все",
    out_of_stock: "Закончился",
    add_btn: "+ Добавить",
    no_foods_found: "Совпадающих блюд не найдено.",
    cart_items_count: "В корзине",
    qty_unit: "блюд",
    cart_title: "Корзина заказа",
    edit_tag: "Изменить",
    modifier_placeholder: "Модификатор (например: без лука)",
    subtotal_cart: "Общая сумма",
    sending_tag: "Отправка на кухню",
    send_kitchen: "Отправить на кухню",
    occupied_table_alert: "Этот стол занят.",
    submit_success: "Заказ успешно отправлен на кухню!",
    submit_error: "Ошибка при отправке заказа."
  }
};

const t = (key) => {
  return dictionary[currentLang.value]?.[key] || key;
};

// Category translation mappings
const categoryTranslations = {
  uz: {
    'Ichimliklar': 'Ichimliklar',
    'Salatlar': 'Salatlar',
    'Shirinliklar': 'Shirinliklar',
    'Taomlar': 'Taomlar'
  },
  ru: {
    'Ichimliklar': 'Напитки',
    'Salatlar': 'Салаты',
    'Shirinliklar': 'Десерты',
    'Taomlar': 'Блюда'
  }
};

const translateCategory = (name) => {
  return categoryTranslations[currentLang.value]?.[name] || name;
};

const tableId = computed(() => waiterStore.activeTableId);
const tableNumber = computed(() => {
  const currentTable = waiterStore.tables.find(t => t.id === tableId.value);
  return currentTable ? currentTable.table_number : t('table_label');
});

// Cart states
const cartItems = computed(() => waiterCartStore.getCartForTable(tableId.value));
const cartCount = computed(() => waiterCartStore.getCartCount(tableId.value));
const cartTotal = computed(() => waiterCartStore.getCartTotal(tableId.value));

onMounted(async () => {
  if (!tableId.value) {
    router.push({ name: 'waiter-tables' });
    return;
  }
  // Initialize Menu items from store
  if (menuStore.categories.length === 0) {
    await menuStore.fetchCategories();
  }
  await menuStore.fetchFoods();
});

const selectCategory = (categoryId) => {
  selectedCategoryId.value = categoryId;
  menuStore.selectedCategoryId = categoryId;
  menuStore.fetchFoods();
};

const filteredFoods = computed(() => {
  const query = searchQuery.value.toLowerCase().trim();
  if (!query) return menuStore.foods;
  return menuStore.foods.filter(f => f.name.toLowerCase().includes(query));
});

const triggerAddFlow = (food) => {
  editingCartItem.value = null;
  activeCustomFood.value = food;
};

const triggerEditFlow = (item) => {
  const food = menuStore.foods.find(f => f.id === item.food_id);
  if (food) {
    editingCartItem.value = item;
    activeCustomFood.value = food;
    showCartDrawer.value = false;
  }
};

const handleCustomAdd = (payload) => {
  if (editingCartItem.value) {
    // Edit action
    waiterCartStore.editCartItem(
      tableId.value,
      editingCartItem.value.food_id,
      editingCartItem.value.size_name,
      payload.size_name,
      payload.price,
      payload.notes
    );
  } else {
    // Add action
    waiterCartStore.addToCart(tableId.value, activeCustomFood.value, payload.size_name, payload.price, payload.notes);
  }
  activeCustomFood.value = null;
  editingCartItem.value = null;
};

const updateQty = (foodId, change, sizeName = null) => {
  waiterCartStore.updateQty(tableId.value, foodId, change, sizeName);
};

const updateNote = (foodId, note, sizeName = null) => {
  waiterCartStore.updateNote(tableId.value, foodId, note, sizeName);
};

const openCartDrawer = () => {
  showCartDrawer.value = true;
};

const closeCartDrawer = () => {
  showCartDrawer.value = false;
};

const toggleTableSelector = () => {
  showTableDropdown.value = !showTableDropdown.value;
};

const switchTable = (table) => {
  showTableDropdown.value = false;
  if (table.status === 'occupied_by_other') {
    alert(t('occupied_table_alert'));
    return;
  }
  waiterStore.selectTable(table.id, table.active_order_id);
};

const backToTables = () => {
  router.push({ name: 'waiter-tables' });
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat('uz-UZ', { style: 'currency', currency: 'UZS', maximumFractionDigits: 0 }).format(value);
};

const submitOrder = async () => {
  submitting.value = true;
  try {
    const response = await fetch('/api/waiter/orders/submit', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('vrestro_token')}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        table_id: tableId.value,
        items: cartItems.value
      })
    });

    const result = await response.json();
    if (!response.ok) {
      throw new Error(result.message || t('submit_error'));
    }

    // Success clearing
    waiterCartStore.clearCart(tableId.value);
    closeCartDrawer();
    alert(t('submit_success'));
    
    // Refresh tables and redirect back
    await waiterStore.fetchTables();
    router.push({ name: 'waiter-tables' });

  } catch (error) {
    alert(error.message);
  } finally {
    submitting.value = false;
  }
};
</script>

<style scoped>
/* Scrollbar removal */
.scrollbar-none::-webkit-scrollbar {
  display: none;
}
.scrollbar-none {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
