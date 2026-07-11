<template>
  <div class="p-6 space-y-6 flex-grow flex flex-col h-full overflow-y-auto">
    <!-- Top Header & Breadcrumbs -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-white">Hisobotlar va Tahlillar (BI Suite)</h1>
        <p class="text-sm text-slate-400">Restoranning moliyaviy oqimlari, menyu tahlili, ombor sarfi va xodimlar samaradorligi monitoringi.</p>
      </div>
      
      <!-- Action Filters -->
      <div class="flex flex-wrap items-center gap-3">
        <!-- Pre-set periods -->
        <div class="inline-flex rounded-xl bg-slate-950/60 p-1 border border-white/5">
          <button 
            v-for="p in periods" 
            :key="p.value"
            @click="selectPeriod(p.value)"
            class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition duration-200"
            :class="reportsStore.activePeriod === p.value ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white'"
          >
            {{ p.label }}
          </button>
        </div>

        <button 
          @click="loadAll"
          :disabled="reportsStore.loading"
          class="flex items-center space-x-2 px-4 py-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 text-xs font-semibold text-white shadow-lg shadow-indigo-600/30 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200"
        >
          <Loader2 v-if="reportsStore.loading" class="w-4 h-4 animate-spin" />
          <RefreshCw v-else class="w-4 h-4" />
          <span>Yangilash</span>
        </button>
      </div>
    </div>

    <!-- Date Range Picker Component (Visible on custom period) -->
    <div 
      v-if="reportsStore.activePeriod === 'custom'"
      class="p-4 rounded-2xl border border-white/5 bg-slate-950/40 backdrop-blur-xl flex flex-wrap items-center gap-4 animate-fadeIn"
    >
      <div class="flex items-center space-x-3 text-sm text-slate-300">
        <span>Oraliqni tanlang:</span>
        <input 
          v-model="reportsStore.startDate"
          type="date" 
          class="bg-white/5 border border-white/10 rounded-xl px-3 py-1.5 text-white focus:outline-none focus:border-indigo-500/50"
        />
        <span>dan</span>
        <input 
          v-model="reportsStore.endDate"
          type="date" 
          class="bg-white/5 border border-white/10 rounded-xl px-3 py-1.5 text-white focus:outline-none focus:border-indigo-500/50"
        />
        <span>gacha</span>
      </div>
      <button 
        @click="loadAll"
        class="px-4 py-1.5 text-xs font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white transition duration-200"
      >
        Qo'llash
      </button>
    </div>

    <!-- 1. Executive Summary Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="relative overflow-hidden rounded-2xl border border-white/5 bg-slate-950/40 p-6 backdrop-blur-xl shadow-xl">
        <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-violet-500/10 blur-xl"></div>
        <div class="flex items-center space-x-4">
          <div class="rounded-lg bg-violet-500/10 p-3 text-violet-400 border border-violet-500/20">
            <DollarSign class="h-6 w-6" />
          </div>
          <div>
            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Jami sotuv tushumi</p>
            <h3 class="text-2xl font-bold text-white mt-1">
              {{ formatCurrency(reportsStore.salesReport?.summary?.grand_invoiced_income) }}
            </h3>
          </div>
        </div>
      </div>

      <div class="relative overflow-hidden rounded-2xl border border-white/5 bg-slate-950/40 p-6 backdrop-blur-xl shadow-xl">
        <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-red-500/10 blur-xl"></div>
        <div class="flex items-center space-x-4">
          <div class="rounded-lg bg-red-500/10 p-3 text-red-400 border border-red-500/20">
            <Tag class="h-6 w-6" />
          </div>
          <div>
            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Chegirmalar summasi</p>
            <h3 class="text-2xl font-bold text-white mt-1">
              - {{ formatCurrency(reportsStore.salesReport?.summary?.disbursed_discounts_total) }}
            </h3>
          </div>
        </div>
      </div>

      <div class="relative overflow-hidden rounded-2xl border border-white/5 bg-slate-950/40 p-6 backdrop-blur-xl shadow-xl">
        <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-emerald-500/10 blur-xl"></div>
        <div class="flex items-center space-x-4">
          <div class="rounded-lg bg-emerald-500/10 p-3 text-emerald-400 border border-emerald-500/20">
            <Coins class="h-6 w-6" />
          </div>
          <div>
            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Keshbek bonus sarfi</p>
            <h3 class="text-2xl font-bold text-white mt-1">
              {{ formatCurrency(reportsStore.salesReport?.summary?.cashback_bonuses_used) }}
            </h3>
          </div>
        </div>
      </div>
    </div>

    <!-- 2. Charts & Sales Progression -->
    <div class="grid grid-cols-1 gap-6">
      <div class="rounded-2xl border border-white/5 bg-slate-950/40 p-6 backdrop-blur-xl shadow-xl space-y-4">
        <div class="flex justify-between items-center">
          <h2 class="text-lg font-bold text-white">Sotuv dinamikasi (Sotuvlar progression)</h2>
          <span class="text-xs text-slate-400 font-medium">UZS hisobida</span>
        </div>
        <div class="relative h-80 w-full">
          <canvas ref="salesChartRef"></canvas>
        </div>
      </div>
    </div>

    <!-- 3. Food Popularity split dashboard -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Top selling -->
      <div class="rounded-2xl border border-white/5 bg-slate-950/40 p-6 backdrop-blur-xl shadow-xl space-y-4">
        <h2 class="text-lg font-bold text-white flex items-center space-x-2">
          <TrendingUp class="w-5 h-5 text-emerald-400" />
          <span>Eng ko'p sotilgan taomlar</span>
        </h2>
        <div v-if="!reportsStore.menuReport?.top_selling?.length" class="text-sm text-slate-500 italic py-6 text-center">
          Ma'lumot mavjud emas
        </div>
        <div v-else class="space-y-4.5">
          <div v-for="(food, idx) in reportsStore.menuReport.top_selling" :key="food.id" class="space-y-1.5">
            <div class="flex justify-between text-sm">
              <span class="font-medium text-slate-200">{{ idx + 1 }}. {{ food.name }}</span>
              <span class="text-indigo-400 font-bold">{{ food.units_sold }} dona ({{ formatCurrency(food.revenue) }})</span>
            </div>
            <!-- custom progress bar -->
            <div class="w-full bg-white/5 rounded-full h-2 overflow-hidden">
              <div 
                class="bg-gradient-to-r from-violet-600 to-indigo-600 h-2 rounded-full transition-all duration-700" 
                :style="{ width: getPercentageOfMax(food.units_sold, reportsStore.menuReport.top_selling[0].units_sold) + '%' }"
              ></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Least selling -->
      <div class="rounded-2xl border border-white/5 bg-slate-950/40 p-6 backdrop-blur-xl shadow-xl space-y-4">
        <h2 class="text-lg font-bold text-white flex items-center space-x-2">
          <TrendingDown class="w-5 h-5 text-red-400" />
          <span>Eng kam sotilgan taomlar (Muzlagan)</span>
        </h2>
        <div v-if="!reportsStore.menuReport?.least_selling?.length" class="text-sm text-slate-500 italic py-6 text-center">
          Ma'lumot mavjud emas
        </div>
        <div v-else class="space-y-3">
          <div 
            v-for="food in reportsStore.menuReport.least_selling" 
            :key="food.id"
            class="p-3.5 rounded-xl border border-white/5 bg-white/2 flex justify-between items-center"
          >
            <div>
              <span class="font-semibold text-white block">{{ food.name }}</span>
              <span class="text-xxs text-slate-500">Sotilgan: {{ food.units_sold }} dona</span>
            </div>
            <div class="text-right">
              <span class="text-xs text-red-400 font-bold block">{{ formatCurrency(food.revenue) }}</span>
              <span class="text-xxs text-amber-400 underline cursor-pointer hover:text-white">Aksiya qo'llash</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 4. Inventory Consumption & Staff Performance -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Ombor Sarfi -->
      <div class="lg:col-span-2 rounded-2xl border border-white/5 bg-slate-950/40 p-6 backdrop-blur-xl shadow-xl space-y-4">
        <h2 class="text-lg font-bold text-white flex items-center space-x-2">
          <Package class="w-5 h-5 text-indigo-400" />
          <span>Sarflangan mahsulotlar (Ombor tahlili)</span>
        </h2>
        
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm border-collapse">
            <thead>
              <tr class="border-b border-white/5 text-xxs uppercase tracking-wider text-slate-400 font-semibold">
                <th class="py-2.5">Mahsulot</th>
                <th class="py-2.5 text-right">Retsept bo'yicha</th>
                <th class="py-2.5 text-right">Manual chiqim</th>
                <th class="py-2.5 text-right">Jami sarf</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5 text-slate-300">
              <tr v-for="item in reportsStore.inventoryReport?.depletions" :key="item.id" class="hover:bg-white/2 transition-colors duration-150">
                <td class="py-2.5 font-semibold text-white">{{ item.name }} ({{ item.unit }})</td>
                <td class="py-2.5 text-right">{{ item.recipe_consumed.toFixed(2) }}</td>
                <td class="py-2.5 text-right">{{ item.manual_consumed.toFixed(2) }}</td>
                <td class="py-2.5 text-right text-indigo-300 font-bold">{{ item.total_consumed.toFixed(2) }}</td>
              </tr>
              <tr v-if="!reportsStore.inventoryReport?.depletions?.length">
                <td colspan="4" class="py-6 text-center text-xs text-slate-500 italic">Hisob-kitob mavjud emas</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Xodimlar KPIs -->
      <div class="rounded-2xl border border-white/5 bg-slate-950/40 p-6 backdrop-blur-xl shadow-xl space-y-5">
        <h2 class="text-lg font-bold text-white flex items-center space-x-2">
          <Users class="w-5 h-5 text-violet-400" />
          <span>Xodimlar KPIs</span>
        </h2>

        <!-- Waiters -->
        <div class="space-y-3">
          <h3 class="text-xs text-slate-400 uppercase tracking-wider font-bold">Buyurtma olgan ofitsiantlar</h3>
          <div v-if="!reportsStore.staffReport?.waiters?.length" class="text-xs text-slate-500 italic">
            Ofitsiantlar faolligi topilmadi
          </div>
          <div v-else class="space-y-2">
            <div 
              v-for="waiter in reportsStore.staffReport.waiters" 
              :key="waiter.id" 
              class="flex justify-between items-center p-2 bg-white/5 rounded-lg border border-white/5"
            >
              <div>
                <span class="text-xs font-semibold text-white block">{{ waiter.name }}</span>
                <span class="text-xxs text-slate-400">{{ waiter.total_orders_taken }} ta buyurtma</span>
              </div>
              <span class="text-xs text-indigo-300 font-bold">{{ formatCurrency(waiter.total_revenue_generated) }}</span>
            </div>
          </div>
        </div>

        <!-- Chefs -->
        <div class="space-y-3 border-t border-white/5 pt-4">
          <h3 class="text-xs text-slate-400 uppercase tracking-wider font-bold">Faol Oshpazlar (Kitchen Counters)</h3>
          <div v-if="!reportsStore.staffReport?.chefs?.length" class="text-xs text-slate-500 italic">
            Oshpazlar faolligi topilmadi
          </div>
          <div v-else class="space-y-2">
            <div 
              v-for="chef in reportsStore.staffReport.chefs" 
              :key="chef.id" 
              class="flex justify-between items-center p-2 bg-white/5 rounded-lg border border-white/5"
            >
              <span class="text-xs font-semibold text-white">{{ chef.name }}</span>
              <span class="text-xs text-emerald-400 font-bold">{{ chef.total_dishes_prepared }} ta taom</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { 
  Loader2, RefreshCw, DollarSign, Tag, Coins, TrendingUp, TrendingDown, 
  Package, Users 
} from 'lucide-vue-next';
import { useReportsStore } from '@/stores/reports';
import Chart from 'chart.js/auto';

const reportsStore = useReportsStore();

const periods = [
  { label: 'Bugun', value: 'today' },
  { label: 'Shu hafta', value: 'week' },
  { label: 'Shu oy', value: 'month' },
  { label: 'Bu yil', value: 'year' },
  { label: 'Custom', value: 'custom' }
];

const salesChartRef = ref(null);
let chartInstance = null;

const selectPeriod = (period) => {
  reportsStore.setPeriod(period);
  loadAll();
};

const loadAll = async () => {
  await reportsStore.fetchAllReports();
  renderSalesChart();
};

const renderSalesChart = () => {
  if (!salesChartRef.value) return;
  if (chartInstance) {
    chartInstance.destroy();
  }

  const chartData = reportsStore.salesReport?.daily_charts || [];
  const labels = chartData.map(d => d.date);
  const totals = chartData.map(d => parseFloat(d.grand_total));
  const discounts = chartData.map(d => parseFloat(d.discount_total));

  chartInstance = new Chart(salesChartRef.value, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [
        {
          label: 'Jami Sotuv (UZS)',
          data: totals,
          borderColor: 'rgb(99, 102, 241)', // Indigo-500
          backgroundColor: 'rgba(99, 102, 241, 0.05)',
          fill: true,
          tension: 0.4,
          borderWidth: 3,
          pointBackgroundColor: 'rgb(99, 102, 241)',
          pointHoverRadius: 7
        },
        {
          label: 'Chegirmalar (UZS)',
          data: discounts,
          borderColor: 'rgb(239, 68, 68)', // Red-500
          backgroundColor: 'rgba(239, 68, 68, 0.02)',
          fill: true,
          tension: 0.4,
          borderWidth: 2,
          borderDash: [5, 5],
          pointBackgroundColor: 'rgb(239, 68, 68)',
          pointHoverRadius: 5
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          labels: {
            color: 'rgb(148, 163, 184)',
            font: { family: 'Outfit, sans-serif', weight: 'bold' }
          }
        }
      },
      scales: {
        x: {
          grid: { color: 'rgba(255, 255, 255, 0.03)' },
          ticks: { color: 'rgb(148, 163, 184)', font: { family: 'Outfit, sans-serif' } }
        },
        y: {
          grid: { color: 'rgba(255, 255, 255, 0.04)' },
          ticks: {
            color: 'rgb(148, 163, 184)',
            font: { family: 'Outfit, sans-serif' },
            callback: function(value) {
              return value >= 1000 ? (value / 1000) + 'k UZS' : value + ' UZS';
            }
          }
        }
      }
    }
  });
};

const getPercentageOfMax = (value, max) => {
  if (!max) return 0;
  return ((value / max) * 100).toFixed(0);
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat('uz-UZ', { style: 'currency', currency: 'UZS', maximumFractionDigits: 0 }).format(value || 0);
};

onMounted(() => {
  loadAll();
});
</script>

<style scoped>
.text-xxs {
  font-size: 0.65rem;
}
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
