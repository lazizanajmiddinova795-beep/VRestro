import 'package:flutter/material.dart';
import '../../../core/constants/app_colors.dart';
import 'waiter_tables_screen.dart';

class WaiterLayoutScreen extends StatelessWidget {
  const WaiterLayoutScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return const Scaffold(
      backgroundColor: AppColors.background,
      body: WaiterTablesScreen(),
    );
  }
}
