package com.example.myapplication.cashier

import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
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
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.lifecycle.viewmodel.compose.viewModel
import com.example.myapplication.data.models.OrderModel
import com.example.myapplication.data.models.TableModel
import com.example.myapplication.ui.theme.*
import androidx.compose.foundation.lazy.grid.GridCells
import androidx.compose.foundation.lazy.grid.LazyVerticalGrid
import androidx.compose.foundation.lazy.grid.items as gridItems

@Composable
fun CashierScreen(onLogout: () -> Unit, vm: CashierViewModel = viewModel()) {
    val state by vm.state.collectAsStateWithLifecycle()
    var showPayment by remember { mutableStateOf(false) }
    val snackbarHost = remember { SnackbarHostState() }

    LaunchedEffect(Unit) { vm.loadTables() }
    LaunchedEffect(state.toast) { state.toast?.let { snackbarHost.showSnackbar(it); vm.clearToast() } }
    LaunchedEffect(state.error) { state.error?.let { snackbarHost.showSnackbar(it); vm.clearToast() } }

    Scaffold(
        snackbarHost = { SnackbarHost(snackbarHost) },
        containerColor = Background,
        topBar = {
            Surface(color = Background) {
                Row(
                    Modifier.fillMaxWidth().statusBarsPadding()
                        .padding(horizontal = 16.dp, vertical = 12.dp),
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    Icon(Icons.Rounded.PointOfSale, null, tint = Primary, modifier = Modifier.size(22.dp))
                    Spacer(Modifier.width(10.dp))
                    Text("Kassir • Stollar", style = MaterialTheme.typography.titleLarge, modifier = Modifier.weight(1f))
                    IconButton(onClick = { vm.loadTables() }) { Icon(Icons.Rounded.Refresh, null, tint = Primary) }
                    IconButton(onClick = onLogout) { Icon(Icons.Rounded.Logout, null, tint = TextSecondary) }
                }
            }
        }
    ) { pad ->
        Box(Modifier.padding(pad)) {
            if (state.isLoading && state.tables.isEmpty()) {
                Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                    CircularProgressIndicator(color = Primary)
                }
            } else {
                Column {
                    // Summary
                    Row(Modifier.padding(horizontal = 16.dp, vertical = 8.dp), horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                        val busy  = state.tables.count { it.status.lowercase() == "occupied" || it.activeOrderId != null }
                        val total = state.tables.filter { it.totalAmount != null }.sumOf { it.totalAmount!! }
                        SummaryChip("Band: $busy", Primary)
                        if (total > 0) SummaryChip("Kutilayotgan: ${"%,.0f".format(total)} so'm", StatusReady)
                    }
                    LazyVerticalGrid(
                        columns = GridCells.Fixed(2),
                        contentPadding = PaddingValues(horizontal = 16.dp, vertical = 8.dp),
                        horizontalArrangement = Arrangement.spacedBy(12.dp),
                        verticalArrangement = Arrangement.spacedBy(12.dp)
                    ) {
                        gridItems(state.tables) { table ->
                            CashierTableCard(table) {
                                if (table.activeOrderId != null) {
                                    vm.loadOrder(table.activeOrderId)
                                    showPayment = true
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    if (showPayment && state.selectedOrder != null) {
        PaymentSheet(
            order = state.selectedOrder!!,
            isLoading = state.isLoading,
            onDismiss = { vm.clearOrder(); showPayment = false },
            onPay = { method ->
                vm.processPayment(state.selectedOrder!!.id, method, state.selectedOrder!!.displayTotal)
                showPayment = false
            }
        )
    }
}

@Composable
private fun SummaryChip(text: String, color: Color) {
    Surface(color = color.copy(0.1f), shape = RoundedCornerShape(20.dp)) {
        Text(text, fontSize = 12.sp, color = color, fontWeight = FontWeight.SemiBold,
            modifier = Modifier.padding(horizontal = 12.dp, vertical = 6.dp))
    }
}

@Composable
private fun CashierTableCard(table: TableModel, onClick: () -> Unit) {
    val isOccupied = table.status.lowercase() == "occupied" || table.activeOrderId != null
    val isReserved = table.status.lowercase() == "reserved"
    val color = when { isOccupied -> TableOccupied; isReserved -> TableReserved; else -> TableFree }

    Box(
        Modifier.fillMaxWidth()
            .shadow(4.dp, RoundedCornerShape(20.dp), spotColor = ShadowDark)
            .background(Surface, RoundedCornerShape(20.dp))
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
            Spacer(Modifier.height(8.dp))
            if (table.totalAmount != null && table.totalAmount > 0) {
                Text("${"%,.0f".format(table.totalAmount)} so'm", fontSize = 14.sp, color = Primary, fontWeight = FontWeight.ExtraBold)
            }
            if (isOccupied) {
                Spacer(Modifier.height(8.dp))
                Surface(color = Primary.copy(0.1f), shape = RoundedCornerShape(8.dp)) {
                    Row(Modifier.padding(horizontal = 10.dp, vertical = 4.dp), verticalAlignment = Alignment.CenterVertically) {
                        Icon(Icons.Rounded.TouchApp, null, tint = Primary, modifier = Modifier.size(13.dp))
                        Spacer(Modifier.width(4.dp))
                        Text("To'lov qilish", fontSize = 11.sp, color = Primary, fontWeight = FontWeight.SemiBold)
                    }
                }
            }
        }
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
private fun PaymentSheet(
    order: OrderModel, isLoading: Boolean,
    onDismiss: () -> Unit, onPay: (String) -> Unit
) {
    var method by remember { mutableStateOf("cash") }

    ModalBottomSheet(onDismissRequest = onDismiss, containerColor = Surface,
        shape = RoundedCornerShape(topStart = 28.dp, topEnd = 28.dp)) {
        Column(Modifier.padding(horizontal = 24.dp).padding(bottom = 36.dp)) {

            // Header
            Row(Modifier.fillMaxWidth(), verticalAlignment = Alignment.CenterVertically) {
                Column(Modifier.weight(1f)) {
                    Text(order.displayNumber, fontWeight = FontWeight.ExtraBold, fontSize = 20.sp)
                    if (order.table != null) Text("Stol: ${order.table.displayName}", fontSize = 13.sp, color = TextSecondary)
                }
                if (order.waiterName != null) {
                    Surface(color = Secondary.copy(0.1f), shape = RoundedCornerShape(10.dp)) {
                        Text("Ofitsiant: ${order.waiterName}", fontSize = 11.sp, color = Secondary, fontWeight = FontWeight.SemiBold,
                            modifier = Modifier.padding(horizontal = 8.dp, vertical = 4.dp))
                    }
                }
            }

            Divider(Modifier.padding(vertical = 16.dp), color = ShadowDark)

            // Items
            order.items.forEach { item ->
                Row(Modifier.fillMaxWidth().padding(vertical = 4.dp)) {
                    Surface(color = Primary.copy(0.1f), shape = CircleShape, modifier = Modifier.size(28.dp)) {
                        Box(contentAlignment = Alignment.Center) {
                            Text("${item.quantity}", fontWeight = FontWeight.Bold, fontSize = 12.sp, color = Primary)
                        }
                    }
                    Spacer(Modifier.width(10.dp))
                    Text(item.displayName, Modifier.weight(1f), color = TextPrimary, fontSize = 14.sp)
                    Text("${"%,.0f".format(item.subtotal)} so'm", fontWeight = FontWeight.SemiBold, fontSize = 13.sp)
                }
            }

            Divider(Modifier.padding(vertical = 16.dp), color = ShadowDark)

            // Total
            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween, verticalAlignment = Alignment.CenterVertically) {
                Text("Jami to'lov:", fontWeight = FontWeight.Bold, fontSize = 17.sp)
                Text("${"%,.0f".format(order.displayTotal)} so'm", fontWeight = FontWeight.ExtraBold, fontSize = 22.sp, color = Primary)
            }

            Spacer(Modifier.height(20.dp))
            Text("To'lov usuli:", fontWeight = FontWeight.SemiBold, color = TextSecondary, fontSize = 13.sp)
            Spacer(Modifier.height(12.dp))
            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                PayBtn("Naqd", "cash", Icons.Rounded.Money, method, Modifier.weight(1f)) { method = it }
                PayBtn("Karta", "card", Icons.Rounded.CreditCard, method, Modifier.weight(1f)) { method = it }
                PayBtn("QR/Click", "qr", Icons.Rounded.QrCode, method, Modifier.weight(1f)) { method = it }
            }

            Spacer(Modifier.height(20.dp))
            Button(onClick = { onPay(method) }, enabled = !isLoading,
                modifier = Modifier.fillMaxWidth().height(54.dp),
                colors = ButtonDefaults.buttonColors(containerColor = StatusReady),
                shape = RoundedCornerShape(16.dp),
                elevation = ButtonDefaults.buttonElevation(6.dp)) {
                if (isLoading) CircularProgressIndicator(Modifier.size(20.dp), color = Color.White, strokeWidth = 2.dp)
                else {
                    Icon(Icons.Rounded.CheckCircle, null, modifier = Modifier.size(20.dp))
                    Spacer(Modifier.width(8.dp))
                    Text("To'lovni Tasdiqlash", fontWeight = FontWeight.Bold, fontSize = 16.sp)
                }
            }
        }
    }
}

@Composable
private fun PayBtn(label: String, value: String, icon: ImageVector, sel: String, modifier: Modifier, onSelect: (String) -> Unit) {
    val selected = sel == value
    Column(
        modifier = modifier
            .shadow(if (selected) 6.dp else 1.dp, RoundedCornerShape(14.dp))
            .background(if (selected) Primary else Surface, RoundedCornerShape(14.dp))
            .border(if (selected) 0.dp else 1.dp, ShadowDark, RoundedCornerShape(14.dp))
            .clickable { onSelect(value) }
            .padding(vertical = 14.dp),
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        Icon(icon, null, tint = if (selected) Color.White else TextSecondary, modifier = Modifier.size(24.dp))
        Spacer(Modifier.height(4.dp))
        Text(label, fontSize = 11.sp, color = if (selected) Color.White else TextSecondary, fontWeight = FontWeight.SemiBold)
    }
}
