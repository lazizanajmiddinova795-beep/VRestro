<template>
  <ChefLayout>
    <div class="space-y-6 bg-[#F8FAFC] min-h-screen p-1">
      <!-- Loading spinner -->
      <div v-if="chefStore.loading" class="flex flex-col items-center justify-center py-20 space-y-4 bg-white rounded-2xl border border-slate-200 shadow-sm">
        <div class="w-12 h-12 rounded-full border-4 border-blue-200 border-t-blue-600 animate-spin"></div>
        <p class="text-slate-600 font-bold text-lg">Buyurtmalar yuklanmoqda...</p>
      </div>

      <!-- Error message -->
      <div v-else-if="chefStore.error" class="p-4 rounded-xl bg-red-50 border-2 border-red-300 text-red-800 flex items-center space-x-3 shadow-md">
        <span class="font-bold text-lg">{{ chefStore.error }}</span>
        <button @click="chefStore.fetchActiveItems" class="px-3 py-1 bg-red-650 text-white rounded-lg text-sm font-bold hover:bg-red-700 transition shadow">
          Qayta urinish
        </button>
      </div>

      <!-- Empty state -->
      <div v-else-if="groupedTickets.length === 0" class="flex flex-col items-center justify-center py-24 text-center space-y-4 bg-white border-2 border-slate-200 rounded-2xl shadow-md">
        <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center border border-slate-300 text-slate-500 shadow-inner">
          <ChefHat class="w-8 h-8" />
        </div>
        <div>
          <h3 class="text-xl font-black text-slate-900">Hozircha buyurtmalar yo'q</h3>
          <p class="text-base text-slate-700 max-w-sm mx-auto mt-2 font-bold">Oshxonada barcha taomlar tayyorlangan yoki yangi buyurtmalar kelib tushmagan.</p>
        </div>
      </div>

      <!-- Tickets Grid -->
      <div 
        v-else 
        class="grid gap-6"
        :class="{
          'grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 text-sm': chefStore.kitchenSettings.layoutScale === 'compact',
          'grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 text-base': chefStore.kitchenSettings.layoutScale === 'normal',
          'grid-cols-1 md:grid-cols-2 lg:grid-cols-3 text-xl': chefStore.kitchenSettings.layoutScale === 'large'
        }"
      >
        <div 
          v-for="ticket in groupedTickets" 
          :key="ticket.id"
          class="flex flex-col justify-between overflow-hidden transition-all duration-200 bg-white border border-slate-200 rounded-2xl shadow-sm"
          :class="[
            ticket.status === 'cooking' ? 'border-amber-400 bg-amber-50/20' : '',
            ticket.status === 'ready' ? 'border-emerald-500 bg-emerald-50/20' : '',
            isOverdue(ticket.created_at) && ticket.status !== 'ready' ? 'border-red-400 bg-red-50/20' : ''
          ]"
        >
          <!-- Card Header Zone -->
          <div 
            class="px-4 py-3.5 border-b border-slate-200 flex items-center justify-between bg-slate-50/50"
          >
            <div>
              <div 
                class="font-black tracking-wide text-lg"
                :class="[
                  ticket.status === 'pending' ? 'text-slate-900' : '',
                  ticket.status === 'cooking' ? 'text-amber-950' : '',
                  ticket.status === 'ready' ? 'text-white' : '',
                  isOverdue(ticket.created_at) && ticket.status !== 'ready' ? 'text-red-950' : ''
                ]"
              >
                {{ ticket.order?.table?.table_number || 'Olib ketish' }}
              </div>
              <div 
                class="text-xs mt-1 flex items-center space-x-1 font-bold"
                :class="[
                  ticket.status === 'pending' ? 'text-slate-700' : '',
                  ticket.status === 'cooking' ? 'text-amber-900' : '',
                  ticket.status === 'ready' ? 'text-emerald-100' : '',
                  isOverdue(ticket.created_at) && ticket.status !== 'ready' ? 'text-red-900' : ''
                ]"
              >
                <span>Ofitsiant: {{ ticket.order?.waiter?.name || 'Tizim' }}</span>
              </div>
            </div>

            <!-- Elapsed Time Timer -->
            <div 
              class="text-sm font-mono font-black px-2 py-1 rounded-md border"
              :class="[
                ticket.status === 'ready' 
                  ? 'text-white bg-emerald-850 border-emerald-650 font-bold' 
                  : (isOverdue(ticket.created_at) 
                      ? 'text-red-900 bg-red-200 border-red-400 animate-pulse font-extrabold' 
                      : (ticket.status === 'cooking' ? 'text-amber-950 bg-amber-200 border-amber-400 font-black' : 'text-slate-900 bg-slate-200 border-slate-350 font-black'))
              ]"
            >
              {{ getElapsedTime(ticket.created_at) }}
            </div>
          </div>

          <!-- Body Line Items -->
          <div class="p-4 flex-grow space-y-4">
            <div 
              class="flex items-center justify-between text-xs border-b pb-1.5 font-bold"
              :class="[
                ticket.status === 'pending' ? 'text-slate-600 border-slate-200' : '',
                ticket.status === 'cooking' ? 'text-amber-900 border-amber-250' : '',
                ticket.status === 'ready' ? 'text-emerald-100 border-emerald-500' : '',
                isOverdue(ticket.created_at) && ticket.status !== 'ready' ? 'text-red-900 border-red-250' : ''
              ]"
            >
              <span>Buyurtma: #{{ ticket.order?.order_number }}</span>
              <span class="capitalize font-black">
                {{ ticket.status === 'cooking' ? 'Tayyorlanmoqda' : (ticket.status === 'pending' ? 'Kutilmoqda' : 'Tayyor!') }}
              </span>
            </div>

            <div class="space-y-4">
              <div 
                v-for="item in ticket.items" 
                :key="item.id" 
                class="flex flex-col space-y-1.5 border-b last:border-b-0 pb-3 last:pb-0"
                :class="[
                  ticket.status === 'pending' ? 'border-slate-100' : '',
                  ticket.status === 'cooking' ? 'border-amber-100' : '',
                  ticket.status === 'ready' ? 'border-emerald-500' : '',
                  isOverdue(ticket.created_at) && ticket.status !== 'ready' ? 'border-red-100' : ''
                ]"
              >
                <div class="flex items-start justify-between">
                  <span 
                    class="text-base leading-tight font-black"
                    :class="[
                      ticket.status === 'ready' ? 'text-white font-bold' : '',
                      ticket.status === 'cooking' ? 'text-amber-950 font-black' : '',
                      ticket.status === 'pending' ? 'text-slate-900 font-black' : '',
                      isOverdue(ticket.created_at) && ticket.status !== 'ready' ? 'text-red-950 font-black' : ''
                    ]"
                  >
                    {{ item.food?.name }}
                    <span 
                      v-if="item.size_name" 
                      class="text-sm font-bold"
                      :class="[
                        ticket.status === 'ready' ? 'text-emerald-100' : '',
                        ticket.status === 'cooking' ? 'text-amber-900 font-bold' : '',
                        ticket.status === 'pending' ? 'text-slate-700 font-bold' : '',
                        isOverdue(ticket.created_at) && ticket.status !== 'ready' ? 'text-red-900 font-bold' : ''
                      ]"
                    >({{ item.size_name }})</span>
                  </span>
                  <span 
                    class="text-lg font-black whitespace-nowrap ml-2"
                    :class="[
                      ticket.status === 'ready' ? 'text-white' : '',
                      ticket.status === 'cooking' ? 'text-amber-950' : '',
                      ticket.status === 'pending' ? 'text-slate-900' : '',
                      isOverdue(ticket.created_at) && ticket.status !== 'ready' ? 'text-red-950' : ''
                    ]"
                  >
                    {{ item.quantity }}x
                  </span>
                </div>
                <!-- Modification Notes Badges: bg-rose-100 text-rose-800 px-2 py-1 rounded font-black -->
                <div v-if="item.notes" class="text-sm bg-rose-100 text-rose-800 px-2 py-1 rounded font-black flex items-center space-x-1 mt-1 border border-rose-250">
                  <span class="not-italic">Izoh:</span>
                  <span>{{ item.notes }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer Action Interactive Keys -->
          <div class="p-4 pt-0">
            <button 
              v-if="ticket.status === 'pending'"
              @click="handleAction(ticket, 'cooking')"
              :disabled="ticket.actioning"
              class="w-full py-3 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-lg font-bold transition-all duration-200 active:scale-[0.98] disabled:opacity-50 flex items-center justify-center space-x-2 shadow-md hover:shadow-lg"
            >
              <Play class="w-5 h-5" />
              <span>Boshlash (Start Cooking)</span>
            </button>

            <button 
              v-else-if="ticket.status === 'cooking'"
              @click="handleAction(ticket, 'ready')"
              :disabled="ticket.actioning"
              class="w-full py-3 px-4 rounded-lg bg-amber-500 text-slate-950 text-lg font-black transition-all duration-200 active:scale-[0.98] disabled:opacity-50 flex items-center justify-center space-x-2 shadow-md hover:bg-amber-650"
            >
              <CheckCircle class="w-5 h-5 animate-bounce" />
              <span>Tayyor! (Mark Ready)</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </ChefLayout>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { useChefStore } from '@/stores/chef';
import ChefLayout from '@/components/ChefLayout.vue';
import { ChefHat, Play, CheckCircle } from 'lucide-vue-next';

const chefStore = useChefStore();
const timeTrigger = ref(Date.now());
let timerInterval = null;

// Watch for incoming new pending orders to play chime alert
watch(
  () => chefStore.activeItems.filter(item => item.status === 'pending').length,
  (newCount, oldCount) => {
    if (newCount > oldCount && chefStore.kitchenSettings.newOrderSound) {
      chefStore.playChime('newOrder');
    }
  }
);

// Group active items by order_id AND status to represent ticket cards
const groupedTickets = computed(() => {
  // We need to access timeTrigger so this computed property re-evaluates
  const _ = timeTrigger.value;
  
  const ticketsMap = {};

  chefStore.activeItems.forEach(item => {
    const key = `${item.order_id}_${item.status}`;
    if (!ticketsMap[key]) {
      ticketsMap[key] = {
        id: key,
        order_id: item.order_id,
        status: item.status,
        created_at: item.created_at, // Use item's creation date or order's creation date
        order: item.order,
        items: [],
        actioning: false
      };
    }
    ticketsMap[key].items.push(item);
    
    // Use the oldest item's timestamp for the ticket
    if (new Date(item.created_at) < new Date(ticketsMap[key].created_at)) {
      ticketsMap[key].created_at = item.created_at;
    }
  });

  // Convert to array and sort by created_at (oldest first)
  return Object.values(ticketsMap).sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
});

// Calculate minutes elapsed since creation
const getElapsedTime = (createdAt) => {
  const diffMs = timeTrigger.value - new Date(createdAt).getTime();
  const diffMins = Math.max(0, Math.floor(diffMs / 60000));
  return `${diffMins} min`;
};

// Check if ticket is older than 20 minutes
const isOverdue = (createdAt) => {
  const diffMs = timeTrigger.value - new Date(createdAt).getTime();
  const diffMins = diffMs / 60000;
  return diffMins > 20;
};

// Process status transition for all items in the ticket
const handleAction = async (ticket, nextStatus) => {
  ticket.actioning = true;
  try {
    // Process items sequentially or in parallel
    const promises = ticket.items.map(item => 
      chefStore.updateItemStatus(item.id, nextStatus)
    );
    await Promise.all(promises);
  } catch (err) {
    console.error("KDS Action Error: ", err);
  } finally {
    ticket.actioning = false;
  }
};

onMounted(() => {
  chefStore.startPolling();
  
  // Update time trigger every 15 seconds to update timers and check alerts
  timerInterval = setInterval(() => {
    timeTrigger.value = Date.now();
    
    // Kechikish ogohlantirish ovozi check
    if (chefStore.kitchenSettings.alertSound) {
      const hasOverdueCooking = groupedTickets.value.some(ticket => 
        ticket.status === 'cooking' && isOverdue(ticket.created_at)
      );
      if (hasOverdueCooking) {
        chefStore.playChime('alert');
      }
    }
  }, 15000);
});

onUnmounted(() => {
  chefStore.stopPolling();
  if (timerInterval) clearInterval(timerInterval);
});
</script>

<style scoped>
.animate-pulse-slow {
  animation: pulse 2.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.8;
  }
}
</style>
