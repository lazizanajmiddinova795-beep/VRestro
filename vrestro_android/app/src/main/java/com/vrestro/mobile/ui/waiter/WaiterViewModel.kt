package com.vrestro.mobile.ui.waiter

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.vrestro.mobile.data.api.RetrofitClient
import com.vrestro.mobile.data.models.*
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch

data class WaiterUiState(
    val tables: List<TableModel> = emptyList(),
    val categories: List<CategoryModel> = emptyList(),
    val foods: List<FoodModel> = emptyList(),
    val activeOrders: List<OrderModel> = emptyList(),
    val cart: Map<FoodModel, Int> = emptyMap(),
    val selectedTable: TableModel? = null,
    val isLoading: Boolean = false,
    val error: String? = null,
    val successMessage: String? = null
)

class WaiterViewModel : ViewModel() {
    private val api = RetrofitClient.api

    private val _state = MutableStateFlow(WaiterUiState())
    val state: StateFlow<WaiterUiState> = _state

    fun loadAll() {
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoading = true, error = null)
            try {
                val tablesResp = api.getWaiterTables()
                val catsResp = api.getCategories()
                val foodsResp = api.getFoods()
                val ordersResp = api.getWaiterActiveOrders()

                _state.value = _state.value.copy(
                    tables = tablesResp.body() ?: emptyList(),
                    categories = catsResp.body() ?: emptyList(),
                    foods = foodsResp.body() ?: emptyList(),
                    activeOrders = ordersResp.body() ?: emptyList(),
                    isLoading = false
                )
            } catch (e: Exception) {
                _state.value = _state.value.copy(
                    isLoading = false,
                    error = "Ma'lumot yuklanmadi: ${e.localizedMessage}"
                )
            }
        }
    }

    fun selectTable(table: TableModel) {
        _state.value = _state.value.copy(selectedTable = table, cart = emptyMap())
    }

    fun addToCart(food: FoodModel) {
        val newCart = _state.value.cart.toMutableMap()
        newCart[food] = (newCart[food] ?: 0) + 1
        _state.value = _state.value.copy(cart = newCart)
    }

    fun removeFromCart(food: FoodModel) {
        val newCart = _state.value.cart.toMutableMap()
        val current = newCart[food] ?: 0
        if (current <= 1) newCart.remove(food) else newCart[food] = current - 1
        _state.value = _state.value.copy(cart = newCart)
    }

    fun clearCart() {
        _state.value = _state.value.copy(cart = emptyMap())
    }

    fun submitOrder() {
        val tableId = _state.value.selectedTable?.id ?: return
        val cart = _state.value.cart
        if (cart.isEmpty()) return

        viewModelScope.launch {
            _state.value = _state.value.copy(isLoading = true, error = null)
            try {
                val items = cart.map { (food, qty) ->
                    SubmitOrderItem(foodId = food.id, quantity = qty)
                }
                val resp = api.submitOrder(SubmitOrderRequest(tableId, items))
                if (resp.isSuccessful) {
                    _state.value = _state.value.copy(
                        isLoading = false,
                        cart = emptyMap(),
                        selectedTable = null,
                        successMessage = "Buyurtma muvaffaqiyatli yuborildi!"
                    )
                    loadAll()
                } else {
                    _state.value = _state.value.copy(
                        isLoading = false,
                        error = "Buyurtma yuborishda xatolik."
                    )
                }
            } catch (e: Exception) {
                _state.value = _state.value.copy(
                    isLoading = false,
                    error = e.localizedMessage
                )
            }
        }
    }

    fun clearMessage() {
        _state.value = _state.value.copy(successMessage = null, error = null)
    }

    fun cartTotal(): Double = _state.value.cart.entries.sumOf { (food, qty) -> food.price * qty }
    fun cartCount(): Int = _state.value.cart.values.sum()
}
