import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/constants/app_colors.dart';
import '../../../shared/widgets/neumorphic_container.dart';
import '../../../shared/widgets/neumorphic_button.dart';
import '../../../shared/widgets/status_badge.dart';
import '../bloc/cashier_bloc.dart';

class CashierOrderScreen extends StatefulWidget {
  final int orderId;
  final String tableNumber;

  const CashierOrderScreen({
    Key? key,
    required this.orderId,
    required this.tableNumber,
  }) : super(key: key);

  @override
  State<CashierOrderScreen> createState() => _CashierOrderScreenState();
}

class _CashierOrderScreenState extends State<CashierOrderScreen> {
  String _selectedPaymentType = 'cash';

  @override
  void initState() {
    super.initState();
    context.read<CashierBloc>().add(FetchOrderDetails(widget.orderId));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.background,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_rounded, color: AppColors.textPrimary),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          'Buyurtma #${widget.orderId} (Stol ${widget.tableNumber})',
          style: const TextStyle(
            color: AppColors.textPrimary,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
      body: BlocConsumer<CashierBloc, CashierState>(
        listener: (context, state) {
          if (state is PaymentSuccess) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text('Toʻlov muvaffaqiyatli qabul qilindi!'),
                backgroundColor: AppColors.statusReady,
              ),
            );
            Navigator.pop(context);
          } else if (state is CashierError) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text(state.message),
                backgroundColor: AppColors.statusNew,
              ),
            );
          }
        },
        builder: (context, state) {
          if (state is CashierLoading) {
            return const Center(child: CircularProgressIndicator(color: AppColors.primary));
          }

          if (state is OrderDetailsLoaded) {
            final order = state.order;

            return Column(
              children: [
                Expanded(
                  child: ListView(
                    padding: const EdgeInsets.all(16),
                    children: [
                      // Order Info Header Card
                      NeumorphicContainer(
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Column(
                              crossAxisAlignment: CrossAlignment.start,
                              children: [
                                Text(
                                  order.orderNumber,
                                  style: const TextStyle(
                                    fontSize: 18,
                                    fontWeight: FontWeight.bold,
                                    color: AppColors.textPrimary,
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  'Ofitsiant: ${order.waiterName ?? "Nomaʼlum"}',
                                  style: const TextStyle(
                                    color: AppColors.textSecondary,
                                    fontSize: 13,
                                  ),
                                ),
                              ],
                            ),
                            StatusBadge(status: order.status),
                          ],
                        ),
                      ),
                      const SizedBox(height: 16),

                      const Text(
                        'Buyurtma tarkibi',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                          color: AppColors.textPrimary,
                        ),
                      ),
                      const SizedBox(height: 10),

                      // List of items
                      ...order.items.map((item) {
                        return Padding(
                          padding: const EdgeInsets.only(bottom: 10),
                          child: NeumorphicContainer(
                            padding: const EdgeInsets.all(14),
                            child: Row(
                              children: [
                                Container(
                                  width: 32,
                                  height: 32,
                                  decoration: BoxDecoration(
                                    color: AppColors.primary.withOpacity(0.1),
                                    shape: BoxShape.circle,
                                  ),
                                  child: Center(
                                    child: Text(
                                      '${item.quantity}x',
                                      style: const TextStyle(
                                        color: AppColors.primary,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Text(
                                    item.foodName,
                                    style: const TextStyle(
                                      fontWeight: FontWeight.w600,
                                      color: AppColors.textPrimary,
                                    ),
                                  ),
                                ),
                                Text(
                                  '${(item.price * item.quantity).toStringAsFixed(0)} soʻm',
                                  style: const TextStyle(
                                    fontWeight: FontWeight.bold,
                                    color: AppColors.textPrimary,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        );
                      }).toList(),
                    ],
                  ),
                ),

                // Payment Bottom Sheet
                NeumorphicContainer(
                  borderRadius: 24,
                  padding: const EdgeInsets.all(20),
                  child: SafeArea(
                    child: Column(
                      crossAxisAlignment: CrossAlignment.stretch,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text(
                              'Jami toʻlov summasi:',
                              style: TextStyle(
                                fontSize: 16,
                                color: AppColors.textSecondary,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            Text(
                              '${order.totalAmount.toStringAsFixed(0)} soʻm',
                              style: const TextStyle(
                                fontSize: 22,
                                color: AppColors.primary,
                                fontWeight: FontWeight.w900,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 16),

                        // Payment type selection
                        Row(
                          children: [
                            _buildPaymentOption('Naqd', 'cash', Icons.money_rounded),
                            const SizedBox(width: 10),
                            _buildPaymentOption('Karta', 'card', Icons.credit_card_rounded),
                            const SizedBox(width: 10),
                            _buildPaymentOption('Click', 'click', Icons.phone_android_rounded),
                          ],
                        ),
                        const SizedBox(height: 20),

                        NeumorphicButton(
                          text: 'Toʻlovni Qabul Qilish',
                          icon: Icons.check_circle_outline_rounded,
                          onPressed: () {
                            context.read<CashierBloc>().add(
                                  ProcessPaymentRequested(
                                    orderId: order.id,
                                    amount: order.totalAmount,
                                    paymentType: _selectedPaymentType,
                                  ),
                                );
                          },
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            );
          }

          return const Center(child: Text('Buyurtma yuklanmoqda...'));
        },
      ),
    );
  }

  Widget _buildPaymentOption(String label, String value, IconData icon) {
    final bool isSelected = _selectedPaymentType == value;
    return Expanded(
      child: NeumorphicContainer(
        isSelected: isSelected,
        padding: const EdgeInsets.symmetric(vertical: 12),
        onTap: () => setState(() => _selectedPaymentType = value),
        child: Column(
          children: [
            Icon(
              icon,
              color: isSelected ? AppColors.primary : AppColors.textSecondary,
              size: 22,
            ),
            const SizedBox(height: 4),
            Text(
              label,
              style: TextStyle(
                fontSize: 12,
                fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                color: isSelected ? AppColors.primary : AppColors.textSecondary,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
