<template>
  <div class="space-y-6 pb-28">
    <!-- Header summary pills -->
    <div class="grid grid-cols-4 gap-2">
      <div class="backdrop-blur-md bg-white/5 border border-white/5 p-2 rounded-xl text-center">
        <span class="text-[9px] text-slate-400 font-bold block">{{ t('total') }}</span>
        <span class="text-sm font-bold text-white mt-1 block">{{ totalItems }}</span>
      </div>
      <div class="backdrop-blur-md bg-white/5 border border-white/5 p-2 rounded-xl text-center">
        <span class="text-[9px] text-slate-400 font-bold block text-slate-400">{{ t('pending') }}</span>
        <span class="text-sm font-bold text-slate-300 mt-1 block">{{ pendingCount }}</span>
      </div>
      <div class="backdrop-blur-md bg-white/5 border border-white/5 p-2 rounded-xl text-center border-cyan-500/25">
        <span class="text-[9px] text-cyan-400 font-bold block">{{ t('cooking') }}</span>
        <span class="text-sm font-bold text-cyan-400 mt-1 block">{{ cookingCount }}</span>
      </div>
      <div class="backdrop-blur-md bg-white/5 border border-white/5 p-2 rounded-xl text-center border-emerald-500/25">
        <span class="text-[9px] text-emerald-400 font-bold block">{{ t('ready') }}</span>
        <span class="text-sm font-bold text-emerald-400 mt-1 block">{{ readyCount }}</span>
      </div>
    </div>

    <!-- Active grouped order cards -->
    <div v-if="waiterStore.activeOrders.length === 0" class="text-center py-20 text-slate-500 text-xs">
      {{ t('no_orders') }}
    </div>

    <div v-else class="space-y-4">
      <div 
        v-for="order in waiterStore.activeOrders" 
        :key="order.id"
        class="backdrop-blur-xl bg-slate-900/50 border border-white/5 rounded-2xl p-4 space-y-3"
      >
        <!-- Table ID & elapsed time metrics header -->
        <div class="flex justify-between items-center border-b border-white/5 pb-2.5">
          <div class="flex items-center space-x-2">
            <span class="text-sm font-bold text-white">{{ order.table?.table_number || t('table') }}</span>
            <span class="text-[10px] px-2 py-0.5 rounded bg-white/5 border border-white/10 text-slate-400">
              {{ order.order_number }}
            </span>
          </div>
          <span class="text-[10px] text-slate-500 font-semibold">{{ formatTime(order.created_at) }} {{ t('dispatched') }}</span>
        </div>

        <!-- Grouped items listing -->
        <div class="space-y-2.5">
          <div 
            v-for="item in order.items" 
            :key="item.id"
            class="flex justify-between items-center py-1.5 border-b border-white/5 last:border-0"
          >
            <div class="min-w-0 pr-2">
              <h5 class="text-xs font-bold text-white truncate">{{ item.food?.name }}</h5>
              <div class="flex items-center space-x-2 mt-0.5">
                <span class="text-3xs text-slate-400">{{ item.quantity }} {{ t('qty_unit') }}</span>
                <span v-if="item.size_name" class="text-3xs px-1 rounded bg-violet-600/10 border border-violet-500/20 text-violet-400">
                  {{ item.size_name }}
                </span>
                <span v-if="item.notes" class="text-3xs text-orange-400/90 italic truncate max-w-40" :title="translateNotes(item.notes)">
                  * {{ translateNotes(item.notes) }}
                </span>
              </div>
            </div>

            <!-- Status Indicator Pills & Cancellation trigger -->
            <div class="flex items-center space-x-2 shrink-0">
              <span 
                v-if="item.status === 'pending'"
                class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-xl bg-slate-800 border border-white/5 text-3xs font-bold text-slate-400"
              >
                <span class="w-1.5 h-1.5 rounded-full bg-slate-400 animate-pulse mr-1"></span>
                {{ t('pending') }}
              </span>
              
              <span 
                v-else-if="item.status === 'cooking'"
                class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-xl bg-cyan-950/20 border border-cyan-500/40 text-3xs font-bold text-cyan-400 shadow-[0_0_10px_rgba(34,211,238,0.1)]"
              >
                <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-ping mr-1"></span>
                {{ t('cooking') }}
              </span>

              <span 
                v-else-if="item.status === 'ready'"
                class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-xl bg-emerald-950/30 border border-emerald-500/50 text-3xs font-bold text-emerald-400 animate-bounceGlow"
              >
                ✓ {{ t('ready') }}
              </span>

              <span 
                v-else
                class="px-2.5 py-1 rounded-xl bg-white/5 border border-white/10 text-3xs font-bold text-slate-500"
              >
                {{ t('delivered') }}
              </span>

              <!-- Action Safeguard -->
              <button 
                v-if="item.status === 'pending'"
                @click="cancelItem(item)"
                class="p-1 rounded-lg bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-white transition duration-150"
                :title="t('cancel_tooltip')"
              >
                <Trash2 class="w-3.5 h-3.5" />
              </button>
              <span 
                v-else-if="['cooking', 'ready'].includes(item.status)"
                class="p-1 text-slate-600 cursor-not-allowed"
                :title="t('lock_tooltip')"
              >
                <Lock class="w-3.5 h-3.5" />
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
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

const cancelItem = async (item) => {
  if (!confirm(t('confirm_cancel'))) return;

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

    alert(t('cancel_success'));
    // Re-fetch status to update tracking feed
    await waiterStore.fetchActiveStatus();
    await waiterStore.fetchTables(); // Refresh tables view status (Emerald green state changes)
  } catch (error) {
    alert(error.message);
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
