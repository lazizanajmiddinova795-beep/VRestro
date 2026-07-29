package com.example.myapplication.waiter

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.myapplication.data.api.RetrofitClient
import com.example.myapplication.data.models.*
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch

data class WaiterState(
    val tables: List<TableModel> = emptyList(),
    val categories: List<CategoryModel> = emptyList(),
    val foods: List<FoodModel> = emptyList(),
    val activeOrders: List<OrderModel> = emptyList(),
    val cart: LinkedHashMap<FoodModel, Int> = LinkedHashMap(),
    val selectedTable: TableModel? = null,
    val isLoading: Boolean = false,
    val error: String? = null,
    val toast: String? = null
)

class WaiterViewModel : ViewModel() {
    private val api = RetrofitClient.api
    private val _state = MutableStateFlow(WaiterState())
    val state: StateFlow<WaiterState> = _state

    fun loadAll() {
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoading = true, error = null)
            try {
                val tables  = api.getWaiterTables().body() ?: emptyList()
                val cats    = api.getCategories().body() ?: emptyList()
                val foods   = api.getFoods().body() ?: emptyList()
                val orders  = api.getWaiterActiveOrders().body() ?: emptyList()
                _state.value = _state.value.copy(
                    tables = tables, categories = cats,
                    foods = foods, activeOrders = orders, isLoading = false
                )
            } catch (e: Exception) {
                _state.value = _state.value.copy(isLoading = false, error = e.localizedMessage)
            }
        }
    }

    fun selectTable(table: TableModel) =
        _state.value.let { _state.value = it.copy(selectedTable = table, cart = LinkedHashMap()) }

    fun addToCart(food: FoodModel) {
        val cart = LinkedHashMap(_state.value.cart)
        cart[food] = (cart[food] ?: 0) + 1
        _state.value = _state.value.copy(cart = cart)
    }

    fun removeFromCart(food: FoodModel) {
        val cart = LinkedHashMap(_state.value.cart)
        val cur = cart[food] ?: 0
        if (cur <= 1) cart.remove(food) else cart[food] = cur - 1
        _state.value = _state.value.copy(cart = cart)
    }

    fun clearCart() = _state.value.let { _state.value = it.copy(cart = LinkedHashMap()) }

    fun submitOrder() {
        val tableId = _state.value.selectedTable?.id ?: return
        val cart = _state.value.cart
        if (cart.isEmpty()) return
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoading = true)
            try {
                val items = cart.map { (f, q) -> SubmitOrderItem(f.id, q) }
                val resp = api.submitOrder(SubmitOrderRequest(tableId, items))
                if (resp.isSuccessful) {
                    _state.value = _state.value.copy(
                        isLoading = false, cart = LinkedHashMap(),
                        selectedTable = null, toast = "Buyurtma muvaffaqiyatli yuborildi! ✓"
                    )
                    loadAll()
                } else {
                    _state.value = _state.value.copy(isLoading = false, error = "Buyurtma yuborishda xatolik")
                }
            } catch (e: Exception) {
                _state.value = _state.value.copy(isLoading = false, error = e.localizedMessage)
            }
        }
    }

    fun cartTotal() = _state.value.cart.entries.sumOf { (f, q) -> f.price * q }
    fun cartCount() = _state.value.cart.values.sum()
    fun clearToast() = _state.value.let { _state.value = it.copy(toast = null, error = null) }
}
