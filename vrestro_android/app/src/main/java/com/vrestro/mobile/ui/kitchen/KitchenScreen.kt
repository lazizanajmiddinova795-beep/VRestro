package com.vrestro.mobile.ui.kitchen

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
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.lifecycle.viewmodel.compose.viewModel
import com.vrestro.mobile.data.models.KitchenItemModel
import com.vrestro.mobile.ui.theme.*

@Composable
fun KitchenScreen(
    onLogout: () -> Unit,
    vm: KitchenViewModel = viewModel()
) {
    val state by vm.state.collectAsStateWithLifecycle()
    var selectedTab by remember { mutableIntStateOf(0) }

    LaunchedEffect(Unit) {
        vm.loadItems()
        vm.startAutoRefresh()
    }

    LaunchedEffect(selectedTab) {
        if (selectedTab == 1) vm.loadStopList() else vm.loadItems()
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
                    Icon(Icons.Rounded.OutdoorGrill, null, tint = Primary, modifier = Modifier.size(24.dp))
                    Spacer(Modifier.width(10.dp))
                    Text(
                        if (selectedTab == 0) "Oshxona • Monitor" else "Stop-List",
                        style = MaterialTheme.typography.titleLarge,
                        modifier = Modifier.weight(1f)
                    )
                    // Pending count badge
                    val pendingCount = state.items.count { it.status.lowercase() in listOf("new", "pending") }
                    if (pendingCount > 0 && selectedTab == 0) {
                        Badge(containerColor = StatusNew) {
                            Text(pendingCount.toString(), color = Color.White, fontWeight = FontWeight.Bold)
                        }
                        Spacer(Modifier.width(8.dp))
                    }
                    IconButton(onClick = { if (selectedTab == 0) vm.loadItems() else vm.loadStopList() }) {
                        Icon(Icons.Rounded.Refresh, null, tint = Primary)
                    }
                    IconButton(onClick = onLogout) {
                        Icon(Icons.Rounded.Logout, null, tint = TextSecondary)
                    }
                }
            }
        },
        bottomBar = {
            NavigationBar(containerColor = Surface) {
                NavigationBarItem(
                    selected = selectedTab == 0,
                    onClick = { selectedTab = 0 },
                    icon = { Icon(Icons.Rounded.Monitor, null) },
                    label = { Text("Monitor") }
                )
                NavigationBarItem(
                    selected = selectedTab == 1,
                    onClick = { selectedTab = 1 },
                    icon = { Icon(Icons.Rounded.Block, null) },
                    label = { Text("Stop-List") }
                )
            }
        }
    ) { padding ->
        Box(Modifier.padding(padding)) {
            when (selectedTab) {
                0 -> KitchenMonitorTab(state = state, vm = vm)
                1 -> StopListTab(state = state)
            }
        }
    }
}

@Composable
private fun KitchenMonitorTab(state: KitchenUiState, vm: KitchenViewModel) {
    if (state.isLoading && state.items.isEmpty()) {
        Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
            CircularProgressIndicator(color = Primary)
        }
        return
    }
    if (state.items.isEmpty()) {
        Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
            Column(horizontalAlignment = Alignment.CenterHorizontally) {
                Icon(Icons.Rounded.CheckCircle, null, tint = StatusReady, modifier = Modifier.size(64.dp))
                Spacer(Modifier.height(12.dp))
                Text("Barcha buyurtmalar bajarildi!", color = TextSecondary, fontSize = 16.sp)
            }
        }
        return
    }
    LazyColumn(
        contentPadding = PaddingValues(16.dp),
        verticalArrangement = Arrangement.spacedBy(12.dp)
    ) {
        // Group by status: new first, then preparing, then ready
        val sorted = state.items.sortedWith(compareBy {
            when (it.status.lowercase()) {
                "new", "pending" -> 0
                "preparing" -> 1
                "ready" -> 2
                else -> 3
            }
        })
        items(sorted) { item ->
            KitchenItemCard(item = item, onStatusChange = { newStatus ->
                vm.updateItemStatus(item.id, newStatus)
            })
        }
    }
}

@Composable
private fun KitchenItemCard(item: KitchenItemModel, onStatusChange: (String) -> Unit) {
    val (statusColor, statusLabel, nextStatus, nextLabel) = when (item.status.lowercase()) {
        "new", "pending" -> listOf(StatusNew, "Yangi", "preparing", "Tayyorlashni boshlash")
        "preparing" -> listOf(StatusPreparing, "Tayyorlanmoqda", "ready", "Tayyor")
        "ready" -> listOf(StatusReady, "Tayyor", "served", "Berildi")
        else -> listOf(TextSecondary, item.status, "", "")
    }

    Column(
        modifier = Modifier
            .fillMaxWidth()
            .shadow(4.dp, RoundedCornerShape(16.dp), spotColor = ShadowDark)
            .background(Surface, RoundedCornerShape(16.dp))
            .padding(16.dp)
    ) {
        Row(Modifier.fillMaxWidth(), verticalAlignment = Alignment.CenterVertically) {
            // Status indicator
            Box(
                Modifier
                    .size(12.dp)
                    .clip(CircleShape)
                    .background(statusColor as Color)
            )
            Spacer(Modifier.width(10.dp))
            Column(Modifier.weight(1f)) {
                Text(
                    item.food?.name ?: "Taom #${item.foodId}",
                    fontWeight = FontWeight.Bold, fontSize = 16.sp
                )
                Text(
                    "Stol: ${item.table?.name ?: "?"} • Buyurtma #${item.orderId}",
                    fontSize = 12.sp, color = TextSecondary
                )
            }
            // Quantity badge
            Box(
                modifier = Modifier
                    .size(40.dp)
                    .clip(CircleShape)
                    .background(Primary),
                contentAlignment = Alignment.Center
            ) {
                Text(
                    "×${item.quantity}",
                    color = Color.White,
                    fontWeight = FontWeight.ExtraBold,
                    fontSize = 14.sp
                )
            }
        }

        if (!item.note.isNullOrEmpty()) {
            Spacer(Modifier.height(8.dp))
            Surface(color = StatusPending.copy(0.1f), shape = RoundedCornerShape(8.dp)) {
                Row(Modifier.padding(horizontal = 10.dp, vertical = 6.dp)) {
                    Icon(Icons.Rounded.Note, null, tint = StatusPending, modifier = Modifier.size(14.dp))
                    Spacer(Modifier.width(6.dp))
                    Text(item.note, fontSize = 12.sp, color = TextPrimary)
                }
            }
        }

        Spacer(Modifier.height(12.dp))
        Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween, verticalAlignment = Alignment.CenterVertically) {
            Surface(
                color = (statusColor as Color).copy(0.15f),
                shape = RoundedCornerShape(8.dp)
            ) {
                Text(
                    statusLabel as String,
                    color = statusColor,
                    fontSize = 12.sp,
                    fontWeight = FontWeight.SemiBold,
                    modifier = Modifier.padding(horizontal = 10.dp, vertical = 4.dp)
                )
            }
            if (nextStatus.toString().isNotEmpty()) {
                Button(
                    onClick = { onStatusChange(nextStatus.toString()) },
                    colors = ButtonDefaults.buttonColors(
                        containerColor = when (nextStatus) {
                            "preparing" -> StatusPreparing
                            "ready" -> StatusReady
                            else -> TextSecondary
                        }
                    ),
                    shape = RoundedCornerShape(10.dp),
                    contentPadding = PaddingValues(horizontal = 16.dp, vertical = 8.dp)
                ) {
                    Text(nextLabel.toString(), fontSize = 13.sp, fontWeight = FontWeight.SemiBold)
                }
            }
        }
    }
}

@Composable
private fun StopListTab(state: KitchenUiState) {
    if (state.isLoading && state.stopListFoods.isEmpty()) {
        Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
            CircularProgressIndicator(color = Primary)
        }
        return
    }
    val unavailable = state.stopListFoods.filter { !it.isAvailable }
    if (unavailable.isEmpty()) {
        Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
            Column(horizontalAlignment = Alignment.CenterHorizontally) {
                Icon(Icons.Rounded.CheckCircle, null, tint = StatusReady, modifier = Modifier.size(64.dp))
                Spacer(Modifier.height(12.dp))
                Text("Stop-list bo'sh", color = TextSecondary, fontSize = 16.sp)
            }
        }
        return
    }
    LazyColumn(
        contentPadding = PaddingValues(16.dp),
        verticalArrangement = Arrangement.spacedBy(8.dp)
    ) {
        item {
            Row(Modifier.padding(bottom = 4.dp)) {
                Icon(Icons.Rounded.Warning, null, tint = StatusPending, modifier = Modifier.size(18.dp))
                Spacer(Modifier.width(8.dp))
                Text("${unavailable.size} ta taom mavjud emas", color = StatusPending, fontWeight = FontWeight.SemiBold)
            }
        }
        items(unavailable) { food ->
            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .shadow(2.dp, RoundedCornerShape(12.dp), spotColor = ShadowDark)
                    .background(Surface, RoundedCornerShape(12.dp))
                    .border(1.dp, StatusNew.copy(0.3f), RoundedCornerShape(12.dp))
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
                    Text("Mavjud emas", color = StatusNew, fontSize = 11.sp,
                        fontWeight = FontWeight.SemiBold,
                        modifier = Modifier.padding(horizontal = 8.dp, vertical = 4.dp))
                }
            }
        }
    }
}
