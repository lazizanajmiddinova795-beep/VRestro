import 'package:flutter/material.dart';
import '../../core/theme/neumorphic_theme.dart';
import '../../core/constants/app_colors.dart';

class NeumorphicContainer extends StatelessWidget {
  final Widget child;
  final double borderRadius;
  final EdgeInsetsGeometry padding;
  final EdgeInsetsGeometry? margin;
  final bool isPressed;
  final bool isSelected;
  final VoidCallback? onTap;
  final double? width;
  final double? height;

  const NeumorphicContainer({
    Key? key,
    required this.child,
    this.borderRadius = 16.0,
    this.padding = const EdgeInsets.all(16.0),
    this.margin,
    this.isPressed = false,
    this.isSelected = false,
    this.onTap,
    this.width,
    this.height,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    Widget content = Container(
      width: width,
      height: height,
      padding: padding,
      margin: margin,
      decoration: isPressed
          ? NeumorphicTheme.neumorphicPressedBox(borderRadius: borderRadius)
          : NeumorphicTheme.neumorphicBox(
              borderRadius: borderRadius,
              isSelected: isSelected,
            ),
      child: child,
    );

    if (onTap != null) {
      return GestureDetector(
        onTap: onTap,
        child: content,
      );
    }

    return content;
  }
}
