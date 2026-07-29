package com.vrestro.mobile.ui.waiter

import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.LazyRow
import androidx.compose.foundation.lazy.grid.GridCells
import androidx.compose.foundation.lazy.grid.LazyVerticalGrid
import androidx.compose.foundation.lazy.grid.items
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.rounded.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.draw.shadow
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.lifecycle.viewmodel.compose.viewModel
import com.vrestro.mobile.data.models.FoodModel
import com.vrestro.mobile.data.models.TableModel
import com.vrestro.mobile.ui.theme.*

@Composable
fun WaiterScreen(
    onLogout: () -> Unit,
    vm: WaiterViewModel = viewModel()
) {
    val state by vm.state.collectAsStateWithLifecycle()
    var selectedTab by remember { mutableIntStateOf(0) } // 0=Tables, 1=Order, 2=Active
    var selectedCategory by remember { mutableStateOf<Int?>(null) }

    LaunchedEffect(Unit) { vm.loadAll() }

    LaunchedEffect(state.successMessage) {
        if (state.successMessage != null) {
            selectedTab = 0
            vm.clearMessage()
        }
    }

    Scaffold(
        containerColor = Background,
        topBar = {
            WaiterTopBar(
                title = when (selectedTab) {
                    0 -> "Ofitsiant • Stollar"
                    1 -> "Buyurtma Berish"
                    else -> "Faol Buyurtmalar"
                },
                cartCount = vm.cartCount(),
                onLogout = onLogout,
                onCartClick = { if (vm.cartCount() > 0) selectedTab = 1 }
            )
        },
        bottomBar = {
            NavigationBar(containerColor = Surface) {
                NavigationBarItem(
                    selected = selectedTab == 0,
                    onClick = { selectedTab = 0 },
                    icon = { Icon(Icons.Rounded.TableRestaurant, contentDescription = null) },
                    label = { Text("Stollar") }
                )
                NavigationBarItem(
                    selected = selectedTab == 1,
                    onClick = { if (state.selectedTable != null) selectedTab = 1 },
                    icon = {
                        BadgedBox(badge = {
                            if (vm.cartCount() > 0)
                                Badge { Text(vm.cartCount().toString()) }
                        }) { Icon(Icons.Rounded.ShoppingCart, contentDescription = null) }
                    },
                    label = { Text("Buyurtma") }
                )
                NavigationBarItem(
                    selected = selectedTab == 2,
                    onClick = { selectedTab = 2 },
                    icon = { Icon(Icons.Rounded.ListAlt, contentDescription = null) },
                    label = { Text("Faol") }
                )
            }
        }
    ) { padding ->
        Box(Modifier.padding(padding)) {
            when (selectedTab) {
                0 -> TablesTab(
                    tables = state.tables,
                    isLoading = state.isLoading,
                    selectedTable = state.selectedTable,
                    onTableClick = { table ->
                        vm.selectTable(table)
                        selectedTab = 1
                    },
                    onRefresh = { vm.loadAll() }
                )
                1 -> OrderTab(
                    state = state,
                    selectedCategory = selectedCategory,
                    onCategorySelect = { selectedCategory = it },
                    onAddFood = { vm.addToCart(it) },
                    onRemoveFood = { vm.removeFromCart(it) },
                    onSubmit = { vm.submitOrder() },
                    onClear = { vm.clearCart() }
                )
                2 -> ActiveOrdersTab(orders = state.activeOrders, isLoading = state.isLoading)
            }
        }
    }
}

@Composable
private fun WaiterTopBar(title: String, cartCount: Int, onLogout: () -> Unit, onCartClick: () -> Unit) {
    Surface(color = Background, shadowElevation = 0.dp) {
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .statusBarsPadding()
                .padding(horizontal = 16.dp, vertical = 12.dp),
            verticalAlignment = Alignment.CenterVertically
        ) {
            Text(title, style = MaterialTheme.typography.titleLarge, modifier = Modifier.weight(1f))
            if (cartCount > 0) {
                BadgedBox(
                    badge = { Badge { Text(cartCount.toString()) } },
                    modifier = Modifier.clickable { onCartClick() }
                ) {
                    Icon(Icons.Rounded.ShoppingCart, null, tint = Primary)
                }
                Spacer(Modifier.width(16.dp))
            }
            IconButton(onClick = onLogout) {
                Icon(Icons.Rounded.Logout, null, tint = TextSecondary)
            }
        }
    }
}

@Composable
private fun TablesTab(
    tables: List<TableModel>,
    isLoading: Boolean,
    selectedTable: TableModel?,
    onTableClick: (TableModel) -> Unit,
    onRefresh: () -> Unit
) {
    if (isLoading && tables.isEmpty()) {
        Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
            CircularProgressIndicator(color = Primary)
        }
        return
    }
    LazyVerticalGrid(
        columns = GridCells.Fixed(2),
        contentPadding = PaddingValues(16.dp),
        horizontalArrangement = Arrangement.spacedBy(12.dp),
        verticalArrangement = Arrangement.spacedBy(12.dp)
    ) {
        items(tables) { table ->
            TableCard(table = table, isSelected = selectedTable?.id == table.id, onClick = { onTableClick(table) })
        }
    }
}

@Composable
private fun TableCard(table: TableModel, isSelected: Boolean, onClick: () -> Unit) {
    val isOccupied = table.status.lowercase() == "occupied" || table.activeOrderId != null
    val statusColor = when {
        isOccupied -> StatusNew
        table.status.lowercase() == "reserved" -> StatusPending
        else -> StatusReady
    }
    Box(
        modifier = Modifier
            .fillMaxWidth()
            .shadow(if (isSelected) 8.dp else 4.dp, RoundedCornerShape(20.dp), spotColor = ShadowDark)
            .background(if (isSelected) Primary.copy(alpha = 0.08f) else Surface, RoundedCornerShape(20.dp))
            .border(if (isSelected) 2.dp else 0.dp, if (isSelected) Primary else Color.Transparent, RoundedCornerShape(20.dp))
            .clickable { onClick() }
            .padding(16.dp)
    ) {
        Column {
            Row(verticalAlignment = Alignment.CenterVertically) {
                Icon(Icons.Rounded.TableRestaurant, null,
                    tint = if (isOccupied) StatusNew else StatusReady, modifier = Modifier.size(20.dp))
                Spacer(Modifier.width(8.dp))
                Text("Stol ${table.name}", fontWeight = FontWeight.Bold, fontSize = 16.sp, color = TextPrimary)
            }
            Spacer(Modifier.height(8.dp))
            Row(verticalAlignment = Alignment.CenterVertically) {
                Box(Modifier.size(8.dp).clip(CircleShape).background(statusColor))
                Spacer(Modifier.width(6.dp))
                Text(
                    text = when {
                        isOccupied -> "Band"
                        table.status.lowercase() == "reserved" -> "Rezerv"
                        else -> "Bo'sh"
                    },
                    fontSize = 12.sp, color = statusColor, fontWeight = FontWeight.SemiBold
                )
            }
            Spacer(Modifier.height(4.dp))
            Text("${table.capacity} kishi", fontSize = 12.sp, color = TextSecondary)
        }
    }
}

@Composable
private fun OrderTab(
    state: WaiterUiState,
    selectedCategory: Int?,
    onCategorySelect: (Int?) -> Unit,
    onAddFood: (FoodModel) -> Unit,
    onRemoveFood: (FoodModel) -> Unit,
    onSubmit: () -> Unit,
    onClear: () -> Unit
) {
    val table = state.selectedTable
    if (table == null) {
        Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
            Column(horizontalAlignment = Alignment.CenterHorizontally) {
                Icon(Icons.Rounded.TableRestaurant, null, tint = TextSecondary, modifier = Modifier.size(64.dp))
                Spacer(Modifier.height(16.dp))
                Text("Avval stol tanlang", color = TextSecondary, fontSize = 16.sp)
            }
        }
        return
    }

    Column(Modifier.fillMaxSize()) {
        // Table header
        Surface(color = Primary.copy(alpha = 0.1f)) {
            Row(Modifier.fillMaxWidth().padding(16.dp), verticalAlignment = Alignment.CenterVertically) {
                Icon(Icons.Rounded.TableRestaurant, null, tint = Primary)
                Spacer(Modifier.width(8.dp))
                Text("Stol ${table.name}", fontWeight = FontWeight.Bold, color = Primary)
                Spacer(Modifier.weight(1f))
                if (state.cart.isNotEmpty()) {
                    TextButton(onClick = onClear) { Text("Tozalash", color = StatusNew) }
                }
            }
        }

        // Category filter
        LazyRow(contentPadding = PaddingValues(horizontal = 16.dp, vertical = 8.dp),
            horizontalArrangement = Arrangement.spacedBy(8.dp)) {
            item {
                FilterChip(selected = selectedCategory == null,
                    onClick = { onCategorySelect(null) }, label = { Text("Barchasi") })
            }
            items(state.categories) { cat ->
                FilterChip(selected = selectedCategory == cat.id,
                    onClick = { onCategorySelect(cat.id) }, label = { Text(cat.name) })
            }
        }

        // Foods list
        val filtered = if (selectedCategory == null) state.foods
        else state.foods.filter { it.categoryId == selectedCategory }

        LazyColumn(
            modifier = Modifier.weight(1f),
            contentPadding = PaddingValues(16.dp),
            verticalArrangement = Arrangement.spacedBy(8.dp)
        ) {
            items(filtered) { food ->
                FoodItemRow(food = food, qty = state.cart[food] ?: 0,
                    onAdd = { onAddFood(food) }, onRemove = { onRemoveFood(food) })
            }
        }

        // Cart summary + Submit
        if (state.cart.isNotEmpty()) {
            Surface(color = Surface, shadowElevation = 8.dp) {
                Column(Modifier.padding(16.dp)) {
                    Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                        Text("Jami:", fontWeight = FontWeight.Bold, fontSize = 16.sp)
                        Text("${"%,.0f".format(state.cart.entries.sumOf { (f, q) -> f.price * q })} so'm",
                            fontWeight = FontWeight.ExtraBold, fontSize = 18.sp, color = Primary)
                    }
                    Spacer(Modifier.height(12.dp))
                    Button(
                        onClick = onSubmit,
                        modifier = Modifier.fillMaxWidth().height(50.dp),
                        enabled = !state.isLoading,
                        colors = ButtonDefaults.buttonColors(containerColor = Primary),
                        shape = RoundedCornerShape(14.dp)
                    ) {
                        if (state.isLoading) CircularProgressIndicator(Modifier.size(20.dp), color = Color.White)
                        else {
                            Icon(Icons.Rounded.Send, null)
                            Spacer(Modifier.width(8.dp))
                            Text("Buyurtma Yuborish", fontWeight = FontWeight.Bold)
                        }
                    }
                }
            }
        }
    }
}

@Composable
private fun FoodItemRow(food: FoodModel, qty: Int, onAdd: () -> Unit, onRemove: () -> Unit) {
    Row(
        modifier = Modifier
            .fillMaxWidth()
            .shadow(3.dp, RoundedCornerShape(14.dp), spotColor = ShadowDark)
            .background(Surface, RoundedCornerShape(14.dp))
            .padding(12.dp),
        verticalAlignment = Alignment.CenterVertically
    ) {
        Column(Modifier.weight(1f)) {
            Text(food.name, fontWeight = FontWeight.SemiBold, fontSize = 14.sp, color = TextPrimary)
            Text("${"%,.0f".format(food.price)} so'm", fontSize = 13.sp, color = Primary)
        }
        Row(verticalAlignment = Alignment.CenterVertically) {
            if (qty > 0) {
                IconButton(onClick = onRemove, modifier = Modifier.size(32.dp)) {
                    Icon(Icons.Rounded.Remove, null, tint = StatusNew, modifier = Modifier.size(18.dp))
                }
                Text(qty.toString(), fontWeight = FontWeight.Bold, fontSize = 16.sp,
                    modifier = Modifier.widthIn(min = 24.dp), textAlign = TextAlign.Center)
            }
            IconButton(onClick = onAdd, modifier = Modifier.size(32.dp)) {
                Icon(Icons.Rounded.Add, null, tint = StatusReady, modifier = Modifier.size(18.dp))
            }
        }
    }
}

@Composable
private fun ActiveOrdersTab(orders: List<com.vrestro.mobile.data.models.OrderModel>, isLoading: Boolean) {
    if (isLoading && orders.isEmpty()) {
        Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
            CircularProgressIndicator(color = Primary)
        }
        return
    }
    if (orders.isEmpty()) {
        Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
            Text("Faol buyurtmalar yo'q", color = TextSecondary)
        }
        return
    }
    LazyColumn(contentPadding = PaddingValues(16.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
        items(orders) { order ->
            OrderCard(order = order)
        }
    }
}

@Composable
private fun OrderCard(order: com.vrestro.mobile.data.models.OrderModel) {
    val statusColor = when (order.status.lowercase()) {
        "new", "pending" -> StatusNew
        "preparing" -> StatusPreparing
        "ready" -> StatusReady
        "served" -> StatusServed
        else -> TextSecondary
    }
    Column(
        modifier = Modifier
            .fillMaxWidth()
            .shadow(4.dp, RoundedCornerShape(16.dp), spotColor = ShadowDark)
            .background(Surface, RoundedCornerShape(16.dp))
            .padding(16.dp)
    ) {
        Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween, verticalAlignment = Alignment.CenterVertically) {
            Text("Buyurtma #${order.id}", fontWeight = FontWeight.Bold, fontSize = 15.sp)
            Surface(color = statusColor.copy(0.15f), shape = RoundedCornerShape(8.dp)) {
                Text(order.status, color = statusColor, fontSize = 12.sp,
                    fontWeight = FontWeight.SemiBold, modifier = Modifier.padding(horizontal = 8.dp, vertical = 4.dp))
            }
        }
        Spacer(Modifier.height(8.dp))
        order.items.forEach { item ->
            Text("• ${item.quantity}x ${item.food?.name ?: "Taom"}", fontSize = 13.sp, color = TextSecondary)
        }
        Spacer(Modifier.height(8.dp))
        Text("Jami: ${"%,.0f".format(order.total)} so'm", fontWeight = FontWeight.Bold, color = Primary)
    }
}
