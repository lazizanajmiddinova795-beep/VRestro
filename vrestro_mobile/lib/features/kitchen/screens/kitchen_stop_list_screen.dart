import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/constants/app_colors.dart';
import '../../../shared/widgets/neumorphic_container.dart';
import '../bloc/kitchen_bloc.dart';

class KitchenStopListScreen extends StatefulWidget {
  const KitchenStopListScreen({Key? key}) : super(key: key);

  @override
  State<KitchenStopListScreen> createState() => _KitchenStopListScreenState();
}

class _KitchenStopListScreenState extends State<KitchenStopListScreen> {
  @override
  void initState() {
    super.initState();
    context.read<KitchenBloc>().add(FetchKitchenStopList());
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.background,
        elevation: 0,
        title: const Text(
          'Oshxona • Stop-List Boshqaruvi',
          style: TextStyle(
            color: AppColors.textPrimary,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
      body: BlocBuilder<KitchenBloc, KitchenState>(
        builder: (context, state) {
          if (state is KitchenLoading) {
            return const Center(child: CircularProgressIndicator(color: AppColors.primary));
          }

          if (state is KitchenStopListLoaded) {
            final foods = state.foods;

            return ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: foods.length,
              itemBuilder: (context, index) {
                final food = foods[index];

                return Padding(
                  padding: const EdgeInsets.only(bottom: 12),
                  child: NeumorphicContainer(
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Column(
                          crossAxisAlignment: CrossAlignment.start,
                          children: [
                            Text(
                              food.name,
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                                color: food.isAvailable
                                    ? AppColors.textPrimary
                                    : AppColors.textLight,
                                decoration: food.isAvailable
                                    ? null
                                    : TextDecoration.lineThrough,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              '${food.price.toStringAsFixed(0)} soʻm',
                              style: const TextStyle(
                                fontSize: 13,
                                color: AppColors.textSecondary,
                              ),
                            ),
                          ],
                        ),
                        Switch(
                          value: food.isAvailable,
                          activeColor: AppColors.statusReady,
                          inactiveThumbColor: AppColors.statusNew,
                          onChanged: (val) {
                            context.read<KitchenBloc>().add(
                                  ToggleFoodAvailabilityRequested(food.id),
                                );
                          },
                        ),
                      ],
                    ),
                  ),
                );
              },
            );
          }

          return const Center(child: Text('Stop-list yuklanmoqda...'));
        },
      ),
    );
  }
}
