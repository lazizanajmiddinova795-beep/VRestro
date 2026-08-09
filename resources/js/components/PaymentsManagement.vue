<template>
  <div class="p-6 space-y-6 flex-grow flex flex-col h-full overflow-y-auto">
    <!-- Top Header & Breadcrumbs -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ settingsStore.t('payments.title_full') }}</h1>
        <p class="text-sm text-slate-500">{{ settingsStore.t('payments.subtitle_full') }}</p>
      </div>
    </div>

    <!-- 1. Financial Overview Bar (Top Mini-Widgets) -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <!-- Bugungi jami tushum -->
      <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-indigo-50 blur-xl"></div>
        <div class="flex items-center space-x-4">
          <div class="rounded-lg bg-indigo-50 p-3 text-indigo-600 border border-indigo-100">
            <DollarSign class="h-6 w-6" />
          </div>
          <div>
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ settingsStore.t('payments.today_total_revenue') }}</p>
            <h3 class="text-xl font-bold text-slate-900 mt-1">{{ formatCurrency(paymentStore.todayRevenue.total_revenue) }}</h3>
          </div>
        </div>
      </div>

      <!-- Naqd ulushi -->
      <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-emerald-50 blur-xl"></div>
        <div class="flex flex-col justify-between h-full">
          <div class="flex items-center space-x-4">
            <div class="rounded-lg bg-emerald-50 p-3 text-emerald-600 border border-emerald-100">
              <Banknote class="h-6 w-6" />
            </div>
            <div>
              <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ settingsStore.t('payments.cash_payment') }}</p>
              <h3 class="text-lg font-bold text-slate-900 mt-1">{{ formatCurrency(paymentStore.todayRevenue.cash_total) }}</h3>
            </div>
          </div>
          <div class="mt-4">
            <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
              <div class="bg-emerald-500 h-1.5 rounded-full transition-all duration-500" :style="{ width: getPercentage(paymentStore.todayRevenue.cash_total) + '%' }"></div>
            </div>
            <div class="flex justify-between items-center text-xxs text-slate-500 mt-1">
              <span>{{ settingsStore.t('payments.share') }}</span>
              <span>{{ getPercentage(paymentStore.todayRevenue.cash_total) }}%</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Karta ulushi -->
      <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-cyan-50 blur-xl"></div>
        <div class="flex flex-col justify-between h-full">
          <div class="flex items-center space-x-4">
            <div class="rounded-lg bg-cyan-50 p-3 text-cyan-600 border border-cyan-100">
              <CreditCard class="h-6 w-6" />
            </div>
            <div>
              <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ settingsStore.t('payments.card_payment') }}</p>
              <h3 class="text-lg font-bold text-slate-900 mt-1">{{ formatCurrency(paymentStore.todayRevenue.card_total) }}</h3>
            </div>
          </div>
          <div class="mt-4">
            <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
              <div class="bg-cyan-500 h-1.5 rounded-full transition-all duration-500" :style="{ width: getPercentage(paymentStore.todayRevenue.card_total) + '%' }"></div>
            </div>
            <div class="flex justify-between items-center text-xxs text-slate-500 mt-1">
              <span>{{ settingsStore.t('payments.share') }}</span>
              <span>{{ getPercentage(paymentStore.todayRevenue.card_total) }}%</span>
            </div>
          </div>
        </div>
      </div>

      <!-- QR / Payme / Click ulushi -->
      <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-purple-50 blur-xl"></div>
        <div class="flex flex-col justify-between h-full">
          <div class="flex items-center space-x-4">
            <div class="rounded-lg bg-purple-50 p-3 text-purple-600 border border-purple-100">
              <QrCode class="h-6 w-6" />
            </div>
            <div>
              <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ settingsStore.t('payments.qr_click_payme') }}</p>
              <h3 class="text-lg font-bold text-slate-900 mt-1">{{ formatCurrency(paymentStore.todayRevenue.qr_total) }}</h3>
            </div>
          </div>
          <div class="mt-4">
            <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
              <div class="bg-purple-500 h-1.5 rounded-full transition-all duration-500" :style="{ width: getPercentage(paymentStore.todayRevenue.qr_total) + '%' }"></div>
            </div>
            <div class="flex justify-between items-center text-xxs text-slate-500 mt-1">
              <span>{{ settingsStore.t('payments.share') }}</span>
              <span>{{ getPercentage(paymentStore.todayRevenue.qr_total) }}%</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Workspace (Split Grid) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start flex-grow">

      <!-- 2. Active Unpaid Orders Sidebar (Left) -->
      <div class="lg:col-span-4 flex flex-col h-full space-y-4">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-4 flex flex-col flex-grow min-h-[500px]">
          <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
            <h2 class="text-base font-semibold text-slate-900 flex items-center gap-2">
              <Clock class="w-4 h-4 text-amber-500" />
              {{ settingsStore.t('payments.active_orders') }}
            </h2>
            <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-600 border border-amber-200 text-xs font-bold">
              {{ unpaidOrders.length }} ta
            </span>
          </div>

          <!-- Loading state -->
          <div v-if="ordersLoading" class="flex flex-col items-center justify-center py-12 flex-grow">
            <div class="w-10 h-10 border-4 border-t-indigo-500 border-slate-200 rounded-full animate-spin"></div>
            <span class="text-xs text-slate-500 mt-3">{{ settingsStore.t('payments.orders_loading') }}</span>
          </div>

          <!-- Empty state -->
          <div v-else-if="unpaidOrders.length === 0" class="flex flex-col items-center justify-center py-12 flex-grow text-center">
            <CheckCircle class="w-12 h-12 text-slate-300 mb-2" />
            <span class="text-sm font-semibold text-slate-700">{{ settingsStore.t('payments.no_unpaid') }}</span>
            <span class="text-xs text-slate-500 mt-1">{{ settingsStore.t('payments.all_closed') }}</span>
          </div>

          <!-- Active list -->
          <div v-else class="space-y-2 overflow-y-auto max-h-[500px] flex-grow pr-1">
            <button
              v-for="order in unpaidOrders"
              :key="order.id"
              @click="selectOrder(order)"
              class="w-full text-left p-3 rounded-xl border transition-all duration-200 flex flex-col gap-2"
              :class="selectedOrder?.id === order.id
                ? 'bg-indigo-50 border-indigo-400 shadow-sm'
                : 'bg-slate-50 border-slate-200 hover:border-slate-300 hover:bg-slate-100'"
            >
              <div class="flex justify-between items-center">
                <span class="text-sm font-bold text-slate-900">{{ order.order_number }}</span>
                <span class="px-2 py-0.5 rounded-full text-xxs uppercase tracking-wider font-bold"
                      :class="getStatusClass(order.status)">
                  {{ getStatusText(order.status) }}
                </span>
              </div>
              <div class="flex justify-between items-center text-xs text-slate-500">
                <span class="flex items-center gap-1">
                  <User class="w-3.5 h-3.5" />
                  {{ order.waiter?.name || settingsStore.t('payments.waitstaff_fallback') }}
                </span>
                <span class="font-semibold text-slate-700">
                  {{ settingsStore.t('payments.table_label') }}: {{ order.table?.table_number || settingsStore.t('payments.takeaway') }}
                </span>
              </div>
              <div class="flex justify-between items-center border-t border-slate-200 pt-2 mt-1">
                <span class="text-xs text-slate-500">{{ settingsStore.t('payments.total_sum') }}</span>
                <span class="text-sm font-extrabold text-indigo-600">{{ formatCurrency(order.total_amount) }}</span>
              </div>
            </button>
          </div>
        </div>
      </div>

      <!-- 3. Interactive Checkout Workspace (Right) -->
      <div class="lg:col-span-8">
        <div v-if="!selectedOrder" class="rounded-2xl border border-slate-200 bg-white shadow-sm p-12 flex flex-col items-center justify-center text-center h-[500px]">
          <ShoppingBag class="w-16 h-16 text-indigo-300 border border-indigo-100 rounded-2xl p-3 mb-4" />
          <h3 class="text-lg font-bold text-slate-900">{{ settingsStore.t('payments.checkout_workspace') }}</h3>
          <p class="text-sm text-slate-500 mt-2 max-w-sm">{{ settingsStore.t('payments.checkout_hint') }}</p>
        </div>

        <div v-else class="rounded-2xl border border-slate-200 bg-white shadow-sm p-6 flex flex-col space-y-6">
          <!-- Order Title -->
          <div class="flex justify-between items-start border-b border-slate-100 pb-4">
            <div>
              <div class="flex items-center gap-2">
                <h3 class="text-lg font-bold text-slate-900">{{ selectedOrder.order_number }}</h3>
                <span class="px-2 py-0.5 rounded-full text-xxs font-bold bg-slate-100 text-slate-700">
                  {{ settingsStore.t('payments.table_label') }}: {{ selectedOrder.table?.table_number || settingsStore.t('payments.unknown') }}
                </span>
              </div>
              <p class="text-xs text-slate-500 mt-1">{{ settingsStore.t('payments.waiter_label') }}: {{ selectedOrder.waiter?.name || settingsStore.t('payments.system_fallback') }} | {{ settingsStore.t('payments.date_label') }}: {{ formatDate(selectedOrder.created_at) }}</p>
            </div>
            <button @click="selectedOrder = null" class="p-1 rounded-lg bg-slate-100 text-slate-500 hover:text-slate-900 border border-slate-200">
              <X class="w-4 h-4" />
            </button>
          </div>

          <!-- Items list -->
          <div class="space-y-3">
            <h4 class="text-xs font-semibold uppercase text-slate-500 tracking-wider">{{ settingsStore.t('payments.order_contents') }}</h4>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 max-h-[180px] overflow-y-auto space-y-2">
              <div v-for="item in selectedOrder.items" :key="item.id" class="flex justify-between items-center text-sm text-slate-700">
                <div class="flex flex-col">
                  <span>{{ item.food?.name }}</span>
                  <span class="text-xxs text-slate-500" v-if="item.notes">{{ settingsStore.t('payments.notes_label') }}: {{ item.notes }}</span>
                </div>
                <div class="flex items-center space-x-8 text-right font-medium">
                  <span class="text-slate-500 text-xs">{{ item.quantity }} x {{ formatCurrency(item.price) }}</span>
                  <span class="text-slate-900 font-semibold">{{ formatCurrency(item.quantity * item.price) }}</span>
                </div>
              </div>
            </div>
            <div class="flex justify-between items-center pt-2">
              <span class="text-sm font-semibold text-slate-500">{{ settingsStore.t('payments.bill_total') }}</span>
              <span class="text-lg font-black text-slate-900">{{ formatCurrency(selectedOrder.total_amount) }}</span>
            </div>
          </div>

          <!-- Loyalty Integration Checkbox / Customer selector -->
          <div class="border-t border-slate-100 pt-4 space-y-4">
            <h4 class="text-xs font-semibold uppercase text-slate-500 tracking-wider">{{ settingsStore.t('payments.customer_loyalty') }}</h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Customer select -->
              <div class="relative">
                <label class="block text-xs font-medium text-slate-500 mb-1.5">{{ settingsStore.t('payments.select_customer') }}</label>
                <div class="relative flex items-center">
                  <Search class="absolute left-3 w-4 h-4 text-slate-400" />
                  <input
                    type="text"
                    :placeholder="settingsStore.t('payments.customer_search_placeholder')"
                    v-model="customerSearchQuery"
                    @input="searchCustomers"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 placeholder-slate-400"
                  />
                </div>

                <!-- Dropdown -->
                <div v-if="showCustomerDropdown && filteredCustomers.length > 0" class="absolute z-50 w-full mt-1 bg-white border border-slate-200 rounded-xl max-h-48 overflow-y-auto shadow-xl p-1">
                  <button
                    v-for="c in filteredCustomers"
                    :key="c.id"
                    @click="selectCustomer(c)"
                    class="w-full text-left px-3 py-2 text-xs text-slate-700 hover:bg-indigo-600 hover:text-white rounded-lg flex justify-between items-center"
                  >
                    <span>{{ c.name }} ({{ c.phone }})</span>
                    <span class="bg-indigo-50 text-indigo-600 font-bold px-1.5 py-0.5 rounded text-xxs">{{ settingsStore.t('payments.balance_label') }} {{ formatCurrency(c.bonus_balance) }}</span>
                  </button>
                </div>
              </div>

              <!-- Selected Customer details & redeem -->
              <div class="flex flex-col justify-end">
                <div v-if="linkedCustomer" class="rounded-xl border border-indigo-100 bg-indigo-50/50 p-3 flex flex-col justify-between">
                  <div class="flex justify-between items-center text-xs">
                    <span class="font-bold text-slate-900">{{ linkedCustomer.name }}</span>
                    <button @click="unlinkCustomer" class="text-red-500 hover:underline text-xxs">{{ settingsStore.t('delete') }}</button>
                  </div>
                  <div class="flex justify-between items-center text-xxs text-slate-500 mt-1">
                    <span>{{ settingsStore.t('payments.available_bonus') }} {{ formatCurrency(linkedCustomer.bonus_balance) }}</span>
                  </div>
                  <!-- Use bonus field -->
                  <div class="mt-2 flex items-center gap-2">
                    <input
                      type="number"
                      :placeholder="settingsStore.t('payments.use_bonus_placeholder')"
                      v-model.number="bonusUsed"
                      @input="validateBonus"
                      class="w-full bg-white border border-slate-200 rounded-lg py-1.5 px-3 text-xs text-slate-900 focus:outline-none focus:border-indigo-500"
                    />
                    <button
                      @click="useMaxBonus"
                      class="px-2.5 py-1.5 rounded-lg bg-indigo-100 border border-indigo-200 text-indigo-700 hover:bg-indigo-600 hover:text-white text-xs font-semibold shrink-0 transition"
                    >
                      MAX
                    </button>
                  </div>
                </div>
                <div v-else class="rounded-xl border border-dashed border-slate-200 p-4 flex items-center justify-center text-slate-400 text-xs">
                  {{ settingsStore.t('payments.guest_checkout') }}
                </div>
              </div>
            </div>
          </div>

          <!-- Payment Method Selectors -->
          <div class="border-t border-slate-100 pt-4 space-y-3">
            <h4 class="text-xs font-semibold uppercase text-slate-500 tracking-wider">{{ settingsStore.t('payments.payment_type') }}</h4>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <button
                v-for="method in ['cash', 'card', 'qr', 'mixed']"
                :key="method"
                @click="paymentMethod = method"
                class="py-3 px-4 rounded-xl border font-bold text-xs uppercase tracking-wider flex flex-col items-center justify-center gap-2 transition"
                :class="paymentMethod === method
                  ? 'bg-indigo-50 border-indigo-400 text-indigo-700 shadow-sm'
                  : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100 hover:border-slate-300'"
              >
                <Banknote v-if="method === 'cash'" class="w-5 h-5" />
                <CreditCard v-if="method === 'card'" class="w-5 h-5" />
                <QrCode v-if="method === 'qr'" class="w-5 h-5" />
                <Layers v-if="method === 'mixed'" class="w-5 h-5" />
                <span>{{ getMethodLabel(method) }}</span>
              </button>
            </div>
          </div>

          <!-- Mixed Payment inputs -->
          <div v-if="paymentMethod === 'mixed'" class="rounded-xl border border-slate-200 bg-slate-50 p-4 space-y-4 animate-fadeIn">
            <h4 class="text-xs font-semibold uppercase text-slate-500 tracking-wider">{{ settingsStore.t('payments.mixed_payment_title') }}</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-xxs text-slate-500 mb-1">{{ settingsStore.t('payments.cash_amount') }}</label>
                <input
                  type="number"
                  v-model.number="mixedCash"
                  class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm text-slate-900 focus:outline-none focus:border-indigo-500"
                />
              </div>
              <div>
                <label class="block text-xxs text-slate-500 mb-1">{{ settingsStore.t('payments.card_amount') }}</label>
                <input
                  type="number"
                  v-model.number="mixedCard"
                  class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm text-slate-900 focus:outline-none focus:border-indigo-500"
                />
              </div>
              <div>
                <label class="block text-xxs text-slate-500 mb-1">{{ settingsStore.t('payments.qr_amount') }}</label>
                <input
                  type="number"
                  v-model.number="mixedQr"
                  class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm text-slate-900 focus:outline-none focus:border-indigo-500"
                />
              </div>
            </div>

            <div class="flex justify-between items-center text-xs text-slate-500 border-t border-slate-200 pt-2">
              <span>{{ settingsStore.t('payments.bonus_used_label') }} <strong>{{ formatCurrency(bonusUsed) }}</strong></span>
              <span>{{ settingsStore.t('payments.entered_label') }} <strong :class="mixedTotalEqualsAmount ? 'text-emerald-600' : 'text-red-500'">{{ formatCurrency(mixedCash + mixedCard + mixedQr + bonusUsed) }}</strong> / {{ formatCurrency(selectedOrder.total_amount) }}</span>
            </div>
          </div>

          <!-- Checkout summary & action button -->
          <div class="border-t border-slate-100 pt-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="text-xs text-slate-500">
              <div v-if="bonusUsed > 0">{{ settingsStore.t('payments.bonus_discount') }} <strong class="text-emerald-600">{{ formatCurrency(bonusUsed) }}</strong></div>
              <div v-if="linkedCustomer">{{ settingsStore.t('payments.cashback_will_add') }} <strong class="text-indigo-600">{{ formatCurrency((selectedOrder.total_amount - bonusUsed) * 0.05) }}</strong></div>
              <div class="mt-1 text-sm text-slate-900 font-bold">{{ settingsStore.t('payments.final_amount') }} <strong class="text-lg text-indigo-600 font-extrabold">{{ formatCurrency(selectedOrder.total_amount - bonusUsed) }}</strong></div>
            </div>

            <button
              @click="submitPayment"
              :disabled="loading || (paymentMethod === 'mixed' && !mixedTotalEqualsAmount)"
              class="px-6 py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 disabled:opacity-50 text-white font-bold text-sm tracking-wide shadow-md shadow-emerald-500/20 hover:scale-102 transition flex items-center justify-center gap-2 cursor-pointer"
            >
              <Loader2 v-if="loading" class="w-4 h-4 animate-spin" />
              <CheckCircle v-else class="w-4 h-4" />
              <span>{{ settingsStore.t('payments.complete_payment') }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- 4. Payment History -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-5">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
        <h2 class="text-base font-semibold text-slate-900 flex items-center gap-2">
          <History class="w-4 h-4 text-indigo-500" />
          {{ settingsStore.t('payments.history_title') }}
        </h2>
        <div class="flex flex-wrap items-center gap-2">
          <input
            type="date"
            v-model="historyFilters.date_from"
            @change="loadHistory"
            class="bg-slate-50 border border-slate-200 rounded-lg py-1.5 px-3 text-xs text-slate-900 focus:outline-none focus:border-indigo-500"
          />
          <input
            type="date"
            v-model="historyFilters.date_to"
            @change="loadHistory"
            class="bg-slate-50 border border-slate-200 rounded-lg py-1.5 px-3 text-xs text-slate-900 focus:outline-none focus:border-indigo-500"
          />
          <select
            v-model="historyFilters.payment_method"
            @change="loadHistory"
            class="bg-slate-50 border border-slate-200 rounded-lg py-1.5 px-3 text-xs text-slate-900 focus:outline-none focus:border-indigo-500"
          >
            <option value="">{{ settingsStore.t('payments.all_types') }}</option>
            <option value="cash">{{ settingsStore.t('payments.cash') }}</option>
            <option value="card">{{ settingsStore.t('payments.card') }}</option>
            <option value="click">Click</option>
            <option value="payme">Payme</option>
          </select>
          <button @click="loadHistory" class="p-1.5 rounded-lg bg-indigo-50 border border-indigo-200 text-indigo-600 hover:bg-indigo-600 hover:text-white transition">
            <RefreshCw class="w-3.5 h-3.5" />
          </button>
        </div>
      </div>

      <div v-if="paymentStore.loading" class="flex items-center justify-center py-10">
        <Loader2 class="w-6 h-6 text-indigo-500 animate-spin" />
      </div>

      <div v-else-if="paymentStore.error" class="text-center py-10 text-red-500 text-xs">
        {{ paymentStore.error }}
      </div>

      <div v-else-if="paymentStore.payments.length === 0" class="text-center py-10 text-slate-500 text-xs">
        {{ settingsStore.t('payments.no_history') }}
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full border-collapse text-left">
          <thead>
            <tr class="border-b border-slate-200 text-slate-500 text-xxs font-bold uppercase tracking-wider">
              <th class="px-4 py-3">{{ settingsStore.t('payments.col_date') }}</th>
              <th class="px-4 py-3">{{ settingsStore.t('payments.col_customer') }}</th>
              <th class="px-4 py-3">{{ settingsStore.t('payments.col_table_order') }}</th>
              <th class="px-4 py-3">{{ settingsStore.t('payments.col_type') }}</th>
              <th class="px-4 py-3">{{ settingsStore.t('status') }}</th>
              <th class="px-4 py-3 text-right">{{ settingsStore.t('total') }}</th>
              <th class="px-4 py-3 text-center">Amallar</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-sm">
            <tr v-for="p in paymentStore.payments" :key="p.id" class="hover:bg-slate-50 transition">
              <td class="px-4 py-3 text-xs text-slate-600">{{ formatDate(p.created_at) }}</td>
              <td class="px-4 py-3 text-xs text-slate-600">{{ p.customer?.name || settingsStore.t('payments.guest_fallback') }}</td>
              <td class="px-4 py-3 text-xs text-slate-600">
                {{ p.order?.table?.table_number ? settingsStore.t('payments.table_label') + ': ' + p.order.table.table_number : (p.order?.order_number || '—') }}
              </td>
              <td class="px-4 py-3 text-xs text-slate-600 uppercase">{{ p.payment_method }}</td>
              <td class="px-4 py-3">
                <span
                  class="px-2 py-0.5 rounded-full text-xxs font-bold uppercase"
                  :class="p.status === 'refunded' ? 'bg-red-50 text-red-600 border border-red-200' : 'bg-emerald-50 text-emerald-600 border border-emerald-200'"
                >
                  {{ p.status === 'refunded' ? settingsStore.t('payments.refunded') : settingsStore.t('payments.completed') }}
                </span>
              </td>
              <td class="px-4 py-3 text-right font-bold text-slate-900">{{ formatCurrency(p.total_amount) }}</td>
              <td class="px-4 py-3 text-center">
                <div class="flex items-center justify-center gap-2">
                  <button @click="printReceiptAction(p.id)" class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition" title="Chop etish">
                    <Printer class="w-4 h-4" />
                  </button>
                  <button @click="printReceiptAction(p.id)" class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition" title="Yuklab olish (PDF)">
                    <Download class="w-4 h-4" />
                  </button>
                  <button @click="openEditModal(p)" class="p-1.5 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-600 hover:text-white transition" title="Tahrirlash">
                    <Pencil class="w-4 h-4" />
                  </button>
                  <button @click="confirmDeletePayment(p)" class="p-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition" title="O'chirish">
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Alert Dialog -->
    <div v-if="alertMessage" class="fixed bottom-6 right-6 z-50 rounded-2xl bg-white border border-emerald-200 p-4 shadow-2xl flex items-center gap-3 animate-slideIn">
      <div class="rounded-lg bg-emerald-50 text-emerald-600 p-2 border border-emerald-200">
        <CheckCircle class="w-5 h-5" />
      </div>
      <div>
        <h4 class="text-sm font-bold text-slate-900">{{ alertTitle }}</h4>
        <p class="text-xs text-slate-500 mt-0.5">{{ alertMessage }}</p>
      </div>
    </div>

    <!-- Edit Payment Modal -->
    <div v-if="editingPayment" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl animate-scaleIn">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
          <h3 class="font-bold text-slate-900 text-lg">To'lovni Tahrirlash #{{ editingPayment.id }}</h3>
          <button @click="editingPayment = null" class="text-slate-400 hover:text-slate-600">
            <X class="w-5 h-5" />
          </button>
        </div>
        
        <div class="space-y-3">
          <div>
            <label class="block text-xs font-bold text-slate-600 mb-1">To'lov Turi</label>
            <select v-model="editForm.payment_method" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm">
              <option value="cash">Naqd (Cash)</option>
              <option value="card">Karta (Card)</option>
              <option value="qr">QR / Click / Payme</option>
              <option value="mixed">Aralash (Mixed)</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 mb-1">Jami Summa (UZS)</label>
            <input type="number" v-model.number="editForm.total_amount" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm font-mono font-bold" />
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
          <button @click="editingPayment = null" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
            Bekor qilish
          </button>
          <button @click="savePaymentEdit" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition">
            Saqlash
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useSettingsStore } from '@/stores/settings';
const settingsStore = useSettingsStore();
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import {
  DollarSign, Banknote, CreditCard, QrCode, Layers,
  Clock, CheckCircle, User, ShoppingBag, X, Search, Loader2, History, RefreshCw, Printer, Download, Pencil, Trash2
} from 'lucide-vue-next';
import { usePaymentStore } from '@/stores/payment';
import { useOrdersStore } from '@/stores/orders';
import { useCustomerStore } from '@/stores/customers';

const paymentStore = usePaymentStore();
const ordersStore = useOrdersStore();
const customerStore = useCustomerStore();
const router = useRouter();

const printReceiptAction = (paymentId) => {
  const url = router.resolve({ path: '/cashier/receipts', query: { print: paymentId } }).href;
  window.open(url, '_blank');
};

// UI States
const selectedOrder = ref(null);
const paymentMethod = ref('cash');
const loading = ref(false);

// Customer loyalty
const customerSearchQuery = ref('');
const showCustomerDropdown = ref(false);
const linkedCustomer = ref(null);
const bonusUsed = ref(0);

// Mixed payments state
const mixedCash = ref(0);
const mixedCard = ref(0);
const mixedQr = ref(0);

// Alert Notification
const alertTitle = ref('');
const alertMessage = ref('');

// Payment history
const historyFilters = ref({
  date_from: '',
  date_to: '',
  payment_method: ''
});

const loadHistory = async () => {
  const filters = {};
  if (historyFilters.value.date_from) filters.date_from = historyFilters.value.date_from;
  if (historyFilters.value.date_to) filters.date_to = historyFilters.value.date_to;
  if (historyFilters.value.payment_method) filters.payment_method = historyFilters.value.payment_method;
  await paymentStore.fetchPayments(filters);
};

// Load initial data
onMounted(async () => {
  await paymentStore.fetchTodayRevenue();
  await ordersStore.fetchOrders();
  await customerStore.fetchCustomers();
  await loadHistory();
});

// Computed properties
const ordersLoading = computed(() => ordersStore.loading);
const unpaidOrders = computed(() => {
  // Show active status orders: 'ready' or 'cooking' or 'new'
  return ordersStore.orders.filter(o => o.status !== 'delivered' && o.status !== 'cancelled');
});

const filteredCustomers = computed(() => {
  if (!customerSearchQuery.value) return [];
  const q = customerSearchQuery.value.toLowerCase();
  return customerStore.customers.filter(c =>
    c.name.toLowerCase().includes(q) || c.phone.includes(q)
  );
});

const mixedTotalEqualsAmount = computed(() => {
  if (!selectedOrder.value) return false;
  const sum = mixedCash.value + mixedCard.value + mixedQr.value + bonusUsed.value;
  return Math.abs(sum - parseFloat(selectedOrder.value.total_amount)) < 0.01;
});

// Methods
const formatCurrency = (val) => {
  if (val === undefined || val === null) return '0 UZS';
  return new Intl.NumberFormat('uz-UZ').format(val) + ' UZS';
};

const getPercentage = (val) => {
  const total = parseFloat(paymentStore.todayRevenue.total_revenue) || 1;
  return Math.round((parseFloat(val) / total) * 100);
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleString('uz-UZ', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit' });
};

const getStatusClass = (status) => {
  switch (status) {
    case 'new': return 'bg-blue-50 text-blue-600 border border-blue-200';
    case 'cooking': return 'bg-amber-50 text-amber-600 border border-amber-200';
    case 'ready': return 'bg-emerald-50 text-emerald-600 border border-emerald-200';
    default: return 'bg-slate-100 text-slate-500 border border-slate-200';
  }
};

const getStatusText = (status) => {
  switch (status) {
    case 'new': return settingsStore.t('payments.status_new');
    case 'cooking': return settingsStore.t('kitchen.cooking');
    case 'ready': return settingsStore.t('kitchen.ready');
    default: return status;
  }
};

const getMethodLabel = (method) => {
  switch (method) {
    case 'cash': return settingsStore.t('payments.method_cash');
    case 'card': return settingsStore.t('payments.method_card');
    case 'qr': return settingsStore.t('payments.method_qr');
    case 'mixed': return settingsStore.t('payments.method_mixed');
    default: return method;
  }
};

const selectOrder = (order) => {
  selectedOrder.value = order;
  paymentMethod.value = 'cash';
  linkedCustomer.value = null;
  customerSearchQuery.value = '';
  bonusUsed.value = 0;

  // Pre-fill mixed amounts
  mixedCash.value = parseFloat(order.total_amount);
  mixedCard.value = 0;
  mixedQr.value = 0;
};

// Customer Selection
const searchCustomers = () => {
  showCustomerDropdown.value = customerSearchQuery.value.length > 0;
};

const selectCustomer = (c) => {
  linkedCustomer.value = c;
  customerSearchQuery.value = c.name;
  showCustomerDropdown.value = false;
  bonusUsed.value = 0;
};

const unlinkCustomer = () => {
  linkedCustomer.value = null;
  customerSearchQuery.value = '';
  bonusUsed.value = 0;
};

const validateBonus = () => {
  if (!linkedCustomer.value) {
    bonusUsed.value = 0;
    return;
  }
  const maxAvailable = parseFloat(linkedCustomer.value.bonus_balance);
  const maxPossible = parseFloat(selectedOrder.value.total_amount);
  if (bonusUsed.value < 0) bonusUsed.value = 0;
  if (bonusUsed.value > maxAvailable) bonusUsed.value = maxAvailable;
  if (bonusUsed.value > maxPossible) bonusUsed.value = maxPossible;
};

const useMaxBonus = () => {
  if (!linkedCustomer.value) return;
  const maxAvailable = parseFloat(linkedCustomer.value.bonus_balance);
  const maxPossible = parseFloat(selectedOrder.value.total_amount);
  bonusUsed.value = Math.min(maxAvailable, maxPossible);
};

// Submit Payment
const submitPayment = async () => {
  if (!selectedOrder.value) return;
  loading.value = true;

  const payload = {
    order_id: selectedOrder.value.id,
    customer_id: linkedCustomer.value ? linkedCustomer.value.id : null,
    payment_method: paymentMethod.value,
    bonus_used: bonusUsed.value
  };

  if (paymentMethod.value === 'mixed') {
    payload.cash_amount = mixedCash.value;
    payload.card_amount = mixedCard.value;
    payload.qr_amount = mixedQr.value;
  }

  try {
    const payment = await paymentStore.processPayment(payload);

    // Refresh lists
    await ordersStore.fetchOrders();
    await loadHistory();
    selectedOrder.value = null;

    // Show Success message
    triggerAlert(settingsStore.t('payments.success_title'), `${settingsStore.t('payments.success_message')} ${formatCurrency(payment.total_amount)}`);
  } catch (err) {
    console.error(err);
    alert(err.message || settingsStore.t('payments.error_completing'));
  } finally {
    loading.value = false;
  }
};

const editingPayment = ref(null);
const editForm = ref({
  payment_method: 'cash',
  total_amount: 0
});

const openEditModal = (p) => {
  editingPayment.value = p;
  editForm.value = {
    payment_method: p.payment_method || 'cash',
    total_amount: p.total_amount || 0
  };
};

const savePaymentEdit = async () => {
  if (!editingPayment.value) return;
  try {
    await paymentStore.updatePayment(editingPayment.value.id, editForm.value);
    triggerAlert("Muvaffaqiyatli", "To'lov ma'lumotlari yangilandi.");
    editingPayment.value = null;
  } catch (err) {
    alert(err.message || "Tahrirlashda xatolik yuz berdi.");
  }
};

const confirmDeletePayment = async (p) => {
  if (confirm(`Haqiqatdan ham #${p.id} sonli to'lovni o'chirib tashlamoqchimisiz?`)) {
    try {
      await paymentStore.deletePayment(p.id);
      triggerAlert("Muvaffaqiyatli", "To'lov o'chirildi.");
    } catch (err) {
      alert(err.message || "O'chirishda xatolik yuz berdi.");
    }
  }
};

const triggerAlert = (title, message) => {
  alertTitle.value = title;
  alertMessage.value = message;
  setTimeout(() => {
    alertMessage.value = '';
  }, 4000);
};
</script>

<style scoped>
.text-xxs {
  font-size: 0.65rem;
}
.animate-fadeIn {
  animation: fadeIn 0.3s ease-out;
}
.animate-slideIn {
  animation: slideIn 0.3s ease-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-4px); }
  to { opacity: 1; transform: translateY(0); }
}
@keyframes slideIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
