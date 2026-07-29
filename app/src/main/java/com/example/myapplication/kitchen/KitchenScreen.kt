package com.example.myapplication.kitchen

import androidx.compose.foundation.background
import androidx.compose.foundation.border
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
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.lifecycle.viewmodel.compose.viewModel
import com.example.myapplication.data.models.FoodModel
import com.example.myapplication.data.models.KitchenItemModel
import com.example.myapplication.ui.theme.*

@Composable
fun KitchenScreen(onLogout: () -> Unit, vm: KitchenViewModel = viewModel()) {
    val state by vm.state.collectAsStateWithLifecycle()
    var tab by remember { mutableIntStateOf(0) }
    val snackbarHost = remember { SnackbarHostState() }

    LaunchedEffect(Unit) { vm.loadItems(); vm.startAutoRefresh() }
    LaunchedEffect(tab) { if (tab == 1) vm.loadStopList() else vm.loadItems() }
    LaunchedEffect(state.error) { state.error?.let { snackbarHost.showSnackbar(it); vm.clearError() } }

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
                    Icon(Icons.Rounded.OutdoorGrill, null, tint = Primary, modifier = Modifier.size(22.dp))
                    Spacer(Modifier.width(10.dp))
                    Text(if (tab == 0) "Oshxona Monitor" else "Stop-List",
                        style = MaterialTheme.typography.titleLarge, modifier = Modifier.weight(1f))
                    val pending = state.items.count { it.status.lowercase() in listOf("new", "pending") }
                    if (pending > 0 && tab == 0) {
                        Badge(containerColor = StatusNew) { Text(pending.toString(), color = Color.White, fontWeight = FontWeight.Bold, fontSize = 11.sp) }
                        Spacer(Modifier.width(8.dp))
                    }
                    IconButton(onClick = { if (tab == 0) vm.loadItems() else vm.loadStopList() }) {
                        Icon(Icons.Rounded.Refresh, null, tint = Primary)
                    }
                    IconButton(onClick = onLogout) { Icon(Icons.Rounded.Logout, null, tint = TextSecondary) }
                }
            }
        },
        bottomBar = {
            NavigationBar(containerColor = Surface, tonalElevation = 0.dp) {
                NavigationBarItem(selected = tab == 0, onClick = { tab = 0 },
                    icon = { Icon(Icons.Rounded.Monitor, null) }, label = { Text("Monitor") })
                NavigationBarItem(selected = tab == 1, onClick = { tab = 1 },
                    icon = { Icon(Icons.Rounded.Block, null) }, label = { Text("Stop-List") })
            }
        }
    ) { pad ->
        Box(Modifier.padding(pad)) {
            when (tab) {
                0 -> MonitorTab(state, vm)
                1 -> StopListTab(state.stopList)
            }
        }
    }
}

@Composable
private fun MonitorTab(state: KitchenState, vm: KitchenViewModel) {
    if (state.isLoading && state.items.isEmpty()) {
        Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) { CircularProgressIndicator(color = Primary) }
        return
    }
    if (state.items.isEmpty()) {
        Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
            Column(horizontalAlignment = Alignment.CenterHorizontally) {
                Icon(Icons.Rounded.CheckCircle, null, tint = StatusReady, modifier = Modifier.size(64.dp))
                Spacer(Modifier.height(12.dp))
                Text("Barcha buyurtmalar tayyor!", color = TextSecondary, fontSize = 15.sp)
            }
        }
        return
    }

    val sorted = state.items.sortedBy {
        when (it.status.lowercase()) { "new", "pending" -> 0; "preparing" -> 1; "ready" -> 2; else -> 3 }
    }

    LazyColumn(contentPadding = PaddingValues(16.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
        // Status summary bar
        item {
            val newCount  = sorted.count { it.status.lowercase() in listOf("new","pending") }
            val prepCount = sorted.count { it.status.lowercase() == "preparing" }
            val readCount = sorted.count { it.status.lowercase() == "ready" }
            Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                if (newCount  > 0) MiniChip("Yangi: $newCount",  StatusNew)
                if (prepCount > 0) MiniChip("Tayyorlanmoqda: $prepCount", StatusPreparing)
                if (readCount > 0) MiniChip("Tayyor: $readCount", StatusReady)
            }
        }
        items(sorted, key = { it.id }) { item -> KitchenItemCard(item) { vm.updateStatus(item.id, it) } }
    }
}

@Composable
private fun MiniChip(text: String, color: Color) {
    Surface(color = color.copy(0.12f), shape = RoundedCornerShape(20.dp)) {
        Text(text, fontSize = 11.sp, color = color, fontWeight = FontWeight.SemiBold,
            modifier = Modifier.padding(horizontal = 10.dp, vertical = 5.dp))
    }
}

@Composable
private fun KitchenItemCard(item: KitchenItemModel, onStatusChange: (String) -> Unit) {
    val (statusColor, statusLabel, nextStatus, nextLabel) = when (item.status.lowercase()) {
        "new", "pending" -> Quad(StatusNew, "Yangi", "preparing", "Tayyorlashni boshlash")
        "preparing"      -> Quad(StatusPreparing, "Tayyorlanmoqda", "ready", "Tayyor ✓")
        "ready"          -> Quad(StatusReady, "Tayyor", "served", "Berildi")
        else             -> Quad(TextSecondary, item.status, "", "")
    }

    Column(
        Modifier.fillMaxWidth()
            .shadow(4.dp, RoundedCornerShape(18.dp), spotColor = ShadowDark)
            .background(Surface, RoundedCornerShape(18.dp))
            .border(1.dp, statusColor.copy(0.2f), RoundedCornerShape(18.dp))
            .padding(16.dp)
    ) {
        Row(Modifier.fillMaxWidth(), verticalAlignment = Alignment.CenterVertically) {
            // Color indicator
            Box(Modifier.width(4.dp).height(48.dp).clip(RoundedCornerShape(2.dp)).background(statusColor))
            Spacer(Modifier.width(12.dp))
            Column(Modifier.weight(1f)) {
                Text(item.food?.name ?: "Taom #${item.foodId}", fontWeight = FontWeight.Bold, fontSize = 16.sp)
                Text(buildString {
                    item.table?.let { append("Stol: ${it.displayName}") }
                    append("  •  Buyurtma #${item.orderId}")
                    item.createdAt?.take(5)?.let { append("  •  $it") }
                }, fontSize = 11.sp, color = TextSecondary)
            }
            // Quantity badge
            Box(Modifier.size(44.dp).clip(CircleShape).background(Primary), contentAlignment = Alignment.Center) {
                Text("×${item.quantity}", color = Color.White, fontWeight = FontWeight.ExtraBold, fontSize = 14.sp)
            }
        }

        if (!item.note.isNullOrEmpty()) {
            Spacer(Modifier.height(10.dp))
            Surface(color = Accent.copy(0.1f), shape = RoundedCornerShape(10.dp)) {
                Row(Modifier.padding(horizontal = 10.dp, vertical = 6.dp), verticalAlignment = Alignment.CenterVertically) {
                    Icon(Icons.Rounded.StickyNote2, null, tint = Accent, modifier = Modifier.size(14.dp))
                    Spacer(Modifier.width(6.dp))
                    Text(item.note, fontSize = 12.sp, color = TextPrimary)
                }
            }
        }

        Spacer(Modifier.height(12.dp))
        Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween, verticalAlignment = Alignment.CenterVertically) {
            Surface(color = statusColor.copy(0.12f), shape = RoundedCornerShape(8.dp)) {
                Text(statusLabel, color = statusColor, fontSize = 12.sp, fontWeight = FontWeight.SemiBold,
                    modifier = Modifier.padding(horizontal = 10.dp, vertical = 4.dp))
            }
            if (nextStatus.isNotEmpty()) {
                Button(
                    onClick = { onStatusChange(nextStatus) },
                    colors = ButtonDefaults.buttonColors(containerColor = when (nextStatus) {
                        "preparing" -> StatusPreparing; "ready" -> StatusReady; else -> TextSecondary
                    }),
                    shape = RoundedCornerShape(12.dp),
                    contentPadding = PaddingValues(horizontal = 18.dp, vertical = 8.dp),
                    elevation = ButtonDefaults.buttonElevation(4.dp)
                ) {
                    Text(nextLabel, fontSize = 13.sp, fontWeight = FontWeight.SemiBold)
                }
            }
        }
    }
}

@Composable
private fun StopListTab(foods: List<FoodModel>) {
    val unavailable = foods.filter { !it.isAvailable }
    if (unavailable.isEmpty()) {
        Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
            Column(horizontalAlignment = Alignment.CenterHorizontally) {
                Icon(Icons.Rounded.CheckCircle, null, tint = StatusReady, modifier = Modifier.size(64.dp))
                Spacer(Modifier.height(12.dp))
                Text("Stop-list bo'sh", color = TextSecondary, fontSize = 15.sp)
            }
        }
        return
    }
    LazyColumn(contentPadding = PaddingValues(16.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
        item {
            Row(Modifier.padding(bottom = 4.dp), verticalAlignment = Alignment.CenterVertically) {
                Icon(Icons.Rounded.Warning, null, tint = StatusPending, modifier = Modifier.size(18.dp))
                Spacer(Modifier.width(8.dp))
                Text("${unavailable.size} ta taom mavjud emas", color = StatusPending, fontWeight = FontWeight.SemiBold)
            }
        }
        items(unavailable) { food ->
            Row(
                Modifier.fillMaxWidth()
                    .shadow(2.dp, RoundedCornerShape(14.dp), spotColor = ShadowDark)
                    .background(Surface, RoundedCornerShape(14.dp))
                    .border(1.dp, StatusNew.copy(0.25f), RoundedCornerShape(14.dp))
                    .padding(14.dp),
                verticalAlignment = Alignment.CenterVertically
            ) {
                Icon(Icons.Rounded.Block, null, tint = StatusNew, modifier = Modifier.size(20.dp))
                Spacer(Modifier.width(12.dp))
                Column(Modifier.weight(1f)) {
                    Text(food.name, fontWeight = FontWeight.SemiBold, color = TextPrimary)
                    Text("${"%,.0f".format(food.price)} so'm", fontSize = 12.sp, color = TextSecondary)
                }
                Surface(color = StatusNew.copy(0.1f), shape = RoundedCornerShape(8.dp)) {
                    Text("Mavjud emas", color = StatusNew, fontSize = 11.sp, fontWeight = FontWeight.SemiBold,
                        modifier = Modifier.padding(horizontal = 8.dp, vertical = 4.dp))
                }
            }
        }
    }
}

// Helper to destructure 4 values
private data class Quad<A, B, C, D>(val first: A, val second: B, val third: C, val fourth: D)
private operator fun <A, B, C, D> Quad<A, B, C, D>.component1() = first
private operator fun <A, B, C, D> Quad<A, B, C, D>.component2() = second
private operator fun <A, B, C, D> Quad<A, B, C, D>.component3() = third
private operator fun <A, B, C, D> Quad<A, B, C, D>.component4() = fourth
