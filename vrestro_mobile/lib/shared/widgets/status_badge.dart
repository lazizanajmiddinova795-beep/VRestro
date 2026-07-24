import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';

class StatusBadge extends StatelessWidget {
  final String status;

  const StatusBadge({Key? key, required this.status}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    Color bg;
    Color textColor;
    String label;

    switch (status.toLowerCase()) {
      case 'new':
      case 'kutilmoqda':
        bg = AppColors.statusNew.withOpacity(0.15);
        textColor = AppColors.statusNew;
        label = 'Yangi / Kutilmoqda';
        break;
      case 'cooking':
      case 'tayyorlanmoqda':
        bg = AppColors.statusCooking.withOpacity(0.15);
        textColor = AppColors.statusCooking;
        label = 'Tayyorlanmoqda';
        break;
      case 'ready':
      case 'tayyor':
        bg = AppColors.statusReady.withOpacity(0.15);
        textColor = AppColors.statusReady;
        label = 'Tayyor';
        break;
      case 'delivered':
      case 'yetkazildi':
        bg = AppColors.statusDelivered.withOpacity(0.15);
        textColor = AppColors.statusDelivered;
        label = 'Yetkazildi';
        break;
      case 'paid':
      case 'toʻlangan':
        bg = AppColors.statusPaid.withOpacity(0.15);
        textColor = AppColors.statusPaid;
        label = 'Toʻlangan';
        break;
      default:
        bg = Colors.grey.withOpacity(0.15);
        textColor = Colors.black87;
        label = status;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: textColor.withOpacity(0.3)),
      ),
      child: Text(
        label,
        style: TextStyle(
          color: textColor,
          fontWeight: FontWeight.bold,
          fontSize: 12,
        ),
      ),
    );
  }
}
