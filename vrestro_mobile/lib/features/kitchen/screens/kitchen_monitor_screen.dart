import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/constants/app_colors.dart';
import '../../../shared/widgets/neumorphic_container.dart';
import '../../../shared/widgets/status_badge.dart';
import '../bloc/kitchen_bloc.dart';

class KitchenMonitorScreen extends StatefulWidget {
  const KitchenMonitorScreen({Key? key}) : super(key: key);

  @override
  State<KitchenMonitorScreen> createState() => _KitchenMonitorScreenState();
}

class _KitchenMonitorScreenState extends State<KitchenMonitorScreen> {
  Timer? _autoRefreshTimer;

  @override
  void initState() {
    super.initState();
    context.read<KitchenBloc>().add(FetchKitchenOrders());
    // Auto-polling every 5 seconds for live kitchen updates
    _autoRefreshTimer = Timer.periodic(const Duration(seconds: 5), (_) {
      if (mounted) {
        context.read<KitchenBloc>().add(FetchKitchenOrders());
      }
    });
  }

  @override
  void dispose() {
    _autoRefreshTimer?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.background,
        elevation: 0,
        title: const Text(
          'Oshpaz Monitori (KDS Live)',
          style: TextStyle(
            color: AppColors.textPrimary,
            fontWeight: FontWeight.bold,
          ),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: AppColors.primary),
            onPressed: () => context.read<KitchenBloc>().add(FetchKitchenOrders()),
          ),
        ],
      ),
      body: BlocBuilder<KitchenBloc, KitchenState>(
        builder: (context, state) {
          if (state is KitchenLoading && state is! KitchenOrdersLoaded) {
            return const Center(child: CircularProgressIndicator(color: AppColors.primary));
          }

          if (state is KitchenOrdersLoaded) {
            final orders = state.orders;
            if (orders.isEmpty) {
              return const Center(
                child: Text(
                  'Hozircha tayyorlanadigan buyurtmalar yoʻq 🎉',
                  style: TextStyle(fontSize: 16, color: AppColors.textSecondary),
                ),
              );
            }

            return ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: orders.length,
              itemBuilder: (context, index) {
                final order = orders[index];

                return Padding(
                  padding: const EdgeInsets.only(bottom: 16),
                  child: NeumorphicContainer(
                    borderRadius: 20,
                    child: Column(
                      crossAxisAlignment: CrossAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                      horizontal: 10, vertical: 4),
                                  decoration: BoxDecoration(
                                    color: AppColors.primary.withOpacity(0.12),
                                    borderRadius: BorderRadius.circular(10),
                                  ),
                                  child: Text(
                                    'Stol #${order.tableName ?? order.tableId ?? "—"}',
                                    style: const TextStyle(
                                      fontWeight: FontWeight.bold,
                                      color: AppColors.primary,
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 10),
                                Text(
                                  order.orderNumber,
                                  style: const TextStyle(
                                    fontWeight: FontWeight.bold,
                                    color: AppColors.textPrimary,
                                  ),
                                ),
                              ],
                            ),
                            StatusBadge(status: order.status),
                          ],
                        ),
                        const Divider(height: 24),

                        // List of items in this ticket
                        ...order.items.map((item) {
                          final bool isCooking = item.status.toLowerCase() == 'cooking';
                          final bool isReady = item.status.toLowerCase() == 'ready';

                          return Padding(
                            padding: const EdgeInsets.symmetric(vertical: 6),
                            child: Row(
                              children: [
                                Text(
                                  '${item.quantity}x',
                                  style: const TextStyle(
                                    fontWeight: FontWeight.w900,
                                    fontSize: 16,
                                    color: AppColors.primary,
                                  ),
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Text(
                                    item.foodName,
                                    style: TextStyle(
                                      fontSize: 15,
                                      fontWeight: FontWeight.bold,
                                      decoration: isReady
                                          ? TextDecoration.lineThrough
                                          : null,
                                      color: isReady
                                          ? AppColors.textLight
                                          : AppColors.textPrimary,
                                    ),
                                  ),
                                ),

                                // Status action button
                                if (!isReady)
                                  ElevatedButton(
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: isCooking
                                          ? AppColors.statusReady
                                          : AppColors.statusCooking,
                                      elevation: 0,
                                      padding: const EdgeInsets.symmetric(
                                          horizontal: 12, vertical: 6),
                                      shape: RoundedRectangleBorder(
                                        borderRadius: BorderRadius.circular(12),
                                      ),
                                    ),
                                    onPressed: () {
                                      final nextStatus =
                                          isCooking ? 'ready' : 'cooking';
                                      context.read<KitchenBloc>().add(
                                            UpdateItemStatusRequested(
                                              itemId: item.id,
                                              newStatus: nextStatus,
                                            ),
                                          );
                                    },
                                    child: Text(
                                      isCooking ? 'Tayyor!' : 'Pishirish',
                                      style: const TextStyle(
                                        color: Colors.white,
                                        fontSize: 12,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                  )
                                else
                                  const Icon(
                                    Icons.check_circle_rounded,
                                    color: AppColors.statusReady,
                                  ),
                              ],
                            ),
                          );
                        }).toList(),
                      ],
                    ),
                  ),
                );
              },
            );
          }

          return const Center(child: Text('Buyurtmalar yuklanmoqda...'));
        },
      ),
    );
  }
}
