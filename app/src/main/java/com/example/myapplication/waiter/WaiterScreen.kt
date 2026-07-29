package com.example.myapplication.waiter

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
import com.example.myapplication.data.models.FoodModel
import com.example.myapplication.data.models.TableModel
import com.example.myapplication.ui.theme.*

@Composable
fun WaiterScreen(onLogout: () -> Unit, vm: WaiterViewModel = viewModel()) {
    val state by vm.state.collectAsStateWithLifecycle()
    var tab by remember { mutableIntStateOf(0) }
    var selCategory by remember { mutableStateOf<Int?>(null) }
    val snackbarHost = remember { SnackbarHostState() }

    LaunchedEffect(Unit) { vm.loadAll() }
    LaunchedEffect(state.toast) {
        state.toast?.let { snackbarHost.showSnackbar(it); vm.clearToast() }
    }
    LaunchedEffect(state.error) {
        state.error?.let { snackbarHost.showSnackbar(it); vm.clearToast() }
    }

    Scaffold(
        snackbarHost = { SnackbarHost(snackbarHost) },
        containerColor = Background,
        topBar = { WaiterTopBar(vm, tab, onLogout) { if (vm.cartCount() > 0) tab = 1 } },
        bottomBar = {
            NavigationBar(containerColor = Surface, tonalElevation = 0.dp) {
                listOf(
                    Triple(Icons.Rounded.TableRestaurant, "Stollar", 0),
                    Triple(Icons.Rounded.ShoppingCart, "Buyurtma", 1),
                    Triple(Icons.Rounded.ListAlt, "Faol", 2)
                ).forEach { (icon, label, idx) ->
                    NavigationBarItem(
                        selected = tab == idx, onClick = {
                            if (idx == 1 && state.selectedTable == null) return@NavigationBarItem
                            tab = idx
                        },
                        icon = {
                            if (idx == 1 && vm.cartCount() > 0) {
                                BadgedBox(badge = { Badge { Text(vm.cartCount().toString()) } }) {
                                    Icon(icon, null)
                                }
                            } else Icon(icon, null)
                        },
                        label = { Text(label) }
                    )
                }
            }
        }
    ) { pad ->
        Box(Modifier.padding(pad)) {
            when (tab) {
                0 -> TablesTab(state, vm) { tab = 1 }
                1 -> OrderTab(state, selCategory, { selCategory = it }, vm) { tab = 0 }
                2 -> ActiveOrdersTab(state)
            }
        }
    }
}

@Composable
private fun WaiterTopBar(vm: WaiterViewModel, tab: Int, onLogout: () -> Unit, onCart: () -> Unit) {
    Surface(color = Background, shadowElevation = 0.dp) {
        Row(
            Modifier.fillMaxWidth().statusBarsPadding()
                .padding(horizontal = 16.dp, vertical = 12.dp),
            verticalAlignment = Alignment.CenterVertically
        ) {
            Icon(Icons.Rounded.RestaurantMenu, null, tint = Primary, modifier = Modifier.size(22.dp))
            Spacer(Modifier.width(10.dp))
            Text(
                when (tab) { 0 -> "Ofitsiant • Stollar"; 1 -> "Buyurtma Berish"; else -> "Faol Buyurtmalar" },
                style = MaterialTheme.typography.titleLarge, modifier = Modifier.weight(1f)
            )
            if (vm.cartCount() > 0) {
                BadgedBox(badge = { Badge { Text(vm.cartCount().toString()) } },
                    modifier = Modifier.clickable { onCart() }) {
                    Icon(Icons.Rounded.ShoppingCart, null, tint = Primary)
                }
                Spacer(Modifier.width(16.dp))
            }
            IconButton(onClick = onLogout) { Icon(Icons.Rounded.Logout, null, tint = TextSecondary) }
        }
    }
}

@Composable
private fun TablesTab(state: WaiterState, vm: WaiterViewModel, onTableSelected: () -> Unit) {
    if (state.isLoading && state.tables.isEmpty()) {
        Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
            CircularProgressIndicator(color = Primary)
        }
        return
    }
    Column {
        // Summary chips
        Row(Modifier.padding(horizontal = 16.dp, vertical = 8.dp), horizontalArrangement = Arrangement.spacedBy(8.dp)) {
            val free  = state.tables.count { it.status.lowercase() == "free" && it.activeOrderId == null }
            val busy  = state.tables.count { it.status.lowercase() == "occupied" || it.activeOrderId != null }
            StatusChip("Bo'sh: $free", TableFree)
            StatusChip("Band: $busy", TableOccupied)
        }
        LazyVerticalGrid(
            columns = GridCells.Fixed(2),
            contentPadding = PaddingValues(horizontal = 16.dp, vertical = 8.dp),
            horizontalArrangement = Arrangement.spacedBy(12.dp),
            verticalArrangement = Arrangement.spacedBy(12.dp)
        ) {
            items(state.tables) { table ->
                TableCard(table, selected = state.selectedTable?.id == table.id) {
                    vm.selectTable(table)
                    onTableSelected()
                }
            }
        }
    }
}

@Composable
private fun StatusChip(text: String, color: Color) {
    Surface(color = color.copy(0.12f), shape = RoundedCornerShape(20.dp)) {
        Row(Modifier.padding(horizontal = 12.dp, vertical = 6.dp), verticalAlignment = Alignment.CenterVertically) {
            Box(Modifier.size(8.dp).clip(CircleShape).background(color))
            Spacer(Modifier.width(6.dp))
            Text(text, fontSize = 12.sp, color = color, fontWeight = FontWeight.SemiBold)
        }
    }
}

@Composable
private fun TableCard(table: TableModel, selected: Boolean, onClick: () -> Unit) {
    val isOccupied = table.status.lowercase() == "occupied" || table.activeOrderId != null
    val isReserved = table.status.lowercase() == "reserved"
    val color = when { isOccupied -> TableOccupied; isReserved -> TableReserved; else -> TableFree }

    Box(
        modifier = Modifier
            .fillMaxWidth()
            .shadow(if (selected) 8.dp else 4.dp, RoundedCornerShape(20.dp), spotColor = ShadowDark)
            .background(if (selected) Primary.copy(0.06f) else Surface, RoundedCornerShape(20.dp))
            .border(if (selected) 2.dp else 0.5.dp, if (selected) Primary else ShadowDark, RoundedCornerShape(20.dp))
            .clickable(onClick = onClick)
            .padding(16.dp)
    ) {
        Column {
            Row(verticalAlignment = Alignment.CenterVertically) {
                Icon(Icons.Rounded.TableRestaurant, null, tint = color, modifier = Modifier.size(20.dp))
                Spacer(Modifier.width(8.dp))
                Text(table.displayName, fontWeight = FontWeight.Bold, fontSize = 15.sp, color = TextPrimary, modifier = Modifier.weight(1f))
            }
            Spacer(Modifier.height(10.dp))
            Row(verticalAlignment = Alignment.CenterVertically) {
                Box(Modifier.size(7.dp).clip(CircleShape).background(color))
                Spacer(Modifier.width(6.dp))
                Text(when { isOccupied -> "Band"; isReserved -> "Rezerv"; else -> "Bo'sh" },
                    fontSize = 11.sp, color = color, fontWeight = FontWeight.SemiBold)
            }
            Spacer(Modifier.height(4.dp))
            Text("${table.capacity} kishi", fontSize = 11.sp, color = TextLight)
            if (table.totalAmount != null && table.totalAmount > 0) {
                Spacer(Modifier.height(6.dp))
                Text("${"%,.0f".format(table.totalAmount)} so'm", fontSize = 13.sp, color = Primary, fontWeight = FontWeight.Bold)
            }
        }
    }
}

@Composable
private fun OrderTab(
    state: WaiterState,
    selCat: Int?,
    onCatSelect: (Int?) -> Unit,
    vm: WaiterViewModel,
    goBack: () -> Unit
) {
    val table = state.selectedTable
    if (table == null) {
        Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
            Column(horizontalAlignment = Alignment.CenterHorizontally) {
                Icon(Icons.Rounded.TableRestaurant, null, tint = TextSecondary, modifier = Modifier.size(64.dp))
                Spacer(Modifier.height(12.dp))
                Text("Avval stol tanlang", color = TextSecondary, fontSize = 16.sp)
                Spacer(Modifier.height(16.dp))
                Button(onClick = goBack, colors = ButtonDefaults.buttonColors(containerColor = Primary)) {
                    Text("Stollar ro'yxatiga o'tish")
                }
            }
        }
        return
    }

    Column(Modifier.fillMaxSize()) {
        // Table header
        Surface(color = Primary.copy(0.08f)) {
            Row(Modifier.fillMaxWidth().padding(horizontal = 16.dp, vertical = 12.dp), verticalAlignment = Alignment.CenterVertically) {
                Icon(Icons.Rounded.TableRestaurant, null, tint = Primary, modifier = Modifier.size(20.dp))
                Spacer(Modifier.width(8.dp))
                Text("Stol: ${table.displayName}", fontWeight = FontWeight.Bold, color = Primary)
                Spacer(Modifier.weight(1f))
                if (state.cart.isNotEmpty()) {
                    TextButton(onClick = { vm.clearCart() }) {
                        Icon(Icons.Rounded.Delete, null, tint = StatusNew, modifier = Modifier.size(16.dp))
                        Spacer(Modifier.width(4.dp))
                        Text("Tozalash", color = StatusNew, fontSize = 13.sp)
                    }
                }
            }
        }

        // Category filter
        LazyRow(contentPadding = PaddingValues(horizontal = 16.dp, vertical = 8.dp), horizontalArrangement = Arrangement.spacedBy(8.dp)) {
            item {
                FilterChip(selected = selCat == null, onClick = { onCatSelect(null) }, label = { Text("Barchasi") },
                    colors = FilterChipDefaults.filterChipColors(selectedContainerColor = Primary, selectedLabelColor = Color.White))
            }
            items(state.categories) { cat ->
                FilterChip(selected = selCat == cat.id, onClick = { onCatSelect(cat.id) }, label = { Text(cat.name) },
                    colors = FilterChipDefaults.filterChipColors(selectedContainerColor = Primary, selectedLabelColor = Color.White))
            }
        }

        val filteredFoods = if (selCat == null) state.foods else state.foods.filter { it.categoryId == selCat }

        LazyColumn(
            Modifier.weight(1f),
            contentPadding = PaddingValues(16.dp),
            verticalArrangement = Arrangement.spacedBy(8.dp)
        ) {
            items(filteredFoods) { food ->
                FoodRow(food = food, qty = state.cart[food] ?: 0,
                    onAdd = { vm.addToCart(food) }, onRemove = { vm.removeFromCart(food) })
            }
        }

        // Cart summary
        if (state.cart.isNotEmpty()) {
            Surface(color = Surface, shadowElevation = 8.dp, tonalElevation = 0.dp) {
                Column(Modifier.padding(16.dp)) {
                    Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                        Text("Jami (${vm.cartCount()} ta):", fontWeight = FontWeight.Bold, fontSize = 15.sp)
                        Text("${"%,.0f".format(vm.cartTotal())} so'm",
                            fontWeight = FontWeight.ExtraBold, fontSize = 18.sp, color = Primary)
                    }
                    Spacer(Modifier.height(12.dp))
                    Button(
                        onClick = { vm.submitOrder() },
                        modifier = Modifier.fillMaxWidth().height(52.dp),
                        enabled = !state.isLoading,
                        colors = ButtonDefaults.buttonColors(containerColor = Primary),
                        shape = RoundedCornerShape(16.dp),
                        elevation = ButtonDefaults.buttonElevation(6.dp)
                    ) {
                        if (state.isLoading) CircularProgressIndicator(Modifier.size(20.dp), color = Color.White, strokeWidth = 2.dp)
                        else {
                            Icon(Icons.Rounded.Send, null, modifier = Modifier.size(18.dp))
                            Spacer(Modifier.width(8.dp))
                            Text("Buyurtma Yuborish", fontWeight = FontWeight.SemiBold, fontSize = 15.sp)
                        }
                    }
                }
            }
        }
    }
}

@Composable
private fun FoodRow(food: FoodModel, qty: Int, onAdd: () -> Unit, onRemove: () -> Unit) {
    Row(
        Modifier
            .fillMaxWidth()
            .shadow(3.dp, RoundedCornerShape(14.dp), spotColor = ShadowDark)
            .background(Surface, RoundedCornerShape(14.dp))
            .padding(12.dp),
        verticalAlignment = Alignment.CenterVertically
    ) {
        Column(Modifier.weight(1f)) {
            Text(food.name, fontWeight = FontWeight.SemiBold, fontSize = 14.sp, color = TextPrimary)
            Text("${"%,.0f".format(food.price)} so'm", fontSize = 13.sp, color = Primary, fontWeight = FontWeight.Bold)
        }
        Row(verticalAlignment = Alignment.CenterVertically) {
            AnimatedQtyControl(qty = qty, onAdd = onAdd, onRemove = onRemove)
        }
    }
}

@Composable
private fun AnimatedQtyControl(qty: Int, onAdd: () -> Unit, onRemove: () -> Unit) {
    if (qty > 0) {
        IconButton(onClick = onRemove, modifier = Modifier.size(34.dp)) {
            Icon(Icons.Rounded.Remove, null, tint = StatusNew, modifier = Modifier.size(18.dp))
        }
        Box(Modifier.widthIn(min = 28.dp), contentAlignment = Alignment.Center) {
            Text(qty.toString(), fontWeight = FontWeight.ExtraBold, fontSize = 16.sp, textAlign = TextAlign.Center)
        }
    }
    IconButton(onClick = onAdd, modifier = Modifier.size(34.dp)) {
        Icon(Icons.Rounded.Add, null, tint = StatusReady, modifier = Modifier.size(18.dp))
    }
}

@Composable
private fun ActiveOrdersTab(state: WaiterState) {
    if (state.activeOrders.isEmpty()) {
        Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
            Column(horizontalAlignment = Alignment.CenterHorizontally) {
                Icon(Icons.Rounded.CheckCircle, null, tint = StatusReady, modifier = Modifier.size(60.dp))
                Spacer(Modifier.height(12.dp))
                Text("Faol buyurtmalar yo'q", color = TextSecondary, fontSize = 15.sp)
            }
        }
        return
    }
    LazyColumn(contentPadding = PaddingValues(16.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
        items(state.activeOrders) { order ->
            val statusColor = when (order.status.lowercase()) {
                "new", "pending" -> StatusNew; "preparing" -> StatusPreparing
                "ready" -> StatusReady; "served" -> StatusServed; else -> TextSecondary
            }
            Column(
                Modifier.fillMaxWidth()
                    .shadow(4.dp, RoundedCornerShape(16.dp), spotColor = ShadowDark)
                    .background(Surface, RoundedCornerShape(16.dp))
                    .padding(16.dp)
            ) {
                Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween, verticalAlignment = Alignment.CenterVertically) {
                    Text(order.displayNumber, fontWeight = FontWeight.Bold, fontSize = 15.sp)
                    Surface(color = statusColor.copy(0.14f), shape = RoundedCornerShape(8.dp)) {
                        Text(order.status, color = statusColor, fontSize = 11.sp, fontWeight = FontWeight.SemiBold,
                            modifier = Modifier.padding(horizontal = 8.dp, vertical = 4.dp))
                    }
                }
                if (order.table != null) {
                    Text("Stol: ${order.table.displayName}", fontSize = 13.sp, color = TextSecondary)
                }
                Spacer(Modifier.height(8.dp))
                order.items.forEach { item ->
                    Text("• ${item.quantity}x ${item.displayName}", fontSize = 13.sp, color = TextSecondary)
                }
                Spacer(Modifier.height(6.dp))
                Text("Jami: ${"%,.0f".format(order.displayTotal)} so'm", fontWeight = FontWeight.Bold, color = Primary)
            }
        }
    }
}
