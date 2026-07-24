import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/constants/app_colors.dart';
import '../../../shared/widgets/neumorphic_container.dart';
import '../../../shared/widgets/neumorphic_button.dart';
import '../../../shared/models/table_model.dart';
import '../../../shared/models/food_model.dart';
import '../../../shared/models/category_model.dart';
import '../bloc/waiter_bloc.dart';

class WaiterOrderScreen extends StatefulWidget {
  final TableModel table;

  const WaiterOrderScreen({Key? key, required this.table}) : super(key: key);

  @override
  State<WaiterOrderScreen> createState() => _WaiterOrderScreenState();
}

class _WaiterOrderScreenState extends State<WaiterOrderScreen> {
  int? _selectedCategoryId;
  final Map<int, int> _selectedQuantities = {}; // foodId -> qty

  @override
  void initState() {
    super.initState();
    context.read<WaiterBloc>().add(FetchMenuAndCategories());
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
          'Yangi Buyurtma • Stol #${widget.table.number}',
          style: const TextStyle(
            color: AppColors.textPrimary,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
      body: BlocConsumer<WaiterBloc, WaiterState>(
        listener: (context, state) {
          if (state is OrderSubmissionSuccess) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text('Buyurtma oshxonaga muvaffaqiyatli yuborildi!'),
                backgroundColor: AppColors.statusReady,
              ),
            );
            Navigator.pop(context);
          } else if (state is WaiterError) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text(state.message),
                backgroundColor: AppColors.statusNew,
              ),
            );
          }
        },
        builder: (context, state) {
          if (state is WaiterLoading) {
            return const Center(child: CircularProgressIndicator(color: AppColors.primary));
          }

          if (state is WaiterMenuLoaded) {
            final categories = state.categories;
            final foods = _selectedCategoryId == null
                ? state.foods
                : state.foods.where((f) => f.categoryId == _selectedCategoryId).toList();

            double totalSum = 0;
            int totalItems = 0;
            _selectedQuantities.forEach((foodId, qty) {
              final food = state.foods.firstWhere((f) => f.id == foodId);
              totalSum += food.price * qty;
              totalItems += qty;
            });

            return Column(
              children: [
                // Category Selector horizontal bar
                SizedBox(
                  height: 54,
                  child: ListView.builder(
                    scrollDirection: Axis.horizontal,
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    itemCount: categories.length + 1,
                    itemBuilder: (context, index) {
                      if (index == 0) {
                        final isSelected = _selectedCategoryId == null;
                        return Padding(
                          padding: const EdgeInsets.only(right: 10),
                          child: NeumorphicContainer(
                            isSelected: isSelected,
                            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                            onTap: () => setState(() => _selectedCategoryId = null),
                            child: Center(
                              child: Text(
                                'Barchasi',
                                style: TextStyle(
                                  fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                                  color: isSelected ? AppColors.primary : AppColors.textSecondary,
                                ),
                              ),
                            ),
                          ),
                        );
                      }

                      final cat = categories[index - 1];
                      final isSelected = _selectedCategoryId == cat.id;

                      return Padding(
                        padding: const EdgeInsets.only(right: 10),
                        child: NeumorphicContainer(
                          isSelected: isSelected,
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                          onTap: () => setState(() => _selectedCategoryId = cat.id),
                          child: Center(
                            child: Text(
                              cat.name,
                              style: TextStyle(
                                fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                                color: isSelected ? AppColors.primary : AppColors.textSecondary,
                              ),
                            ),
                          ),
                        ),
                      );
                    },
                  ),
                ),
                const SizedBox(height: 12),

                // Dishes grid
                Expanded(
                  child: GridView.builder(
                    padding: const EdgeInsets.all(16),
                    gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: 2,
                      crossAxisSpacing: 14,
                      mainAxisSpacing: 14,
                      childAspectRatio: 0.95,
                    ),
                    itemCount: foods.length,
                    itemBuilder: (context, index) {
                      final food = foods[index];
                      final qty = _selectedQuantities[food.id] ?? 0;

                      return NeumorphicContainer(
                        borderRadius: 18,
                        child: Column(
                          crossAxisAlignment: CrossAlignment.start,
                          children: [
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAlignment.start,
                                children: [
                                  Text(
                                    food.name,
                                    maxLines: 2,
                                    overflow: TextOverflow.ellipsis,
                                    style: const TextStyle(
                                      fontWeight: FontWeight.bold,
                                      fontSize: 14,
                                      color: AppColors.textPrimary,
                                    ),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    '${food.price.toStringAsFixed(0)} soʻm',
                                    style: const TextStyle(
                                      fontWeight: FontWeight.w900,
                                      color: AppColors.primary,
                                      fontSize: 14,
                                    ),
                                  ),
                                ],
                              ),
                            ),

                            // Quantity increment/decrement buttons
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                GestureDetector(
                                  onTap: () {
                                    if (qty > 0) {
                                      setState(() {
                                        if (qty == 1) {
                                          _selectedQuantities.remove(food.id);
                                        } else {
                                          _selectedQuantities[food.id] = qty - 1;
                                        }
                                      });
                                    }
                                  },
                                  child: Container(
                                    width: 32,
                                    height: 32,
                                    decoration: BoxDecoration(
                                      color: AppColors.cardBg,
                                      borderRadius: BorderRadius.circular(10),
                                      boxShadow: const [
                                        BoxShadow(
                                          color: AppColors.darkShadow,
                                          offset: Offset(2, 2),
                                          blurRadius: 4,
                                        ),
                                      ],
                                    ),
                                    child: const Icon(Icons.remove, size: 18),
                                  ),
                                ),
                                Text(
                                  '$qty',
                                  style: const TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                GestureDetector(
                                  onTap: () {
                                    setState(() {
                                      _selectedQuantities[food.id] = qty + 1;
                                    });
                                  },
                                  child: Container(
                                    width: 32,
                                    height: 32,
                                    decoration: BoxDecoration(
                                      color: AppColors.primary,
                                      borderRadius: BorderRadius.circular(10),
                                    ),
                                    child: const Icon(Icons.add, size: 18, color: Colors.white),
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      );
                    },
                  ),
                ),

                // Order summary bar
                if (totalItems > 0)
                  NeumorphicContainer(
                    borderRadius: 24,
                    padding: const EdgeInsets.all(20),
                    child: SafeArea(
                      child: Row(
                        children: [
                          Column(
                            crossAxisAlignment: CrossAlignment.start,
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Text(
                                '$totalItems ta taom tanlandi',
                                style: const TextStyle(
                                  color: AppColors.textSecondary,
                                  fontSize: 12,
                                ),
                              ),
                              Text(
                                '${totalSum.toStringAsFixed(0)} soʻm',
                                style: const TextStyle(
                                  color: AppColors.primary,
                                  fontSize: 20,
                                  fontWeight: FontWeight.w900,
                                ),
                              ),
                            ],
                          ),
                          const Spacer(),
                          NeumorphicButton(
                            text: 'Oshxonaga yuborish',
                            icon: Icons.send_rounded,
                            onPressed: () {
                              final List<Map<String, dynamic>> itemsList = [];
                              _selectedQuantities.forEach((foodId, qty) {
                                itemsList.add({
                                  'food_id': foodId,
                                  'quantity': qty,
                                });
                              });

                              context.read<WaiterBloc>().add(
                                    SubmitNewOrderRequested(
                                      tableId: widget.table.id,
                                      items: itemsList,
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

          return const Center(child: Text('Menyu yuklanmoqda...'));
        },
      ),
    );
  }
}
