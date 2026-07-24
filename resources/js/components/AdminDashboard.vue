<template>
  <div class="min-h-screen bg-transparent text-slate-900 dark:text-slate-100 flex flex-col font-sans selection:bg-indigo-500 selection:text-white">
    <!-- Header (If standalone) -->
    <header class="sticky top-0 z-50 backdrop-blur-md border-b border-slate-200/80 dark:border-white/5 bg-white/80 dark:bg-slate-950/40 px-6 py-4 hidden">
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
            <span class="text-xs bg-violet-500/10 text-violet-600 dark:text-violet-300 border border-violet-500/20 px-2.5 py-1 rounded-full font-bold">
              Administrator
            </span>
            <span class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ authStore.user?.name }}</span>
          </div>
          <button @click="handleLogout" class="px-4 py-2 text-xs font-bold rounded-lg bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 hover:bg-red-500/10 hover:border-red-500/20 text-slate-700 dark:text-slate-300 hover:text-red-600 dark:hover:text-red-400 transition duration-300">
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

      <!-- Sub-navigation tabs to merge Boshqaruv & Tahlillar -->
      <div class="flex items-center space-x-1 bg-white border border-slate-200 rounded-xl p-1 mb-8 shrink-0 w-max shadow-sm">
        <button 
          @click="switchTab('overview')"
          class="px-5 py-2.5 rounded-lg text-xs font-extrabold transition duration-200"
          :class="activeTab === 'overview' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
        >
          Umumiy Boshqaruv
        </button>
        <button 
          @click="switchTab('analytics')"
          class="px-5 py-2.5 rounded-lg text-xs font-extrabold transition duration-200"
          :class="activeTab === 'analytics' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
        >
          Tahlillar va Hisobotlar (BI Suite)
        </button>
      </div>

      <!-- Tab 1: Overview Dashboard -->
      <div v-if="activeTab === 'overview'">
        <!-- Loading / Error States -->
        <div v-if="dashboardStore.loading" class="flex flex-col items-center justify-center py-40 space-y-4">
          <Loader2 class="w-12 h-12 text-slate-900 animate-spin" />
          <p class="text-slate-600 text-sm font-bold animate-pulse">Tahliliy ma'lumotlar yuklanmoqda...</p>
        </div>

        <div v-else-if="dashboardStore.error" class="max-w-md mx-auto my-20 p-6 rounded-2xl bg-red-50 border border-red-200 text-center space-y-4 shadow-sm">
          <AlertTriangle class="w-12 h-12 text-red-500 mx-auto" />
          <h3 class="text-lg font-bold text-slate-900">Xatolik yuz berdi</h3>
          <p class="text-sm text-red-600 font-semibold">{{ dashboardStore.error }}</p>
          <button @click="dashboardStore.fetchAnalytics" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-bold transition duration-200">
            Qayta urinish
          </button>
        </div>

        <div v-else-if="dashboardStore.metrics" class="space-y-8 animate-fadeIn">
          <!-- Dashboard Widgets -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            
            <!-- Widget 1: Revenue -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm relative overflow-hidden group hover:border-slate-300 transition-all duration-300">
              <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-extrabold text-slate-500 tracking-wider uppercase">Bugungi Tushum</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-700 font-bold">
                  <DollarSign class="w-5 h-5" />
                </div>
              </div>
              <div class="space-y-2">
                <h3 class="text-2xl font-black text-slate-900 tracking-tight">
                  {{ formatCurrency(dashboardStore.metrics.widgets.revenue.value) }}
                </h3>
                <div class="flex items-center space-x-2">
                  <span 
                    class="inline-flex items-center text-xs font-extrabold px-2 py-0.5 rounded-full border"
                    :class="dashboardStore.metrics.widgets.revenue.is_increase ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-red-50 border-red-200 text-red-700'"
                  >
                    <TrendingUp v-if="dashboardStore.metrics.widgets.revenue.is_increase" class="w-3.5 h-3.5 mr-1" />
                    <TrendingDown v-else class="w-3.5 h-3.5 mr-1" />
                    {{ Math.abs(dashboardStore.metrics.widgets.revenue.change_percent) }}%
                  </span>
                  <span class="text-xs text-slate-500 font-bold">kechagiga nisbatan</span>
                </div>
              </div>
            </div>

            <!-- Widget 2: Total Orders -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm relative overflow-hidden group hover:border-slate-300 transition-all duration-300">
              <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-extrabold text-slate-500 tracking-wider uppercase">Buyurtmalar</span>
                <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-900 font-bold">
                  <ShoppingBag class="w-5 h-5" />
                </div>
              </div>
              <div class="space-y-2">
                <h3 class="text-2xl font-black text-slate-900 tracking-tight">
                  {{ dashboardStore.metrics.widgets.orders.total }} ta
                </h3>
                <p class="text-xs text-slate-500 font-bold flex items-center space-x-2">
                  <span class="text-slate-900 font-extrabold">{{ dashboardStore.metrics.widgets.orders.active }} faol</span>
                  <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                  <span class="text-emerald-700 font-extrabold">{{ dashboardStore.metrics.widgets.orders.completed }} yopilgan</span>
                </p>
              </div>
            </div>

            <!-- Widget 3: Kitchen Load -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm relative overflow-hidden group hover:border-slate-300 transition-all duration-300">
              <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-extrabold text-slate-500 tracking-wider uppercase">Oshxona Yuklamasi</span>
                <div class="w-10 h-10 rounded-xl bg-cyan-50 border border-cyan-200 flex items-center justify-center text-cyan-700 font-bold">
                  <ChefHat class="w-5 h-5" />
                </div>
              </div>
              <div class="space-y-2">
                <h3 class="text-2xl font-black text-slate-900 tracking-tight flex items-center space-x-2">
                  <span>{{ dashboardStore.metrics.widgets.kitchen_load }} ta</span>
                  <span v-if="dashboardStore.metrics.widgets.kitchen_load > 0" class="inline-flex w-2.5 h-2.5 rounded-full bg-cyan-500 animate-ping"></span>
                </h3>
                <p class="text-xs text-slate-500 font-bold">tayyorlanayotgan buyurtmalar</p>
              </div>
            </div>

            <!-- Widget 4: Daily Expenses -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm relative overflow-hidden group hover:border-slate-300 transition-all duration-300">
              <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-extrabold text-slate-500 tracking-wider uppercase">Bugungi Xarajatlar</span>
                <div class="w-10 h-10 rounded-xl bg-red-50 border border-red-200 flex items-center justify-center text-red-700 font-bold">
                  <TrendingDown class="w-5 h-5" />
                </div>
              </div>
              <div class="space-y-2">
                <h3 class="text-2xl font-black text-slate-900 tracking-tight">
                  {{ formatCurrency(dashboardStore.metrics.widgets.expenses) }}
                </h3>
                <p class="text-xs text-slate-500 font-bold">tizimga kiritilgan xarajatlar</p>
              </div>
            </div>
          </div>

          <!-- Weekly Sales Chart Container -->
          <div class="bg-white dark:bg-slate-900/60 border-2 border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6">
              <div>
                <h2 class="text-slate-900 dark:text-white font-black text-lg tracking-tight">Haftalik Savdo Dinamikasi</h2>
                <p class="text-slate-500 dark:text-slate-400 font-extrabold text-xs mt-0.5">So'nggi 7 kunlik sotuvlar va xarajatlar tahlili</p>
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
            <div class="lg:col-span-2 bg-white dark:bg-slate-900/60 border-2 border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 shadow-sm flex flex-col justify-between">
              <div class="mb-4">
                <h2 class="text-slate-900 dark:text-white font-black text-lg tracking-tight">Top 5 Taomlar</h2>
                <p class="text-slate-500 dark:text-slate-400 font-extrabold text-xs mt-0.5">Eng ko'p sotilgan va ommabop taomlar ro'yxati</p>
              </div>
              <div class="overflow-x-auto flex-grow">
                <table class="w-full text-left text-sm">
                  <thead>
                    <tr class="border-b border-slate-200/80 dark:border-white/5 text-slate-500 dark:text-slate-400 font-extrabold text-xs uppercase tracking-wider">
                      <th class="py-3 font-extrabold">Raqam</th>
                      <th class="py-3 font-extrabold">Nomi</th>
                      <th class="py-3 font-extrabold text-center">Miqdori</th>
                      <th class="py-3 font-extrabold text-right">Tushum</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-200/80 dark:divide-white/5">
                    <tr v-for="(item, idx) in dashboardStore.metrics.tables.top_selling" :key="item.name" class="hover:bg-slate-100/60 dark:hover:bg-white/5 transition-colors duration-150">
                      <td class="py-3 font-bold">
                        <span class="w-6 h-6 rounded-md bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-black border border-indigo-500/20">
                          {{ idx + 1 }}
                        </span>
                      </td>
                      <td class="py-3 font-black text-slate-900 dark:text-white text-sm">{{ item.name }}</td>
                      <td class="py-3 text-center font-bold text-slate-700 dark:text-slate-300 text-sm">{{ item.quantity }} ta</td>
                      <td class="py-3 text-right font-black text-indigo-600 dark:text-indigo-400 text-sm">{{ formatCurrency(item.revenue) }}</td>
                    </tr>
                    <tr v-if="dashboardStore.metrics.tables.top_selling.length === 0">
                      <td colspan="4" class="py-8 text-center text-xs text-slate-500 font-bold">Ma'lumotlar mavjud emas</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Live Orders Stream (Grid 3 cols width) -->
            <div class="lg:col-span-3 bg-white dark:bg-slate-900/60 border-2 border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 shadow-sm flex flex-col justify-between">
              <div class="mb-4">
                <h2 class="text-slate-900 dark:text-white font-black text-lg tracking-tight">Jonli Buyurtmalar Oqimi</h2>
                <p class="text-slate-500 dark:text-slate-400 font-extrabold text-xs mt-0.5">Tizimga kirib kelayotgan oxirgi 5 ta buyurtmalar monitoringi</p>
              </div>
              <div class="overflow-x-auto flex-grow">
                <table class="w-full text-left text-sm">
                  <thead>
                    <tr class="border-b border-slate-200/80 dark:border-white/5 text-slate-500 dark:text-slate-400 font-extrabold text-xs uppercase tracking-wider">
                      <th class="py-3 font-extrabold">Buyurtma ID</th>
                      <th class="py-3 font-extrabold">Stol / Xizmatchi</th>
                      <th class="py-3 font-extrabold text-center">Status</th>
                      <th class="py-3 font-extrabold text-right">Summa</th>
                      <th class="py-3 font-extrabold text-right">Vaqti</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-200/80 dark:divide-white/5">
                    <tr v-for="order in dashboardStore.metrics.tables.live_orders" :key="order.id" class="hover:bg-slate-100/60 dark:hover:bg-white/5 transition-colors duration-150">
                      <td class="py-3 font-black text-slate-900 dark:text-white text-sm">#{{ order.id }}</td>
                      <td class="py-3">
                        <span class="block text-slate-900 dark:text-slate-200 font-bold text-sm">{{ order.table_id }}-stol</span>
                        <span class="block text-xs text-slate-500 font-semibold">{{ order.waiter_name }}</span>
                      </td>
                      <td class="py-3 text-center">
                        <span 
                          class="inline-flex px-2.5 py-1 rounded-full text-xs font-extrabold border"
                          :class="getStatusBadgeClass(order.status)"
                        >
                          {{ getStatusLabel(order.status) }}
                        </span>
                      </td>
                      <td class="py-3 text-right font-black text-indigo-600 dark:text-indigo-400 text-sm">{{ formatCurrency(order.total_amount) }}</td>
                      <td class="py-3 text-right text-xs text-slate-500 font-bold">{{ order.created_at }}</td>
                    </tr>
                    <tr v-if="dashboardStore.metrics.tables.live_orders.length === 0">
                      <td colspan="5" class="py-8 text-center text-xs text-slate-500 font-bold">Faol buyurtmalar mavjud emas</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

          </div>
        </div>
      </div>

      <!-- Tab 2: Analytics & Reports Dashboard -->
      <div v-if="activeTab === 'analytics'" class="space-y-6">
        <!-- Date range & refresh tools -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h2 class="text-xl font-black tracking-tight text-slate-900 dark:text-white">Hisobotlar va Tahlillar (BI Suite)</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-extrabold mt-0.5">Restoranning moliyaviy oqimlari, menyu tahlili, ombor sarfi va xodimlar samaradorligi monitoringi.</p>
          </div>
          
          <div class="flex flex-wrap items-center gap-3">
            <div class="inline-flex rounded-xl bg-white dark:bg-slate-950/60 p-1 border-2 border-slate-200/80 dark:border-white/5 shadow-sm">
              <button 
                v-for="p in periods" 
                :key="p.value"
                @click="selectPeriod(p.value)"
                class="px-3.5 py-1.5 text-xs font-extrabold rounded-lg transition duration-200"
                :class="reportsStore.activePeriod === p.value ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
              >
                {{ p.label }}
              </button>
            </div>

            <button 
              @click="loadAll"
              :disabled="reportsStore.loading"
              class="flex items-center space-x-2 px-4 py-2 rounded-xl bg-indigo-600 text-xs font-extrabold text-white shadow-md shadow-indigo-600/30 hover:scale-[1.02] transition duration-200"
            >
              <Loader2 v-if="reportsStore.loading" class="w-4 h-4 animate-spin" />
              <RefreshCw v-else class="w-4 h-4" />
              <span>Yangilash</span>
            </button>
          </div>
        </div>

        <!-- Custom dates -->
        <div 
          v-if="reportsStore.activePeriod === 'custom'"
          class="p-4 rounded-2xl border-2 border-slate-200/80 dark:border-white/5 bg-white dark:bg-slate-950/40 flex flex-wrap items-center gap-4 animate-fadeIn shadow-sm"
        >
          <div class="flex items-center space-x-3 text-sm text-slate-700 dark:text-slate-300 font-bold">
            <span>Oraliqni tanlang:</span>
            <input 
              v-model="reportsStore.startDate"
              type="date" 
              class="bg-slate-50 dark:bg-white/5 border border-slate-300 dark:border-white/10 rounded-xl px-3 py-1.5 text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500"
            />
            <span>dan</span>
            <input 
              v-model="reportsStore.endDate"
              type="date" 
              class="bg-slate-50 dark:bg-white/5 border border-slate-300 dark:border-white/10 rounded-xl px-3 py-1.5 text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500"
            />
            <span>gacha</span>
          </div>
          <button 
            @click="loadAll"
            class="px-4 py-1.5 text-xs font-extrabold rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white transition duration-200 shadow-sm"
          >
            Qo'llash
          </button>
        </div>

        <!-- Executive Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="relative overflow-hidden rounded-3xl border-2 border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900/60 p-6 shadow-sm animate-fadeIn">
            <div class="flex items-center space-x-4">
              <div class="rounded-xl bg-violet-500/10 p-3 text-violet-600 dark:text-violet-400 border border-violet-500/20 font-bold">
                <DollarSign class="h-6 w-6" />
              </div>
              <div>
                <p class="text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Jami sotuv tushumi</p>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-1">
                  {{ formatCurrencyBI(reportsStore.salesReport?.summary?.grand_invoiced_income) }}
                </h3>
              </div>
            </div>
          </div>

          <div class="relative overflow-hidden rounded-3xl border-2 border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900/60 p-6 shadow-sm animate-fadeIn">
            <div class="flex items-center space-x-4">
              <div class="rounded-xl bg-red-500/10 p-3 text-red-600 dark:text-red-400 border border-red-500/20 font-bold">
                <Tag class="h-6 w-6" />
              </div>
              <div>
                <p class="text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Chegirmalar summasi</p>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-1">
                  - {{ formatCurrencyBI(reportsStore.salesReport?.summary?.disbursed_discounts_total) }}
                </h3>
              </div>
            </div>
          </div>

          <div class="relative overflow-hidden rounded-3xl border-2 border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900/60 p-6 shadow-sm animate-fadeIn">
            <div class="flex items-center space-x-4">
              <div class="rounded-xl bg-emerald-500/10 p-3 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 font-bold">
                <Coins class="h-6 w-6" />
              </div>
              <div>
                <p class="text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Keshbek bonus sarfi</p>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-1">
                  {{ formatCurrencyBI(reportsStore.salesReport?.summary?.cashback_bonuses_used) }}
                </h3>
              </div>
            </div>
          </div>
        </div>

        <!-- Charts -->
        <div class="rounded-3xl border-2 border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900/60 p-6 shadow-sm space-y-4">
          <div class="flex justify-between items-center">
            <h2 class="text-lg font-black text-slate-900 dark:text-white">Sotuv dinamikasi (Sotuvlar progression)</h2>
            <span class="text-xs text-slate-500 dark:text-slate-400 font-extrabold">UZS hisobida</span>
          </div>
          <div class="relative h-80 w-full">
            <canvas ref="salesChartRef"></canvas>
          </div>
        </div>

        <!-- Food Popularity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div class="rounded-3xl border-2 border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900/60 p-6 shadow-sm space-y-4">
            <h2 class="text-lg font-black text-slate-900 dark:text-white flex items-center space-x-2">
              <TrendingUp class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
              <span>Eng ko'p sotilgan taomlar</span>
            </h2>
            <div v-if="!reportsStore.menuReport?.top_selling?.length" class="text-sm text-slate-500 italic py-6 text-center font-bold">
              Ma'lumot mavjud emas
            </div>
            <div v-else class="space-y-4.5">
              <div v-for="(food, idx) in reportsStore.menuReport.top_selling" :key="food.id" class="space-y-1.5">
                <div class="flex justify-between text-sm">
                  <span class="font-black text-slate-900 dark:text-slate-200">{{ idx + 1 }}. {{ food.name }}</span>
                  <span class="text-indigo-600 dark:text-indigo-400 font-black">{{ food.units_sold }} dona ({{ formatCurrencyBI(food.revenue) }})</span>
                </div>
                <div class="w-full bg-slate-100 dark:bg-white/5 rounded-full h-2 overflow-hidden">
                  <div 
                    class="bg-gradient-to-r from-violet-600 to-indigo-600 h-2 rounded-full transition-all duration-700" 
                    :style="{ width: getPercentageOfMax(food.units_sold, reportsStore.menuReport.top_selling[0].units_sold) + '%' }"
                  ></div>
                </div>
              </div>
            </div>
          </div>

          <div class="rounded-3xl border-2 border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900/60 p-6 shadow-sm space-y-4">
            <h2 class="text-lg font-black text-slate-900 dark:text-white flex items-center space-x-2">
              <TrendingDown class="w-5 h-5 text-red-600 dark:text-red-400" />
              <span>Eng kam sotilgan taomlar (Muzlagan)</span>
            </h2>
            <div v-if="!reportsStore.menuReport?.least_selling?.length" class="text-sm text-slate-500 italic py-6 text-center font-bold">
              Ma'lumot mavjud emas
            </div>
            <div v-else class="space-y-3">
              <div 
                v-for="food in reportsStore.menuReport.least_selling" 
                :key="food.id"
                class="p-3.5 rounded-xl border border-slate-200 dark:border-white/5 bg-slate-50/60 dark:bg-white/2 flex justify-between items-center"
              >
                <div>
                  <span class="font-black text-slate-900 dark:text-white block text-sm">{{ food.name }}</span>
                  <span class="text-xs text-slate-500 font-bold">Sotilgan: {{ food.units_sold }} dona</span>
                </div>
                <div class="text-right">
                  <span class="text-xs text-red-600 dark:text-red-400 font-black block">{{ formatCurrencyBI(food.revenue) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Depletions & Staff KPIs -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div class="lg:col-span-2 rounded-3xl border-2 border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900/60 p-6 shadow-sm space-y-4">
            <h2 class="text-lg font-black text-slate-900 dark:text-white flex items-center space-x-2">
              <Package class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
              <span>Sarflangan mahsulotlar (Ombor tahlili)</span>
            </h2>
            
            <div class="overflow-x-auto">
              <table class="w-full text-left text-sm border-collapse">
                <thead>
                  <tr class="border-b border-slate-200/80 dark:border-white/5 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 font-extrabold">
                    <th class="py-2.5">Mahsulot</th>
                    <th class="py-2.5 text-right">Retsept bo'yicha</th>
                    <th class="py-2.5 text-right">Manual chiqim</th>
                    <th class="py-2.5 text-right">Jami sarf</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/80 dark:divide-white/5">
                  <tr v-for="item in reportsStore.inventoryReport?.depletions" :key="item.id" class="hover:bg-slate-100/60 dark:hover:bg-white/2 transition-colors duration-150">
                    <td class="py-2.5 font-black text-slate-900 dark:text-white">{{ item.name }} ({{ item.unit }})</td>
                    <td class="py-2.5 text-right font-bold text-slate-700 dark:text-slate-300">{{ item.recipe_consumed.toFixed(2) }}</td>
                    <td class="py-2.5 text-right font-bold text-slate-700 dark:text-slate-300">{{ item.manual_consumed.toFixed(2) }}</td>
                    <td class="py-2.5 text-right text-indigo-300 font-bold">{{ item.total_consumed.toFixed(2) }}</td>
                  </tr>
                  <tr v-if="!reportsStore.inventoryReport?.depletions?.length">
                    <td colspan="4" class="py-6 text-center text-xs text-slate-500 italic">Hisob-kitob mavjud emas</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="rounded-2xl border border-white/5 bg-slate-950/40 p-6 backdrop-blur-xl shadow-xl space-y-5">
            <h2 class="text-lg font-bold text-white flex items-center space-x-2">
              <Users class="w-5 h-5 text-violet-400" />
              <span>Xodimlar KPIs</span>
            </h2>

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
                  <span class="text-xs text-indigo-300 font-bold">{{ formatCurrencyBI(waiter.total_revenue_generated) }}</span>
                </div>
              </div>
            </div>

            <div class="space-y-3 border-t border-white/5 pt-4">
              <h3 class="text-xs text-slate-400 uppercase tracking-wider font-bold">Faol Oshpazlar</h3>
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
  TrendingUp, TrendingDown, RefreshCw, Tag, Coins, Package, Users
} from 'lucide-vue-next';
import { useAuthStore } from '@/stores/auth';
import { useDashboardStore } from '@/stores/dashboard';
import { useReportsStore } from '@/stores/reports';
import Chart from 'chart.js/auto';

const authStore = useAuthStore();
const dashboardStore = useDashboardStore();
const reportsStore = useReportsStore();
const router = useRouter();

// Active tab tab definition
const activeTab = ref('overview');

const switchTab = (tab) => {
  activeTab.value = tab;
  if (tab === 'overview') {
    setTimeout(initChart, 50);
  } else {
    setTimeout(renderSalesChart, 50);
  }
};

// Overview Chart refs
const chartCanvas = ref(null);
let chartInstance = null;

// BI Suite reports settings
const periods = [
  { label: 'Bugun', value: 'today' },
  { label: 'Shu hafta', value: 'week' },
  { label: 'Shu oy', value: 'month' },
  { label: 'Bu yil', value: 'year' },
  { label: 'Custom', value: 'custom' }
];

const salesChartRef = ref(null);
let salesChartInstance = null;

const selectPeriod = (period) => {
  reportsStore.setPeriod(period);
  loadAll();
};

const loadAll = async () => {
  await reportsStore.fetchAllReports();
  renderSalesChart();
};

// Lifecycle
onMounted(async () => {
  await Promise.all([
    dashboardStore.fetchAnalytics(),
    reportsStore.fetchAllReports()
  ]);
  initChart();
  renderSalesChart();
});

// Watch for data updates to re-draw chart
watch(() => dashboardStore.metrics, () => {
  initChart();
}, { deep: true });

// Chart.js initialization for Overview
const initChart = () => {
  if (!chartCanvas.value || !dashboardStore.metrics) return;

  if (chartInstance) {
    chartInstance.destroy();
  }

  const weeklyData = dashboardStore.metrics.charts.weekly;
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
            color: document.documentElement.classList.contains('light-theme') ? '#334155' : 'rgb(148, 163, 184)',
            font: { family: 'Outfit, sans-serif', weight: 'bold' }
          }
        },
        tooltip: {
          backgroundColor: document.documentElement.classList.contains('light-theme') ? 'rgba(255, 255, 255, 0.95)' : 'rgba(15, 23, 42, 0.9)',
          titleColor: document.documentElement.classList.contains('light-theme') ? '#0f172a' : '#ffffff',
          bodyColor: document.documentElement.classList.contains('light-theme') ? '#334155' : '#e2e8f0',
          titleFont: { family: 'Outfit, sans-serif', weight: 'bold' },
          bodyFont: { family: 'Outfit, sans-serif' },
          padding: 12,
          borderColor: document.documentElement.classList.contains('light-theme') ? 'rgba(203, 213, 225, 0.8)' : 'rgba(255, 255, 255, 0.1)',
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
          grid: { color: document.documentElement.classList.contains('light-theme') ? 'rgba(203, 213, 225, 0.6)' : 'rgba(255, 255, 255, 0.03)' },
          ticks: { color: document.documentElement.classList.contains('light-theme') ? '#475569' : 'rgb(148, 163, 184)', font: { family: 'Outfit, sans-serif', weight: 'bold' } }
        },
        y: {
          grid: { color: document.documentElement.classList.contains('light-theme') ? 'rgba(203, 213, 225, 0.6)' : 'rgba(255, 255, 255, 0.04)' },
          ticks: {
            color: document.documentElement.classList.contains('light-theme') ? '#475569' : 'rgb(148, 163, 184)',
            font: { family: 'Outfit, sans-serif', weight: 'bold' },
            callback: function(value) {
              return value >= 1000 ? (value / 1000) + 'k' : value;
            }
          }
        }
      }
    }
  });
};

// Chart.js initialization for BI Suite reports
const renderSalesChart = () => {
  if (!salesChartRef.value || !reportsStore.salesReport) return;
  
  if (salesChartInstance) {
    salesChartInstance.destroy();
  }

  const chartData = reportsStore.salesReport.daily_charts || [];
  const labels = chartData.map(d => d.date);
  const totals = chartData.map(d => parseFloat(d.grand_total));
  const discounts = chartData.map(d => parseFloat(d.discount_total));

  salesChartInstance = new Chart(salesChartRef.value, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [
        {
          label: 'Jami Sotuv (UZS)',
          data: totals,
          borderColor: 'rgb(99, 102, 241)', // Indigo-505
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
            color: document.documentElement.classList.contains('light-theme') ? '#334155' : 'rgb(148, 163, 184)',
            font: { family: 'Outfit, sans-serif', weight: 'bold' }
          }
        }
      },
      scales: {
        x: {
          grid: { color: document.documentElement.classList.contains('light-theme') ? 'rgba(203, 213, 225, 0.6)' : 'rgba(255, 255, 255, 0.03)' },
          ticks: { color: document.documentElement.classList.contains('light-theme') ? '#475569' : 'rgb(148, 163, 184)', font: { family: 'Outfit, sans-serif', weight: 'bold' } }
        },
        y: {
          grid: { color: document.documentElement.classList.contains('light-theme') ? 'rgba(203, 213, 225, 0.6)' : 'rgba(255, 255, 255, 0.04)' },
          ticks: {
            color: document.documentElement.classList.contains('light-theme') ? '#475569' : 'rgb(148, 163, 184)',
            font: { family: 'Outfit, sans-serif', weight: 'bold' },
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
const getPercentageOfMax = (value, max) => {
  if (!max) return 0;
  return ((value / max) * 100).toFixed(0);
};

const formatCurrency = (val) => {
  return new Intl.NumberFormat('uz-UZ').format(val) + ' UZS';
};

const formatCurrencyBI = (value) => {
  return new Intl.NumberFormat('uz-UZ', { style: 'currency', currency: 'UZS', maximumFractionDigits: 0 }).format(value || 0);
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
