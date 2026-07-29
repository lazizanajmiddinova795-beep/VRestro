package com.vrestro.mobile.data.models

import com.google.gson.annotations.SerializedName

data class UserModel(
    @SerializedName("id") val id: Int,
    @SerializedName("name") val name: String,
    @SerializedName("login") val login: String,
    @SerializedName("role") val role: String,
    @SerializedName("phone") val phone: String? = null
)

data class LoginRequest(
    @SerializedName("login") val login: String,
    @SerializedName("password") val password: String
)

data class OtpRequest(
    @SerializedName("user_id") val userId: Int,
    @SerializedName("otp") val otp: String
)

data class LoginResponse(
    @SerializedName("token") val token: String?,
    @SerializedName("user") val user: UserModel?,
    @SerializedName("requires_otp") val requiresOtp: Boolean = false,
    @SerializedName("message") val message: String? = null
)

data class TableModel(
    @SerializedName("id") val id: Int,
    @SerializedName("name") val name: String,
    @SerializedName("status") val status: String,       // free, occupied, reserved
    @SerializedName("capacity") val capacity: Int = 4,
    @SerializedName("active_order_id") val activeOrderId: Int? = null
)

data class CategoryModel(
    @SerializedName("id") val id: Int,
    @SerializedName("name") val name: String,
    @SerializedName("name_uz") val nameUz: String? = null
)

data class FoodModel(
    @SerializedName("id") val id: Int,
    @SerializedName("name") val name: String,
    @SerializedName("price") val price: Double,
    @SerializedName("category_id") val categoryId: Int? = null,
    @SerializedName("category") val category: CategoryModel? = null,
    @SerializedName("image") val image: String? = null,
    @SerializedName("is_available") val isAvailable: Boolean = true
)

data class OrderItemModel(
    @SerializedName("id") val id: Int,
    @SerializedName("food_id") val foodId: Int,
    @SerializedName("food") val food: FoodModel? = null,
    @SerializedName("quantity") val quantity: Int,
    @SerializedName("price") val price: Double,
    @SerializedName("status") val status: String,  // new, pending, preparing, ready, served
    @SerializedName("note") val note: String? = null
)

data class OrderModel(
    @SerializedName("id") val id: Int,
    @SerializedName("table_id") val tableId: Int,
    @SerializedName("table") val table: TableModel? = null,
    @SerializedName("status") val status: String,
    @SerializedName("total") val total: Double = 0.0,
    @SerializedName("items") val items: List<OrderItemModel> = emptyList(),
    @SerializedName("created_at") val createdAt: String? = null,
    @SerializedName("waiter") val waiter: UserModel? = null
)

data class KitchenItemModel(
    @SerializedName("id") val id: Int,
    @SerializedName("order_id") val orderId: Int,
    @SerializedName("food_id") val foodId: Int,
    @SerializedName("food") val food: FoodModel? = null,
    @SerializedName("table") val table: TableModel? = null,
    @SerializedName("quantity") val quantity: Int,
    @SerializedName("status") val status: String,
    @SerializedName("note") val note: String? = null,
    @SerializedName("created_at") val createdAt: String? = null
)

data class PaymentModel(
    @SerializedName("id") val id: Int,
    @SerializedName("order_id") val orderId: Int,
    @SerializedName("amount") val amount: Double,
    @SerializedName("method") val method: String,
    @SerializedName("created_at") val createdAt: String? = null
)

data class SubmitOrderItem(
    @SerializedName("food_id") val foodId: Int,
    @SerializedName("quantity") val quantity: Int,
    @SerializedName("note") val note: String? = null
)

data class SubmitOrderRequest(
    @SerializedName("table_id") val tableId: Int,
    @SerializedName("items") val items: List<SubmitOrderItem>
)

data class ApiResponse<T>(
    @SerializedName("data") val data: T? = null,
    @SerializedName("message") val message: String? = null,
    @SerializedName("success") val success: Boolean = true
)
