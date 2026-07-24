class ApiConstants {
  // Base URL configuration (Default for Android emulator local server)
  static const String baseUrl = 'http://10.0.2.2:8000/api';

  // Auth endpoints
  static const String login = '/auth/login';
  static const String userProfile = '/user';

  // Waiter endpoints
  static const String waiterTables = '/waiter/tables';
  static const String waiterSubmitOrder = '/waiter/orders/submit';
  static const String waiterActiveOrders = '/waiter/orders/active-status';
  static const String waiterCancelItem = '/waiter/order-item';

  // Cashier endpoints
  static const String cashierTables = '/cashier/tables';
  static const String payments = '/payments';
  static const String orderPrintData = '/orders';

  // Chef / Kitchen KDS endpoints
  static const String chefItems = '/chef/items';
  static const String chefUpdateStatus = '/chef/items';
  static const String kitchenFoods = '/kitchen/foods';

  // General Menu & Orders
  static const String orders = '/orders';
  static const String categories = '/menu/categories';
  static const String foods = '/menu/foods';
  static const String tables = '/tables';
  static const String notifications = '/notifications';
}
