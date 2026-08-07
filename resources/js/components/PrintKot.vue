<template>
  <Teleport to="body">
    <!-- Hidden element that only shows up during printing -->
    <div v-if="order" id="print-kot-container" class="print-only thermal-ticket p-4 bg-white text-black">
      <div class="ticket-center mb-4">
        <h2 class="text-xl font-bold uppercase tracking-wider mb-1">Buyurtma Cheki</h2>
        <div class="text-xs uppercase font-bold bg-gray-200 py-1 px-2 inline-block rounded">
          KOT (Kitchen Order Ticket)
        </div>
      </div>

      <div class="ticket-divider"></div>
      
      <div class="flex justify-between text-sm font-bold my-2">
        <span>Stol: {{ order.table?.table_number || order.table?.name || 'Olib ketish' }}</span>
        <span>Buyurtma: #{{ order.order_number || order.id }}</span>
      </div>
      
      <div class="flex justify-between text-xs my-1">
        <span>Sana: {{ formatDate(order.created_at || new Date()) }}</span>
        <span>Ofitsiant: {{ order.waiter?.name || 'Noma\'lum' }}</span>
      </div>

      <div class="ticket-divider"></div>

      <table class="ticket-table w-full text-sm mt-3 mb-2">
        <thead>
          <tr class="border-b-2 border-black">
            <th class="text-left py-1">Nomi</th>
            <th class="text-center py-1 w-12">Soni</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in order.items" :key="item.id" class="border-b border-gray-300 border-dashed">
            <td class="py-2">
              <div class="font-bold">{{ item.food?.name || item.name }}</div>
              <div v-if="item.notes" class="text-xs italic mt-0.5 text-gray-700">* {{ item.notes }}</div>
              <div v-if="item.options && item.options.length" class="text-xs text-gray-700 mt-0.5 ml-2">
                <div v-for="opt in item.options" :key="opt.id">- {{ opt.name }}</div>
              </div>
            </td>
            <td class="text-center font-bold text-lg py-2">
              x{{ item.quantity }}
            </td>
          </tr>
        </tbody>
      </table>

      <div class="ticket-divider mt-4"></div>
      
      <div class="ticket-center text-xs mt-4">
        <p>Chop etildi: {{ formatDate(new Date()) }}</p>
        <p class="mt-1 font-bold">Iltimos, tayyorlashni boshlang!</p>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
  order: {
    type: Object,
    default: null
  }
});

watch(() => props.order, (newVal) => {
  if (newVal) {
    document.body.classList.add('printing-kot');
  } else {
    document.body.classList.remove('printing-kot');
  }
}, { immediate: true });

onUnmounted(() => {
  document.body.classList.remove('printing-kot');
});

const formatDate = (dateString) => {
  if (!dateString) return '';
  const d = new Date(dateString);
  return d.toLocaleString('ru-RU', {
    day: '2-digit', month: '2-digit', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  });
};
</script>

<style>
/* By default, hide the print container entirely on screen */
#print-kot-container {
  display: none !important;
}

@media print {
  /* Hide all direct children of body except our container, ONLY when printing-kot class is present */
  body.printing-kot > *:not(#print-kot-container) {
    display: none !important;
  }
  
  /* Show only our print container */
  #print-kot-container, #print-kot-container * {
    visibility: visible !important;
  }
  
  #print-kot-container {
    display: block !important;
    position: absolute;
    left: 0;
    top: 0;
    width: 80mm;
    margin: 0;
    padding: 0;
  }

  .thermal-ticket {
    font-family: 'Courier New', Courier, monospace;
    color: #000;
  }
  .ticket-center { text-align: center; }
  .ticket-divider { border-bottom: 2px dashed #000; margin: 8px 0; }
  .ticket-table { width: 100%; border-collapse: collapse; }
  .ticket-table th, .ticket-table td { padding: 4px 0; }
}
</style>
