import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'core/theme/neumorphic_theme.dart';
import 'core/constants/app_colors.dart';
import 'features/auth/bloc/auth_bloc.dart';
import 'features/auth/screens/login_screen.dart';
import 'features/cashier/bloc/cashier_bloc.dart';
import 'features/cashier/screens/cashier_layout_screen.dart';
import 'features/waiter/bloc/waiter_bloc.dart';
import 'features/waiter/screens/waiter_layout_screen.dart';
import 'features/kitchen/bloc/kitchen_bloc.dart';
import 'features/kitchen/screens/chef_layout_screen.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const VRestroApp());
}

class VRestroApp extends StatelessWidget {
  const VRestroApp({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return MultiBlocProvider(
      providers: [
        BlocProvider<AuthBloc>(
          create: (context) => AuthBloc()..add(CheckAuthStatus()),
        ),
        BlocProvider<CashierBloc>(
          create: (context) => CashierBloc(),
        ),
        BlocProvider<WaiterBloc>(
          create: (context) => WaiterBloc(),
        ),
        BlocProvider<KitchenBloc>(
          create: (context) => KitchenBloc(),
        ),
      ],
      child: MaterialApp(
        title: 'VRestro Mobile',
        debugShowCheckedModeBanner: false,
        theme: NeumorphicTheme.lightTheme,
        home: const RootRoleRouter(),
      ),
    );
  }
}

class RootRoleRouter extends StatelessWidget {
  const RootRoleRouter({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<AuthBloc, AuthState>(
      builder: (context, state) {
        if (state is AuthLoading) {
          return const Scaffold(
            backgroundColor: AppColors.background,
            body: Center(
              child: CircularProgressIndicator(color: AppColors.primary),
            ),
          );
        }

        if (state is Authenticated) {
          final role = state.user.role.toLowerCase();

          if (role.contains('cashier') || role.contains('kassir')) {
            return const CashierLayoutScreen();
          } else if (role.contains('chef') || role.contains('oshpaz') || role.contains('kitchen')) {
            return const ChefLayoutScreen();
          } else {
            // Default to Waiter role
            return const WaiterLayoutScreen();
          }
        }

        return const LoginScreen();
      },
    );
  }
}
