<template>
  <div class="h-full flex flex-col md:flex-row gap-6 overflow-hidden print-container bg-slate-50 p-4 rounded-3xl">
    
    <!-- LEFT SIDE: Invoice History Ledger (hidden on print) -->
    <div class="w-full md:w-3/5 flex flex-col h-full overflow-hidden bg-white border-2 border-slate-200 rounded-3xl p-6 shadow-sm no-print">
      <div class="flex justify-between items-center mb-5 shrink-0">
        <div>
          <h2 class="text-slate-900 font-black text-2xl tracking-tight">{{ settingsStore.t('app_title') }} - {{ cashierStore.t('cheklar_tarixi') }}</h2>
          <p class="text-slate-500 font-bold text-sm mt-1">{{ cashierStore.t('cheklar_jurnali_desc') }}</p>
        </div>
        <div class="flex items-center space-x-3 shrink-0">
          <button 
            @click="openNewPaymentModal"
            class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 font-bold text-xs text-white shadow-md hover:scale-[1.01] transition-all flex items-center justify-center space-x-2"
          >
            <Plus class="w-4 h-4" />
            <span>{{ cashierStore.t('yangi_chek') }}</span>
          </button>
          <button 
            @click="refreshReceipts" 
            :disabled="receiptsStore.loading"
            class="p-2.5 rounded-xl bg-slate-100 border border-slate-200 hover:bg-slate-200 text-slate-700 hover:text-slate-955 transition duration-200 disabled:opacity-50"
            :title="cashierStore.t('yangilash')"
          >
            <RotateCw class="w-4 h-4" :class="{'animate-spin': receiptsStore.loading}" />
          </button>
        </div>
      </div>

      <!-- Ledger Table -->
      <div class="flex-grow overflow-y-auto pr-1">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-slate-200 text-slate-50 font-extrabold text-xs tracking-wider uppercase">
              <th class="py-3 px-4 text-slate-500">{{ cashierStore.t('sana_vaqt') }}</th>
              <th class="py-3 px-4 text-slate-500">{{ cashierStore.t('chek_no') }}</th>
              <th class="py-3 px-4 text-slate-500">{{ cashierStore.t('stol') }}</th>
              <th class="py-3 px-4 text-slate-500">{{ cashierStore.t('summa') }}</th>
              <th class="py-3 px-4 text-right text-slate-500">{{ cashierStore.t('amallar') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr 
              v-for="item in receiptsStore.payments" 
              :key="item.id"
              class="hover:bg-slate-50 transition duration-150 group cursor-pointer text-xs"
              :class="{'bg-indigo-50 border-l-4 border-indigo-650': selectedPayment?.id === item.id}"
              @click="selectPayment(item)"
            >
              <td class="py-3.5 px-4 text-slate-600 font-bold text-sm">
                {{ formatDateTime(item.created_at) }}
              </td>
              <td class="py-3.5 px-4 text-slate-950 font-black text-sm">
                <span class="bg-slate-100 px-2 py-1 rounded-md">#{{ String(item.id).padStart(6, '0') }}</span>
              </td>
              <td class="py-3.5 px-4 text-slate-800 font-black text-sm">
                {{ item.order?.table?.table_number ? item.order.table.table_number.replace('Stol', cashierStore.t('stol')) : cashierStore.t('nomalum') }}
              </td>
              <td class="py-3.5 px-4 text-indigo-600 font-black text-base">
                {{ formatCurrency(item.total_amount) }}
              </td>
              <td class="py-3.5 px-4 text-right" @click.stop>
                <div class="inline-flex items-center space-x-1.5">
                  <span 
                    v-if="item.is_printed" 
                    class="px-1.5 py-0.5 rounded bg-emerald-50 border border-emerald-200 text-emerald-800 text-[10px] font-black"
                    :title="cashierStore.t('chop_etilgan')"
                  >
                    {{ cashierStore.t('chop_etilgan') }}
                  </span>
                  <!-- Print trigger button with dropdown -->
                  <div class="relative inline-block text-left">
                    <button 
                      @click="togglePrintDropdown(item.id)"
                      class="bg-slate-100 hover:bg-slate-200 text-slate-700 p-2.5 rounded-full transition-colors border border-slate-200"
                    >
                      <Printer class="w-3.5 h-3.5" />
                    </button>
                    <!-- Mini Dropdown options -->
                    <div 
                      v-if="activeDropdownId === item.id" 
                      class="absolute right-0 mt-1 w-44 rounded-xl bg-white border border-slate-250 shadow-xl py-1 z-50 animate-scaleIn text-left text-xxs font-semibold"
                    >
                      <button @click="triggerDirectPrint(item, 'pre-check')" class="w-full px-3.5 py-2 text-slate-700 hover:bg-slate-50 hover:text-slate-950 flex items-center space-x-2">
                        <FileText class="w-3.5 h-3.5" />
                        <span class="text-xs font-bold">{{ cashierStore.t('navbat_cheki') }}</span>
                      </button>
                      <button @click="triggerDirectPrint(item, 'kot')" class="w-full px-3.5 py-2 text-slate-700 hover:bg-slate-50 hover:text-slate-950 flex items-center space-x-2">
                        <ChefHat class="w-3.5 h-3.5 text-amber-600" />
                        <span class="text-xs font-bold text-amber-700">Oshpaz cheki</span>
                      </button>
                      <button @click="triggerDirectPrint(item, 'invoice')" class="w-full px-3.5 py-2 text-slate-700 hover:bg-slate-50 hover:text-slate-955 flex items-center space-x-2">
                        <Receipt class="w-3.5 h-3.5" />
                        <span class="text-xs font-bold">{{ cashierStore.t('hisob_cheki') }}</span>
                      </button>
                      <button @click="triggerDirectPrint(item, 'mix')" class="w-full px-3.5 py-2 text-slate-700 hover:bg-slate-50 hover:text-slate-955 flex items-center space-x-2">
                        <FileSpreadsheet class="w-3.5 h-3.5" />
                        <span class="text-xs font-bold">{{ cashierStore.t('aralash_chek') }}</span>
                      </button>
                    </div>
                  </div>
                </div>
              </td>
            </tr>
            <tr v-if="receiptsStore.payments.length === 0 && !receiptsStore.loading">
              <td colspan="5" class="py-8 text-center text-slate-500 text-xs">
                {{ cashierStore.t('receipts_not_found') }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- RIGHT SIDE: Skeuomorphic Thermal Receipt Preview -->
    <div class="w-full md:w-2/5 flex flex-col h-full overflow-hidden no-print">
      <div class="bg-white border-2 border-slate-200 rounded-3xl p-5 shadow-sm flex flex-col h-full overflow-hidden justify-between">
        
        <!-- Live Preview Title & Print Mode triggers -->
        <div class="shrink-0 pb-4 border-b border-slate-200">
          <h3 class="text-slate-900 font-black text-lg pb-1">{{ cashierStore.t('chek_korinishi') }}</h3>
          <div class="grid grid-cols-4 gap-2 mt-3.5" v-if="selectedPayment">
            <button 
              @click="printMode = 'pre-check'"
              class="px-2.5 py-2 rounded-xl text-[10px] transition duration-200 border"
              :class="printMode === 'pre-check' ? 'bg-indigo-600 border-indigo-500 text-white font-black' : 'bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200 font-bold'"
            >
              {{ cashierStore.t('navbat_cheki') }}
            </button>
            <button 
              @click="printMode = 'invoice'"
              class="px-2.5 py-2 rounded-xl text-[10px] transition duration-200 border"
              :class="printMode === 'invoice' ? 'bg-indigo-600 border-indigo-500 text-white font-black' : 'bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200 font-bold'"
            >
              {{ cashierStore.t('tolov_cheki') }}
            </button>
            <button 
              @click="printMode = 'mix'"
              class="px-2.5 py-2 rounded-xl text-[10px] transition duration-200 border"
              :class="printMode === 'mix' ? 'bg-indigo-600 border-indigo-500 text-white font-black' : 'bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200 font-bold'"
            >
              {{ cashierStore.t('mix_chop') }}
            </button>
          </div>
        </div>

        <!-- Skeuomorphic Paper Roll Preview Container -->
        <div class="flex-grow overflow-y-auto p-4 flex justify-center bg-slate-50 border border-slate-200 rounded-2xl my-4">
          
          <div 
            v-if="selectedPayment"
            id="thermal-receipt-paper" 
            class="w-full max-w-[280px] bg-white text-slate-950 shadow-lg p-4 flex flex-col font-mono text-[10px] leading-relaxed relative border-t-2 border-slate-300 overflow-hidden select-none"
            style="color: #0c0a09;"
          >
            <!-- Skeuomorphic jagged tear bottom -->
            <div class="absolute bottom-0 left-0 right-0 h-2 bg-repeat-x pointer-events-none" style="background-image: radial-gradient(circle, transparent, transparent 50%, #ffffff 50%, #ffffff); background-size: 8px 8px; transform: translateY(4px); z-index: 10;"></div>

            <!-- Receipt Header -->
            <div class="text-center space-y-1 mb-3">
              <img :src="'/foodflow_logo.png'" class="mx-auto w-10 h-10 mb-1" style="filter: grayscale(100%);" alt="Logo" />
              <h4 class="text-xs font-black uppercase tracking-wider">{{ settingStore.branding.name || 'FoodFlow' }}</h4>
              <p class="text-[9px] text-slate-650 leading-tight">{{ settingStore.branding.address || 'Toshkent, O\'zbekiston' }}</p>
              <p class="text-[9px] text-slate-650">Tel: {{ settingStore.branding.phone || '+998 90 123 45 67' }}</p>
            </div>

            <!-- Double separator line -->
            <div class="border-b border-dashed border-slate-300 mb-2"></div>

            <!-- Metadata info -->
            <div class="space-y-0.5 mb-2">
              <div class="flex justify-between">
                <span>{{ cashierStore.t('chek_no') }}:</span>
                <span class="font-bold">#{{ String(selectedPayment.id).padStart(6, '0') }}</span>
              </div>
              <div class="flex justify-between">
                <span>{{ cashierStore.t('buyurtma_no') }}:</span>
                <span>{{ selectedPayment.order?.order_number }}</span>
              </div>
              <div class="flex justify-between">
                <span>{{ cashierStore.t('sana') }}:</span>
                <span>{{ formatDateTime(selectedPayment.created_at) }}</span>
              </div>
              <div class="flex justify-between">
                <span>{{ cashierStore.t('stol') }}:</span>
                <span class="font-bold">{{ selectedPayment.order?.table?.table_number ? selectedPayment.order.table.table_number.replace('Stol', cashierStore.t('stol')) : cashierStore.t('nomalum') }}</span>
              </div>
              <div class="flex justify-between">
                <span>{{ cashierStore.t('kassir') }}:</span>
                <span>{{ authStore.user?.name }}</span>
              </div>
              <div class="flex justify-between" v-if="selectedPayment.order?.waiter?.name">
                <span>{{ cashierStore.t('ofitsiant') }}:</span>
                <span>{{ selectedPayment.order.waiter.name }}</span>
              </div>
            </div>

            <!-- Dash Separator line -->
            <div class="border-b border-dashed border-slate-300 mb-2"></div>

            <!-- Pre-check Header if mode pre-check or mix -->
            <div v-if="printMode === 'pre-check' || printMode === 'mix'" class="text-center font-black text-[9px] uppercase tracking-wider bg-slate-100 py-0.5 mb-2 rounded">
              *** {{ cashierStore.t('navbat_cheki').toUpperCase() }} ***
            </div>
            <div v-if="printMode === 'pre-check' || printMode === 'mix'" class="text-center font-black text-2xl text-slate-900 tracking-wider mb-2">
              №{{ selectedPayment.order?.order_number }}
            </div>

            <!-- Items Table -->
            <div class="space-y-1 mb-2">
              <div class="grid grid-cols-12 font-bold border-b border-slate-200 pb-0.5 mb-1 text-[9px]">
                <span class="col-span-6">{{ cashierStore.t('nomi') }}</span>
                <span class="col-span-2 text-center">{{ cashierStore.t('soni') }}</span>
                <span class="col-span-4 text-right">{{ cashierStore.t('summa') }}</span>
              </div>
              <div 
                v-for="item in selectedPayment.order?.items" 
                :key="item.id"
                class="grid grid-cols-12 text-[9px] leading-tight"
              >
                <span class="col-span-6 truncate">{{ item.food?.name || '' }}</span>
                <span class="col-span-2 text-center">x{{ item.quantity }}</span>
                <span class="col-span-4 text-right">{{ formatCurrency(item.quantity * item.price) }}</span>
              </div>
            </div>

            <div class="border-b border-dashed border-slate-300 mb-2"></div>

            <!-- Financial Settlement details -->
            <div class="space-y-1 mb-3">
              <div class="flex justify-between">
                <span>{{ cashierStore.t('oraliq_jami') }}:</span>
                <span>{{ formatCurrency(getSubtotal()) }}</span>
              </div>
              <div class="flex justify-between" v-if="getDiscountAmount() > 0">
                <span>{{ cashierStore.t('chegirma') }}:</span>
                <span>-{{ formatCurrency(getDiscountAmount()) }}</span>
              </div>
              <div class="flex justify-between">
                <span>{{ cashierStore.t('xizmat_haqi') }} ({{ serviceChargeRate }}%):</span>
                <span>{{ formatCurrency(getServiceCharge()) }}</span>
              </div>
              <div class="flex justify-between">
                <span>{{ cashierStore.t('qqs') }} ({{ taxRate }}%):</span>
                <span>{{ formatCurrency(getTax()) }}</span>
              </div>

              <!-- Final payment split details (only on Final Check or mix) -->
              <template v-if="printMode === 'invoice' || printMode === 'mix'">
                <div class="border-b border-slate-200 my-1"></div>
                <div class="text-[9px] font-black uppercase text-slate-800 pb-0.5">{{ cashierStore.t('tolov_shakli') }}:</div>
                <div class="space-y-0.5 pl-1 text-[9px] text-slate-700">
                  <div class="flex justify-between" v-if="parseFloat(selectedPayment.cash_amount) > 0">
                    <span>{{ cashierStore.t('naqd') }}:</span>
                    <span>{{ formatCurrency(selectedPayment.cash_amount) }}</span>
                  </div>
                  <div class="flex justify-between" v-if="parseFloat(selectedPayment.card_amount) > 0">
                    <span>{{ cashierStore.t('karta') }}:</span>
                    <span>{{ formatCurrency(selectedPayment.card_amount) }}</span>
                  </div>
                  <div class="flex justify-between" v-if="parseFloat(selectedPayment.qr_amount) > 0">
                    <span>{{ cashierStore.t('qr_tolov') }}:</span>
                    <span>{{ formatCurrency(selectedPayment.qr_amount) }}</span>
                  </div>
                  <div class="flex justify-between" v-if="parseFloat(selectedPayment.bonus_used) > 0">
                    <span>{{ cashierStore.t('bonusdan') }}:</span>
                    <span>{{ formatCurrency(selectedPayment.bonus_used) }}</span>
                  </div>
                </div>
              </template>

              <div class="border-b border-dashed border-slate-300 my-1"></div>
              <div class="flex justify-between font-black text-xs">
                <span>{{ cashierStore.t('jami_to_lov') }}:</span>
                <span>{{ formatCurrency(selectedPayment.total_amount) }}</span>
              </div>
            </div>

            <!-- QR code dynamic image -->
            <div class="flex flex-col items-center justify-center my-3.5 space-y-1">
              <img :src="qrCodeUrl" class="w-20 h-20 border border-slate-200 p-1 bg-white" alt="QR Code" />
              <span class="text-[7px] text-slate-500 font-mono tracking-widest uppercase">{{ cashierStore.t('scan_to_verify') }}</span>
            </div>

            <!-- Greeting Footer -->
            <div class="text-center text-[8px] text-slate-650 mt-2 space-y-0.5 leading-tight">
              <p>{{ settingStore.settings.receipt_header || 'FoodFlow - Xizmatimizdan mamnunmisiz?' }}</p>
              <p class="font-black">{{ settingStore.settings.receipt_footer || 'Xaridingiz uchun rahmat! Yana keling!' }}</p>
            </div>

          </div>

          <div v-else class="flex flex-col items-center justify-center text-slate-400 font-extrabold text-sm space-y-2 text-center py-24 px-4 border-2 border-dashed border-slate-300 rounded-2xl w-full bg-slate-50">
            <Receipt class="w-10 h-10 text-slate-405" />
            <p class="text-xs font-semibold">{{ cashierStore.t('select_receipt_prompt') || 'Chop etish yoki virtual ko\'rish...' }}</p>
          </div>

        </div>

        <!-- Print Execution Button -->
        <button 
          v-if="selectedPayment"
          @click="executePrint"
          class="w-full shrink-0 py-3.5 rounded-2xl bg-indigo-600 hover:bg-indigo-750 font-black text-sm text-white tracking-wide shadow-md transition duration-200 flex items-center justify-center space-x-2"
        >
          <Printer class="w-4.5 h-4.5" />
          <span>{{ cashierStore.t('print_receipt') }}</span>
        </button>
      </div>
    </div>

    <!-- ACTUAL PRINT ONLY CONTENT (Hidden on screen, shown when printing) -->
    <div id="physical-thermal-receipt" class="print-only" v-if="selectedPayment && printMode !== 'kot'">
      <div class="ticket-ticket">
        <div class="ticket-center" style="margin-bottom: 5px;">
           <img :src="'/foodflow_logo.png'" style="width: 48px; height: 48px; display: block; margin: 0 auto; filter: grayscale(100%);" alt="Logo" />
        </div>
        <div class="ticket-center font-bold font-large">{{ settingStore.branding.name || 'FoodFlow' }}</div>
        <div class="ticket-center">{{ settingStore.branding.address || 'Toshkent, O\'zbekiston' }}</div>
        <div class="ticket-center">Tel: {{ settingStore.branding.phone || '+998 90 123 45 67' }}</div>
        
        <div class="ticket-divider"></div>

        <div>{{ cashierStore.t('chek_no') }}: #{{ String(selectedPayment.id).padStart(6, '0') }}</div>
        <div>{{ cashierStore.t('buyurtma_no') }}: {{ selectedPayment.order?.order_number }}</div>
        <div>{{ cashierStore.t('sana') }}: {{ formatDateTime(selectedPayment.created_at) }}</div>
        <div>{{ cashierStore.t('stol') }}: {{ selectedPayment.order?.table?.table_number }}</div>
        <div>{{ cashierStore.t('kassir') }}: {{ authStore.user?.name }}</div>
        <div v-if="selectedPayment.order?.waiter?.name">{{ cashierStore.t('ofitsiant') }}: {{ selectedPayment.order.waiter.name }}</div>

        <div class="ticket-divider"></div>

        <div v-if="printMode === 'pre-check' || printMode === 'mix'" class="ticket-center ticket-bold bg-gray-header">
          *** {{ cashierStore.t('navbat_cheki').toUpperCase() }} ***
        </div>
        <div v-if="printMode === 'pre-check' || printMode === 'mix'" class="ticket-center ticket-order-number">
          №{{ selectedPayment.order?.order_number }}
        </div>

        <table class="ticket-table">
          <thead>
            <tr>
              <th align="left">{{ cashierStore.t('nomi') }}</th>
              <th align="center">{{ cashierStore.t('soni') }}</th>
              <th align="right">{{ cashierStore.t('summa') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in selectedPayment.order?.items" :key="item.id">
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
            <span>{{ formatCurrency(getSubtotal()) }}</span>
          </div>
          <div class="flex-row font-italic" v-if="getDiscountAmount() > 0">
            <span>{{ cashierStore.t('chegirma') }}:</span>
            <span>-{{ formatCurrency(getDiscountAmount()) }}</span>
          </div>
          <div class="flex-row">
            <span>{{ cashierStore.t('xizmat_haqi') }} ({{ serviceChargeRate }}%):</span>
            <span>{{ formatCurrency(getServiceCharge()) }}</span>
          </div>
          <div class="flex-row">
            <span>{{ cashierStore.t('qqs') }} ({{ taxRate }}%):</span>
            <span>{{ formatCurrency(getTax()) }}</span>
          </div>

          <template v-if="printMode === 'invoice' || printMode === 'mix'">
            <div class="ticket-divider-thin"></div>
            <div class="ticket-bold">{{ cashierStore.t('tolov_shakli') }}:</div>
            <div class="flex-row pl-2" v-if="parseFloat(selectedPayment.cash_amount) > 0">
              <span>{{ cashierStore.t('naqd') }}:</span>
              <span>{{ formatCurrency(selectedPayment.cash_amount) }}</span>
            </div>
            <div class="flex-row pl-2" v-if="parseFloat(selectedPayment.card_amount) > 0">
              <span>{{ cashierStore.t('karta') }}:</span>
              <span>{{ formatCurrency(selectedPayment.card_amount) }}</span>
            </div>
            <div class="flex-row pl-2" v-if="parseFloat(selectedPayment.qr_amount) > 0">
              <span>{{ cashierStore.t('qr_tolov') }}:</span>
              <span>{{ formatCurrency(selectedPayment.qr_amount) }}</span>
            </div>
            <div class="flex-row pl-2" v-if="parseFloat(selectedPayment.bonus_used) > 0">
              <span>{{ cashierStore.t('bonusdan') }}:</span>
              <span>{{ formatCurrency(selectedPayment.bonus_used) }}</span>
            </div>
          </template>

          <div class="ticket-divider"></div>
          <div class="flex-row ticket-bold font-large">
            <span>{{ cashierStore.t('jami_to_lov') }}:</span>
            <span>{{ formatCurrency(selectedPayment.total_amount) }}</span>
          </div>
        </div>

        <div class="ticket-divider"></div>

        <!-- QR Code print-only version -->
        <div class="ticket-center" style="margin: 12px 0;">
          <img :src="qrCodeUrl" style="width: 2.2cm; height: 2.2cm; display: block; margin: 0 auto;" alt="QR Code" />
          <div style="font-size: 7.5pt; font-family: monospace; margin-top: 4px; text-transform: uppercase;">{{ cashierStore.t('scan_to_verify') }}</div>
        </div>

        <div class="ticket-divider"></div>

        <div class="ticket-center ticket-footer-text">
          <p>{{ settingStore.settings.receipt_header || 'FoodFlow - Xizmatimizdan mamnunmisiz?' }}</p>
          <p class="ticket-bold">{{ settingStore.settings.receipt_footer || 'Xaridingiz uchun rahmat! Yana keling!' }}</p>
        </div>
      </div>
    </div>

    <!-- MODAL: ADD NEW PAYMENT (BILLING CHECKOUT & MANUAL ENTRY) -->
    <Transition name="fade">
      <div 
        v-if="showPaymentModal" 
        class="fixed inset-0 z-50 backdrop-blur-md bg-slate-900/30 flex items-center justify-center p-6 no-print"
        @click.self="showPaymentModal = false"
      >
        <div class="w-full max-w-md backdrop-blur-xl bg-white border border-slate-200 rounded-3xl p-6 shadow-2xl space-y-5 animate-scaleIn text-left text-slate-900 max-h-[90vh] overflow-y-auto">

          <div class="flex justify-between items-center border-b border-slate-200 pb-3">
            <h3 class="text-base font-bold text-slate-900 flex items-center space-x-2">
              <Plus class="w-5 h-5 text-indigo-600" />
              <span>{{ cashierStore.t('yangi_tolov_kiritish') }}</span>
            </h3>
            <button @click="showPaymentModal = false" class="p-1 rounded-lg bg-slate-100 text-slate-500 hover:text-slate-900 transition">
              <X class="w-4 h-4" />
            </button>
          </div>

          <div class="space-y-4">
            <!-- 1. Select Table (Any table can be selected) -->
            <div class="space-y-1.5">
              <label class="text-xxs text-slate-500 font-bold uppercase tracking-wider">{{ cashierStore.t('stol') }}</label>
              <select
                v-model="newPaymentForm.table_id"
                @change="handleTableSelect"
                class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 focus:border-indigo-500 text-sm text-slate-900 focus:outline-none transition"
              >
                <option value="" disabled>{{ cashierStore.t('select_table_placeholder') }}</option>
                <option
                  v-for="t in allTablesList"
                  :key="t.id"
                  :value="t.id"
                >
                  {{ t.table_number ? t.table_number.replace('Stol', cashierStore.t('stol')) : '' }} ({{ t.status === 'occupied' ? cashierStore.t('table_occupied') : cashierStore.t('table_free') }})
                </option>
              </select>
            </div>

            <!-- Loader or details -->
            <div v-if="loadingOrderDetails" class="py-6 flex flex-col items-center justify-center space-y-2">
              <RotateCw class="w-6 h-6 text-indigo-500 animate-spin" />
              <span class="text-xxs text-slate-500">{{ cashierStore.t('order_loading') }}</span>
            </div>

            <template v-else-if="selectedOrderDetails">

              <!-- Food items adding section (Always available, lets cashier add items manually too!) -->
              <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                <div class="flex justify-between items-center">
                  <span class="text-xxs text-slate-500 font-bold uppercase tracking-wider">{{ cashierStore.t('buyurtma_tarkibi') }}</span>
                  <button
                    type="button"
                    @click="showAddFoodForm = true"
                    class="px-2.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-[10px] font-bold text-white transition flex items-center space-x-1"
                  >
                    <Plus class="w-3.5 h-3.5" />
                    <span>{{ cashierStore.t('taom_qo_shish') }}</span>
                  </button>
                </div>

                <!-- Add food inline dropdown -->
                <div v-if="showAddFoodForm" class="p-3 rounded-xl bg-white border border-indigo-200 space-y-3">
                  <div class="space-y-1.5">
                    <label class="text-[10px] text-slate-500 font-bold uppercase">{{ cashierStore.t('taom_tanlang') }}</label>
                    <select
                      v-model="addFoodForm.food_id"
                      class="w-full px-3 py-2 rounded-lg bg-white border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-indigo-500"
                    >
                      <option value="" disabled>{{ cashierStore.t('select_food_placeholder') }}</option>
                      <option v-for="f in allFoods" :key="f.id" :value="f.id">
                        {{ f.name }} - {{ formatCurrency(f.price) }}
                      </option>
                    </select>
                  </div>

                  <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                      <label class="text-[10px] text-slate-500 font-bold uppercase">{{ cashierStore.t('soni') }}</label>
                      <input
                        v-model.number="addFoodForm.quantity"
                        type="number"
                        min="1"
                        class="w-full px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs text-slate-900 font-mono focus:outline-none focus:border-indigo-500"
                      />
                    </div>
                    <div class="flex items-end justify-end space-x-2">
                      <button @click="showAddFoodForm = false" class="px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-[10px] font-bold text-slate-600">{{ cashierStore.t('bekor_qilish') }}</button>
                      <button @click="addFoodToOrder" class="px-3 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-[10px] font-bold text-white">{{ cashierStore.t('taom_qo_shish') }}</button>
                    </div>
                  </div>
                </div>

                <!-- Items list -->
                <div class="divide-y divide-slate-200 max-h-32 overflow-y-auto space-y-1.5 pr-1">
                  <div
                    v-for="(item, index) in selectedOrderDetails.items"
                    :key="index"
                    class="flex justify-between items-center text-xs pt-1.5"
                  >
                    <span class="text-slate-600 truncate max-w-[200px]">{{ item.food?.name || cashierStore.t('unknown_item') }} <span class="text-slate-400">x{{ item.quantity }}</span></span>
                    <div class="flex items-center space-x-3">
                      <span class="font-semibold text-slate-900 font-mono">{{ formatCurrency(item.quantity * (item.price || item.food?.price)) }}</span>
                      <button @click="removeFoodFromOrder(index)" class="text-rose-500 hover:text-rose-600 transition" :title="settingsStore.t('delete')">
                        <X class="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </div>
                  <div v-if="!selectedOrderDetails.items || selectedOrderDetails.items.length === 0" class="text-center py-4 text-xs text-slate-500">
                    {{ cashierStore.t('no_items_yet') }}
                  </div>
                </div>

                <!-- Financial details calculation -->
                <div class="border-t border-slate-200 pt-3 space-y-1.5 text-xs font-semibold">
                  <div class="flex justify-between">
                    <span class="text-slate-500">{{ cashierStore.t('oraliq_jami') }}:</span>
                    <span class="font-mono text-slate-900">{{ formatCurrency(orderCalculations.subtotal) }}</span>
                  </div>
                  <div class="flex justify-between text-emerald-400" v-if="orderCalculations.discount > 0">
                    <span>{{ cashierStore.t('chegirma') }}:</span>
                    <span class="font-mono">-{{ formatCurrency(orderCalculations.discount) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-slate-500">{{ cashierStore.t('xizmat_haqi') }} ({{ serviceChargeRate }}%):</span>
                    <span class="font-mono text-slate-900">{{ formatCurrency(orderCalculations.service) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-slate-500">{{ cashierStore.t('qqs') }} ({{ taxRate }}%):</span>
                    <span class="font-mono text-slate-900">{{ formatCurrency(orderCalculations.tax) }}</span>
                  </div>
                  <div class="border-t border-dashed border-slate-200 my-1"></div>
                  <div class="flex justify-between text-sm font-black text-indigo-600">
                    <span>{{ cashierStore.t('jami_to_lov') }}:</span>
                    <span class="font-mono text-base">{{ formatCurrency(orderCalculations.total) }}</span>
                  </div>
                </div>
              </div>

              <!-- 2. Select Customer (Loyalty) -->
              <div class="space-y-1.5">
                <div class="flex justify-between items-center">
                  <label class="text-xxs text-slate-500 font-bold uppercase tracking-wider">{{ cashierStore.t('customer_loyalty_label') }}</label>
                  <span class="text-[10px] text-indigo-600 font-bold" v-if="selectedCustomer">
                    {{ cashierStore.t('bonus_label') }} {{ formatCurrency(selectedCustomer.bonus_balance) }}
                  </span>
                </div>
                <select
                  v-model="newPaymentForm.customer_id"
                  @change="handleCustomerSelect"
                  class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 focus:border-indigo-500 text-sm text-slate-900 focus:outline-none transition"
                >
                  <option :value="null">{{ cashierStore.t('mehmon') }}</option>
                  <option
                    v-for="c in customers"
                    :key="c.id"
                    :value="c.id"
                  >
                    {{ c.name }} ({{ c.phone }})
                  </option>
                </select>
              </div>

              <!-- 3. Payment Method -->
              <div class="space-y-1.5">
                <label class="text-xxs text-slate-500 font-bold uppercase tracking-wider">{{ cashierStore.t('tolov_shakli') }}</label>
                <select
                  v-model="newPaymentForm.payment_method"
                  @change="handlePaymentMethodChange"
                  class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 focus:border-indigo-500 text-sm text-slate-900 focus:outline-none transition"
                >
                  <option value="cash">{{ cashierStore.t('naqd') }}</option>
                  <option value="card">{{ cashierStore.t('karta') }}</option>
                  <option value="qr">{{ cashierStore.t('qr_tolov') }}</option>
                  <option value="mixed">{{ cashierStore.t('mixed_payment_option') }}</option>
                </select>
              </div>

              <!-- Split Amount Inputs -->
              <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 space-y-3.5 text-xs">
                <!-- Cash Amount -->
                <div class="grid grid-cols-3 items-center gap-2" v-if="newPaymentForm.payment_method === 'cash' || newPaymentForm.payment_method === 'mixed'">
                  <span class="text-slate-500 font-semibold">{{ cashierStore.t('cash_amount_label') }}</span>
                  <input
                    v-model.number="newPaymentForm.cash_amount"
                    type="number"
                    placeholder="0"
                    :disabled="newPaymentForm.payment_method === 'cash'"
                    class="col-span-2 px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-900 text-xs font-mono focus:outline-none focus:border-indigo-500"
                  />
                </div>
                <!-- Card Amount -->
                <div class="grid grid-cols-3 items-center gap-2" v-if="newPaymentForm.payment_method === 'card' || newPaymentForm.payment_method === 'mixed'">
                  <span class="text-slate-500 font-semibold">{{ cashierStore.t('card_amount_label') }}</span>
                  <input
                    v-model.number="newPaymentForm.card_amount"
                    type="number"
                    placeholder="0"
                    :disabled="newPaymentForm.payment_method === 'card'"
                    class="col-span-2 px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-900 text-xs font-mono focus:outline-none focus:border-indigo-500"
                  />
                </div>
                <!-- QR Amount -->
                <div class="grid grid-cols-3 items-center gap-2" v-if="newPaymentForm.payment_method === 'qr' || newPaymentForm.payment_method === 'mixed'">
                  <span class="text-slate-500 font-semibold">{{ cashierStore.t('qr_amount_label') }}</span>
                  <input
                    v-model.number="newPaymentForm.qr_amount"
                    type="number"
                    placeholder="0"
                    :disabled="newPaymentForm.payment_method === 'qr'"
                    class="col-span-2 px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-900 text-xs font-mono focus:outline-none focus:border-indigo-500"
                  />
                </div>
                <!-- Bonus used -->
                <div class="grid grid-cols-3 items-center gap-2" v-if="selectedCustomer && selectedCustomer.bonus_balance > 0">
                  <span class="text-slate-500 font-semibold">{{ cashierStore.t('bonus_amount_label') }}</span>
                  <input
                    v-model.number="newPaymentForm.bonus_used"
                    type="number"
                    placeholder="0"
                    :max="Math.min(selectedCustomer.bonus_balance, orderCalculations.total)"
                    class="col-span-2 px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-900 text-xs font-mono focus:outline-none focus:border-indigo-500"
                  />
                </div>
              </div>

              <!-- Validation Balance warning -->
              <div
                v-if="!isSplitAmountValid && newPaymentForm.payment_method === 'mixed'"
                class="p-2.5 rounded-xl bg-amber-50 border border-amber-200 text-xxs text-amber-700 font-semibold"
              >
                {{ cashierStore.t('split_warning_prefix') }} {{ formatCurrency(totalEnteredAmount) }} ({{ cashierStore.t('split_warning_total') }} {{ formatCurrency(orderCalculations.total) }}).
              </div>
            </template>
          </div>

          <div class="flex justify-end space-x-2 pt-2 border-t border-slate-200">
            <button
              v-if="selectedOrderDetails && selectedOrderDetails.id"
              type="button"
              @click="triggerVoidFlow(selectedOrderDetails.id)"
              class="px-4 py-2 bg-rose-50 hover:bg-rose-500 border border-rose-200 rounded-xl text-xs font-semibold text-rose-600 hover:text-white transition mr-auto"
            >
              {{ cashierStore.t('void_order_button') }}
            </button>
            <button
              v-if="selectedOrderDetails && selectedOrderDetails.items && selectedOrderDetails.items.length > 0"
              type="button"
              @click="printPreCheckForActiveOrder"
              class="px-4 py-2 bg-slate-100 hover:bg-slate-200 border border-slate-200 rounded-xl text-xs font-semibold text-slate-600 transition flex items-center space-x-1.5"
            >
              <Printer class="w-3.5 h-3.5" />
              <span>{{ cashierStore.t('navbat_cheki') }}</span>
            </button>
            <button @click="showPaymentModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 border border-slate-200 rounded-xl text-xs font-semibold text-slate-600 transition">
              {{ cashierStore.t('bekor_qilish') }}
            </button>
            <button
              @click="submitPayment"
              :disabled="loadingSubmit || !selectedOrderDetails || selectedOrderDetails.items.length === 0 || (newPaymentForm.payment_method === 'mixed' && !isSplitAmountValid)"
              class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold transition disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ loadingSubmit ? cashierStore.t('paying_in_progress') : cashierStore.t('tolovni_yakunlash') }}
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- COMPULSORY VOID ORDER MODAL OVERLAY -->
    <Transition name="fade">
      <div 
        v-if="showVoidModal" 
        class="fixed inset-0 z-[60] backdrop-blur-md bg-slate-900/40 flex items-center justify-center p-6"
        @click.self="showVoidModal = false"
      >
        <div class="w-full max-w-sm backdrop-blur-xl bg-white border border-slate-200 rounded-3xl p-6 shadow-2xl space-y-4 text-left text-slate-900 animate-scaleIn">
          <div class="flex justify-between items-center border-b border-slate-200 pb-2">
            <h3 class="text-sm font-bold text-rose-600 flex items-center space-x-1.5">
              <span>⚠️ {{ cashierStore.t('void_modal_title') }}</span>
            </h3>
            <button @click="showVoidModal = false" class="text-slate-500 hover:text-slate-900">
              <X class="w-4 h-4" />
            </button>
          </div>

          <div class="space-y-3.5">
            <p class="text-xxs text-slate-500 leading-normal">
              {{ cashierStore.t('void_modal_desc') }}
            </p>

            <div class="space-y-1.5">
              <label class="text-xxs text-slate-500 font-bold uppercase tracking-wider">{{ cashierStore.t('void_reason_label') }}</label>
              <select
                v-model="voidForm.reasonSelect"
                class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-rose-500"
              >
                <option value="">{{ cashierStore.t('void_reason_placeholder') }}</option>
                <option value="Mijoz shoshilganligi sababli">{{ cashierStore.t('void_reason_1') }}</option>
                <option value="Taom sifati tufayli">{{ cashierStore.t('void_reason_2') }}</option>
                <option value="Operator xatosi">{{ cashierStore.t('void_reason_3') }}</option>
                <option value="custom">{{ cashierStore.t('void_reason_custom') }}</option>
              </select>
            </div>

            <div class="space-y-1.5">
              <label class="text-xxs text-slate-500 font-bold uppercase tracking-wider">{{ cashierStore.t('void_extra_note_label') }}</label>
              <textarea
                v-model="voidForm.reasonText"
                rows="2"
                :placeholder="cashierStore.t('void_extra_note_placeholder')"
                class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-rose-500 transition resize-none"
              ></textarea>
            </div>
          </div>

          <div class="flex justify-end space-x-2 pt-2 border-t border-slate-200">
            <button @click="showVoidModal = false" class="px-3.5 py-1.5 bg-slate-100 border border-slate-200 rounded-xl text-xxs font-semibold text-slate-600">
              {{ cashierStore.t('back_button') }}
            </button>
            <button
              @click="submitVoidOrder"
              :disabled="loadingVoid || !isVoidReasonProvided"
              class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xxs font-semibold transition disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ loadingVoid ? cashierStore.t('voiding_in_progress') : cashierStore.t('confirm_button') }}
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <PrintKot v-if="printMode === 'kot' && selectedPayment" :order="selectedPayment.order" />
  </div>
</template>

<script setup>
import { useSettingsStore } from '@/stores/settings';
const settingsStore = useSettingsStore();
import { ref, onMounted, computed, watch, markRaw } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { RotateCw, Printer, Receipt, FileText, FileSpreadsheet, Plus, X, HelpCircle, CheckCircle, ChefHat } from 'lucide-vue-next';
import { useReceiptsStore } from '@/stores/receipts';
import PrintKot from '@/components/PrintKot.vue';
import { useAuthStore } from '@/stores/auth';
import { useSettingStore } from '@/stores/settings';
import { useCashierTablesStore } from '@/stores/cashierTables';
import { useCashierStore } from '@/stores/cashier';

const receiptsStore = useReceiptsStore();
const authStore = useAuthStore();
const settingStore = useSettingStore();
const cashierTablesStore = useCashierTablesStore();
const cashierStore = useCashierStore();
const route = useRoute();
const router = useRouter();

// States
const selectedPayment = ref(null);
const printMode = ref('invoice'); // 'pre-check' | 'invoice' | 'mix'
const activeDropdownId = ref(null);

// Modal states
const showPaymentModal = ref(false);
const allTablesList = ref([]);
const allFoods = ref([]);
const customers = ref([]);
const selectedOrderDetails = ref(null);
const loadingOrderDetails = ref(false);
const loadingSubmit = ref(false);
const selectedCustomer = ref(null);

// Manual item entry states
const showAddFoodForm = ref(false);
const addFoodForm = ref({
  food_id: '',
  quantity: 1
});

// Void modal states
const showVoidModal = ref(false);
const loadingVoid = ref(false);
const voidTargetOrderId = ref(null);
const voidForm = ref({
  reasonSelect: '',
  reasonText: ''
});

const isVoidReasonProvided = computed(() => {
  const finalReason = voidForm.value.reasonSelect === 'custom' 
    ? voidForm.value.reasonText.trim()
    : voidForm.value.reasonSelect + (voidForm.value.reasonText.trim() ? ': ' + voidForm.value.reasonText.trim() : '');
  return finalReason.trim().length >= 5;
});

const triggerVoidFlow = (orderId) => {
  voidTargetOrderId.value = orderId;
  voidForm.value = {
    reasonSelect: '',
    reasonText: ''
  };
  showVoidModal.value = true;
};

const submitVoidOrder = async () => {
  if (!voidTargetOrderId.value) return;

  const finalReason = voidForm.value.reasonSelect === 'custom' 
    ? voidForm.value.reasonText.trim()
    : voidForm.value.reasonSelect + (voidForm.value.reasonText.trim() ? ': ' + voidForm.value.reasonText.trim() : '');

  loadingVoid.value = true;
  try {
    const token = localStorage.getItem('vrestro_token');
    const response = await fetch(`/api/cashier/orders/${voidTargetOrderId.value}/void`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        cancellation_reason: finalReason
      })
    });

    const result = await response.json();
    if (!response.ok) {
      throw new Error(result.message || cashierStore.t('void_error'));
    }

    alert(cashierStore.t('void_success'));
    showVoidModal.value = false;
    showPaymentModal.value = false;

    // Refresh cashier views
    receiptsStore.fetchPayments();
    cashierTablesStore.fetchCashierTables();
  } catch (error) {
    alert(error.message);
  } finally {
    loadingVoid.value = false;
  }
};

// Form
const newPaymentForm = ref({
  table_id: '',
  customer_id: null,
  payment_method: 'cash',
  cash_amount: 0,
  card_amount: 0,
  qr_amount: 0,
  bonus_used: 0
});

// Modal dialog state for confirmations
const modal = ref({
  show: false,
  type: 'info',
  title: '',
  message: '',
  icon: null
});

// Settings defaults
const taxRate = computed(() => settingStore.settings.tax_rate || 12);
const serviceChargeRate = computed(() => settingStore.settings.service_charge_rate || 10);

const qrCodeUrl = computed(() => {
  const name = settingStore.branding?.name || 'FoodFlow';
  const address = settingStore.branding?.address || "Toshkent, O'zbekiston";
  return `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(name + ' - ' + address)}`;
});

const selectPayment = (payment) => {
  selectedPayment.value = payment;
  activeDropdownId.value = null;
};

const togglePrintDropdown = (id) => {
  if (activeDropdownId.value === id) {
    activeDropdownId.value = null;
  } else {
    activeDropdownId.value = id;
  }
};

const refreshReceipts = () => {
  receiptsStore.fetchPayments();
};

const triggerDirectPrint = (payment, mode) => {
  selectedPayment.value = payment;
  printMode.value = mode;
  activeDropdownId.value = null;
  
  setTimeout(() => {
    executePrint();
  }, 150);
};

// Totals Helper calculations
const getSubtotal = () => {
  if (!selectedPayment.value || !selectedPayment.value.order) return 0;
  const items = selectedPayment.value.order.items || [];
  return items.reduce((acc, item) => acc + (parseFloat(item.price) * item.quantity), 0);
};

const getDiscountAmount = () => {
  if (!selectedPayment.value || !selectedPayment.value.order) return 0;
  return parseFloat(selectedPayment.value.order.discount_amount) || 0;
};

const getServiceCharge = () => {
  const sub = getSubtotal() - getDiscountAmount();
  return sub * (parseFloat(serviceChargeRate.value) / 100);
};

const getTax = () => {
  const sub = getSubtotal() - getDiscountAmount();
  return sub * (parseFloat(taxRate.value) / 100);
};

// Calculations for active order in new payment
const orderCalculations = computed(() => {
  if (!selectedOrderDetails.value) return { subtotal: 0, discount: 0, service: 0, tax: 0, total: 0 };
  const items = selectedOrderDetails.value.items || [];
  const subtotal = items.reduce((acc, it) => acc + (parseFloat(it.price || it.food?.price || 0) * it.quantity), 0);
  const discount = parseFloat(selectedOrderDetails.value.discount_amount) || 0;
  const service = (subtotal - discount) * (parseFloat(serviceChargeRate.value) / 100);
  const tax = (subtotal - discount) * (parseFloat(taxRate.value) / 100);
  const total = subtotal - discount + service + tax;
  return { subtotal, discount, service, tax, total };
});

const totalEnteredAmount = computed(() => {
  const form = newPaymentForm.value;
  return (parseFloat(form.cash_amount) || 0) + 
         (parseFloat(form.card_amount) || 0) + 
         (parseFloat(form.qr_amount) || 0) + 
         (parseFloat(form.bonus_used) || 0);
});

const isSplitAmountValid = computed(() => {
  return Math.abs(totalEnteredAmount.value - orderCalculations.value.total) < 1;
});

// Formatting helpers
const formatCurrency = (val) => {
  if (val === undefined || val === null) return '0 UZS';
  return new Intl.NumberFormat('uz-UZ').format(Math.round(val)) + ' UZS';
};

const formatDateTime = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleString('uz-UZ', { 
    day: '2-digit', 
    month: '2-digit', 
    year: 'numeric',
    hour: '2-digit', 
    minute: '2-digit',
    hour12: false
  });
};

const executePrint = async () => {
  if (!selectedPayment.value) return;
  window.print();
  if (printMode.value === 'pre-check') {
    await receiptsStore.markOrderAsPrinted(selectedPayment.value.order_id);
  } else {
    await receiptsStore.markPaymentAsPrinted(selectedPayment.value.id);
  }
};

// Print a pre-check (navbat cheki) for the order currently being paid,
// BEFORE the payment is actually submitted - lets the cashier show the
// customer what they ordered and owe before charging them. Builds a
// payment-shaped object out of the live order so the existing pre-check
// template (which reads from selectedPayment) can render it unchanged.
const printPreCheckForActiveOrder = () => {
  if (!selectedOrderDetails.value) return;
  selectedPayment.value = {
    id: selectedOrderDetails.value.id,
    order_id: selectedOrderDetails.value.id,
    created_at: new Date().toISOString(),
    total_amount: orderCalculations.value.total,
    cash_amount: 0,
    card_amount: 0,
    qr_amount: 0,
    bonus_used: 0,
    order: selectedOrderDetails.value
  };
  printMode.value = 'pre-check';
  setTimeout(() => {
    executePrint();
  }, 150);
};

// Modal Operations
const openNewPaymentModal = async () => {
  // Reset form
  newPaymentForm.value = {
    table_id: '',
    customer_id: null,
    payment_method: 'cash',
    cash_amount: 0,
    card_amount: 0,
    qr_amount: 0,
    bonus_used: 0
  };
  selectedOrderDetails.value = null;
  selectedCustomer.value = null;
  showAddFoodForm.value = false;
  
  // Load all tables
  try {
    const authStore = useAuthStore();
    const res = await fetch('/api/tables', {
      headers: { 'Authorization': `Bearer ${authStore.token}`, 'Accept': 'application/json' }
    });
    if (res.ok) {
      allTablesList.value = await res.json();
    }
  } catch (err) {
    console.error(err);
  }

  // Load menu foods
  try {
    const authStore = useAuthStore();
    const res = await fetch('/api/menu/foods', {
      headers: { 'Authorization': `Bearer ${authStore.token}`, 'Accept': 'application/json' }
    });
    if (res.ok) {
      allFoods.value = await res.json();
    }
  } catch (err) {
    console.error(err);
  }
  
  // Load customers
  try {
    const authStore = useAuthStore();
    const res = await fetch('/api/customers', {
      headers: { 'Authorization': `Bearer ${authStore.token}`, 'Accept': 'application/json' }
    });
    if (res.ok) {
      const data = await res.json();
      customers.value = data.data || data;
    }
  } catch (err) {
    console.error('Mijozlarni yuklashda xatolik: ' + err.message);
  }
  
  showPaymentModal.value = true;
};

const handleTableSelect = async () => {
  const table = allTablesList.value.find(t => t.id === newPaymentForm.value.table_id);
  if (!table) return;

  if (table.status === 'occupied') {
    // Search for active order
    // In cashierTables api, we fetch cashier stollar list that contains active order_id. Let's find it.
    await cashierTablesStore.fetchCashierTables();
    const activeTable = cashierTablesStore.tables.find(t => t.id === table.id);
    const activeOrderId = activeTable ? activeTable.order_id : null;

    if (activeOrderId) {
      loadingOrderDetails.value = true;
      selectedOrderDetails.value = null;
      try {
        const authStore = useAuthStore();
        const res = await fetch(`/api/orders/${activeOrderId}`, {
          headers: { 'Authorization': `Bearer ${authStore.token}`, 'Accept': 'application/json' }
        });
        if (res.ok) {
          selectedOrderDetails.value = await res.json();
          handlePaymentMethodChange();
        }
      } catch (err) {
        console.error(err);
      } finally {
        loadingOrderDetails.value = false;
      }
      return;
    }
  }

  // If table is empty or doesn't have active order_id, initialize an empty manual order layout
  selectedOrderDetails.value = {
    table_id: table.id,
    items: []
  };
  handlePaymentMethodChange();
};

const addFoodToOrder = () => {
  if (!addFoodForm.value.food_id || addFoodForm.value.quantity < 1) return;
  const food = allFoods.value.find(f => f.id === addFoodForm.value.food_id);
  if (!food) return;

  // Add to items
  selectedOrderDetails.value.items.push({
    food_id: food.id,
    quantity: addFoodForm.value.quantity,
    price: food.price,
    food: {
      name: food.name,
      price: food.price
    }
  });

  // Reset food add form
  addFoodForm.value = { food_id: '', quantity: 1 };
  showAddFoodForm.value = false;
  handlePaymentMethodChange();
};

const removeFoodFromOrder = (index) => {
  selectedOrderDetails.value.items.splice(index, 1);
  handlePaymentMethodChange();
};

const handleCustomerSelect = () => {
  selectedCustomer.value = customers.value.find(c => c.id === newPaymentForm.value.customer_id) || null;
  newPaymentForm.value.bonus_used = 0;
};

const handlePaymentMethodChange = () => {
  const method = newPaymentForm.value.payment_method;
  const remaining = Math.max(0, orderCalculations.value.total - (parseFloat(newPaymentForm.value.bonus_used) || 0));

  // Reset all
  newPaymentForm.value.cash_amount = 0;
  newPaymentForm.value.card_amount = 0;
  newPaymentForm.value.qr_amount = 0;

  if (method === 'cash') {
    newPaymentForm.value.cash_amount = remaining;
  } else if (method === 'card') {
    newPaymentForm.value.card_amount = remaining;
  } else if (method === 'qr') {
    newPaymentForm.value.qr_amount = remaining;
  }
};

// Keep the active single-method amount in sync when a bonus is entered/changed.
watch(() => newPaymentForm.value.bonus_used, (newBonus) => {
  const method = newPaymentForm.value.payment_method;
  if (method === 'mixed') return;
  const remaining = Math.max(0, orderCalculations.value.total - (parseFloat(newBonus) || 0));
  if (method === 'cash') newPaymentForm.value.cash_amount = remaining;
  else if (method === 'card') newPaymentForm.value.card_amount = remaining;
  else if (method === 'qr') newPaymentForm.value.qr_amount = remaining;
});

const submitPayment = async () => {
  if (!selectedOrderDetails.value || selectedOrderDetails.value.items.length === 0) return;
  
  loadingSubmit.value = true;
  try {
    let orderId = selectedOrderDetails.value.id;

    // 1. If it's a new manual order (no order ID yet), we must create it first
    if (!orderId) {
      const orderPayload = {
        table_id: selectedOrderDetails.value.table_id || null,
        waiter_id: authStore.user?.id || null,
        items: selectedOrderDetails.value.items.map(it => ({
          food_id: it.food_id,
          quantity: it.quantity
        }))
      };

      const authStoreRef = useAuthStore();
      const orderRes = await fetch('/api/orders', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Bearer ${authStoreRef.token}`
        },
        body: JSON.stringify(orderPayload)
      });

      const orderData = await orderRes.json();
      if (!orderRes.ok) {
        throw new Error(orderData.message || cashierStore.t('order_create_error'));
      }
      orderId = orderData.order.id;
    }

    // 2. Submit payment
    const payload = {
      order_id: orderId,
      customer_id: newPaymentForm.value.customer_id,
      payment_method: newPaymentForm.value.payment_method,
      cash_amount: newPaymentForm.value.cash_amount,
      card_amount: newPaymentForm.value.card_amount,
      qr_amount: newPaymentForm.value.qr_amount,
      bonus_used: newPaymentForm.value.bonus_used
    };
    
    const payment = await receiptsStore.processPayment(payload);
    
    // Select newly created payment to preview
    selectedPayment.value = receiptsStore.payments.find(p => p.id === payment.id) || payment;
    printMode.value = 'invoice';
    showPaymentModal.value = false;
    
    // Refresh tables in layout
    cashierTablesStore.fetchCashierTables();
    
    // Show success dialog
    modal.value = {
      show: true,
      type: 'success',
      title: cashierStore.t('payment_success_title'),
      message: cashierStore.t('payment_success_message'),
      icon: markRaw(CheckCircle)
    };
  } catch (err) {
    alert(err.message);
  } finally {
    loadingSubmit.value = false;
  }
};

const closeModal = () => {
  modal.value.show = false;
};

onMounted(async () => {
  settingStore.fetchSettings();
  await receiptsStore.fetchPayments();

  // Coming straight from a just-completed POS checkout (CashierOrder.vue) -
  // auto-select and print that receipt instead of leaving the cashier to
  // find it in the list themselves.
  const printPaymentId = route.query.print;
  if (printPaymentId) {
    const payment = receiptsStore.payments.find(p => String(p.id) === String(printPaymentId));
    if (payment) {
      triggerDirectPrint(payment, 'invoice');
    }
    router.replace({ path: '/cashier/receipts' });
  }
});
</script>

<style>
/* CSS styles targeting screen preview */
.animate-scaleIn {
  animation: scaleIn 0.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes scaleIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}

/* Hide print content on standard screens */
.print-only {
  display: none;
}

/* Print CSS Configurations specifically for 80mm roll */
@media print {
  body * {
    visibility: hidden;
    background: #ffffff !important;
    color: #000000 !important;
  }
  
  .no-print, .no-print * {
    display: none !important;
    visibility: hidden !important;
  }

  .print-only, .print-only * {
    visibility: visible;
  }

  #physical-thermal-receipt {
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
    font-size: 11pt;
    line-height: 1.3;
    width: 100%;
    color: #000000;
  }

  .ticket-center {
    text-align: center;
  }

  .ticket-bold {
    font-weight: bold;
  }

  .font-large {
    font-size: 13pt;
  }

  .font-italic {
    font-style: italic;
  }

  .ticket-divider {
    border-bottom: 2px dashed #000000;
    margin: 8px 0;
  }

  .ticket-divider-thin {
    border-bottom: 1px solid #000000;
    margin: 4px 0;
  }

  .bg-gray-header {
    background-color: #f3f4f6 !important;
    padding: 2px 0;
  }

  .ticket-order-number {
    font-size: 22pt;
    font-weight: bold;
    letter-spacing: 1px;
    margin: 4px 0 6px;
  }

  .ticket-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 4px;
    font-size: 10pt;
  }

  .ticket-table th {
    border-bottom: 1px solid #000000;
    font-weight: bold;
    padding-bottom: 2px;
  }

  .ticket-table td {
    padding: 3px 0;
  }

  .ticket-totals {
    font-size: 10.5pt;
  }

  .flex-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 2px;
  }

  .pl-2 {
    padding-left: 10px;
  }

  .ticket-footer-text {
    font-size: 9pt;
    margin-top: 8px;
  }
}
</style>
