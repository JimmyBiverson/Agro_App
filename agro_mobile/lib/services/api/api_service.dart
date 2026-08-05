import '../../models/user.dart';
import '../../models/product.dart';
import '../../models/order.dart';
import '../../models/payment.dart';
import '../../models/customer.dart';
import '../../models/inventory.dart';
import '../../models/notification.dart';
// Removed unused import

abstract class ApiService {
  Future<void> initialize();

  void setToken(String? token);
  void setUserRole(String? role);

  // Site Settings (public – no auth required)
  Future<Map<String, dynamic>> getSiteSettings();

  // Auth
  Future<Map<String, dynamic>> login(String email, String password);
  Future<void> logout();
  Future<void> refreshToken();
  Future<void> changePassword(String currentPassword, String newPassword);

  // Profile
  Future<User> getProfile();
  Future<User> updateProfile(Map<String, dynamic> data);

  // Products
  Future<List<ProductCategory>> getCategories();
  Future<List<Product>> getProducts({String? categoryId});
  Future<Product> getProduct(String id);
  Future<List<PriceSlab>> getPriceSlabs(String productId);

  // Orders
  Future<List<Order>> getOrders({String? status, String? franchiseId});
  Future<Order> getOrder(String id);
  Future<Order> createOrder(Map<String, dynamic> orderData);
  Future<Order> approveOrder(String id, {String? deliveryDate, String? notes});
  Future<Order> declineOrder(String id, String reason);
  Future<Order> adjustOrder(String id, Map<String, dynamic> adjustments);
  Future<Order> confirmDelivery(String id, {String? notes});

  // Inventory
  Future<List<InventoryItem>> getInventory({String? franchiseId});
  Future<List<InventoryMovement>> getInventoryMovements({String? productId});

  // Sales
  Future<List<Map<String, dynamic>>> getSales({String? dateFrom, String? dateTo});
  Future<Map<String, dynamic>> createSale(Map<String, dynamic> saleData);

  // Customers
  Future<List<Customer>> getCustomers();
  Future<Customer> getCustomer(String id);
  Future<Customer> createCustomer(Map<String, dynamic> customerData);

  // Payments (Franchise)
  Future<List<Payment>> getPayments({String? status});
  Future<Payment> getPayment(String id);
  Future<Payment> submitPayment(Map<String, dynamic> paymentData);
  Future<String> uploadPaymentProof(String paymentId, List<int> fileBytes, String fileName);

  // Payments (Finance)
  Future<List<Payment>> getFinancePayments({String? status, String? franchiseId});
  Future<Payment> getFinancePayment(String id);
  Future<Payment> verifyPayment(String id, {double? verifiedAmount, String? notes});
  Future<Payment> acceptPayment(String id, {String? notes});
  Future<Payment> rejectPayment(String id, String reason);
  Future<List<Payment>> getPendingFinancePayments();

  // Dashboard
  Future<Map<String, dynamic>> getDashboardStats();
  Future<Map<String, dynamic>> getFinanceDashboardStats();
  Future<List<Map<String, dynamic>>> getSalesAnalytics({String? period});
  Future<AccountSummary> getAccountSummary();

  // Chat
  Future<List<Map<String, dynamic>>> getConversations();
  Future<Map<String, dynamic>> createConversation(String subject, String initialMessage);
  Future<Map<String, dynamic>> getConversation(String id);
  Future<Map<String, dynamic>> sendMessage(String conversationId, String message);

  // Notifications
  Future<List<NotificationItem>> getNotifications({bool? unreadOnly});
  Future<void> markNotificationRead(String id);
  Future<void> markAllNotificationsRead();
  Future<int> getUnreadNotificationCount();
}
