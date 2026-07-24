import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/constants/app_colors.dart';
import '../../auth/bloc/auth_bloc.dart';
import 'cashier_tables_screen.dart';

class CashierLayoutScreen extends StatelessWidget {
  const CashierLayoutScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      body: const CashierTablesScreen(),
    );
  }
}
