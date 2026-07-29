package com.vrestro.mobile.ui.cashier

import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
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
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.lifecycle.viewmodel.compose.viewModel
import com.vrestro.mobile.data.models.OrderModel
import com.vrestro.mobile.data.models.TableModel
import com.vrestro.mobile.ui.theme.*

@Composable
fun CashierScreen(
    onLogout: () -> Unit,
    vm: CashierViewModel = viewModel()
) {
    val state by vm.state.collectAsStateWithLifecycle()
    var showOrderSheet by remember { mutableStateOf(false) }

    LaunchedEffect(Unit) { vm.loadTables() }

    LaunchedEffect(state.successMessage) {
        if (state.successMessage != null) {
            showOrderSheet = false
            vm.clearMessage()
        }
    }

    Scaffold(
        containerColor = Background,
        topBar = {
            Surface(color = Background) {
                Row(
                    modifier = Modifier
                        .fillMaxWidth()
                        .statusBarsPadding()
                        .padding(horizontal = 16.dp, vertical = 12.dp),
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    Icon(Icons.Rounded.PointOfSale, null, tint = Primary, modifier = Modifier.size(24.dp))
                    Spacer(Modifier.width(10.dp))
                    Text("Kassir • Stollar", style = MaterialTheme.typography.titleLarge, modifier = Modifier.weight(1f))
                    IconButton(onClick = { vm.loadTables() }) {
                        Icon(Icons.Rounded.Refresh, null, tint = Primary)
                    }
                    IconButton(onClick = onLogout) {
                        Icon(Icons.Rounded.Logout, null, tint = TextSecondary)
                    }
                }
            }
        }
    ) { padding ->
        Box(Modifier.padding(padding)) {
            if (state.isLoading && state.tables.isEmpty()) {
                Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                    CircularProgressIndicator(color = Primary)
                }
            } else {
                LazyVerticalGrid(
                    columns = GridCells.Fixed(2),
                    contentPadding = PaddingValues(16.dp),
                    horizontalArrangement = Arrangement.spacedBy(12.dp),
                    verticalArrangement = Arrangement.spacedBy(12.dp)
                ) {
                    items(state.tables) { table ->
                        CashierTableCard(
                            table = table,
                            onClick = {
                                if (table.activeOrderId != null) {
                                    vm.loadOrder(table.activeOrderId)
                                    showOrderSheet = true
                                }
                            }
                        )
                    }
                }
            }
        }
    }

    // Order Sheet
    if (showOrderSheet && state.selectedOrder != null) {
        OrderPaymentSheet(
            order = state.selectedOrder!!,
            isLoading = state.isLoading,
            onDismiss = { vm.clearOrder(); showOrderSheet = false },
            onPayment = { method -> vm.processPayment(state.selectedOrder!!.id, method) }
        )
    }
}

@Composable
private fun CashierTableCard(table: TableModel, onClick: () -> Unit) {
    val isOccupied = table.status.lowercase() == "occupied" || table.activeOrderId != null
    val statusColor = when {
        isOccupied -> StatusNew
        table.status.lowercase() == "reserved" -> StatusPending
        else -> StatusReady
    }
    Box(
        modifier = Modifier
            .fillMaxWidth()
            .shadow(4.dp, RoundedCornerShape(20.dp), spotColor = ShadowDark)
            .background(Surface, RoundedCornerShape(20.dp))
            .clickable { onClick() }
            .padding(16.dp)
    ) {
        Column {
            Row(verticalAlignment = Alignment.CenterVertically) {
                Icon(Icons.Rounded.TableRestaurant, null, tint = statusColor, modifier = Modifier.size(22.dp))
                Spacer(Modifier.width(8.dp))
                Text("Stol ${table.name}", fontWeight = FontWeight.Bold, fontSize = 16.sp, color = TextPrimary, modifier = Modifier.weight(1f))
            }
            Spacer(Modifier.height(10.dp))
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
            Spacer(Modifier.height(8.dp))
            if (isOccupied) {
                Surface(color = Primary.copy(0.1f), shape = RoundedCornerShape(8.dp)) {
                    Row(Modifier.padding(horizontal = 10.dp, vertical = 4.dp), verticalAlignment = Alignment.CenterVertically) {
                        Icon(Icons.Rounded.TouchApp, null, tint = Primary, modifier = Modifier.size(14.dp))
                        Spacer(Modifier.width(4.dp))
                        Text("To'lov qilish", fontSize = 12.sp, color = Primary, fontWeight = FontWeight.SemiBold)
                    }
                }
            }
        }
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
private fun OrderPaymentSheet(
    order: OrderModel,
    isLoading: Boolean,
    onDismiss: () -> Unit,
    onPayment: (String) -> Unit
) {
    var selectedMethod by remember { mutableStateOf("cash") }

    ModalBottomSheet(
        onDismissRequest = onDismiss,
        containerColor = Surface,
        shape = RoundedCornerShape(topStart = 24.dp, topEnd = 24.dp)
    ) {
        Column(Modifier.padding(horizontal = 20.dp).padding(bottom = 32.dp)) {
            Text("Buyurtma #${order.id}", fontWeight = FontWeight.ExtraBold, fontSize = 20.sp)
            Spacer(Modifier.height(16.dp))

            // Items
            order.items.forEach { item ->
                Row(Modifier.fillMaxWidth().padding(vertical = 4.dp)) {
                    Text("${item.quantity}x", fontWeight = FontWeight.Bold, color = Primary, modifier = Modifier.width(36.dp))
                    Text(item.food?.name ?: "Taom", Modifier.weight(1f), color = TextPrimary)
                    Text("${"%,.0f".format(item.price * item.quantity)} so'm", fontWeight = FontWeight.SemiBold)
                }
            }

            Divider(Modifier.padding(vertical = 12.dp))
            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                Text("Jami:", fontWeight = FontWeight.Bold, fontSize = 18.sp)
                Text("${"%,.0f".format(order.total)} so'm", fontWeight = FontWeight.ExtraBold, fontSize = 20.sp, color = Primary)
            }

            Spacer(Modifier.height(20.dp))
            Text("To'lov usuli:", fontWeight = FontWeight.SemiBold, color = TextSecondary)
            Spacer(Modifier.height(12.dp))
            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                PaymentMethodBtn("Naqd", "cash", Icons.Rounded.Money, selectedMethod, Modifier.weight(1f)) { selectedMethod = it }
                PaymentMethodBtn("Karta", "card", Icons.Rounded.CreditCard, selectedMethod, Modifier.weight(1f)) { selectedMethod = it }
                PaymentMethodBtn("QR", "qr", Icons.Rounded.QrCode, selectedMethod, Modifier.weight(1f)) { selectedMethod = it }
            }

            Spacer(Modifier.height(20.dp))
            Button(
                onClick = { onPayment(selectedMethod) },
                enabled = !isLoading,
                modifier = Modifier.fillMaxWidth().height(52.dp),
                colors = ButtonDefaults.buttonColors(containerColor = StatusReady),
                shape = RoundedCornerShape(14.dp)
            ) {
                if (isLoading) CircularProgressIndicator(Modifier.size(20.dp), color = Color.White)
                else {
                    Icon(Icons.Rounded.CheckCircle, null)
                    Spacer(Modifier.width(8.dp))
                    Text("To'lovni Tasdiqlash", fontWeight = FontWeight.Bold, fontSize = 16.sp)
                }
            }
        }
    }
}

@Composable
private fun PaymentMethodBtn(
    label: String, method: String, icon: ImageVector,
    selected: String, modifier: Modifier, onSelect: (String) -> Unit
) {
    val isSelected = selected == method
    Column(
        modifier = modifier
            .shadow(if (isSelected) 6.dp else 2.dp, RoundedCornerShape(14.dp))
            .background(if (isSelected) Primary else Surface, RoundedCornerShape(14.dp))
            .border(if (isSelected) 0.dp else 1.dp, ShadowDark, RoundedCornerShape(14.dp))
            .clickable { onSelect(method) }
            .padding(vertical = 14.dp),
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        Icon(icon, null, tint = if (isSelected) Color.White else TextSecondary)
        Spacer(Modifier.height(4.dp))
        Text(label, fontSize = 12.sp, color = if (isSelected) Color.White else TextSecondary, fontWeight = FontWeight.SemiBold)
    }
}
