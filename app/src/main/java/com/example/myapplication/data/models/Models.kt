package com.example.myapplication.data.models

import com.google.gson.annotations.SerializedName

data class UserModel(
    @SerializedName("id") val id: Int = 0,
    @SerializedName("name") val name: String = "",
    @SerializedName("login") val login: String = "",
    @SerializedName("email") val email: String = "",
    @SerializedName("role") val role: String = "waiter"
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
    @SerializedName("token") val token: String? = null,
    @SerializedName("user") val user: UserModel? = null,
    @SerializedName("requires_otp") val requiresOtp: Boolean = false,
    @SerializedName("message") val message: String? = null
)

data class TableModel(
    @SerializedName("id") val id: Int = 0,
    @SerializedName("name") val name: String = "",
    @SerializedName("number") val number: String = "",
    @SerializedName("status") val status: String = "free",
    @SerializedName("capacity") val capacity: Int = 4,
    @SerializedName("active_order_id") val activeOrderId: Int? = null,
    @SerializedName("total_amount") val totalAmount: Double? = null
) {
    val displayName: String get() = name.ifEmpty { number }.ifEmpty { "Stol $id" }
}

data class CategoryModel(
    @SerializedName("id") val id: Int = 0,
    @SerializedName("name") val name: String = ""
)

data class FoodModel(
    @SerializedName("id") val id: Int = 0,
    @SerializedName("name") val name: String = "",
    @SerializedName("price") val price: Double = 0.0,
    @SerializedName("category_id") val categoryId: Int = 0,
    @SerializedName("category") val category: CategoryModel? = null,
    @SerializedName("is_available") val isAvailable: Boolean = true,
    @SerializedName("image_path") val imagePath: String? = null,
    @SerializedName("slug") val slug: String = ""
)

data class OrderItemModel(
    @SerializedName("id") val id: Int = 0,
    @SerializedName("food_id") val foodId: Int = 0,
    @SerializedName("food") val food: FoodModel? = null,
    @SerializedName("food_name") val foodName: String? = null,
    @SerializedName("quantity") val quantity: Int = 1,
    @SerializedName("price") val price: Double = 0.0,
    @SerializedName("status") val status: String = "pending",
    @SerializedName("notes") val notes: String? = null
) {
    val displayName: String get() = food?.name ?: foodName ?: "Taom #$foodId"
    val subtotal: Double get() = price * quantity
}

data class OrderModel(
    @SerializedName("id") val id: Int = 0,
    @SerializedName("order_number") val orderNumber: String? = null,
    @SerializedName("table_id") val tableId: Int? = null,
    @SerializedName("table") val table: TableModel? = null,
    @SerializedName("waiter") val waiter: UserModel? = null,
    @SerializedName("waiter_name") val waiterName: String? = null,
    @SerializedName("total_amount") val totalAmount: Double = 0.0,
    @SerializedName("total") val total: Double = 0.0,
    @SerializedName("status") val status: String = "new",
    @SerializedName("items") val items: List<OrderItemModel> = emptyList(),
    @SerializedName("created_at") val createdAt: String? = null
) {
    val displayTotal: Double get() = if (total > 0) total else totalAmount
    val displayNumber: String get() = orderNumber ?: "#$id"
}

data class KitchenItemModel(
    @SerializedName("id") val id: Int = 0,
    @SerializedName("order_id") val orderId: Int = 0,
    @SerializedName("food_id") val foodId: Int = 0,
    @SerializedName("food") val food: FoodModel? = null,
    @SerializedName("table") val table: TableModel? = null,
    @SerializedName("quantity") val quantity: Int = 1,
    @SerializedName("status") val status: String = "new",
    @SerializedName("note") val note: String? = null,
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
