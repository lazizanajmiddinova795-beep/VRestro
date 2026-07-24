import 'dart:convert';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:equatable/equatable.dart';
import 'package:dio/dio.dart';
import '../../../core/network/dio_client.dart';
import '../../../core/constants/api_constants.dart';
import '../../../core/services/storage_service.dart';
import '../../../shared/models/user_model.dart';

// Events
abstract class AuthEvent extends Equatable {
  const AuthEvent();
  @override
  List<Object?> get props => [];
}

class LoginRequested extends AuthEvent {
  final String login;
  final String password;

  const LoginRequested({required this.login, required this.password});

  @override
  List<Object?> get props => [login, password];
}

// Step 2 of the Admin login flow: the 8-digit code sent to Telegram.
class OtpSubmitted extends AuthEvent {
  final int userId;
  final String otp;

  const OtpSubmitted({required this.userId, required this.otp});

  @override
  List<Object?> get props => [userId, otp];
}

class CheckAuthStatus extends AuthEvent {}

class LogoutRequested extends AuthEvent {}

// States
abstract class AuthState extends Equatable {
  const AuthState();
  @override
  List<Object?> get props => [];
}

class AuthInitial extends AuthState {}

class AuthLoading extends AuthState {}

class Authenticated extends AuthState {
  final UserModel user;
  final String token;

  const Authenticated({required this.user, required this.token});

  @override
  List<Object?> get props => [user, token];
}

// Admin accounts require an 8-digit Telegram OTP before a token is issued.
class OtpRequired extends AuthState {
  final int userId;
  final String name;

  const OtpRequired({required this.userId, required this.name});

  @override
  List<Object?> get props => [userId, name];
}

class Unauthenticated extends AuthState {}

class AuthFailure extends AuthState {
  final String errorMessage;

  const AuthFailure({required this.errorMessage});

  @override
  List<Object?> get props => [errorMessage];
}

// BLoC
class AuthBloc extends Bloc<AuthEvent, AuthState> {
  final Dio _dio = DioClient().dio;

  AuthBloc() : super(AuthInitial()) {
    on<CheckAuthStatus>(_onCheckAuthStatus);
    on<LoginRequested>(_onLoginRequested);
    on<OtpSubmitted>(_onOtpSubmitted);
    on<LogoutRequested>(_onLogoutRequested);
  }

  Future<void> _onCheckAuthStatus(
      CheckAuthStatus event, Emitter<AuthState> emit) async {
    emit(AuthLoading());
    try {
      final token = await StorageService.getToken();
      final userJson = await StorageService.getUserData();

      if (token != null && token.isNotEmpty && userJson != null) {
        final user = UserModel.fromJson(jsonDecode(userJson));
        emit(Authenticated(user: user, token: token));
      } else {
        emit(Unauthenticated());
      }
    } catch (_) {
      emit(Unauthenticated());
    }
  }

  Future<void> _onLoginRequested(
      LoginRequested event, Emitter<AuthState> emit) async {
    emit(AuthLoading());
    try {
      final response = await _dio.post(
        ApiConstants.login,
        data: {
          'login': event.login,
          'password': event.password,
        },
      );

      final data = response.data;

      if (data['requires_otp'] == true) {
        final userData = data['user'];
        emit(OtpRequired(
          userId: userData['id'] is int ? userData['id'] : int.parse(userData['id'].toString()),
          name: userData['name'] ?? '',
        ));
        return;
      }

      final token = data['token'] ?? '';
      final user = UserModel.fromJson(data['user']);

      await StorageService.saveToken(token);
      await StorageService.saveUserData(jsonEncode(user.toJson()), user.role);

      emit(Authenticated(user: user, token: token));
    } on DioException catch (e) {
      final msg = e.response?.data['message'] ?? 'Login failed. Network or server error.';
      emit(AuthFailure(errorMessage: msg));
    } catch (e) {
      emit(AuthFailure(errorMessage: e.toString()));
    }
  }

  Future<void> _onOtpSubmitted(
      OtpSubmitted event, Emitter<AuthState> emit) async {
    emit(AuthLoading());
    try {
      final response = await _dio.post(
        ApiConstants.verifyOtp,
        data: {
          'user_id': event.userId,
          'otp': event.otp,
        },
      );

      final data = response.data;
      final token = data['token'] ?? '';
      final user = UserModel.fromJson(data['user']);

      await StorageService.saveToken(token);
      await StorageService.saveUserData(jsonEncode(user.toJson()), user.role);

      emit(Authenticated(user: user, token: token));
    } on DioException catch (e) {
      final msg = e.response?.data['message'] ?? 'Tasdiqlash kodi noto\'g\'ri yoki eskirgan.';
      emit(AuthFailure(errorMessage: msg));
    } catch (e) {
      emit(AuthFailure(errorMessage: e.toString()));
    }
  }

  Future<void> _onLogoutRequested(
      LogoutRequested event, Emitter<AuthState> emit) async {
    await StorageService.clearAll();
    emit(Unauthenticated());
  }
}
