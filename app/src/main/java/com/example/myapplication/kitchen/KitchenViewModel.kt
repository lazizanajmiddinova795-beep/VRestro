package com.example.myapplication.kitchen

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.myapplication.data.api.RetrofitClient
import com.example.myapplication.data.models.*
import kotlinx.coroutines.delay
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch

data class KitchenState(
    val items: List<KitchenItemModel> = emptyList(),
    val stopList: List<FoodModel> = emptyList(),
    val isLoading: Boolean = false,
    val error: String? = null
)

class KitchenViewModel : ViewModel() {
    private val api = RetrofitClient.api
    private val _state = MutableStateFlow(KitchenState())
    val state: StateFlow<KitchenState> = _state

    fun loadItems() {
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoading = true, error = null)
            try {
                val items = api.getChefItems().body() ?: emptyList()
                _state.value = _state.value.copy(items = items, isLoading = false)
            } catch (e: Exception) {
                _state.value = _state.value.copy(isLoading = false, error = e.localizedMessage)
            }
        }
    }

    fun loadStopList() {
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoading = true)
            try {
                val foods = api.getKitchenFoods().body() ?: emptyList()
                _state.value = _state.value.copy(stopList = foods, isLoading = false)
            } catch (e: Exception) {
                _state.value = _state.value.copy(isLoading = false, error = e.localizedMessage)
            }
        }
    }

    fun updateStatus(itemId: Int, newStatus: String) {
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
                delay(10_000)
                try {
                    val items = api.getChefItems().body() ?: emptyList()
                    _state.value = _state.value.copy(items = items)
                } catch (_: Exception) {}
            }
        }
    }

    fun clearError() = _state.value.let { _state.value = it.copy(error = null) }
}
