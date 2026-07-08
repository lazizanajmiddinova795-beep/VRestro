<template>
  <div class="min-h-screen bg-radial from-slate-900 via-slate-950 to-black text-slate-100 flex flex-col font-sans selection:bg-indigo-500 selection:text-white">
    <!-- Header -->
    <header class="sticky top-0 z-50 backdrop-blur-md border-b border-white/5 bg-slate-950/40 px-6 py-4">
      <div class="max-w-7xl mx-auto flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <router-link to="/" class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-violet-600 to-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-500/20">
              <ChefHat class="w-6 h-6 text-white" />
            </div>
            <span class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-white via-indigo-200 to-indigo-400 tracking-wider">
              VRestro Admin
            </span>
          </router-link>
        </div>
        <div class="flex items-center space-x-4">
          <div class="flex items-center space-x-3 mr-4">
            <span class="text-xs bg-violet-500/10 text-violet-300 border border-violet-500/20 px-2.5 py-1 rounded-full font-medium">
              Administrator
            </span>
            <span class="text-sm font-medium text-slate-300">{{ authStore.user?.name }}</span>
          </div>
          <button @click="handleLogout" class="px-4 py-2 text-xs font-semibold rounded-lg bg-white/5 border border-white/10 hover:bg-red-500/10 hover:border-red-500/20 hover:text-red-400 transition duration-300">
            Chiqish
          </button>
        </div>
      </div>
    </header>

    <!-- Main Container -->
    <main class="flex-grow max-w-7xl w-full mx-auto px-6 py-8 relative">
      <!-- Decorative Background Glows -->
      <div class="absolute top-1/4 left-1/4 -translate-x-1/2 -translate-y-1/2 w-96 h-96 rounded-full bg-violet-600/5 blur-[120px] pointer-events-none"></div>
      <div class="absolute bottom-1/4 right-1/4 translate-x-1/2 translate-y-1/2 w-96 h-96 rounded-full bg-indigo-600/5 blur-[120px] pointer-events-none"></div>

      <!-- Loading / Error States -->
      <div v-if="dashboardStore.loading" class="flex flex-col items-center justify-center py-40 space-y-4">
        <Loader2 class="w-12 h-12 text-indigo-500 animate-spin" />
        <p class="text-slate-400 text-sm font-medium animate-pulse">Tahliliy ma'lumotlar yuklanmoqda...</p>
      </div>

      <div v-else-if="dashboardStore.error" class="max-w-md mx-auto my-20 p-6 rounded-2xl bg-red-500/10 border border-red-500/20 text-center space-y-4">
        <AlertTriangle class="w-12 h-12 text-red-400 mx-auto" />
        <h3 class="text-lg font-bold text-white">Xatolik yuz berdi</h3>
        <p class="text-sm text-red-300/80">{{ dashboardStore.error }}</p>
        <button @click="dashboardStore.fetchAnalytics" class="px-5 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl text-xs font-bold transition duration-200">
          Qayta urinish
        </button>
      </div>

      <div v-else-if="dashboardStore.metrics" class="space-y-8 animate-fadeIn">
        <!-- Dashboard Widgets -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          
          <!-- Widget 1: Revenue -->
          <div class="backdrop-blur-md bg-slate-900/40 border border-white/5 rounded-2xl p-6 shadow-2xl relative overflow-hidden group hover:border-violet-500/20 transition-all duration-300">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-violet-600/5 rounded-full blur-xl pointer-events-none"></div>
            <div class="flex items-center justify-between mb-4">
              <span class="text-xs font-semibold text-slate-400 tracking-wider uppercase">Bugungi Tushum</span>
              <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/25 flex items-center justify-center text-emerald-400">
                <DollarSign class="w-5 h-5" />
              </div>
            </div>
            <div class="space-y-2">
              <h3 class="text-2xl font-extrabold text-white tracking-tight">
                {{ formatCurrency(dashboardStore.metrics.widgets.revenue.value) }}
              </h3>
              <div class="flex items-center space-x-2">
                <span 
                  class="inline-flex items-center text-xs font-bold px-2 py-0.5 rounded-full border"
                  :class="dashboardStore.metrics.widgets.revenue.is_increase ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400' : 'bg-red-500/10 border-red-500/20 text-red-400'"
                >
                  <TrendingUp v-if="dashboardStore.metrics.widgets.revenue.is_increase" class="w-3.5 h-3.5 mr-1" />
                  <TrendingDown v-else class="w-3.5 h-3.5 mr-1" />
                  {{ Math.abs(dashboardStore.metrics.widgets.revenue.change_percent) }}%
                </span>
                <span class="text-xs text-slate-400">kechagiga nisbatan</span>
              </div>
            </div>
          </div>

          <!-- Widget 2: Total Orders -->
          <div class="backdrop-blur-md bg-slate-900/40 border border-white/5 rounded-2xl p-6 shadow-2xl relative overflow-hidden group hover:border-indigo-500/20 transition-all duration-300">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-indigo-600/5 rounded-full blur-xl pointer-events-none"></div>
            <div class="flex items-center justify-between mb-4">
              <span class="text-xs font-semibold text-slate-400 tracking-wider uppercase">Buyurtmalar</span>
              <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/25 flex items-center justify-center text-indigo-400">
                <ShoppingBag class="w-5 h-5" />
              </div>
            </div>
            <div class="space-y-2">
              <h3 class="text-2xl font-extrabold text-white tracking-tight">
                {{ dashboardStore.metrics.widgets.orders.total }} ta
              </h3>
              <p class="text-xs text-slate-400 flex items-center space-x-2">
                <span class="text-indigo-400 font-bold">{{ dashboardStore.metrics.widgets.orders.active }} faol</span>
                <span class="w-1 h-1 rounded-full bg-slate-600"></span>
                <span class="text-emerald-400 font-bold">{{ dashboardStore.metrics.widgets.orders.completed }} yopilgan</span>
              </p>
            </div>
          </div>

          <!-- Widget 3: Kitchen Load -->
          <div class="backdrop-blur-md bg-slate-900/40 border border-white/5 rounded-2xl p-6 shadow-2xl relative overflow-hidden group hover:border-cyan-500/20 transition-all duration-300">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-cyan-600/5 rounded-full blur-xl pointer-events-none"></div>
            <div class="flex items-center justify-between mb-4">
              <span class="text-xs font-semibold text-slate-400 tracking-wider uppercase">Oshxona Yuklamasi</span>
              <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-500/25 flex items-center justify-center text-cyan-400" :class="{'animate-pulse bg-cyan-500/20': dashboardStore.metrics.widgets.kitchen_load > 0}">
                <ChefHat class="w-5 h-5" />
              </div>
            </div>
            <div class="space-y-2">
              <h3 class="text-2xl font-extrabold text-white tracking-tight flex items-center space-x-2">
                <span>{{ dashboardStore.metrics.widgets.kitchen_load }} ta</span>
                <span v-if="dashboardStore.metrics.widgets.kitchen_load > 0" class="inline-flex w-2.5 h-2.5 rounded-full bg-cyan-400 animate-ping"></span>
              </h3>
              <p class="text-xs text-slate-400">tayyorlanayotgan buyurtmalar</p>
            </div>
          </div>

          <!-- Widget 4: Daily Expenses -->
          <div class="backdrop-blur-md bg-slate-900/40 border border-white/5 rounded-2xl p-6 shadow-2xl relative overflow-hidden group hover:border-red-500/20 transition-all duration-300">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-red-600/5 rounded-full blur-xl pointer-events-none"></div>
            <div class="flex items-center justify-between mb-4">
              <span class="text-xs font-semibold text-slate-400 tracking-wider uppercase">Bugungi Xarajatlar</span>
              <div class="w-10 h-10 rounded-xl bg-red-500/10 border border-red-500/25 flex items-center justify-center text-red-400">
                <TrendingDown class="w-5 h-5" />
              </div>
            </div>
            <div class="space-y-2">
              <h3 class="text-2xl font-extrabold text-white tracking-tight">
                {{ formatCurrency(dashboardStore.metrics.widgets.expenses) }}
              </h3>
              <p class="text-xs text-slate-400">tizimga kiritilgan xarajatlar</p>
            </div>
          </div>
          
        </div>

        <!-- Weekly Sales Chart -->
        <div class="backdrop-blur-md bg-slate-900/30 border border-white/5 rounded-3xl p-6 shadow-2xl">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h2 class="text-lg font-bold text-white">Haftalik Savdo Dinamikasi</h2>
              <p class="text-xs text-slate-400">So'nggi 7 kunlik sotuvlar va xarajatlar tahlili</p>
            </div>
          </div>
          <!-- Chart Canvas Container -->
          <div class="relative w-full h-80">
            <canvas ref="chartCanvas"></canvas>
          </div>
        </div>

        <!-- Tables Section (Top Items & Live stream) -->
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
          
          <!-- Top Selling Items (Grid 2 cols width) -->
          <div class="lg:col-span-2 backdrop-blur-md bg-slate-900/30 border border-white/5 rounded-3xl p-6 shadow-2xl flex flex-col justify-between">
            <div class="mb-4">
              <h2 class="text-lg font-bold text-white">Top 5 Taomlar</h2>
              <p class="text-xs text-slate-400">Eng ko'p sotilgan va ommabop taomlar ro'yxati</p>
            </div>
            <div class="overflow-x-auto flex-grow">
              <table class="w-full text-left text-sm">
                <thead>
                  <tr class="border-b border-white/5 text-xs text-slate-400 uppercase tracking-wider">
                    <th class="py-3 font-semibold">Raqam</th>
                    <th class="py-3 font-semibold">Nomi</th>
                    <th class="py-3 font-semibold text-center">Miqdori</th>
                    <th class="py-3 font-semibold text-right">Tushum</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-slate-300">
                  <tr v-for="(item, idx) in dashboardStore.metrics.tables.top_selling" :key="item.name" class="hover:bg-white/5 transition-colors duration-150">
                    <td class="py-3 font-medium">
                      <span class="w-6 h-6 rounded-md bg-indigo-500/10 text-indigo-400 flex items-center justify-center text-xs font-bold border border-indigo-500/20">
                        {{ idx + 1 }}
                      </span>
                    </td>
                    <td class="py-3 font-medium text-white">{{ item.name }}</td>
                    <td class="py-3 text-center">{{ item.quantity }} ta</td>
                    <td class="py-3 text-right font-bold text-slate-200">{{ formatCurrency(item.revenue) }}</td>
                  </tr>
                  <tr v-if="dashboardStore.metrics.tables.top_selling.length === 0">
                    <td colspan="4" class="py-8 text-center text-xs text-slate-500">Ma'lumotlar mavjud emas</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Live Orders Stream (Grid 3 cols width) -->
          <div class="lg:col-span-3 backdrop-blur-md bg-slate-900/30 border border-white/5 rounded-3xl p-6 shadow-2xl flex flex-col justify-between">
            <div class="mb-4">
              <h2 class="text-lg font-bold text-white">Jonli Buyurtmalar Oqimi</h2>
              <p class="text-xs text-slate-400">Tizimga kirib kelayotgan oxirgi 5 ta buyurtmalar monitoringi</p>
            </div>
            <div class="overflow-x-auto flex-grow">
              <table class="w-full text-left text-sm">
                <thead>
                  <tr class="border-b border-white/5 text-xs text-slate-400 uppercase tracking-wider">
                    <th class="py-3 font-semibold">Buyurtma ID</th>
                    <th class="py-3 font-semibold">Stol / Xizmatchi</th>
                    <th class="py-3 font-semibold text-center">Status</th>
                    <th class="py-3 font-semibold text-right">Summa</th>
                    <th class="py-3 font-semibold text-right">Vaqti</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-slate-300">
                  <tr v-for="order in dashboardStore.metrics.tables.live_orders" :key="order.id" class="hover:bg-white/5 transition-colors duration-150">
                    <td class="py-3 font-semibold text-white">#{{ order.id }}</td>
                    <td class="py-3">
                      <span class="block text-slate-200 font-medium">{{ order.table_id }}-stol</span>
                      <span class="block text-xs text-slate-500">{{ order.waiter_name }}</span>
                    </td>
                    <td class="py-3 text-center">
                      <span 
                        class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold border"
                        :class="getStatusBadgeClass(order.status)"
                      >
                        {{ getStatusLabel(order.status) }}
                      </span>
                    </td>
                    <td class="py-3 text-right font-bold text-indigo-300">{{ formatCurrency(order.total_amount) }}</td>
                    <td class="py-3 text-right text-xs text-slate-500 font-medium">{{ order.created_at }}</td>
                  </tr>
                  <tr v-if="dashboardStore.metrics.tables.live_orders.length === 0">
                    <td colspan="5" class="py-8 text-center text-xs text-slate-500">Faol buyurtmalar mavjud emas</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

        </div>

      </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-white/5 py-8 text-center text-xs text-slate-500 mt-12 bg-slate-950/20">
      <div class="max-w-7xl mx-auto px-6">
        &copy; 2026 VRestro. Barcha huquqlar himoyalangan. Boshqaruv Panel Platformasi.
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { 
  ChefHat, Loader2, AlertTriangle, DollarSign, ShoppingBag, 
  TrendingUp, TrendingDown 
} from 'lucide-vue-next';
import { useAuthStore } from '@/stores/auth';
import { useDashboardStore } from '@/stores/dashboard';
import Chart from 'chart.js/auto';

const authStore = useAuthStore();
const dashboardStore = useDashboardStore();
const router = useRouter();

// Refs
const chartCanvas = ref(null);
let chartInstance = null;

// Lifecycle
onMounted(async () => {
  await dashboardStore.fetchAnalytics();
  initChart();
});

// Watch for data updates to re-draw chart
watch(() => dashboardStore.metrics, () => {
  initChart();
}, { deep: true });

// Chart.js initialization
const initChart = () => {
  if (!chartCanvas.value || !dashboardStore.metrics) return;

  // Destroy previous instance to avoid canvas reuse error
  if (chartInstance) {
    chartInstance.destroy();
  }

  const weeklyData = dashboardStore.metrics.charts.weekly;
  
  // Format labels: get localized day name (Monday, Tuesday...)
  const labels = weeklyData.map(d => d.day_name);
  const sales = weeklyData.map(d => d.sales);
  const expenses = weeklyData.map(d => d.expenses);

  const ctx = chartCanvas.value.getContext('2d');
  
  chartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [
        {
          label: 'Sotuv (UZS)',
          data: sales,
          borderColor: 'rgb(124, 58, 237)', // Violet
          backgroundColor: 'rgba(124, 58, 237, 0.05)',
          fill: true,
          tension: 0.4,
          borderWidth: 3,
          pointBackgroundColor: 'rgb(124, 58, 237)',
          pointHoverRadius: 7,
          shadowColor: 'rgba(124, 58, 237, 0.4)',
        },
        {
          label: 'Xarajat (UZS)',
          data: expenses,
          borderColor: 'rgb(239, 68, 68)', // Red
          backgroundColor: 'rgba(239, 68, 68, 0.02)',
          fill: true,
          tension: 0.4,
          borderWidth: 2,
          borderDash: [5, 5],
          pointBackgroundColor: 'rgb(239, 68, 68)',
          pointHoverRadius: 6,
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          labels: {
            color: 'rgb(148, 163, 184)', // slate-400
            font: {
              family: 'Outfit, sans-serif',
              weight: 'bold',
            }
          }
        },
        tooltip: {
          backgroundColor: 'rgba(15, 23, 42, 0.9)',
          titleFont: { family: 'Outfit, sans-serif' },
          bodyFont: { family: 'Outfit, sans-serif' },
          padding: 12,
          borderColor: 'rgba(255, 255, 255, 0.1)',
          borderWidth: 1,
          displayColors: true,
          callbacks: {
            label: function(context) {
              let label = context.dataset.label || '';
              if (label) {
                label += ': ';
              }
              if (context.parsed.y !== null) {
                label += new Intl.NumberFormat('uz-UZ').format(context.parsed.y) + ' UZS';
              }
              return label;
            }
          }
        }
      },
      scales: {
        x: {
          grid: {
            color: 'rgba(255, 255, 255, 0.03)',
          },
          ticks: {
            color: 'rgb(148, 163, 184)',
            font: { family: 'Outfit, sans-serif' }
          }
        },
        y: {
          grid: {
            color: 'rgba(255, 255, 255, 0.04)',
          },
          ticks: {
            color: 'rgb(148, 163, 184)',
            font: { family: 'Outfit, sans-serif' },
            callback: function(value) {
              return value >= 1000 ? (value / 1000) + 'k' : value;
            }
          }
        }
      }
    }
  });
};

// Helpers
const formatCurrency = (val) => {
  return new Intl.NumberFormat('uz-UZ').format(val) + ' UZS';
};

const getStatusLabel = (status) => {
  const labels = {
    'pending': 'Kutilmoqda',
    'cooking': 'Tayyorlanmoqda',
    'ready': 'Tayyor',
    'paid': 'To\'langan',
    'cancelled': 'Bekor qilingan'
  };
  return labels[status] || status;
};

const getStatusBadgeClass = (status) => {
  const classes = {
    'pending': 'bg-amber-500/10 border-amber-500/20 text-amber-400',
    'cooking': 'bg-violet-500/10 border-violet-500/20 text-violet-400',
    'ready': 'bg-cyan-500/10 border-cyan-500/20 text-cyan-400',
    'paid': 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400',
    'cancelled': 'bg-red-500/10 border-red-500/20 text-red-400'
  };
  return classes[status] || '';
};

const handleLogout = () => {
  authStore.logout();
  router.push('/login');
};
</script>

<style scoped>
.animate-fadeIn {
  animation: fadeIn 0.4s ease-out forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
