<template>
  <div class="h-full flex flex-col lg:flex-row gap-6 overflow-hidden bg-[#F1F5F9] p-1">
    
    <!-- LEFT COLUMN: Categories and Foods Grid (60% width) -->
    <div class="w-full lg:w-3/5 flex flex-col h-full overflow-hidden bg-white border border-slate-200 rounded-3xl p-6 shadow-md">
      <!-- Menu Type Selector (Oshxona / Bar) -->
      <div class="flex items-center space-x-2 mb-4 bg-slate-100 p-1.5 rounded-2xl shrink-0 w-fit">
        <button 
          @click="setMenuType('kitchen')"
          class="px-5 py-2 rounded-xl text-xs font-bold transition duration-200"
          :class="selectedMenuType === 'kitchen' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
        >
          🍲 Taomlar
        </button>
        <button 
          @click="setMenuType('bar')"
          class="px-5 py-2 rounded-xl text-xs font-bold transition duration-200"
          :class="selectedMenuType === 'bar' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
        >
          🍷 Bar
        </button>
      </div>

      <!-- Category Tabs -->
      <div class="flex items-center space-x-2 overflow-x-auto pb-3 shrink-0 scrollbar-thin">
        <button 
          @click="selectedCategory = 'all'"
          class="px-4 py-2 rounded-xl text-xs font-bold transition duration-200 shrink-0 border"
          :class="selectedCategory === 'all' ? 'bg-indigo-600 border-indigo-650 text-white font-black shadow-sm' : 'bg-slate-100 border border-slate-300 text-slate-700 hover:bg-slate-200'"
        >
          {{ cashierStore.t('barchasi') }}
        </button>
        <button 
          v-for="cat in availableCategories" 
          :key="cat.id"
          @click="selectedCategory = cat.id"
          class="px-4 py-2 rounded-xl text-xs font-bold transition duration-200 shrink-0 border"
          :class="selectedCategory === cat.id ? 'bg-indigo-600 border-indigo-650 text-white font-black shadow-sm' : 'bg-slate-100 border border-slate-300 text-slate-700 hover:bg-slate-200'"
        >
          {{ cat.name }}
        </button>
      </div>

      <!-- Foods Grid -->
      <div class="flex-grow overflow-y-auto mt-4 pr-1">
        <div 
          v-if="filteredFoods.length > 0"
          class="grid grid-cols-2 sm:grid-cols-3 gap-4"
        >
          <button
            v-for="food in filteredFoods"
            :key="food.id"
            @click="triggerAddFlow(food)"
            :disabled="!food.is_available"
            class="group text-left border rounded-3xl p-4 flex flex-col justify-between min-h-[140px] relative transition-all duration-300 hover:scale-[1.02] overflow-hidden"
            :class="food.is_available 
              ? 'bg-slate-50 border-slate-200 hover:border-indigo-500/40 shadow-sm' 
              : 'bg-slate-100/50 border-slate-200 opacity-50 cursor-not-allowed'"
          >
            <!-- Background gradient hover -->
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

            <div class="space-y-2 relative z-10">
              <!-- Category Badge -->
              <span class="text-[9px] uppercase font-extrabold tracking-widest text-indigo-600">
                {{ food.category?.name || 'Menyu' }}
              </span>
              <h3 class="text-sm font-black text-slate-900 tracking-wide truncate max-w-full">
                {{ food.name }}
              </h3>
            </div>

            <!-- Price & Availability status -->
            <div class="mt-4 flex items-center justify-between w-full relative z-10">
              <span class="text-xs font-mono font-black text-indigo-600">
                {{ formatCurrency(food.price) }}
              </span>
              <span 
                v-if="!food.is_available" 
                class="text-[9px] font-bold text-rose-700 bg-rose-100 border border-rose-300 px-2 py-0.5 rounded-full"
              >
                Mavjud emas
              </span>
              <span 
                v-else
                class="w-7 h-7 rounded-lg bg-indigo-50 border border-indigo-200 flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition duration-200"
              >
                <Plus class="w-4 h-4" />
              </span>
            </div>
          </button>
        </div>

        <div v-else-if="loading" class="grid grid-cols-2 sm:grid-cols-3 gap-4">
          <div v-for="n in 9" :key="n" class="bg-slate-100 border border-slate-200 rounded-3xl p-5 min-h-[140px] animate-pulse"></div>
        </div>

        <div v-else class="text-center py-16 bg-slate-50 border border-slate-200 rounded-3xl p-8">
          <p class="text-slate-700 font-bold text-sm">Ushbu turkumda taomlar topilmadi.</p>
        </div>
      </div>
    </div>

    <!-- RIGHT COLUMN: Shopping Cart Drawer (40% width) -->
    <!-- Receipt Wrapper (Savatcha qismi): bg-white border-l border-slate-200 p-6 -->
    <div class="w-full lg:w-2/5 flex flex-col h-full overflow-hidden bg-white border-l border-slate-200 p-6 shadow-md justify-between">
      <div class="flex-grow flex flex-col overflow-hidden">
        <h3 class="text-xs font-extrabold uppercase text-slate-700 tracking-wider shrink-0 mb-3">{{ cashierStore.t('savatcha') }}</h3>

        <!-- Table / Takeaway / Delivery selector -->
        <div class="shrink-0 mb-4 space-y-2">
          <label class="text-3xs text-slate-500 font-bold uppercase tracking-wider">Buyurtma Turi</label>
          <div class="grid grid-cols-3 gap-1.5 p-1 bg-slate-100 rounded-xl">
            <button
              type="button"
              @click="orderType = 'dine_in'"
              class="py-1.5 px-2 rounded-lg text-xs font-bold transition flex items-center justify-center gap-1"
              :class="orderType === 'dine_in' ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
            >
              Stolda
            </button>
            <button
              type="button"
              @click="orderType = 'takeaway'"
              class="py-1.5 px-2 rounded-lg text-xs font-bold transition flex items-center justify-center gap-1"
              :class="orderType === 'takeaway' ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
            >
              Olib ketish
            </button>
            <button
              type="button"
              @click="orderType = 'delivery'"
              class="py-1.5 px-2 rounded-lg text-xs font-bold transition flex items-center justify-center gap-1"
              :class="orderType === 'delivery' ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
            >
              Dastavka
            </button>
          </div>

          <!-- If Delivery: Phone Number Input -->
          <div v-if="orderType === 'delivery'" class="space-y-1 animate-fadeIn">
            <label class="text-3xs text-slate-500 font-bold uppercase tracking-wider">Mijoz Telefon Raqami *</label>
            <input
              type="text"
              v-model="customerPhone"
              placeholder="+998 (90) 123-45-67"
              class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 focus:border-indigo-500 text-xs font-bold text-slate-900 focus:outline-none transition"
            />
          </div>

          <!-- Table Selector -->
          <div v-if="orderType !== 'delivery'" class="space-y-1">
            <label class="text-3xs text-slate-500 font-bold uppercase tracking-wider">Stol Tanlash (Ixtiyoriy)</label>
            <select
              v-model="selectedTableId"
              class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 focus:border-indigo-500 text-xs font-bold text-slate-900 focus:outline-none transition"
            >
              <option :value="null">Stol biriktirilmagan</option>
              <option
                v-for="t in allTablesList"
                :key="t.id"
                :value="t.id"
              >
                {{ t.table_number }} ({{ t.status === 'occupied' ? cashierStore.t('band') : cashierStore.t('bo_sh') }})
              </option>
            </select>
          </div>
        </div>

        <!-- Cart Items List -->
        <div class="flex-grow overflow-y-auto pr-1 divide-y divide-slate-200 space-y-2 mb-4">
          <div
            v-for="item in cashierStore.cart"
            :key="item.food_id + '-' + (item.size_name || 'default')"
            class="flex items-center justify-between py-2 text-xs"
          >
            <div @click="triggerEditFlow(item)" class="space-y-0.5 truncate max-w-[180px] cursor-pointer group">
              <h4 class="font-bold text-slate-900 truncate group-hover:text-indigo-600 transition-colors flex items-center gap-1">
                {{ item.name }}
                <span v-if="item.size_name" class="text-[9px] px-1.5 py-0.5 rounded bg-indigo-50 border border-indigo-200 text-indigo-700 font-bold">
                  {{ item.size_name }}
                </span>
              </h4>
              <p class="font-mono text-slate-650 text-[10px]">{{ formatCurrency(item.price) }}</p>
            </div>

            <!-- Increments -->
            <div class="flex items-center space-x-2.5 shrink-0">
              <button
                @click="cashierStore.updateQuantity(item.food_id, -1, item.size_name)"
                class="w-6 h-6 rounded-lg bg-slate-100 hover:bg-slate-200 border border-slate-300 text-slate-750 flex items-center justify-center font-black"
              >
                -
              </button>
              <span class="font-bold font-mono text-slate-900 text-xs w-4 text-center">{{ item.quantity }}</span>
              <button
                @click="cashierStore.updateQuantity(item.food_id, 1, item.size_name)"
                class="w-6 h-6 rounded-lg bg-slate-100 hover:bg-slate-200 border border-slate-300 text-slate-750 flex items-center justify-center font-black"
              >
                +
              </button>

              <button
                @click="cashierStore.removeFromCart(item.food_id, item.size_name)"
                class="p-1 rounded bg-rose-100 text-rose-700 hover:bg-rose-600 hover:text-white transition"
              >
                <Trash2 class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>

          <div v-if="cashierStore.cart.length === 0" class="text-center py-16 text-slate-500 text-xs">
            Savatcha hozircha bo'sh. Chap tomondan taom qo'shing.
          </div>
        </div>
      </div>

      <!-- Financial calculations & checkout -->
      <div class="border-t border-slate-200 pt-4 space-y-3 shrink-0">
        <div class="space-y-1.5 text-xs font-semibold">
          <div class="flex justify-between">
            <span class="text-slate-600 font-bold">{{ cashierStore.t('oraliq_jami') }}:</span>
            <span class="font-mono text-slate-900 font-bold">{{ formatCurrency(totals.subtotal) }}</span>
          </div>
          <div class="flex justify-between" v-if="totals.service > 0">
            <span class="text-slate-600 font-bold">{{ cashierStore.t('xizmat_haqi') }} ({{ serviceChargeRate }}%):</span>
            <span class="font-mono text-slate-900 font-bold">{{ formatCurrency(totals.service) }}</span>
          </div>
          <div class="flex justify-between" v-if="totals.tax > 0">
            <span class="text-slate-600 font-bold">{{ cashierStore.t('qqs') }} ({{ taxRate }}%):</span>
            <span class="font-mono text-slate-900 font-bold">{{ formatCurrency(totals.tax) }}</span>
          </div>
          <div class="border-t border-dashed border-slate-200 my-2"></div>
          <!-- Monetary Aggregations Display: text-slate-950 font-black text-2xl md:text-3xl -->
          <div class="flex justify-between text-slate-955 font-black text-2xl">
            <span>{{ cashierStore.t('jami_to_lov') }}:</span>
            <span class="font-mono text-slate-950 font-black text-2xl md:text-3xl">{{ formatCurrency(totals.total) }}</span>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
          <!-- Button 1: Send Order / Mark Table Occupied (No immediate payment) -->
          <button 
            v-if="orderType !== 'delivery'"
            @click="submitOrderOnly"
            :disabled="cashierStore.cart.length === 0 || loadingOrderSubmit"
            class="py-3.5 px-3 rounded-2xl bg-slate-800 hover:bg-slate-900 font-extrabold text-xs text-white shadow-md transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-1.5"
          >
            <Send v-if="!loadingOrderSubmit" class="w-4 h-4" />
            <Loader2 v-else class="w-4 h-4 animate-spin" />
            <span>{{ selectedTableId ? 'Stolni Band qil (Oshxonaga yubor)' : 'Oshxonaga Yubor' }}</span>
          </button>

          <!-- Button 2: Immediate Checkout & Payment -->
          <button 
            @click="openCheckout"
            :disabled="cashierStore.cart.length === 0"
            class="py-3.5 px-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 font-extrabold text-xs text-white shadow-md transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-1.5"
            :class="orderType === 'delivery' ? 'w-full col-span-2' : ''"
          >
            <CreditCard class="w-4 h-4" />
            <span>{{ cashierStore.t('to_lovga_o_tish') }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Product Options Sheet: portion/size selection before adding to cart -->
    <ProductOptionsSheet
      v-if="activeCustomFood"
      :food="activeCustomFood"
      :initialSizeName="editingCartItem ? editingCartItem.size_name : null"
      :initialNotes="editingCartItem ? editingCartItem.notes : ''"
      :initialQuantity="editingCartItem ? editingCartItem.quantity : 1"
      @close="activeCustomFood = null; editingCartItem = null"
      @add="handleCustomAdd"
    />

    <!-- MODAL: FAST POS BILLING CHECKOUT -->
    <Transition name="fade">
      <div 
        v-if="showCheckoutModal" 
        class="fixed inset-0 z-50 backdrop-blur-md bg-black/50 flex items-center justify-center p-6 no-print"
        @click.self="showCheckoutModal = false"
      >
        <div class="w-full max-w-md bg-white border-2 border-slate-300 rounded-3xl p-6 shadow-2xl space-y-5 animate-scaleIn text-left text-slate-900 max-h-[90vh] overflow-y-auto">
          
          <div class="flex justify-between items-center border-b border-slate-200 pb-3">
            <h3 class="text-base font-black text-slate-900 flex items-center space-x-2">
              <Receipt class="w-5 h-5 text-indigo-600" />
              <span>Tezkor POS To'lovi</span>
            </h3>
            <button @click="showCheckoutModal = false" class="p-1 rounded-lg bg-slate-100 text-slate-500 hover:text-slate-950 transition">
              <X class="w-4 h-4" />
            </button>
          </div>

          <div class="space-y-4">
            <!-- Order Summary -->
            <div class="p-4 rounded-2xl bg-slate-50 border-2 border-slate-200 space-y-2 text-xs font-bold text-slate-800">
              <div class="flex justify-between text-slate-600">
                <span>Savatchadagi taomlar soni:</span>
                <span>{{ cashierStore.cart.reduce((acc, i) => acc + i.quantity, 0) }} ta</span>
              </div>
              <div class="flex justify-between text-indigo-700 font-black text-sm pt-1 border-t border-slate-200 mt-2">
                <span>JAMI SUMMA:</span>
                <span class="font-mono text-slate-950 font-black text-2xl md:text-3xl">{{ formatCurrency(totals.total) }}</span>
              </div>
            </div>

            <!-- Table Selection (Optional, defaults to Takeaway) -->
            <div class="space-y-1.5">
              <label class="text-xxs text-slate-600 font-bold uppercase tracking-wider">Stolni Tanlang (Ixtiyoriy)</label>
              <select 
                v-model="checkoutForm.table_id" 
                class="w-full px-4 py-2.5 rounded-xl bg-white border-2 border-slate-300 focus:border-indigo-600 text-sm text-slate-900 font-bold focus:outline-none transition"
              >
                <option :value="null">Olib ketish (Takeaway / Stol yo'q)</option>
                <option 
                  v-for="t in allTablesList" 
                  :key="t.id" 
                  :value="t.id"
                >
                  {{ t.table_number }} ({{ t.status === 'occupied' ? 'Band' : 'Bo\'sh' }})
                </option>
              </select>
            </div>

            <!-- CRM Customer loyalty -->
            <div class="space-y-1.5">
              <div class="flex justify-between items-center">
                <label class="text-xxs text-slate-600 font-bold uppercase tracking-wider">Mijoz (Sodiqlik tizimi)</label>
                <span class="text-xs text-indigo-650 font-black" v-if="selectedCustomer">
                  Bonus: {{ formatCurrency(selectedCustomer.bonus_balance) }}
                </span>
              </div>
              <select 
                v-model="checkoutForm.customer_id" 
                @change="handleCustomerSelect"
                class="w-full px-4 py-2.5 rounded-xl bg-white border-2 border-slate-300 focus:border-indigo-600 text-sm text-slate-900 font-bold focus:outline-none transition"
              >
                <option :value="null">Mehmon (Mijoz bog'lanmagan)</option>
                <option 
                  v-for="c in customers" 
                  :key="c.id" 
                  :value="c.id"
                >
                  {{ c.name }} ({{ c.phone }})
                </option>
              </select>
            </div>

            <!-- Payment Method -->
            <div class="space-y-1.5">
              <label class="text-xxs text-slate-600 font-bold uppercase tracking-wider">To'lov Usuli *</label>
              <select 
                v-model="checkoutForm.payment_method" 
                @change="handlePaymentMethodChange"
                class="w-full px-4 py-2.5 rounded-xl bg-white border-2 border-slate-300 focus:border-indigo-600 text-sm text-slate-900 font-bold focus:outline-none transition"
              >
                <option value="cash">{{ settingsStore.t('cashier.cash') }} (Cash)</option>
                <option value="card">{{ settingsStore.t('cashier.card') }} (Card)</option>
                <option value="qr">QR to'lov (Payme/Click)</option>
                <option value="mixed">Aralash to'lov (Mixed)</option>
              </select>
            </div>

            <!-- Amount splits -->
            <!-- Payment Split Fields: bg-white border-2 border-slate-300 text-slate-955 font-bold text-lg focus:border-indigo-600 focus:ring-2 -->
            <div class="p-4 rounded-2xl bg-slate-50 border-2 border-slate-200 space-y-3 text-xs font-bold">
              <div class="grid grid-cols-3 items-center gap-2" v-if="checkoutForm.payment_method === 'cash' || checkoutForm.payment_method === 'mixed'">
                <span class="text-slate-700 font-black">{{ settingsStore.t('cashier.cash') }}:</span>
                <input 
                  v-model.number="checkoutForm.cash_amount"
                  type="number"
                  placeholder="0"
                  :disabled="checkoutForm.payment_method === 'cash'"
                  class="col-span-2 px-3 py-2 rounded-lg bg-white border-2 border-slate-300 text-slate-955 font-bold text-base focus:outline-none focus:border-indigo-600"
                />
              </div>
              <div class="grid grid-cols-3 items-center gap-2" v-if="checkoutForm.payment_method === 'card' || checkoutForm.payment_method === 'mixed'">
                <span class="text-slate-700 font-black">{{ settingsStore.t('cashier.card') }}:</span>
                <input 
                  v-model.number="checkoutForm.card_amount"
                  type="number"
                  placeholder="0"
                  :disabled="checkoutForm.payment_method === 'card'"
                  class="col-span-2 px-3 py-2 rounded-lg bg-white border-2 border-slate-300 text-slate-955 font-bold text-base focus:outline-none focus:border-indigo-600"
                />
              </div>
              <div class="grid grid-cols-3 items-center gap-2" v-if="checkoutForm.payment_method === 'qr' || checkoutForm.payment_method === 'mixed'">
                <span class="text-slate-700 font-black">QR to'lov:</span>
                <input 
                  v-model.number="checkoutForm.qr_amount"
                  type="number"
                  placeholder="0"
                  :disabled="checkoutForm.payment_method === 'qr'"
                  class="col-span-2 px-3 py-2 rounded-lg bg-white border-2 border-slate-300 text-slate-955 font-bold text-base focus:outline-none focus:border-indigo-600"
                />
              </div>
              <div class="grid grid-cols-3 items-center gap-2" v-if="selectedCustomer && selectedCustomer.bonus_balance > 0">
                <span class="text-slate-700 font-black">Bonusdan:</span>
                <input 
                  v-model.number="checkoutForm.bonus_used"
                  type="number"
                  placeholder="0"
                  class="col-span-2 px-3 py-2 rounded-lg bg-white border-2 border-slate-300 text-slate-955 font-bold text-base focus:outline-none focus:border-indigo-600"
                />
              </div>
            </div>

            <!-- Balance Split Warning -->
            <div 
              v-if="!isSplitAmountValid && checkoutForm.payment_method === 'mixed'"
              class="p-3 rounded-xl bg-amber-50 border-2 border-amber-300 text-xxs text-amber-800 font-bold"
            >
              Diqqat: To'lov summasi jami miqdorga to'g'ri kelmayapti. Kiritilgan: {{ formatCurrency(totalEnteredAmount) }} (Jami: {{ formatCurrency(totals.total) }}).
            </div>
          </div>

          <div class="flex justify-end space-x-2 pt-4 border-t border-slate-200">
            <button @click="showCheckoutModal = false" class="px-4 py-3 bg-slate-100 hover:bg-slate-200 border border-slate-300 rounded-xl text-xs font-bold text-slate-700 transition">
              Bekor qilish
            </button>
            <!-- The final "To'lovni Yopish (Close Bill)" button must look heavy and powerful: bg-emerald-600 hover:bg-emerald-700 text-white font-black py-4 rounded-xl text-xl shadow-md -->
            <button 
              @click="submitCheckout"
              :disabled="loadingSubmit || (checkoutForm.payment_method === 'mixed' && !isSplitAmountValid)"
              class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-black shadow-md transition disabled:opacity-50"
            >
              {{ loadingSubmit ? 'To\'lanmoqda...' : 'To\'lovni Yopish' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Clean Success Modal -->
    <Transition name="fade">
      <div v-if="modal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-950/50 backdrop-blur-sm" @click="closeModal"></div>
        <div class="relative z-10 w-full max-w-sm bg-white border-2 border-slate-300 rounded-3xl p-6 shadow-2xl text-center space-y-6 animate-scaleIn text-slate-900">
          <div class="w-16 h-16 rounded-full mx-auto flex items-center justify-center bg-emerald-50 border border-emerald-350 text-emerald-600">
            <CheckCircle class="w-8 h-8" />
          </div>
          <div class="space-y-2">
            <h4 class="text-lg font-black text-slate-900">{{ modal.title }}</h4>
            <p class="text-sm text-slate-700 font-bold leading-relaxed">{{ modal.message }}</p>
          </div>
          <button 
            @click="closeModal" 
            class="w-full py-3 rounded-xl font-bold text-sm bg-indigo-600 hover:bg-indigo-700 text-white shadow-md transition duration-200"
          >
            Yopish
          </button>
        </div>
      </div>
    </Transition>

    <!-- ACTUAL PRINT ONLY CONTENT (Hidden on screen, shown when printing) -->
    <div id="physical-thermal-receipt" class="print-only" v-if="lastCompletedPayment">
      <div class="ticket-ticket">
        <div class="ticket-center" style="margin-bottom: 5px;">
           <img :src="'/foodflow_logo.png'" style="width: 48px; height: 48px; display: block; margin: 0 auto; filter: grayscale(100%);" alt="Logo" />
        </div>
        <div class="ticket-center font-bold font-large">{{ settingStore.branding?.name || 'FoodFlow' }}</div>
        <div class="ticket-center">{{ settingStore.branding?.address || 'Toshkent, O\'zbekiston' }}</div>
        <div class="ticket-center">Tel: {{ settingStore.branding?.phone || '+998 90 123 45 67' }}</div>
        
        <div class="ticket-divider"></div>

        <div>{{ cashierStore.t('chek_no') }}: #{{ String(lastCompletedPayment.id).padStart(6, '0') }}</div>
        <div>{{ cashierStore.t('buyurtma_no') }}: {{ lastCompletedPayment.order?.order_number }}</div>
        <div>{{ cashierStore.t('sana') }}: {{ printFormatDateTime(lastCompletedPayment.created_at) }}</div>
        <div v-if="lastCompletedPayment.order?.table?.table_number">{{ cashierStore.t('stol') }}: {{ lastCompletedPayment.order.table.table_number }}</div>
        <div>{{ cashierStore.t('kassir') }}: {{ authStore.user?.name }}</div>

        <div class="ticket-divider"></div>

        <table class="ticket-table">
          <thead>
            <tr>
              <th align="left">{{ cashierStore.t('nomi') }}</th>
              <th align="center">{{ cashierStore.t('soni') }}</th>
              <th align="right">{{ cashierStore.t('summa') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in lastCompletedPayment.order?.items" :key="item.id">
              <td>{{ item.food?.name }}</td>
              <td align="center">x{{ item.quantity }}</td>
              <td align="right">{{ formatCurrency(item.quantity * item.price) }}</td>
            </tr>
          </tbody>
        </table>

        <div class="ticket-divider"></div>

        <div class="ticket-totals">
          <div class="flex-row">
            <span>{{ cashierStore.t('oraliq_jami') }}:</span>
            <span>{{ formatCurrency(printGetSubtotal()) }}</span>
          </div>
          <div class="flex-row font-italic" v-if="printGetDiscountAmount() > 0">
            <span>{{ cashierStore.t('chegirma') }}:</span>
            <span>-{{ formatCurrency(printGetDiscountAmount()) }}</span>
          </div>
          <div class="flex-row" v-if="printGetServiceCharge() > 0">
            <span>{{ cashierStore.t('xizmat_haqi') }} ({{ settingStore.settings?.service_charge_rate || 0 }}%):</span>
            <span>{{ formatCurrency(printGetServiceCharge()) }}</span>
          </div>
          <div class="flex-row" v-if="printGetTax() > 0">
            <span>{{ cashierStore.t('qqs') }} ({{ settingStore.settings?.tax_rate || 0 }}%):</span>
            <span>{{ formatCurrency(printGetTax()) }}</span>
          </div>

          <div class="ticket-divider-thin"></div>
          <div class="ticket-bold">{{ cashierStore.t('tolov_shakli') }}:</div>
          <div class="flex-row pl-2" v-if="parseFloat(lastCompletedPayment.cash_amount) > 0">
            <span>{{ cashierStore.t('naqd') }}:</span>
            <span>{{ formatCurrency(lastCompletedPayment.cash_amount) }}</span>
          </div>
          <div class="flex-row pl-2" v-if="parseFloat(lastCompletedPayment.card_amount) > 0">
            <span>{{ cashierStore.t('karta') }}:</span>
            <span>{{ formatCurrency(lastCompletedPayment.card_amount) }}</span>
          </div>
          <div class="flex-row pl-2" v-if="parseFloat(lastCompletedPayment.qr_amount) > 0">
            <span>{{ cashierStore.t('qr_tolov') }}:</span>
            <span>{{ formatCurrency(lastCompletedPayment.qr_amount) }}</span>
          </div>

          <div class="ticket-divider"></div>
          <div class="flex-row ticket-bold font-large">
            <span>{{ cashierStore.t('jami_to_lov') }}:</span>
            <span>{{ formatCurrency(lastCompletedPayment.total_amount) }}</span>
          </div>
        </div>

        <div class="ticket-divider"></div>

        <div class="ticket-center" style="margin: 12px 0;">
          <img :src="printQrCodeUrl" style="width: 2.2cm; height: 2.2cm; display: block; margin: 0 auto;" alt="QR Code" />
          <div style="font-size: 7.5pt; font-family: monospace; margin-top: 4px; text-transform: uppercase;">{{ cashierStore.t('scan_to_verify') }}</div>
        </div>

        <div class="ticket-divider"></div>

        <div class="ticket-center ticket-footer-text">
          <p>{{ settingStore.settings?.receipt_header || 'FoodFlow - Xizmatimizdan mamnunmisiz?' }}</p>
          <p class="ticket-bold">{{ settingStore.settings?.receipt_footer || 'Xaridingiz uchun rahmat! Yana keling!' }}</p>
        </div>
      </div>
    </div>
  </div>
</template>


<script setup>
import { useSettingsStore } from '@/stores/settings';
const settingsStore = useSettingsStore();
import { ref, onMounted, onUnmounted, computed, watch, markRaw } from 'vue';
import { Plus, Trash2, Receipt, X, CheckCircle, Send, CreditCard, Loader2 } from 'lucide-vue-next';
import { useCashierStore } from '@/stores/cashier';
import { useAuthStore } from '@/stores/auth';
import { useSettingStore } from '@/stores/settings';
import { useRouter, useRoute } from 'vue-router';
import ProductOptionsSheet from './ProductOptionsSheet.vue';

const activeCustomFood = ref(null);
const editingCartItem = ref(null);
const cashierStore = useCashierStore();
const authStore = useAuthStore();
const settingStore = useSettingStore();
const router = useRouter();
const route = useRoute();

const triggerAddFlow = (food) => {
  cashierStore.addToCart(food, null, food.price, '', 1);
};

const triggerEditFlow = (item) => {
  const food = item.food || foods.value.find(f => f.id === item.food_id);
  if (food) {
    editingCartItem.value = item;
    activeCustomFood.value = food;
  }
};

const handleCustomAdd = (payload) => {
  if (editingCartItem.value) {
    cashierStore.editCartItem(
      editingCartItem.value.food_id,
      editingCartItem.value.size_name,
      payload.size_name,
      payload.price,
      payload.notes,
      payload.quantity
    );
  } else {
    cashierStore.addToCart(activeCustomFood.value, payload.size_name, payload.price, payload.notes, payload.quantity);
  }
  activeCustomFood.value = null;
  editingCartItem.value = null;
};


const categories = ref([]);
const foods = ref([]);
const customers = ref([]);
const allTablesList = ref([]);
const loading = ref(false);
const selectedCategory = ref('all');
const selectedMenuType = ref('kitchen'); // 'kitchen' or 'bar'
const selectedTableId = ref(null);
const orderType = ref('dine_in'); // 'dine_in', 'takeaway', 'delivery'
const customerPhone = ref('');

// Checkout modal states
const showCheckoutModal = ref(false);
const selectedCustomer = ref(null);
const loadingSubmit = ref(false);

const checkoutForm = ref({
  table_id: null,
  customer_id: null,
  payment_method: 'cash',
  cash_amount: 0,
  card_amount: 0,
  qr_amount: 0,
  bonus_used: 0
});

// Stats Modal dialog
const modal = ref({
  show: false,
  title: '',
  message: ''
});

// Settings defaults
const taxRate = computed(() => settingStore.settings.tax_rate !== undefined && settingStore.settings.tax_rate !== '' ? Number(settingStore.settings.tax_rate) : 12);
const serviceChargeRate = computed(() => settingStore.settings.service_charge_rate !== undefined && settingStore.settings.service_charge_rate !== '' ? Number(settingStore.settings.service_charge_rate) : 10);

// Computed stats
const setMenuType = (type) => {
  selectedMenuType.value = type;
  selectedCategory.value = 'all'; // reset category on switch
};

const availableCategories = computed(() => {
  const relevantFoods = foods.value.filter(f => 
    selectedMenuType.value === 'kitchen' ? !f.is_bar_item : f.is_bar_item
  );
  const categoryIds = new Set(relevantFoods.map(f => f.category_id));
  return categories.value.filter(c => categoryIds.has(c.id));
});

const filteredFoods = computed(() => {
  const typeFiltered = foods.value.filter(f => 
    selectedMenuType.value === 'kitchen' ? !f.is_bar_item : f.is_bar_item
  );

  if (selectedCategory.value === 'all') {
    return typeFiltered;
  }
  return typeFiltered.filter(f => f.category_id === selectedCategory.value);
});

const totals = computed(() => {
  const cart = cashierStore.cart || [];
  const subtotal = cart.reduce((acc, item) => acc + (item.price * item.quantity), 0);
  const discount = cashierStore.discountAmount;
  const service = (subtotal - discount) * (parseFloat(serviceChargeRate.value) / 100);
  const tax = (subtotal - discount) * (parseFloat(taxRate.value) / 100);
  const total = subtotal - discount + service + tax;
  return { subtotal, service, tax, total };
});

const totalEnteredAmount = computed(() => {
  const form = checkoutForm.value;
  return (parseFloat(form.cash_amount) || 0) + 
         (parseFloat(form.card_amount) || 0) + 
         (parseFloat(form.qr_amount) || 0) + 
         (parseFloat(form.bonus_used) || 0);
});

const isSplitAmountValid = computed(() => {
  return Math.abs(totalEnteredAmount.value - totals.value.total) < 1;
});

const formatCurrency = (val) => {
  if (val === undefined || val === null) return '0 UZS';
  return new Intl.NumberFormat('uz-UZ').format(Math.round(val)) + ' UZS';
};

const openCheckout = async () => {
  checkoutForm.value = {
    table_id: selectedTableId.value,
    customer_id: null,
    payment_method: 'cash',
    cash_amount: totals.value.total,
    card_amount: 0,
    qr_amount: 0,
    bonus_used: 0
  };
  selectedCustomer.value = null;

  // Load tables
  try {
    const res = await fetch('/api/tables', {
      headers: { 'Authorization': `Bearer ${authStore.token}`, 'Accept': 'application/json' }
    });
    if (res.ok) {
      allTablesList.value = await res.json();
    }
  } catch (err) {
    console.error(err);
  }

  // Load customers
  try {
    const res = await fetch('/api/customers', {
      headers: { 'Authorization': `Bearer ${authStore.token}`, 'Accept': 'application/json' }
    });
    if (res.ok) {
      const data = await res.json();
      customers.value = data.data || data;
    }
  } catch (err) {
    console.error(err);
  }

  showCheckoutModal.value = true;
};

const handleCustomerSelect = () => {
  selectedCustomer.value = customers.value.find(c => c.id === checkoutForm.value.customer_id) || null;
  checkoutForm.value.bonus_used = 0;
};

const handlePaymentMethodChange = () => {
  const method = checkoutForm.value.payment_method;
  const remaining = Math.max(0, totals.value.total - (parseFloat(checkoutForm.value.bonus_used) || 0));

  checkoutForm.value.cash_amount = 0;
  checkoutForm.value.card_amount = 0;
  checkoutForm.value.qr_amount = 0;

  if (method === 'cash') {
    checkoutForm.value.cash_amount = remaining;
  } else if (method === 'card') {
    checkoutForm.value.card_amount = remaining;
  } else if (method === 'qr') {
    checkoutForm.value.qr_amount = remaining;
  }
};

// Keep the active single-method amount in sync when a bonus is entered/changed,
// so cash/card/qr always reflects (total - bonus) instead of the full total
// while the bonus is silently deducted underneath.
watch(() => checkoutForm.value.bonus_used, (newBonus) => {
  const method = checkoutForm.value.payment_method;
  if (method === 'mixed') return;
  const remaining = Math.max(0, totals.value.total - (parseFloat(newBonus) || 0));
  if (method === 'cash') checkoutForm.value.cash_amount = remaining;
  else if (method === 'card') checkoutForm.value.card_amount = remaining;
  else if (method === 'qr') checkoutForm.value.qr_amount = remaining;
});

const submitCheckout = async () => {
  if (cashierStore.cart.length === 0) return;

  loadingSubmit.value = true;
  try {
    // 1. Create Order
    const orderPayload = {
      table_id: checkoutForm.value.table_id || selectedTableId.value || null,
      order_type: orderType.value,
      customer_phone: customerPhone.value || null,
      waiter_id: authStore.user?.id || null,
      items: cashierStore.cart.map(it => ({
        food_id: it.food_id,
        quantity: it.quantity,
        size_name: it.size_name || null,
        notes: it.notes || null
      }))
    };

    const orderRes = await fetch('/api/orders', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${authStore.token}`
      },
      body: JSON.stringify(orderPayload)
    });

    const orderData = await orderRes.json();
    if (!orderRes.ok) {
      throw new Error(orderData.message || 'Tezkor buyurtma yaratishda xatolik yuz berdi.');
    }

    // 2. Process final Checkout Payment
    const paymentPayload = {
      order_id: orderData.order.id,
      customer_id: checkoutForm.value.customer_id,
      payment_method: checkoutForm.value.payment_method,
      cash_amount: checkoutForm.value.cash_amount,
      card_amount: checkoutForm.value.card_amount,
      qr_amount: checkoutForm.value.qr_amount,
      bonus_used: checkoutForm.value.bonus_used
    };

    const payRes = await fetch('/api/payments', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${authStore.token}`
      },
      body: JSON.stringify(paymentPayload)
    });

    const payData = await payRes.json();
    if (!payRes.ok) {
      throw new Error(payData.message || 'POS To\'lovni yakunlashda xatolik yuz berdi.');
    }

    // Clear cart and close modal
    cashierStore.clearCart();
    selectedTableId.value = null;
    showCheckoutModal.value = false;

    
    lastCompletedPayment.value = payData.payment;
    
    // Wait for Vue to render the hidden receipt, then print
    setTimeout(() => {
      window.print();
      // Clear the receipt from DOM after printing dialog closes
      lastCompletedPayment.value = null;
    }, 200);

  } catch (err) {
    alert(err.message);
  } finally {
    loadingSubmit.value = false;
  }
};


// Print receipt logic
const lastCompletedPayment = ref(null);

const printQrCodeUrl = computed(() => {
  const name = settingStore.branding?.name || 'FoodFlow';
  const address = settingStore.branding?.address || "Toshkent, O'zbekiston";
  return `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(name + ' - ' + address)}`;
});

const printFormatDateTime = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleString('uz-UZ', { 
    day: '2-digit', month: '2-digit', year: 'numeric',
    hour: '2-digit', minute: '2-digit', hour12: false
  });
};

const printGetSubtotal = () => {
  if (!lastCompletedPayment.value || !lastCompletedPayment.value.order) return 0;
  return lastCompletedPayment.value.order.items.reduce((acc, item) => acc + (parseFloat(item.price) * item.quantity), 0);
};
const printGetDiscountAmount = () => parseFloat(lastCompletedPayment.value?.order?.discount_amount) || 0;
const printGetServiceCharge = () => (printGetSubtotal() - printGetDiscountAmount()) * (parseFloat(settingStore.settings?.service_charge_rate || 0) / 100);
const printGetTax = () => (printGetSubtotal() - printGetDiscountAmount()) * (parseFloat(settingStore.settings?.tax_rate || 0) / 100);

const loadingOrderSubmit = ref(false);


const fetchActiveOrderForTable = async (tableId) => {
  if (!tableId) return;
  try {
    const res = await fetch(`/api/orders?table_id=${tableId}&status=new,cooking,ready,delivered`, {
      headers: { 'Authorization': `Bearer ${authStore.token}`, 'Accept': 'application/json' }
    });
    if (res.ok) {
      const ordersData = await res.json();
      const ordersList = Array.isArray(ordersData) ? ordersData : (ordersData.data || []);
      // Filter specifically for orders belonging to THIS table_id
      const tableOrders = ordersList.filter(o => String(o.table_id) === String(tableId));
      const active = tableOrders.find(o => !o.payments || o.payments.length === 0);
      if (active && active.items && active.items.length > 0) {
        cashierStore.clearCart();
        for (const it of active.items) {
          if (it.food) {
            cashierStore.addToCart(it.food, it.size_name, parseFloat(it.price), it.notes, it.quantity);
          }
        }
      }
    }
  } catch (err) {
    console.error('Active order loading error:', err);
  }
};

watch(selectedTableId, (newId) => {
  if (newId) {
    fetchActiveOrderForTable(newId);
  } else {
    cashierStore.clearCart();
  }
});

const submitOrderOnly = async () => {
  if (cashierStore.cart.length === 0) return;
  loadingOrderSubmit.value = true;

  try {
    const parsedTableId = selectedTableId.value ? parseInt(selectedTableId.value, 10) : null;
    const parsedWaiterId = authStore.user?.id ? parseInt(authStore.user.id, 10) : null;

    const orderPayload = {
      table_id: parsedTableId,
      order_type: orderType.value,
      customer_phone: customerPhone.value || null,
      waiter_id: parsedWaiterId,
      items: cashierStore.cart.map(it => ({
        food_id: parseInt(it.food_id, 10),
        quantity: parseInt(it.quantity, 10),
        size_name: it.size_name || null,
        notes: it.notes || null
      }))
    };

    const res = await fetch('/api/orders', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${authStore.token}`
      },
      body: JSON.stringify(orderPayload)
    });

    const data = await res.json();
    if (!res.ok) {
      const errorMsg = data.errors ? Object.values(data.errors).flat().join('\n') : (data.message || 'Buyurtma yuborishda xatolik yuz berdi.');
      throw new Error(errorMsg);
    }

    cashierStore.clearCart();
    const tableMsg = selectedTableId.value ? ' va stol band qilindi' : '';
    openModal("Muvaffaqiyatli!", `Buyurtma #${data.order_number || data.id || ''} oshxonaga yuborildi${tableMsg}.`, "success", CheckCircle);

    if (selectedTableId.value) {
      setTimeout(() => {
        router.push({ path: '/cashier/tables' });
      }, 1200);
    }
  } catch (err) {
    alert(err.message || 'Buyurtma yuborishda xatolik yuz berdi.');
  } finally {
    loadingOrderSubmit.value = false;
  }
};

const openModal = (title, message, type, icon) => {
  modal.value = { show: true, title, message, type, icon };
};

const closeModal = () => {
  modal.value.show = false;
};

onMounted(async () => {
  loading.value = true;
  
  // Set selectedTableId from query param if available
  if (route.query.table_id) {
    selectedTableId.value = parseInt(route.query.table_id, 10);
    fetchActiveOrderForTable(selectedTableId.value);
  }
  if (route.query.type) {
    orderType.value = route.query.type;
  }
  
  // Load categories
  try {
    const res = await fetch('/api/menu/categories', {
      headers: { 'Authorization': `Bearer ${authStore.token}`, 'Accept': 'application/json' }
    });
    if (res.ok) {
      categories.value = await res.json();
    }
  } catch (err) {
    console.error(err);
  }

  // Load foods
  try {
    const res = await fetch('/api/menu/foods', {
      headers: { 'Authorization': `Bearer ${authStore.token}`, 'Accept': 'application/json' }
    });
    if (res.ok) {
      foods.value = await res.json();
    }
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }

  // Load tables so the table/takeaway selector is populated right away,
  // not only once the checkout modal is opened.
  try {
    const res = await fetch('/api/tables', {
      headers: { 'Authorization': `Bearer ${authStore.token}`, 'Accept': 'application/json' }
    });
    if (res.ok) {
      allTablesList.value = await res.json();
    }
  } catch (err) {
    console.error(err);
  }

  settingStore.fetchSettings();

  window.addEventListener('keydown', handleGlobalKeydown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleGlobalKeydown);
});

// --- Barcode Scanner Logic ---
let barcodeBuffer = '';
let barcodeTimeout = null;

const handleGlobalKeydown = (e) => {
  // Ignore if user is typing in an input field (except if it's the body)
  if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable) {
    return;
  }

  if (e.key === 'Enter') {
    if (barcodeBuffer.length > 2) {
      processBarcodeScan(barcodeBuffer);
    }
    barcodeBuffer = '';
    return;
  }

  if (e.key.length === 1) { // Normal printable character
    barcodeBuffer += e.key;
    clearTimeout(barcodeTimeout);
    barcodeTimeout = setTimeout(() => {
      barcodeBuffer = ''; // reset if typing is too slow (human typing)
    }, 150);
  }
};

const processBarcodeScan = async (barcode) => {
  try {
    const res = await fetch(`/api/menu/foods/barcode/${barcode}`, {
      headers: { 'Authorization': `Bearer ${authStore.token}`, 'Accept': 'application/json' }
    });
    if (res.ok) {
      const food = await res.json();
      if (!food.is_available) {
        alert('Bu mahsulot (shtrix-kod) hozirda mavjud emas!');
        return;
      }
      
      // Add to cart with quantity 1
      cashierStore.addToCart(food, null, food.price, '', 1);
    } else {
      console.warn('Shtrix-kod bo\'yicha mahsulot topilmadi:', barcode);
    }
  } catch (err) {
    console.error('Barcode processing error:', err);
  }
};
</script>

<style>
/* Styling grid limits scrollbar */
.scrollbar-thin::-webkit-scrollbar {
  height: 4px;
}
.scrollbar-thin::-webkit-scrollbar-track {
  background: transparent;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.08);
  border-radius: 2px;
}
</style>
