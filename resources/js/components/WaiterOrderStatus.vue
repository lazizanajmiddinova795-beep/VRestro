<template>
  <div class="space-y-6 pb-28">
    <!-- Header summary pills -->
    <div class="grid grid-cols-4 gap-2">
      <div class="bg-white border-2 border-slate-300 p-2 rounded-xl text-center shadow-sm">
        <span class="text-xs text-slate-600 font-extrabold block">{{ t('total') }}</span>
        <span class="text-base font-black text-slate-900 mt-1 block">{{ totalItems }}</span>
      </div>
      <div class="bg-white border-2 border-slate-300 p-2 rounded-xl text-center shadow-sm">
        <span class="text-xs text-slate-650 font-extrabold block">{{ t('pending') }}</span>
        <span class="text-base font-black text-slate-800 mt-1 block">{{ pendingCount }}</span>
      </div>
      <div class="bg-cyan-50 border-2 border-cyan-300 p-2 rounded-xl text-center shadow-sm">
        <span class="text-xs text-cyan-850 font-extrabold block">{{ t('cooking') }}</span>
        <span class="text-base font-black text-cyan-900 mt-1 block">{{ cookingCount }}</span>
      </div>
      <div class="bg-emerald-50 border-2 border-emerald-300 p-2 rounded-xl text-center shadow-sm">
        <span class="text-xs text-emerald-850 font-extrabold block">{{ t('ready') }}</span>
        <span class="text-base font-black text-emerald-950 mt-1 block">{{ readyCount }}</span>
      </div>
    </div>

    <!-- Active grouped order cards -->
    <div v-if="waiterStore.activeOrders.length === 0" class="text-center py-20 text-slate-700 font-bold text-sm">
      {{ t('no_orders') }}
    </div>

    <div v-else class="space-y-4">
      <div 
        v-for="order in waiterStore.activeOrders" 
        :key="order.id"
        class="bg-white border-2 border-slate-200 rounded-2xl p-4 shadow-sm mb-4 space-y-3"
      >
        <!-- Table ID & elapsed time metrics header -->
        <div class="flex justify-between items-center border-b border-slate-200 pb-2.5">
          <div class="flex items-center space-x-2">
            <span class="text-base font-black text-slate-900">{{ order.table?.table_number || t('table') }}</span>
            <span class="text-xs px-2 py-0.5 rounded bg-slate-100 border border-slate-300 text-slate-800 font-bold">
              {{ order.order_number }}
            </span>
          </div>
          <span class="text-xs text-slate-700 font-black">{{ formatTime(order.created_at) }} {{ t('dispatched') }}</span>
        </div>

        <!-- Grouped items listing -->
        <div class="space-y-2.5">
          <div 
            v-for="item in order.items" 
            :key="item.id"
            class="flex justify-between items-center py-1.5 border-b border-slate-200 last:border-0"
          >
            <div class="min-w-0 pr-2">
              <h5 class="text-base font-black text-slate-900 truncate">{{ item.food?.name }}</h5>
              <div class="flex items-center space-x-2 mt-0.5">
                <span class="text-xs text-slate-700 font-bold">{{ item.quantity }} {{ t('qty_unit') }}</span>
                <span v-if="item.size_name" class="text-xxs px-1 rounded bg-violet-100 border border-violet-300 text-violet-800 font-black">
                  {{ item.size_name }}
                </span>
                <span v-if="item.notes" class="text-sm text-amber-650 font-black bg-amber-50 px-2 py-0.5 rounded truncate max-w-40" :title="translateNotes(item.notes)">
                  * {{ translateNotes(item.notes) }}
                </span>
              </div>
            </div>

            <!-- Status Indicator Pills & Cancellation trigger -->
            <div class="flex items-center space-x-2 shrink-0">
              <span 
                v-if="item.status === 'pending'"
                class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-xl bg-slate-100 border border-slate-300 text-xxs font-black text-slate-700"
              >
                <span class="w-1.5 h-1.5 rounded-full bg-slate-500 animate-pulse mr-1"></span>
                {{ t('pending') }}
              </span>
              
              <span 
                v-else-if="item.status === 'cooking'"
                class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-xl bg-cyan-50 border border-cyan-300 text-xxs font-black text-cyan-800 shadow-[0_0_10px_rgba(6,182,212,0.1)]"
              >
                <span class="w-1.5 h-1.5 rounded-full bg-cyan-500 animate-ping mr-1"></span>
                {{ t('cooking') }}
              </span>

              <span 
                v-else-if="item.status === 'ready'"
                class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-xl bg-emerald-100 border border-emerald-350 text-xxs font-black text-emerald-800 animate-bounceGlow"
              >
                ✓ {{ t('ready') }}
              </span>

              <span 
                v-else
                class="px-2.5 py-1 rounded-xl bg-slate-100 border border-slate-200 text-xxs font-black text-slate-650"
              >
                {{ t('delivered') }}
              </span>

              <!-- Action Safeguard -->
              <button 
                v-if="item.status === 'pending'"
                @click="confirmCancelItem(item)"
                class="p-1 rounded-lg bg-rose-100 hover:bg-rose-600 text-rose-700 hover:text-white transition duration-150"
                :title="t('cancel_tooltip')"
              >
                <Trash2 class="w-3.5 h-3.5" />
              </button>
              <span 
                v-else-if="['cooking', 'ready'].includes(item.status)"
                class="p-1 text-slate-400 cursor-not-allowed"
                :title="t('lock_tooltip')"
              >
                <Lock class="w-3.5 h-3.5" />
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Custom Touch-Friendly Cancel Confirmation Modal -->
    <div 
      v-if="isCancelModalOpen" 
      class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[999] flex items-center justify-center p-4"
    >
      <div class="bg-white border-2 border-slate-350 rounded-3xl w-full max-w-sm shadow-2xl p-6 space-y-6 text-slate-900">
        <div class="text-center space-y-3">
          <div class="w-12 h-12 rounded-full bg-rose-100 border border-rose-300 text-rose-600 flex items-center justify-center mx-auto text-xl font-bold">
            ⚠
          </div>
          <h3 class="text-lg font-black text-slate-900">Bekor qilishni tasdiqlang</h3>
          <p class="text-xs text-slate-700 font-bold leading-relaxed">
            Haqiqatan ham <strong>{{ itemToCancel?.food?.name }}</strong> taomini buyurtmadan o'chirmoqchimisiz?
          </p>
        </div>

        <div class="flex items-center space-x-3 pt-2">
          <button 
            @click="isCancelModalOpen = false"
            class="flex-1 py-3 border-2 border-slate-200 hover:bg-slate-50 text-slate-800 rounded-xl font-bold transition text-xs"
          >
            Bekor qilish
          </button>
          <button 
            @click="executeCancelItem"
            class="flex-1 py-3 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-black transition text-xs shadow-md"
          >
            Ha, o'chirish
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useWaiterStore } from '@/stores/waiter';
import { Trash2, Lock } from 'lucide-vue-next';

const waiterStore = useWaiterStore();

const currentLang = computed(() => localStorage.getItem('waiter_lang') || 'uz');

const dictionary = {
  uz: {
    total: "Jami",
    pending: "Kutilmoqda",
    cooking: "Pishmoqda",
    ready: "Tayyor",
    no_orders: "Hozirda hech qanday faol buyurtmalaringiz yo'q.",
    table: "Stol",
    dispatched: "yuborildi",
    qty_unit: "ta",
    delivered: "Topshirildi",
    confirm_cancel: "Ushbu taomni bekor qilishni tasdiqlaysizmi?",
    cancel_success: "Muvaffaqiyatli bekor qilindi.",
    cancel_tooltip: "Bekor qilish",
    lock_tooltip: "Oshxonada boshlangan (Tahrirlab bo'lmaydi)"
  },
  ru: {
    total: "Всего",
    pending: "В очереди",
    cooking: "Готовится",
    ready: "Готово",
    no_orders: "У вас нет активных заказов на данный момент.",
    table: "Стол",
    dispatched: "отправлено",
    qty_unit: "шт.",
    delivered: "Доставлено",
    confirm_cancel: "Вы действительно хотите отменить это блюдо?",
    cancel_success: "Успешно отменено.",
    cancel_tooltip: "Отменить",
    lock_tooltip: "Начато на кухне (Нельзя изменить)"
  }
};

const t = (key) => {
  return dictionary[currentLang.value]?.[key] || key;
};

// Modifiers translation dictionary
const modifierTranslations = {
  uz: {
    'Piyozsiz': 'Piyozsiz',
    'Achchiq bo\'lsin': 'Achchiq bo\'lsin',
    'Muz bilan': 'Muz bilan',
    'Yog\'siz': 'Yog\'siz',
    'Limon bilan': 'Limon bilan'
  },
  ru: {
    'Piyozsiz': 'Без лука',
    'Achchiq bo\'lsin': 'Острое',
    'Muz bilan': 'С льдом',
    'Yog\'siz': 'Без масла',
    'Limon bilan': 'С лимоном'
  }
};

const translateNotes = (notes) => {
  if (!notes) return '';
  const list = notes.split(',').map(n => n.trim());
  const translated = list.map(item => {
    return modifierTranslations[currentLang.value]?.[item] || item;
  });
  return translated.join(', ');
};

// Calculations of statuses
const allItems = computed(() => {
  return waiterStore.activeOrders.flatMap(o => o.items || []);
});

const totalItems = computed(() => allItems.value.length);
const pendingCount = computed(() => allItems.value.filter(i => i.status === 'pending').length);
const cookingCount = computed(() => allItems.value.filter(i => i.status === 'cooking').length);
const readyCount = computed(() => allItems.value.filter(i => i.status === 'ready').length);

const isCancelModalOpen = ref(false);
const itemToCancel = ref(null);

const confirmCancelItem = (item) => {
  itemToCancel.value = item;
  isCancelModalOpen.value = true;
};

const executeCancelItem = async () => {
  if (!itemToCancel.value) return;
  const item = itemToCancel.value;
  try {
    const token = localStorage.getItem('vrestro_token');
    const response = await fetch(`/api/waiter/order-item/${item.id}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    });

    const result = await response.json();
    if (!response.ok) {
      throw new Error(result.message || 'Taomni bekor qilishda xatolik.');
    }

    waiterStore.triggerToast(t('cancel_success'));
    // Re-fetch status to update tracking feed
    await waiterStore.fetchActiveStatus();
    await waiterStore.fetchTables(); // Refresh tables view status
  } catch (error) {
    waiterStore.triggerToast(error.message);
  } finally {
    isCancelModalOpen.value = false;
    itemToCancel.value = null;
  }
};

onMounted(() => {
  waiterStore.fetchActiveStatus();
});

const formatTime = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleTimeString('uz-UZ', { hour: '2-digit', minute: '2-digit' });
};</script>

<style scoped>
@keyframes bounceGlow {
  0%, 100% {
    transform: translateY(0);
    box-shadow: 0 0 5px rgba(16,185,129,0.2);
  }
  50% {
    transform: translateY(-2px);
    box-shadow: 0 0 15px rgba(16,185,129,0.5);
  }
}

.animate-bounceGlow {
  animation: bounceGlow 1.5s infinite ease-in-out;
}
</style>
