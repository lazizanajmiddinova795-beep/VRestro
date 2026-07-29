package com.vrestro.mobile.data.api

import com.vrestro.mobile.data.models.*
import retrofit2.Response
import retrofit2.http.*

interface VRestroApi {

    // ─── Auth ────────────────────────────────────────────
    @POST("auth/login")
    suspend fun login(@Body request: LoginRequest): Response<LoginResponse>

    @POST("auth/verify-face")
    suspend fun verifyOtp(@Body request: OtpRequest): Response<LoginResponse>

    @GET("user")
    suspend fun getUserProfile(): Response<UserModel>

    // ─── Waiter ──────────────────────────────────────────
    @GET("waiter/tables")
    suspend fun getWaiterTables(): Response<List<TableModel>>

    @POST("waiter/orders/submit")
    suspend fun submitOrder(@Body request: SubmitOrderRequest): Response<OrderModel>

    @GET("waiter/orders/active-status")
    suspend fun getWaiterActiveOrders(): Response<List<OrderModel>>

    @DELETE("waiter/order-item/{id}")
    suspend fun cancelOrderItem(@Path("id") itemId: Int): Response<Unit>

    // ─── Cashier ─────────────────────────────────────────
    @GET("cashier/tables")
    suspend fun getCashierTables(): Response<List<TableModel>>

    @GET("orders/{id}")
    suspend fun getOrderById(@Path("id") orderId: Int): Response<OrderModel>

    @POST("payments")
    suspend fun processPayment(@Body body: Map<String, Any>): Response<PaymentModel>

    // ─── Kitchen / Chef ──────────────────────────────────
    @GET("chef/items")
    suspend fun getChefItems(): Response<List<KitchenItemModel>>

    @PATCH("chef/items/{id}")
    suspend fun updateChefItemStatus(
        @Path("id") itemId: Int,
        @Body body: Map<String, String>
    ): Response<KitchenItemModel>

    @GET("kitchen/foods")
    suspend fun getKitchenFoods(): Response<List<FoodModel>>

    // ─── Menu ────────────────────────────────────────────
    @GET("menu/categories")
    suspend fun getCategories(): Response<List<CategoryModel>>

    @GET("menu/foods")
    suspend fun getFoods(): Response<List<FoodModel>>

    @GET("tables")
    suspend fun getTables(): Response<List<TableModel>>
}
