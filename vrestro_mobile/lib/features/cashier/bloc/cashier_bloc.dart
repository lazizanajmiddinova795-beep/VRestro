import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:equatable/equatable.dart';
import 'package:dio/dio.dart';
import '../../../core/network/dio_client.dart';
import '../../../core/constants/api_constants.dart';
import '../../../shared/models/table_model.dart';
import '../../../shared/models/order_model.dart';
import '../../../shared/models/payment_model.dart';

// Events
abstract class CashierEvent extends Equatable {
  const CashierEvent();
  @override
  List<Object?> get props => [];
}

class FetchCashierTables extends CashierEvent {}

class FetchOrderDetails extends CashierEvent {
  final int orderId;
  const FetchOrderDetails(this.orderId);
  @override
  List<Object?> get props => [orderId];
}

class ProcessPaymentRequested extends CashierEvent {
  final int orderId;
  final double amount;
  final String paymentType; // cash, card, click, payme

  const ProcessPaymentRequested({
    required this.orderId,
    required this.amount,
    required this.paymentType,
  });

  @override
  List<Object?> get props => [orderId, amount, paymentType];
}

// States
abstract class CashierState extends Equatable {
  const CashierState();
  @override
  List<Object?> get props => [];
}

class CashierInitial extends CashierState {}

class CashierLoading extends CashierState {}

class CashierTablesLoaded extends CashierState {
  final List<TableModel> tables;
  const CashierTablesLoaded(this.tables);
  @override
  List<Object?> get props => [tables];
}

class OrderDetailsLoaded extends CashierState {
  final OrderModel order;
  const OrderDetailsLoaded(this.order);
  @override
  List<Object?> get props => [order];
}

class PaymentSuccess extends CashierState {
  final PaymentModel payment;
  const PaymentSuccess(this.payment);
  @override
  List<Object?> get props => [payment];
}

class CashierError extends CashierState {
  final String message;
  const CashierError(this.message);
  @override
  List<Object?> get props => [message];
}

// BLoC
class CashierBloc extends Bloc<CashierEvent, CashierState> {
  final Dio _dio = DioClient().dio;

  CashierBloc() : super(CashierInitial()) {
    on<FetchCashierTables>(_onFetchTables);
    on<FetchOrderDetails>(_onFetchOrderDetails);
    on<ProcessPaymentRequested>(_onProcessPayment);
  }

  Future<void> _onFetchTables(
      FetchCashierTables event, Emitter<CashierState> emit) async {
    emit(CashierLoading());
    try {
      final response = await _dio.get(ApiConstants.cashierTables);
      final List list = response.data['tables'] ?? response.data ?? [];
      final tables = list.map((item) => TableModel.fromJson(item)).toList();
      emit(CashierTablesLoaded(tables));
    } catch (e) {
      emit(CashierError('Stollarni yuklashda xatolik yuz berdi'));
    }
  }

  Future<void> _onFetchOrderDetails(
      FetchOrderDetails event, Emitter<CashierState> emit) async {
    emit(CashierLoading());
    try {
      final response = await _dio.get('${ApiConstants.orders}/${event.orderId}');
      final orderData = response.data['order'] ?? response.data;
      final order = OrderModel.fromJson(orderData);
      emit(OrderDetailsLoaded(order));
    } catch (e) {
      emit(CashierError('Buyurtma ma\'lumotlarini yuklashda xatolik'));
    }
  }

  Future<void> _onProcessPayment(
      ProcessPaymentRequested event, Emitter<CashierState> emit) async {
    emit(CashierLoading());
    try {
      final response = await _dio.post(
        ApiConstants.payments,
        data: {
          'order_id': event.orderId,
          'amount': event.amount,
          'payment_type': event.paymentType,
        },
      );
      final paymentData = response.data['payment'] ?? response.data;
      final payment = PaymentModel.fromJson(paymentData);
      emit(PaymentSuccess(payment));
    } catch (e) {
      emit(CashierError('Toʻlovni amalga oshirishda xatolik'));
    }
  }
}
