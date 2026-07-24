import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/constants/app_colors.dart';
import '../../../shared/widgets/neumorphic_button.dart';
import '../../../shared/widgets/neumorphic_container.dart';
import '../../../shared/widgets/neumorphic_text_field.dart';
import '../bloc/auth_bloc.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({Key? key}) : super(key: key);

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final TextEditingController _loginController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  final TextEditingController _otpController = TextEditingController();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      body: BlocConsumer<AuthBloc, AuthState>(
        listener: (context, state) {
          if (state is AuthFailure) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text(state.errorMessage),
                backgroundColor: AppColors.statusNew,
              ),
            );
          }
        },
        builder: (context, state) {
          return Center(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(24.0),
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 420),
                child: NeumorphicContainer(
                  borderRadius: 24,
                  padding: const EdgeInsets.all(32),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      // VRestro Brand Icon
                      Center(
                        child: Container(
                          width: 72,
                          height: 72,
                          decoration: BoxDecoration(
                            gradient: const LinearGradient(
                              colors: [AppColors.primary, AppColors.primaryLight],
                            ),
                            borderRadius: BorderRadius.circular(20),
                            boxShadow: [
                              BoxShadow(
                                color: AppColors.primary.withOpacity(0.3),
                                blurRadius: 15,
                                offset: const Offset(0, 8),
                              ),
                            ],
                          ),
                          child: const Icon(
                            Icons.restaurant_menu_rounded,
                            size: 38,
                            color: Colors.white,
                          ),
                        ),
                      ),
                      const SizedBox(height: 20),

                      const Text(
                        'VRestro Mobile',
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          fontSize: 26,
                          fontWeight: FontWeight.w900,
                          color: AppColors.textPrimary,
                          letterSpacing: 0.5,
                        ),
                      ),
                      const SizedBox(height: 6),
                      const Text(
                        'Restoran Boshqaruv Tizimi',
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w500,
                          color: AppColors.textSecondary,
                        ),
                      ),
                      const SizedBox(height: 32),

                      // Input fields
                      if (state is OtpRequired) ...[
                        Text(
                          '${state.name}, Telegram orqali yuborilgan 8 xonali kodni kiriting.',
                          textAlign: TextAlign.center,
                          style: const TextStyle(
                            fontSize: 13,
                            color: AppColors.textSecondary,
                          ),
                        ),
                        const SizedBox(height: 20),
                        NeumorphicTextField(
                          controller: _otpController,
                          label: 'Tasdiqlash kodi',
                          hint: '8 xonali kod',
                          prefixIcon: Icons.password_rounded,
                        ),
                        const SizedBox(height: 32),
                        NeumorphicButton(
                          text: 'Tasdiqlash',
                          isLoading: state is AuthLoading,
                          icon: Icons.check_circle_outline_rounded,
                          onPressed: () {
                            context.read<AuthBloc>().add(
                                  OtpSubmitted(
                                    userId: state.userId,
                                    otp: _otpController.text.trim(),
                                  ),
                                );
                          },
                        ),
                      ] else ...[
                        NeumorphicTextField(
                          controller: _loginController,
                          label: 'Login',
                          hint: 'masalan: waiter1',
                          prefixIcon: Icons.person_outline_rounded,
                        ),
                        const SizedBox(height: 20),

                        NeumorphicTextField(
                          controller: _passwordController,
                          label: 'Parol',
                          hint: '••••••••',
                          prefixIcon: Icons.lock_outline_rounded,
                          obscureText: true,
                        ),
                        const SizedBox(height: 32),

                        // Submit button
                        NeumorphicButton(
                          text: 'Tizimga Kirish',
                          isLoading: state is AuthLoading,
                          icon: Icons.login_rounded,
                          onPressed: () {
                            context.read<AuthBloc>().add(
                                  LoginRequested(
                                    login: _loginController.text.trim(),
                                    password: _passwordController.text.trim(),
                                  ),
                                );
                          },
                        ),
                      ],
                    ],
                  ),
                ),
              ),
            ),
          );
        },
      ),
    );
  }
}
