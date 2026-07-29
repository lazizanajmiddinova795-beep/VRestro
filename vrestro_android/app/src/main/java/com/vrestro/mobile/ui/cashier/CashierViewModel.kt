package com.vrestro.mobile.ui.cashier

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.vrestro.mobile.data.api.RetrofitClient
import com.vrestro.mobile.data.models.*
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch

data class CashierUiState(
    val tables: List<TableModel> = emptyList(),
    val selectedOrder: OrderModel? = null,
    val isLoading: Boolean = false,
    val error: String? = null,
    val successMessage: String? = null
)

class CashierViewModel : ViewModel() {
    private val api = RetrofitClient.api

    private val _state = MutableStateFlow(CashierUiState())
    val state: StateFlow<CashierUiState> = _state

    fun loadTables() {
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoading = true, error = null)
            try {
                val resp = api.getCashierTables()
                _state.value = _state.value.copy(
                    tables = resp.body() ?: emptyList(),
                    isLoading = false
                )
            } catch (e: Exception) {
                _state.value = _state.value.copy(
                    isLoading = false,
                    error = "Stollar yuklanmadi: ${e.localizedMessage}"
                )
            }
        }
    }

    fun loadOrder(orderId: Int) {
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoading = true)
            try {
                val resp = api.getOrderById(orderId)
                _state.value = _state.value.copy(
                    selectedOrder = resp.body(),
                    isLoading = false
                )
            } catch (e: Exception) {
                _state.value = _state.value.copy(
                    isLoading = false,
                    error = "Buyurtma yuklanmadi."
                )
            }
        }
    }

    fun processPayment(orderId: Int, method: String) {
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoading = true)
            try {
                val resp = api.processPayment(
                    mapOf("order_id" to orderId, "method" to method)
                )
                if (resp.isSuccessful) {
                    _state.value = _state.value.copy(
                        isLoading = false,
                        selectedOrder = null,
                        successMessage = "To'lov muvaffaqiyatli amalga oshirildi!"
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

    fun clearOrder() { _state.value = _state.value.copy(selectedOrder = null) }
    fun clearMessage() { _state.value = _state.value.copy(successMessage = null, error = null) }
}
