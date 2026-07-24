import 'package:flutter/material.dart';
import '../../../core/constants/app_colors.dart';
import 'kitchen_monitor_screen.dart';
import 'kitchen_stop_list_screen.dart';

class ChefLayoutScreen extends StatefulWidget {
  const ChefLayoutScreen({Key? key}) : super(key: key);

  @override
  State<ChefLayoutScreen> createState() => _ChefLayoutScreenState();
}

class _ChefLayoutScreenState extends State<ChefLayoutScreen> {
  int _currentIndex = 0;

  final List<Widget> _screens = const [
    KitchenMonitorScreen(),
    KitchenStopListScreen(),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      body: IndexedStack(
        index: _currentIndex,
        children: _screens,
      ),
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _currentIndex,
        backgroundColor: AppColors.background,
        selectedItemColor: AppColors.primary,
        unselectedItemColor: AppColors.textSecondary,
        elevation: 10,
        onTap: (index) => setState(() => _currentIndex = index),
        items: const [
          BottomNavigationBarViewItem(
            icon: Icon(Icons.soup_kitchen_rounded),
            label: 'Monitor (KDS)',
          ),
          BottomNavigationBarViewItem(
            icon: Icon(Icons.do_not_disturb_on_rounded),
            label: 'Stop-List',
          ),
        ],
      ),
    );
  }
}

class BottomNavigationBarViewItem extends BottomNavigationBarItem {
  const BottomNavigationBarViewItem({
    required Widget icon,
    required String label,
  }) : super(icon: icon, label: label);
}
