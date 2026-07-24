import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/constants/app_colors.dart';
import '../../../shared/widgets/neumorphic_container.dart';
import '../../../shared/widgets/status_badge.dart';
import '../../../shared/models/table_model.dart';
import '../bloc/cashier_bloc.dart';
import 'cashier_order_screen.dart';

class CashierTablesScreen extends StatefulWidget {
  const CashierTablesScreen({Key? key}) : super(key: key);

  @override
  State<CashierTablesScreen> createState() => _CashierTablesScreenState();
}

class _CashierTablesScreenState extends State<CashierTablesScreen> {
  @override
  void initState() {
    super.initState();
    context.read<CashierBloc>().add(FetchCashierTables());
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.background,
        elevation: 0,
        title: const Text(
          'Kassir • Stollari',
          style: TextStyle(
            color: AppColors.textPrimary,
            fontWeight: FontWeight.bold,
          ),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: AppColors.primary),
            onPressed: () => context.read<CashierBloc>().add(FetchCashierTables()),
          ),
        ],
      ),
      body: BlocBuilder<CashierBloc, CashierState>(
        builder: (context, state) {
          if (state is CashierLoading) {
            return const Center(
              child: CircularProgressIndicator(color: AppColors.primary),
            );
          }

          if (state is CashierTablesLoaded) {
            final tables = state.tables;
            if (tables.isEmpty) {
              return const Center(child: Text('Stollar topilmadi'));
            }

            return GridView.builder(
              padding: const EdgeInsets.all(16),
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 2,
                crossAxisSpacing: 16,
                mainAxisSpacing: 16,
                childAspectRatio: 1.1,
              ),
              itemCount: tables.length,
              itemBuilder: (context, index) {
                final table = tables[index];
                final bool isOccupied = table.status.toLowerCase() == 'occupied' ||
                    table.activeOrderId != null;

                return NeumorphicContainer(
                  borderRadius: 20,
                  isSelected: isOccupied,
                  onTap: () {
                    if (table.activeOrderId != null) {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => CashierOrderScreen(
                            orderId: table.activeOrderId!,
                            tableNumber: table.number,
                          ),
                        ),
                      );
                    } else {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Text('Stol ${table.number} da faol buyurtma yoʻq'),
                        ),
                      );
                    }
                  },
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    crossAxisAlignment: CrossAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            'Stol #${table.number}',
                            style: const TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                              color: AppColors.textPrimary,
                            ),
                          ),
                          StatusBadge(
                            status: isOccupied ? 'Band' : 'Boʻsh',
                          ),
                        ],
                      ),
                      Row(
                        children: [
                          const Icon(Icons.people_outline,
                              size: 16, color: AppColors.textSecondary),
                          const SizedBox(width: 4),
                          Text(
                            'Sigʻim: ${table.capacity} kishi',
                            style: const TextStyle(
                              fontSize: 13,
                              color: AppColors.textSecondary,
                            ),
                          ),
                        ],
                      ),
                      if (table.totalAmount != null)
                        Text(
                          '${table.totalAmount!.toStringAsFixed(0)} soʻm',
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w900,
                            color: AppColors.primary,
                          ),
                        )
                      else
                        const Text(
                          '0 soʻm',
                          style: TextStyle(
                            fontSize: 14,
                            color: AppColors.textLight,
                          ),
                        ),
                    ],
                  ),
                );
              },
            );
          }

          return const Center(child: Text('Stollarni yuklash jarayoni...'));
        },
      ),
    );
  }
}
