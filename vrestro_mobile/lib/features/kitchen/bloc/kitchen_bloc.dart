import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:equatable/equatable.dart';
import 'package:dio/dio.dart';
import '../../../core/network/dio_client.dart';
import '../../../core/constants/api_constants.dart';
import '../../../shared/models/order_model.dart';
import '../../../shared/models/food_model.dart';

// Events
abstract class KitchenEvent extends Equatable {
  const KitchenEvent();
  @override
  List<Object?> get props => [];
}

class FetchKitchenOrders extends KitchenEvent {}

class UpdateItemStatusRequested extends KitchenEvent {
  final int itemId;
  final String newStatus; // cooking, ready

  const UpdateItemStatusRequested({required this.itemId, required this.newStatus});

  @override
  List<Object?> get props => [itemId, newStatus];
}

class FetchKitchenStopList extends KitchenEvent {}

class ToggleFoodAvailabilityRequested extends KitchenEvent {
  final int foodId;
  const ToggleFoodAvailabilityRequested(this.foodId);
  @override
  List<Object?> get props => [foodId];
}

// States
abstract class KitchenState extends Equatable {
  const KitchenState();
  @override
  List<Object?> get props => [];
}

class KitchenInitial extends KitchenState {}

class KitchenLoading extends KitchenState {}

class KitchenOrdersLoaded extends KitchenState {
  final List<OrderModel> orders;
  const KitchenOrdersLoaded(this.orders);
  @override
  List<Object?> get props => [orders];
}

class KitchenStopListLoaded extends KitchenState {
  final List<FoodModel> foods;
  const KitchenStopListLoaded(this.foods);
  @override
  List<Object?> get props => [foods];
}

class KitchenError extends KitchenState {
  final String message;
  const KitchenError(this.message);
  @override
  List<Object?> get props => [message];
}

// BLoC
class KitchenBloc extends Bloc<KitchenEvent, KitchenState> {
  final Dio _dio = DioClient().dio;

  KitchenBloc() : super(KitchenInitial()) {
    on<FetchKitchenOrders>(_onFetchOrders);
    on<UpdateItemStatusRequested>(_onUpdateStatus);
    on<FetchKitchenStopList>(_onFetchStopList);
    on<ToggleFoodAvailabilityRequested>(_onToggleFood);
  }

  Future<void> _onFetchOrders(
      FetchKitchenOrders event, Emitter<KitchenState> emit) async {
    emit(KitchenLoading());
    try {
      final response = await _dio.get(ApiConstants.chefItems);
      // The backend returns a FLAT list of order items (each with a nested
      // `order` object), not a list of orders — group them back into
      // per-order tickets for the KDS screen.
      final List rawItems = response.data is List ? response.data : (response.data['data'] ?? []);

      final Map<int, List<dynamic>> itemsByOrder = {};
      final Map<int, dynamic> orderMetaById = {};
      for (final raw in rawItems) {
        final orderJson = raw['order'] ?? {};
        final orderId = orderJson['id'] is int
            ? orderJson['id']
            : int.tryParse(orderJson['id']?.toString() ?? '') ?? 0;
        itemsByOrder.putIfAbsent(orderId, () => []).add(raw);
        orderMetaById.putIfAbsent(orderId, () => orderJson);
      }

      final orders = itemsByOrder.entries.map((entry) {
        final orderJson = orderMetaById[entry.key] ?? {};
        final items = entry.value.map((i) => OrderItemModel.fromJson(i)).toList();
        return OrderModel(
          id: entry.key,
          orderNumber: orderJson['order_number'] ?? '#${entry.key}',
          tableId: orderJson['table_id'] != null
              ? int.tryParse(orderJson['table_id'].toString())
              : null,
          tableName: orderJson['table']?['table_number']?.toString(),
          waiterId: orderJson['waiter_id'] != null
              ? int.tryParse(orderJson['waiter_id'].toString())
              : null,
          waiterName: orderJson['waiter']?['name'],
          totalAmount: double.tryParse(orderJson['total_amount']?.toString() ?? '') ?? 0.0,
          status: orderJson['status'] ?? 'new',
          items: items,
          createdAt: orderJson['created_at'] != null
              ? DateTime.tryParse(orderJson['created_at']) ?? DateTime.now()
              : DateTime.now(),
        );
      }).toList()
        ..sort((a, b) => a.createdAt.compareTo(b.createdAt));

      emit(KitchenOrdersLoaded(orders));
    } catch (e) {
      emit(const KitchenError('Oshxona buyurtmalarini yuklashda xatolik'));
    }
  }

  Future<void> _onUpdateStatus(
      UpdateItemStatusRequested event, Emitter<KitchenState> emit) async {
    try {
      await _dio.patch(
        '${ApiConstants.chefUpdateStatus}/${event.itemId}/status',
        data: {'status': event.newStatus},
      );
      add(FetchKitchenOrders());
    } catch (e) {
      emit(const KitchenError('Statusni oʻzgartirishda xatolik'));
    }
  }

  Future<void> _onFetchStopList(
      FetchKitchenStopList event, Emitter<KitchenState> emit) async {
    emit(KitchenLoading());
    try {
      final response = await _dio.get(ApiConstants.kitchenFoods);
      final List list = response.data['foods'] ?? response.data ?? [];
      final foods = list.map((item) => FoodModel.fromJson(item)).toList();
      emit(KitchenStopListLoaded(foods));
    } catch (e) {
      emit(const KitchenError('Stop-list maʻlumotlarini yuklashda xatolik'));
    }
  }

  Future<void> _onToggleFood(
      ToggleFoodAvailabilityRequested event, Emitter<KitchenState> emit) async {
    try {
      await _dio.post('${ApiConstants.kitchenFoods}/${event.foodId}/toggle');
      add(FetchKitchenStopList());
    } catch (e) {
      emit(const KitchenError('Taom mavjudligini oʻzgartirishda xatolik'));
    }
  }
}
