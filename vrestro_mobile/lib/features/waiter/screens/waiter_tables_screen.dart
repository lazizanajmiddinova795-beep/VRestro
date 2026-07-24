import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/constants/app_colors.dart';
import '../../../shared/widgets/neumorphic_container.dart';
import '../../../shared/widgets/status_badge.dart';
import '../bloc/waiter_bloc.dart';
import 'waiter_order_screen.dart';

class WaiterTablesScreen extends StatefulWidget {
  const WaiterTablesScreen({Key? key}) : super(key: key);

  @override
  State<WaiterTablesScreen> createState() => _WaiterTablesScreenState();
}

class _WaiterTablesScreenState extends State<WaiterTablesScreen> {
  @override
  void initState() {
    super.initState();
    context.read<WaiterBloc>().add(FetchWaiterTables());
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.background,
        elevation: 0,
        title: const Text(
          'Ofitsiant • Stollar Zali',
          style: TextStyle(
            color: AppColors.textPrimary,
            fontWeight: FontWeight.bold,
          ),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: AppColors.primary),
            onPressed: () => context.read<WaiterBloc>().add(FetchWaiterTables()),
          ),
        ],
      ),
      body: BlocBuilder<WaiterBloc, WaiterState>(
        builder: (context, state) {
          if (state is WaiterLoading) {
            return const Center(child: CircularProgressIndicator(color: AppColors.primary));
          }

          if (state is WaiterTablesLoaded) {
            final tables = state.tables;
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
                final isOccupied = table.status.toLowerCase() == 'occupied';

                return NeumorphicContainer(
                  borderRadius: 20,
                  isSelected: isOccupied,
                  onTap: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => WaiterOrderScreen(table: table),
                      ),
                    );
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
                          StatusBadge(status: isOccupied ? 'Band' : 'Boʻsh'),
                        ],
                      ),
                      Row(
                        children: [
                          const Icon(Icons.chair_outlined,
                              size: 16, color: AppColors.textSecondary),
                          const SizedBox(width: 4),
                          Text(
                            '${table.capacity} kishilik',
                            style: const TextStyle(
                              fontSize: 13,
                              color: AppColors.textSecondary,
                            ),
                          ),
                        ],
                      ),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text(
                            'Buyurtma berish',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                              color: AppColors.primary,
                            ),
                          ),
                          const Icon(
                            Icons.arrow_forward_rounded,
                            size: 16,
                            color: AppColors.primary,
                          ),
                        ],
                      ),
                    ],
                  ),
                );
              },
            );
          }

          return const Center(child: Text('Stollarni yuklash...'));
        },
      ),
    );
  }
}
