<template>
  <ChefLayout>
    <div class="space-y-6">
      <!-- Loading spinner -->
      <div v-if="chefStore.loading" class="flex flex-col items-center justify-center py-20 space-y-4">
        <div class="w-12 h-12 rounded-full border-4 border-amber-500/20 border-t-amber-500 animate-spin"></div>
        <p class="text-slate-400 font-medium">Buyurtmalar yuklanmoqda...</p>
      </div>

      <!-- Error message -->
      <div v-else-if="chefStore.error" class="p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 flex items-center space-x-3">
        <span>{{ chefStore.error }}</span>
        <button @click="chefStore.fetchActiveItems" class="px-3 py-1 bg-red-500/20 rounded-lg text-xs font-semibold hover:bg-red-500/30 transition">
          Qayta urinish
        </button>
      </div>

      <!-- Empty state -->
      <div v-else-if="groupedTickets.length === 0" class="flex flex-col items-center justify-center py-24 text-center space-y-4 bg-white/5 border border-white/5 backdrop-blur-md rounded-2xl">
        <div class="w-16 h-16 rounded-full bg-white/5 flex items-center justify-center border border-white/10 text-slate-500">
          <ChefHat class="w-8 h-8" />
        </div>
        <div>
          <h3 class="text-lg font-bold text-white">Hozircha buyurtmalar yo'q</h3>
          <p class="text-sm text-slate-400 max-w-xs mx-auto mt-1">Oshxonada barcha taomlar tayyorlangan yoki yangi buyurtmalar kelib tushmagan.</p>
        </div>
      </div>

      <!-- Tickets Grid -->
      <div 
        v-else 
        class="grid gap-6"
        :class="{
          'grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 text-xs': chefStore.kitchenSettings.layoutScale === 'compact',
          'grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4': chefStore.kitchenSettings.layoutScale === 'normal',
          'grid-cols-1 md:grid-cols-2 lg:grid-cols-3 text-lg': chefStore.kitchenSettings.layoutScale === 'large'
        }"
      >
        <div 
          v-for="ticket in groupedTickets" 
          :key="ticket.id"
          class="flex flex-col justify-between rounded-2xl border bg-slate-900/50 backdrop-blur-md overflow-hidden transition-all duration-300 hover:scale-[1.01] hover:shadow-xl"
          :class="[
            ticket.status === 'cooking' ? 'border-blue-500/20 shadow-blue-950/20' : 'border-white/5 shadow-black/20',
            isOverdue(ticket.created_at) ? 'border-red-500/40 bg-red-950/10 shadow-red-950/10' : ''
          ]"
        >
          <!-- Card Header Zone -->
          <div 
            class="px-4 py-3 border-b flex items-center justify-between"
            :class="[
              ticket.status === 'cooking' ? 'border-blue-500/10 bg-blue-500/5' : 'border-white/5 bg-white/5',
              isOverdue(ticket.created_at) ? 'border-red-500/15 bg-red-500/5' : ''
            ]"
          >
            <div>
              <div class="font-bold text-white tracking-wide text-base">
                {{ ticket.order?.table?.table_number || 'Olib ketish' }}
              </div>
              <div class="text-[11px] text-slate-400 mt-0.5 flex items-center space-x-1">
                <span>Ofitsiant: {{ ticket.order?.waiter?.name || 'Tizim' }}</span>
              </div>
            </div>

            <!-- Elapsed Time Timer -->
            <div 
              class="text-xs font-mono font-bold px-2 py-1 rounded-md"
              :class="[
                isOverdue(ticket.created_at) 
                  ? 'text-red-500 bg-red-500/10 border border-red-500/20 animate-pulse font-extrabold' 
                  : 'text-amber-400 bg-amber-500/10 border border-amber-500/10'
              ]"
            >
              {{ getElapsedTime(ticket.created_at) }}
            </div>
          </div>

          <!-- Body Line Items -->
          <div class="p-4 flex-grow space-y-3">
            <div class="flex items-center justify-between text-[11px] text-slate-500 border-b border-white/5 pb-1">
              <span>Buyurtma: #{{ ticket.order?.order_number }}</span>
              <span class="capitalize" :class="ticket.status === 'cooking' ? 'text-blue-400' : 'text-amber-400'">
                {{ ticket.status === 'cooking' ? 'Tayyorlanmoqda' : 'Kutilmoqda' }}
              </span>
            </div>

            <div class="space-y-3">
              <div 
                v-for="item in ticket.items" 
                :key="item.id" 
                class="flex flex-col space-y-0.5 border-b border-white/5 last:border-b-0 pb-2 last:pb-0"
              >
                <div class="flex items-start justify-between">
                  <span class="text-sm font-semibold text-slate-200 leading-tight">
                    {{ item.food?.name }}
                    <span v-if="item.size_name" class="text-xs text-slate-400 font-normal">({{ item.size_name }})</span>
                  </span>
                  <span class="text-base font-extrabold text-amber-300 whitespace-nowrap ml-2">
                    {{ item.quantity }}x
                  </span>
                </div>
                <!-- Modification Notes -->
                <div v-if="item.notes" class="text-xs bg-amber-500/5 text-amber-300/80 px-2 py-1 rounded border border-amber-500/10 italic flex items-center space-x-1 mt-1">
                  <span class="font-semibold not-italic">Izoh:</span>
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
              class="w-full py-3.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm transition-all duration-200 active:scale-[0.98] shadow-md shadow-blue-600/20 hover:shadow-blue-500/40 disabled:opacity-50 flex items-center justify-center space-x-2"
            >
              <Play class="w-4 h-4" />
              <span>Boshlash (Start Cooking)</span>
            </button>

            <button 
              v-else-if="ticket.status === 'cooking'"
              @click="handleAction(ticket, 'ready')"
              :disabled="ticket.actioning"
              class="w-full py-3.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm transition-all duration-200 active:scale-[0.98] shadow-md shadow-emerald-600/20 hover:shadow-emerald-500/40 disabled:opacity-50 flex items-center justify-center space-x-2 animate-pulse-slow"
            >
              <CheckCircle class="w-4 h-4 animate-bounce" />
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
