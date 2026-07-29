package com.example.myapplication.cashier

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.myapplication.data.api.RetrofitClient
import com.example.myapplication.data.models.*
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch

data class CashierState(
    val tables: List<TableModel> = emptyList(),
    val selectedOrder: OrderModel? = null,
    val isLoading: Boolean = false,
    val error: String? = null,
    val toast: String? = null
)

class CashierViewModel : ViewModel() {
    private val api = RetrofitClient.api
    private val _state = MutableStateFlow(CashierState())
    val state: StateFlow<CashierState> = _state

    fun loadTables() {
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoading = true, error = null)
            try {
                val tables = api.getCashierTables().body() ?: emptyList()
                _state.value = _state.value.copy(tables = tables, isLoading = false)
            } catch (e: Exception) {
                _state.value = _state.value.copy(isLoading = false, error = e.localizedMessage)
            }
        }
    }

    fun loadOrder(orderId: Int) {
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoading = true)
            try {
                val order = api.getOrderById(orderId).body()
                _state.value = _state.value.copy(selectedOrder = order, isLoading = false)
            } catch (e: Exception) {
                _state.value = _state.value.copy(isLoading = false, error = "Buyurtma yuklanmadi.")
            }
        }
    }

    fun processPayment(orderId: Int, method: String, amount: Double) {
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoading = true)
            try {
                val body = buildMap<String, Any> {
                    put("order_id", orderId)
                    put("payment_method", method)
                    when (method) {
                        "cash" -> put("cash_amount", amount)
                        "card" -> put("card_amount", amount)
                        "qr"   -> put("qr_amount",   amount)
                    }
                }
                val resp = api.processPayment(body)
                if (resp.isSuccessful) {
                    _state.value = _state.value.copy(
                        isLoading = false, selectedOrder = null,
                        toast = "To'lov muvaffaqiyatli qabul qilindi! ✓"
                    )
                    loadTables()
                } else {
                    _state.value = _state.value.copy(isLoading = false, error = "To'lovda xatolik.")
                }
            } catch (e: Exception) {
                _state.value = _state.value.copy(isLoading = false, error = e.localizedMessage)
            }
        }
    }

    fun clearOrder()  = _state.value.let { _state.value = it.copy(selectedOrder = null) }
    fun clearToast()  = _state.value.let { _state.value = it.copy(toast = null, error = null) }
}
