package com.vrestro.mobile.ui.kitchen

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.vrestro.mobile.data.api.RetrofitClient
import com.vrestro.mobile.data.models.*
import kotlinx.coroutines.delay
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch

data class KitchenUiState(
    val items: List<KitchenItemModel> = emptyList(),
    val stopListFoods: List<FoodModel> = emptyList(),
    val isLoading: Boolean = false,
    val error: String? = null,
    val successMessage: String? = null
)

class KitchenViewModel : ViewModel() {
    private val api = RetrofitClient.api

    private val _state = MutableStateFlow(KitchenUiState())
    val state: StateFlow<KitchenUiState> = _state

    fun loadItems() {
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoading = true, error = null)
            try {
                val itemsResp = api.getChefItems()
                _state.value = _state.value.copy(
                    items = itemsResp.body() ?: emptyList(),
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

    fun loadStopList() {
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoading = true)
            try {
                val resp = api.getKitchenFoods()
                _state.value = _state.value.copy(
                    stopListFoods = resp.body() ?: emptyList(),
                    isLoading = false
                )
            } catch (e: Exception) {
                _state.value = _state.value.copy(
                    isLoading = false,
                    error = e.localizedMessage
                )
            }
        }
    }

    fun updateItemStatus(itemId: Int, newStatus: String) {
        viewModelScope.launch {
            try {
                api.updateChefItemStatus(itemId, mapOf("status" to newStatus))
                loadItems()
            } catch (e: Exception) {
                _state.value = _state.value.copy(error = e.localizedMessage)
            }
        }
    }

    fun startAutoRefresh() {
        viewModelScope.launch {
            while (true) {
                delay(10_000L)
                try {
                    val resp = api.getChefItems()
                    _state.value = _state.value.copy(items = resp.body() ?: emptyList())
                } catch (_) {}
            }
        }
    }

    fun clearMessage() { _state.value = _state.value.copy(error = null, successMessage = null) }
}
