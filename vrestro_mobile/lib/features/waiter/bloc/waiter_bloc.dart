import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:equatable/equatable.dart';
import 'package:dio/dio.dart';
import '../../../core/network/dio_client.dart';
import '../../../core/constants/api_constants.dart';
import '../../../shared/models/table_model.dart';
import '../../../shared/models/category_model.dart';
import '../../../shared/models/food_model.dart';
import '../../../shared/models/order_model.dart';

// Events
abstract class WaiterEvent extends Equatable {
  const WaiterEvent();
  @override
  List<Object?> get props => [];
}

class FetchWaiterTables extends WaiterEvent {}

class FetchMenuAndCategories extends WaiterEvent {}

class SubmitNewOrderRequested extends WaiterEvent {
  final int tableId;
  final List<Map<String, dynamic>> items;

  const SubmitNewOrderRequested({required this.tableId, required this.items});

  @override
  List<Object?> get props => [tableId, items];
}

// States
abstract class WaiterState extends Equatable {
  const WaiterState();
  @override
  List<Object?> get props => [];
}

class WaiterInitial extends WaiterState {}

class WaiterLoading extends WaiterState {}

class WaiterTablesLoaded extends WaiterState {
  final List<TableModel> tables;
  const WaiterTablesLoaded(this.tables);
  @override
  List<Object?> get props => [tables];
}

class WaiterMenuLoaded extends WaiterState {
  final List<CategoryModel> categories;
  final List<FoodModel> foods;

  const WaiterMenuLoaded({required this.categories, required this.foods});

  @override
  List<Object?> get props => [categories, foods];
}

class OrderSubmissionSuccess extends WaiterState {
  final OrderModel order;
  const OrderSubmissionSuccess(this.order);
  @override
  List<Object?> get props => [order];
}

class WaiterError extends WaiterState {
  final String message;
  const WaiterError(this.message);
  @override
  List<Object?> get props => [message];
}

// BLoC
class WaiterBloc extends Bloc<WaiterEvent, WaiterState> {
  final Dio _dio = DioClient().dio;

  WaiterBloc() : super(WaiterInitial()) {
    on<FetchWaiterTables>(_onFetchTables);
    on<FetchMenuAndCategories>(_onFetchMenu);
    on<SubmitNewOrderRequested>(_onSubmitOrder);
  }

  Future<void> _onFetchTables(
      FetchWaiterTables event, Emitter<WaiterState> emit) async {
    emit(WaiterLoading());
    try {
      final response = await _dio.get(ApiConstants.waiterTables);
      final List list = response.data['tables'] ?? response.data ?? [];
      final tables = list.map((item) => TableModel.fromJson(item)).toList();
      emit(WaiterTablesLoaded(tables));
    } catch (e) {
      emit(const WaiterError('Stollar roʻyxatini yuklashda xatolik'));
    }
  }

  Future<void> _onFetchMenu(
      FetchMenuAndCategories event, Emitter<WaiterState> emit) async {
    emit(WaiterLoading());
    try {
      final categoriesRes = await _dio.get(ApiConstants.categories);
      final foodsRes = await _dio.get(ApiConstants.foods);

      final List catList = categoriesRes.data['categories'] ?? categoriesRes.data ?? [];
      final List foodList = foodsRes.data['foods'] ?? foodsRes.data ?? [];

      final categories = catList.map((c) => CategoryModel.fromJson(c)).toList();
      final foods = foodList.map((f) => FoodModel.fromJson(f)).toList();

      emit(WaiterMenuLoaded(categories: categories, foods: foods));
    } catch (e) {
      emit(const WaiterError('Menyu maʻlumotlarini yuklashda xatolik'));
    }
  }

  Future<void> _onSubmitOrder(
      SubmitNewOrderRequested event, Emitter<WaiterState> emit) async {
    emit(WaiterLoading());
    try {
      final response = await _dio.post(
        ApiConstants.waiterSubmitOrder,
        data: {
          'table_id': event.tableId,
          'items': event.items,
        },
      );
      final orderData = response.data['order'] ?? response.data;
      final order = OrderModel.fromJson(orderData);
      emit(OrderSubmissionSuccess(order));
    } catch (e) {
      emit(const WaiterError('Buyurtmani yuborishda xatolik yuz berdi'));
    }
  }
}
