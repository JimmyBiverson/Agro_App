import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:flutter/foundation.dart' show kIsWeb;
import '../../core/constants/api_endpoints.dart';
import '../../core/enums/app_enums.dart';
import '../../core/exceptions/app_exception.dart';
import '../../core/utils/formatters.dart';
import '../../core/utils/image_url.dart';
import '../../models/user.dart';
import '../../models/product.dart';
import '../../models/order.dart';
import '../../models/payment.dart';
import '../../models/customer.dart';
import '../../models/inventory.dart';
import '../../models/notification.dart';
import 'api_service.dart';

class HttpApiService implements ApiService {
  String? _token;
  String? _userRole;
  final http.Client _client;

  HttpApiService({http.Client? client}) : _client = client ?? http.Client();

  @override
  Future<void> initialize() async {
    // No-op for HTTP client
  }

  @override
  void setToken(String? token) {
    _token = token;
  }

  @override
  void setUserRole(String? role) {
    _userRole = role;
  }

  Map<String, String> get _headers => {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        if (_token != null) 'Authorization': 'Bearer $_token',
      };

  Future<Map<String, dynamic>> _get(String path,
      {Map<String, String>? queryParams}) async {
    final uri = Uri.parse('${ApiEndpoints.baseUrl}$path')
        .replace(queryParameters: queryParams);
    final response = await _client.get(uri, headers: _headers);
    return _handleResponse(response);
  }

  Future<Map<String, dynamic>> _post(String path,
      {Map<String, dynamic>? body}) async {
    final uri = Uri.parse('${ApiEndpoints.baseUrl}$path');
    final response = await _client.post(
      uri,
      headers: _headers,
      body: body != null ? jsonEncode(body) : null,
    );
    return _handleResponse(response);
  }

  Future<Map<String, dynamic>> _put(String path,
      {Map<String, dynamic>? body}) async {
    final uri = Uri.parse('${ApiEndpoints.baseUrl}$path');
    final response = await _client.put(
      uri,
      headers: _headers,
      body: body != null ? jsonEncode(body) : null,
    );
    return _handleResponse(response);
  }

  Future<Map<String, dynamic>> _postMultipart(
      String path, Map<String, String> fields, List<http.MultipartFile> files) async {
    final uri = Uri.parse('${ApiEndpoints.baseUrl}$path');
    final request = http.MultipartRequest('POST', uri);
    request.headers.addAll({
      'Accept': 'application/json',
      if (_token != null) 'Authorization': 'Bearer $_token',
    });
    request.fields.addAll(fields);
    request.files.addAll(files);

    final streamedResponse = await _client.send(request);
    final response = await http.Response.fromStream(streamedResponse);
    return _handleResponse(response);
  }

  Map<String, dynamic> _handleResponse(http.Response response) {
    final body = jsonDecode(response.body);

    if (response.statusCode >= 200 && response.statusCode < 300) {
      if (body is Map<String, dynamic>) return body;
      if (body is List) return {'data': body};
      return {'data': body};
    }

    if (response.statusCode == 401) {
      throw AuthException(message: 'Session expired. Please login again.');
    }

    if (response.statusCode == 403) {
      final message = body['message'] ?? 'Access denied.';
      throw AppException(message: message, code: 'FORBIDDEN');
    }

    if (response.statusCode == 422) {
      final errors = body['errors'];
      String message = body['message'] ?? 'Validation failed.';
      if (errors != null && errors is Map) {
        final firstField = errors.keys.first;
        final firstError = errors[firstField];
        if (firstError is List && firstError.isNotEmpty) {
          message = firstError.first;
        }
      }
      throw AppException(message: message, code: 'VALIDATION_ERROR');
    }

    if (response.statusCode == 404) {
      throw AppException(
          message: body['message'] ?? 'Resource not found.', code: 'NOT_FOUND');
    }

    throw AppException(
      message: body['message'] ?? 'An error occurred (${response.statusCode})',
      code: 'HTTP_ERROR',
    );
  }

  /// Extract items from a Laravel paginated or direct response
  List<dynamic> _extractList(Map<String, dynamic> response, {String? key}) {
    // Laravel paginated: {data: [...], links: ..., meta: ...}
    if (response.containsKey('data') && response['data'] is List) {
      return response['data'] as List<dynamic>;
    }
    // Direct array wrapped in {data: ...}
    if (key != null && response.containsKey(key)) {
      final val = response[key];
      if (val is List) return val;
    }
    return [];
  }

  /// Extract single item from response
  Map<String, dynamic> _extractOne(Map<String, dynamic> response) {
    if (response.containsKey('data') && response['data'] is Map<String, dynamic>) {
      return response['data'] as Map<String, dynamic>;
    }
    return response;
  }

  // ══════════════════════════════════════════════════════════════
  // SITE SETTINGS (public – no token required)
  // ══════════════════════════════════════════════════════════════

  @override
  Future<Map<String, dynamic>> getSiteSettings() async {
    final uri = Uri.parse('${ApiEndpoints.baseUrl}/settings/public');
    final response = await _client.get(uri, headers: {
      'Accept': 'application/json',
    });
    final body = _handleResponse(response);
    return _extractOne(body);
  }

  // ══════════════════════════════════════════════════════════════
  // AUTH
  // ══════════════════════════════════════════════════════════════


  @override
  Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await _post(ApiEndpoints.login, body: {
      'email': email,
      'password': password,
    });

    final user = _normalizeUser(response['user'] as Map<String, dynamic>? ?? {});
    return {
      'token': response['token'],
      'user': user,
    };
  }

  @override
  Future<void> logout() async {
    try {
      await _post(ApiEndpoints.logout);
    } catch (_) {}
    _token = null;
  }

  @override
  Future<void> refreshToken() async {
    // Laravel Sanctum doesn't use refresh tokens in the same way
    // Token refresh is handled by creating a new login
  }

  @override
  Future<void> changePassword(String currentPassword, String newPassword) async {
    await _post(ApiEndpoints.changePassword, body: {
      'current_password': currentPassword,
      'password': newPassword,
      'password_confirmation': newPassword,
    });
  }

  // ══════════════════════════════════════════════════════════════
  // PROFILE
  // ══════════════════════════════════════════════════════════════

  @override
  Future<User> getProfile() async {
    final response = await _get(ApiEndpoints.me);
    final user = _normalizeUser(response['user'] as Map<String, dynamic>? ?? {});
    return User.fromJson(user);
  }

  @override
  Future<User> updateProfile(Map<String, dynamic> data) async {
    final response = await _put(ApiEndpoints.updateProfile, body: data);
    final user = _normalizeUser(response['user'] as Map<String, dynamic>? ?? {});
    return User.fromJson(user);
  }

  /// Normalize user data from Laravel format to Flutter model format
  Map<String, dynamic> _normalizeUser(Map<String, dynamic> user) {
    return {
      'id': user['id']?.toString() ?? '',
      'name': user['name'] ?? '',
      'email': user['email'] ?? '',
      'phone': user['phone'] ?? '',
      'address': user['address'],
      'gender': user['gender'],
      'role': _mapLaravelRole(user['role']),
      'franchise_id': user['franchise_id']?.toString(),
      'franchise_name': user['franchise_name'],
      'franchise_code': user['franchise_code'],
      'avatar_url': user['avatar'],
      'created_at': user['created_at'],
      'is_active': user['is_active'] ?? true,
    };
  }

  String _mapLaravelRole(dynamic role) {
    switch (role?.toString()) {
      case 'System Administrator':
        return 'systemAdministrator';
      case 'Farmmantra Staff':
        return 'farmmantraStaff';
      case 'Finance Department':
        return 'financeDepartment';
      case 'Franchise Partner':
        return 'franchisePartner';
      default:
        return 'franchisePartner';
    }
  }

  // ══════════════════════════════════════════════════════════════
  // PRODUCTS
  // ══════════════════════════════════════════════════════════════

  @override
  Future<List<ProductCategory>> getCategories() async {
    final response = await _get(ApiEndpoints.categories);
    final list = _extractList(response);
    return list.map((c) => ProductCategory.fromJson({
      'id': c['id']?.toString() ?? '',
      'name': c['name'] ?? '',
      'description': c['description'],
      'icon_url': c['image'],
      'product_count': c['products_count'] ?? 0,
    })).toList();
  }

  @override
  Future<List<Product>> getProducts({String? categoryId}) async {
    final params = <String, String>{};
    if (categoryId != null) params['category_id'] = categoryId;

    final response = await _get(ApiEndpoints.products, queryParams: params.isNotEmpty ? params : null);
    final list = _extractList(response);
    return list.map((p) => _normalizeProduct(p)).toList();
  }

  @override
  Future<Product> getProduct(String id) async {
    final response = await _get('${ApiEndpoints.products}/$id');
    return _normalizeProduct(_extractOne(response));
  }

  Product _normalizeProduct(Map<String, dynamic> p) {
    // Handle nested category
    String categoryName = '';
    String categoryId = '';
    if (p['category'] is Map) {
      categoryName = p['category']['name'] ?? '';
      categoryId = p['category']['id']?.toString() ?? '';
    } else {
      categoryName = p['category_name'] ?? '';
      categoryId = p['category_id']?.toString() ?? '';
    }

    // Handle nested price slabs
    List<PriceSlab> slabs = [];
    final rawSlabs = p['price_slabs'] ?? p['priceSlabs'];
    if (rawSlabs is List) {
      slabs = rawSlabs.map((s) => PriceSlab.fromJson({
        'id': s['id']?.toString() ?? '',
        'product_id': s['product_id']?.toString() ?? '',
        'min_quantity': s['min_quantity'] ?? 0,
        'max_quantity': s['max_quantity'],
        'price_per_unit': s['slab_price'] ?? s['price_per_unit'] ?? 0,
        'label': null,
      })).toList();
    }

    // Extract gallery images from API response
    final List<String> extractedUrls = [];

    // From 'images' array (ProductImage relation with image_path)
    if (p['images'] is List) {
      for (final imgItem in p['images']) {
        if (imgItem is Map) {
          final path = imgItem['image_path'] ?? imgItem['image_url'] ?? imgItem['url'];
          if (path != null) {
            final resolved = resolveImageUrl(path);
            if (resolved.isNotEmpty && !extractedUrls.contains(resolved)) {
              extractedUrls.add(resolved);
            }
          }
        } else if (imgItem is String && imgItem.isNotEmpty) {
          final resolved = resolveImageUrl(imgItem);
          if (resolved.isNotEmpty && !extractedUrls.contains(resolved)) {
            extractedUrls.add(resolved);
          }
        }
      }
    }

    // From 'all_images' array (combined URLs from Product model accessor)
    if (p['all_images'] is List) {
      for (final imgItem in p['all_images']) {
        if (imgItem != null && imgItem.toString().isNotEmpty) {
          final resolved = resolveImageUrl(imgItem);
          if (resolved.isNotEmpty && !extractedUrls.contains(resolved)) {
            extractedUrls.add(resolved);
          }
        }
      }
    }

    // Resolve the primary image and ensure it is first in extractedUrls
    final mainImg = _resolveImageUrl(p['image_url'] ?? p['image']);
    if (mainImg.isNotEmpty) {
      extractedUrls.remove(mainImg);
      extractedUrls.insert(0, mainImg);
    }

    return Product(
      id: p['id']?.toString() ?? '',
      name: p['name'] ?? '',
      description: p['description'],
      categoryId: categoryId,
      categoryName: categoryName,
      unitOfMeasure: p['unit_of_measure'] ?? '',
      packagingDetails: p['packaging_details'],
      standardPrice: Formatters.toDouble(p['standard_price'] ?? p['selling_price']),
      priceSlabs: slabs,
      imageUrl: mainImg.isNotEmpty ? mainImg : (extractedUrls.isNotEmpty ? extractedUrls.first : null),
      imageUrls: extractedUrls,
      isActive: p['is_active'] ?? true,
    );
  }

  String _resolveImageUrl(dynamic raw) => resolveImageUrl(raw);

  @override
  Future<List<PriceSlab>> getPriceSlabs(String productId) async {
    final response = await _get('${ApiEndpoints.products}/$productId/price-slabs');
    final list = _extractList(response);
    return list.map((s) => PriceSlab.fromJson({
      'id': s['id']?.toString() ?? '',
      'product_id': s['product_id']?.toString() ?? '',
      'min_quantity': s['min_quantity'] ?? 0,
      'max_quantity': s['max_quantity'],
      'price_per_unit': s['slab_price'] ?? 0,
      'label': null,
    })).toList();
  }

  // ══════════════════════════════════════════════════════════════
  // ORDERS (Franchise)
  // ══════════════════════════════════════════════════════════════

  @override
  Future<List<Order>> getOrders({String? status, String? franchiseId}) async {
    final params = <String, String>{};
    if (status != null) params['status'] = status;
    if (franchiseId != null) params['franchise_id'] = franchiseId;

    String endpoint;
    if (_userRole == 'farmmantraStaff') {
      endpoint = '/staff/orders';
    } else {
      endpoint = '/franchise/orders';
    }

    final response = await _get(endpoint, queryParams: params.isNotEmpty ? params : null);
    final list = _extractList(response);
    return list.map((o) => _normalizeOrder(o)).toList();
  }

  Order _normalizeOrder(Map<String, dynamic> o) {
    // Handle nested franchise
    String franchiseName = '';
    String franchiseId = '';
    if (o['franchise'] is Map) {
      franchiseName = o['franchise']['name'] ?? '';
      franchiseId = o['franchise']['id']?.toString() ?? '';
    } else {
      franchiseName = o['franchise_name'] ?? '';
      franchiseId = o['franchise_id']?.toString() ?? '';
    }

    // Handle items
    List<OrderItem> items = [];
    if (o['items'] is List) {
      items = (o['items'] as List).map((i) {
        String productName = '';
        String categoryName = '';
        if (i['product'] is Map) {
          productName = i['product']['name'] ?? '';
          if (i['product']['category'] is Map) {
            categoryName = i['product']['category']['name'] ?? '';
          }
        }
        return OrderItem(
          id: i['id']?.toString() ?? '',
          productId: i['product_id']?.toString() ?? '',
          productName: productName.isNotEmpty ? productName : (i['product_name'] ?? ''),
          categoryName: categoryName.isNotEmpty ? categoryName : (i['category_name'] ?? ''),
          quantity: Formatters.toInt(i['quantity']),
          unitPrice: Formatters.toDouble(i['unit_price']),
          totalPrice: Formatters.toDouble(i['subtotal'] ?? i['total_price']),
          adjustedQuantity: i['adjusted_quantity'] == null
              ? null
              : Formatters.toInt(i['adjusted_quantity']),
          notes: i['adjustment_notes'] ?? i['notes'],
        );
      }).toList();
    }

    // Map status
    String statusStr = o['status'] ?? 'pending';

    return Order(
      id: o['id']?.toString() ?? '',
      orderNumber: o['order_number'] ?? '',
      franchiseId: franchiseId,
      franchiseName: franchiseName,
      items: items,
      totalAmount: Formatters.toDouble(o['total_amount']),
      adjustedAmount: o['adjusted_amount'] == null
          ? null
          : Formatters.toDouble(o['adjusted_amount']),
      status: statusStr,
      declineReason: o['decline_reason'],
      adjustmentNotes: o['adjustment_notes'],
      expectedDeliveryDate: _parseDate(o['expected_delivery_date']),
      deliveredAt: _parseDate(o['delivered_at'] ?? o['completed_at'] ?? o['received_at']),
      createdAt: _parseDate(o['created_at'] ?? ''),
      updatedAt: _parseDate(o['updated_at'] ?? ''),
      staffNotes: o['notes'],
    );
  }

  /// Parses a JSON value into a [DateTime], never throwing.
  static DateTime? _parseDate(dynamic value) {
    if (value == null) return null;
    if (value is DateTime) return value;
    if (value is String && value.isNotEmpty) return DateTime.tryParse(value);
    return null;
  }

  @override
  Future<Order> getOrder(String id) async {
    // Try both endpoints
    try {
      final response = await _get('/franchise/orders/$id');
      return _normalizeOrder(_extractOne(response));
    } catch (_) {
      final response = await _get('/staff/orders/$id');
      return _normalizeOrder(_extractOne(response));
    }
  }

  @override
  Future<Order> createOrder(Map<String, dynamic> orderData) async {
    final response = await _post('/franchise/orders', body: orderData);
    return _normalizeOrder(_extractOne(response));
  }

  @override
  Future<Order> approveOrder(String id,
      {String? deliveryDate, String? notes}) async {
    final body = <String, dynamic>{
      'expected_delivery_date': deliveryDate ?? DateTime.now().add(const Duration(days: 3)).toIso8601String().substring(0, 10),
    };
    if (notes != null && notes.isNotEmpty) body['notes'] = notes;

    final response = await _post('/staff/orders/$id/approve', body: body);
    return _normalizeOrder(_extractOne(response));
  }

  @override
  Future<Order> declineOrder(String id, String reason) async {
    final response = await _post('/staff/orders/$id/decline', body: {
      'decline_reason': reason,
    });
    return _normalizeOrder(_extractOne(response));
  }

  @override
  Future<Order> adjustOrder(String id, Map<String, dynamic> adjustments) async {
    final response = await _post('/staff/orders/$id/adjust', body: adjustments);
    return _normalizeOrder(_extractOne(response));
  }

  @override
  Future<Order> confirmDelivery(String id, {String? notes}) async {
    // This calls the stock receipt confirm endpoint
    // First, get the order to find the stock receipt
    final orderResponse = await _get('/franchise/stock-receipts');
    final receipts = _extractList(orderResponse);

    // Find receipt for this order
    String? receiptId;
    for (final r in receipts) {
      if (r['order_id']?.toString() == id) {
        receiptId = r['id']?.toString();
        break;
      }
    }

    if (receiptId == null) {
      throw AppException(
          message: 'No stock receipt found for this order.',
          code: 'NOT_FOUND');
    }

    // Get receipt details to find items
    final receiptDetail = await _get('/franchise/stock-receipts/$receiptId');
    final receipt = _extractOne(receiptDetail);
    final receiptItems = receipt['items'] as List<dynamic>? ?? [];

    final items = receiptItems
        .map((item) => {
              'stock_receipt_item_id': item['id'],
              'received_quantity': item['ordered_quantity'] ?? item['quantity'] ?? 0,
            })
        .toList();

    await _post('/franchise/stock-receipts/$receiptId/confirm',
        body: {'items': items, 'notes': notes});

    return getOrder(id);
  }

  // ══════════════════════════════════════════════════════════════
  // INVENTORY
  // ══════════════════════════════════════════════════════════════

  @override
  Future<List<InventoryItem>> getInventory({String? franchiseId}) async {
    final response = await _get('/franchise/inventory');
    final list = _extractList(response);
    return list.map((i) => _normalizeInventoryItem(i)).toList();
  }

  InventoryItem _normalizeInventoryItem(Map<String, dynamic> i) {
    String productName = '';
    String categoryName = '';
    String unitOfMeasure = '';
    if (i['product'] is Map) {
      productName = i['product']['name'] ?? '';
      unitOfMeasure = i['product']['unit_of_measure'] ?? '';
      if (i['product']['category'] is Map) {
        categoryName = i['product']['category']['name'] ?? '';
      }
    }

    final qty = Formatters.toDouble(i['quantity']);
    final reorder = Formatters.toDouble(i['reorder_level']);
    InventoryAlertLevel alertLevel = InventoryAlertLevel.normal;
    if (qty <= 0) {
      alertLevel = InventoryAlertLevel.outOfStock;
    } else if (qty <= reorder) {
      alertLevel = InventoryAlertLevel.low;
    }

    return InventoryItem(
      id: i['id']?.toString() ?? '',
      productId: i['product_id']?.toString() ?? '',
      productName: productName,
      categoryName: categoryName,
      quantity: qty,
      unitOfMeasure: unitOfMeasure,
      unitCost: Formatters.toDouble(i['product']?['standard_price']),
      totalValue: Formatters.toDouble(i['total_value']),
      franchiseId: i['franchise_id']?.toString(),
      franchiseName: i['franchise']?['name'],
      reorderLevel: reorder,
      alertLevel: alertLevel,
      lastUpdated: i['updated_at'] ?? DateTime.now().toIso8601String(),
    );
  }

  @override
  Future<List<InventoryMovement>> getInventoryMovements(
      {String? productId}) async {
    final params = <String, String>{};
    if (productId != null) params['product_id'] = productId;

    final response =
        await _get(ApiEndpoints.stockMovements, queryParams: params.isNotEmpty ? params : null);
    final list = _extractList(response);
    return list.map((m) {
      String productName = '';
      if (m['product'] is Map) {
        productName = m['product']['name'] ?? '';
      }

      return InventoryMovement(
        id: m['id']?.toString() ?? '',
        productName: productName,
        type: m['type'] ?? '',
        quantity: Formatters.toDouble(m['quantity']).abs(),
        previousQuantity: 0,
        newQuantity: 0,
        referenceId: m['reference_id']?.toString(),
        notes: m['notes'],
        createdAt: m['created_at'] ?? DateTime.now().toIso8601String(),
      );
    }).toList();
  }

  // ══════════════════════════════════════════════════════════════
  // SALES
  // ══════════════════════════════════════════════════════════════

  @override
  Future<List<Map<String, dynamic>>> getSales(
      {String? dateFrom, String? dateTo}) async {
    final params = <String, String>{};
    if (dateFrom != null) params['from_date'] = dateFrom;
    if (dateTo != null) params['to_date'] = dateTo;

    final response = await _get('/franchise/sales',
        queryParams: params.isNotEmpty ? params : null);
    final list = _extractList(response);
    return list.map((s) {
      String customerName = '';
      if (s['customer'] is Map) {
        customerName = s['customer']['name'] ?? '';
      }
      return {
        'id': s['id']?.toString() ?? '',
        'sale_number': s['sale_number'] ?? '',
        'customer_name': customerName,
        'date': s['sale_date'] ?? s['created_at'],
        'total_amount': s['final_amount'] ?? s['total_amount'],
        'items': s['items_count'] ?? (s['items'] as List?)?.length ?? 0,
      };
    }).toList();
  }

  @override
  Future<Map<String, dynamic>> createSale(Map<String, dynamic> saleData) async {
    final response = await _post('/franchise/sales', body: saleData);
    return _extractOne(response);
  }

  // ══════════════════════════════════════════════════════════════
  // CUSTOMERS
  // ══════════════════════════════════════════════════════════════

  @override
  Future<List<Customer>> getCustomers() async {
    final response = await _get('/franchise/customers');
    final list = _extractList(response);
    return list.map((c) => Customer.fromJson({
      'id': c['id']?.toString() ?? '',
      'name': c['name'] ?? '',
      'phone': c['phone'] ?? '',
      'email': c['email'],
      'location': c['address'],
      'notes': null,
      'created_at': c['created_at'],
    })).toList();
  }

  @override
  Future<Customer> getCustomer(String id) async {
    final response = await _get('/franchise/customers/$id');
    final c = _extractOne(response);
    return Customer.fromJson({
      'id': c['id']?.toString() ?? '',
      'name': c['name'] ?? '',
      'phone': c['phone'] ?? '',
      'email': c['email'],
      'location': c['address'],
      'notes': null,
      'created_at': c['created_at'],
    });
  }

  @override
  Future<Customer> createCustomer(Map<String, dynamic> customerData) async {
    final response = await _post('/franchise/customers', body: customerData);
    final c = _extractOne(response);
    return Customer.fromJson({
      'id': c['id']?.toString() ?? '',
      'name': c['name'] ?? '',
      'phone': c['phone'] ?? '',
      'email': c['email'],
      'location': c['address'],
      'notes': null,
      'created_at': c['created_at'],
    });
  }

  // ══════════════════════════════════════════════════════════════
  // PAYMENTS
  // ══════════════════════════════════════════════════════════════

  @override
  Future<List<Payment>> getPayments({String? status}) async {
    final params = <String, String>{};
    if (status != null) params['status'] = status;

    final response = await _get('/franchise/payments',
        queryParams: params.isNotEmpty ? params : null);
    final list = _extractList(response);
    return list.map((p) => _normalizePayment(p)).toList();
  }

  Payment _normalizePayment(Map<String, dynamic> p) {
    String franchiseName = '';
    String franchiseId = '';
    if (p['franchise'] is Map) {
      franchiseName = p['franchise']['name'] ?? '';
      franchiseId = p['franchise']['id']?.toString() ?? '';
    } else {
      franchiseName = p['franchise_name'] ?? '';
      franchiseId = p['franchise_id']?.toString() ?? '';
    }

    return Payment(
      id: p['id']?.toString() ?? '',
      paymentNumber: p['payment_number'] ?? '',
      franchiseId: franchiseId,
      franchiseName: franchiseName,
      amount: Formatters.toDouble(p['amount']),
      transactionReference: p['transaction_reference'] ?? '',
      bankName: p['bank_name'],
      paymentMethod: p['payment_method'],
      status: p['status'] ?? 'pending',
      proofUrl: p['proof_of_payment_path'],
      rejectionReason: p['rejection_reason'],
      verifiedBy: p['verified_by']?.toString(),
      verifiedAt: p['verified_at'],
      submittedAt: p['submitted_at'] ?? p['created_at'] ?? '',
      updatedAt: p['updated_at'] ?? '',
    );
  }

  @override
  Future<Payment> getPayment(String id) async {
    final response = await _get('/franchise/payments/$id');
    return _normalizePayment(_extractOne(response));
  }

  @override
  Future<Payment> submitPayment(Map<String, dynamic> paymentData) async {
    // Convert to multipart for file upload support
    final fields = <String, String>{
      'amount': paymentData['amount'].toString(),
      'payment_method': paymentData['payment_method'] ?? 'cash',
    };
    if (paymentData['transaction_reference'] != null) {
      fields['transaction_reference'] = paymentData['transaction_reference'];
    }
    if (paymentData['bank_name'] != null) {
      fields['bank_name'] = paymentData['bank_name'];
    }
    if (paymentData['notes'] != null) {
      fields['notes'] = paymentData['notes'];
    }

    // Handle file upload
    List<http.MultipartFile> files = [];
    if (paymentData['proof_file_path'] != null && !kIsWeb) {
      // File upload only supported on native platforms
      // ignore: avoid_dynamic_calls
      try {
        // Use conditional import approach - on native, read file bytes
        final filePath = paymentData['proof_file_path'] as String;
        files.add(await http.MultipartFile.fromPath(
          'proof_of_payment',
          filePath,
        ));
      } catch (_) {
        // File not available - submit without proof
      }
    }

    final response = await _postMultipart('/franchise/payments', fields, files);
    return _normalizePayment(_extractOne(response));
  }

  @override
  Future<String> uploadPaymentProof(
      String paymentId, List<int> fileBytes, String fileName) async {
    final request = http.MultipartRequest(
      'POST',
      Uri.parse(
          '${ApiEndpoints.baseUrl}/franchise/payments/$paymentId/upload-proof'),
    );
    request.headers.addAll({
      'Accept': 'application/json',
      if (_token != null) 'Authorization': 'Bearer $_token',
    });
    request.files.add(http.MultipartFile.fromBytes(
      'proof_of_payment',
      fileBytes,
      filename: fileName,
    ));

    final streamedResponse = await _client.send(request);
    final response = await http.Response.fromStream(streamedResponse);
    final body = _handleResponse(response);
    return body['data']?['proof_of_payment_path'] ?? '';
  }

  @override
  Future<Payment> verifyPayment(String id, {double? verifiedAmount, String? notes}) async {
    final body = <String, dynamic>{
      'verified_amount': verifiedAmount ?? 0,
    };
    if (notes != null) body['finance_notes'] = notes;
    final response = await _post('/finance/payments/$id/verify', body: body);
    return _normalizePayment(_extractOne(response));
  }

  @override
  Future<Payment> acceptPayment(String id, {String? notes}) async {
    final response = await _post('/finance/payments/$id/accept');
    final data = response['data'];
    if (data is Map<String, dynamic> && data.containsKey('payment')) {
      return _normalizePayment(data['payment'] as Map<String, dynamic>);
    }
    return _normalizePayment(_extractOne(response));
  }

  @override
  Future<Payment> rejectPayment(String id, String reason) async {
    final response = await _post('/finance/payments/$id/reject', body: {
      'rejection_reason': reason,
    });
    return _normalizePayment(_extractOne(response));
  }

  // ══════════════════════════════════════════════════════════════
  // FINANCE PAYMENTS
  // ══════════════════════════════════════════════════════════════

  @override
  Future<List<Payment>> getFinancePayments({String? status, String? franchiseId}) async {
    final params = <String, String>{};
    if (status != null) params['status'] = status;
    if (franchiseId != null) params['franchise_id'] = franchiseId;

    final response = await _get('/finance/payments',
        queryParams: params.isNotEmpty ? params : null);
    final list = _extractList(response);
    return list.map((p) => _normalizePayment(p)).toList();
  }

  @override
  Future<Payment> getFinancePayment(String id) async {
    final response = await _get('/finance/payments/$id');
    return _normalizePayment(_extractOne(response));
  }

  @override
  Future<List<Payment>> getPendingFinancePayments() async {
    final response = await _get('/finance/payments/pending');
    final list = _extractList(response);
    return list.map((p) => _normalizePayment(p)).toList();
  }

  @override
  Future<Map<String, dynamic>> getFinanceDashboardStats() async {
    final response = await _get('/finance/dashboard');
    final data = response['data'] is Map<String, dynamic>
        ? response['data'] as Map<String, dynamic>
        : response;
    return data;
  }

  // ══════════════════════════════════════════════════════════════
  // DASHBOARD
  // ══════════════════════════════════════════════════════════════

  @override
  Future<Map<String, dynamic>> getDashboardStats() async {
    try {
      final response = await _get('/franchise/dashboard');
      final data = response['data'] is Map<String, dynamic>
          ? response['data'] as Map<String, dynamic>
          : response;
      final summary = data['summary'] ?? {};
      return {
        'total_sales_this_month': summary['total_sales_this_month'] ?? 0,
        'total_sales_ytd': summary['total_sales_ytd'] ?? 0,
        'total_orders_pending': summary['pending_orders'] ?? 0,
        'inventory_value': summary['total_inventory_value'] ?? 0,
        'outstanding_balance': summary['outstanding_balance'] ?? 0,
        'credit_limit': summary['credit_limit'] ?? 0,
        'low_stock_items': summary['low_stock_items'] ?? 0,
        'sales_by_category': data['sales_by_category'] ?? [],
      };
    } catch (_) {
      return {
        'total_sales_this_month': 0,
        'total_sales_ytd': 0,
        'total_orders_pending': 0,
        'inventory_value': 0,
        'outstanding_balance': 0,
        'credit_limit': 0,
        'low_stock_items': 0,
        'sales_by_category': [],
      };
    }
  }

  @override
  Future<List<Map<String, dynamic>>> getSalesAnalytics({String? period}) async {
    try {
      final response = await _get('/franchise/dashboard');
      final data = response['data'] is Map<String, dynamic>
          ? response['data'] as Map<String, dynamic>
          : response;
      final trend = data['sales_trend'] ?? [];
      return (trend as List).map((t) => {
        'month': t['date'] ?? '',
        'amount': t['total_sales'] ?? 0,
      }).toList();
    } catch (_) {
      return [];
    }
  }

  @override
  Future<AccountSummary> getAccountSummary() async {
    try {
      final response = await _get('/franchise/dashboard');
      final data = response['data'] is Map<String, dynamic>
          ? response['data'] as Map<String, dynamic>
          : response;
      final summary = data['summary'] ?? {};
      return AccountSummary.fromJson({
        'total_sales': summary['total_sales_ytd'] ?? 0,
        'total_payments': 0,
        'outstanding_balance': summary['outstanding_balance'] ?? 0,
        'credit_limit': summary['credit_limit'] ?? 0,
      });
    } catch (_) {
      return const AccountSummary(
        totalSales: 0,
        totalPayments: 0,
        outstandingBalance: 0,
        creditLimit: 0,
      );
    }
  }

  // ══════════════════════════════════════════════════════════════
  // CHAT
  // ══════════════════════════════════════════════════════════════

  @override
  Future<List<Map<String, dynamic>>> getConversations() async {
    final response = await _get('/conversations');
    final list = _extractList(response);
    return list.map((c) => c as Map<String, dynamic>).toList();
  }

  @override
  Future<Map<String, dynamic>> createConversation(String subject, String initialMessage) async {
    final response = await _post('/conversations', body: {
      'subject': subject,
      'message': initialMessage,
    });
    return _extractOne(response);
  }

  @override
  Future<Map<String, dynamic>> getConversation(String id) async {
    final response = await _get('/conversations/$id');
    return _extractOne(response);
  }

  @override
  Future<Map<String, dynamic>> sendMessage(String conversationId, String message) async {
    final response = await _post('/conversations/$conversationId/messages', body: {
      'message': message,
    });
    return _extractOne(response);
  }

  // ══════════════════════════════════════════════════════════════
  // NOTIFICATIONS
  // ══════════════════════════════════════════════════════════════

  @override
  Future<List<NotificationItem>> getNotifications({bool? unreadOnly}) async {
    final params = <String, String>{};
    if (unreadOnly == true) params['unreadOnly'] = '1';

    try {
      final response = await _get(ApiEndpoints.notifications,
          queryParams: params.isNotEmpty ? params : null);
      final list = _extractList(response);
      return list.map((n) => NotificationItem.fromJson({
        'id': n['id']?.toString() ?? '',
        'title': n['title'] ?? '',
        'message': n['message'] ?? '',
        'type': n['type'] ?? 'general',
        'is_read': n['is_read'] ?? false,
        'created_at': n['created_at'],
        'reference_id': n['reference_id']?.toString(),
        'reference_type': n['reference_type'],
      })).toList();
    } catch (_) {
      return [];
    }
  }

  @override
  Future<void> markNotificationRead(String id) async {
    try {
      await _post('${ApiEndpoints.notifications}/$id/read');
    } catch (_) {}
  }

  @override
  Future<void> markAllNotificationsRead() async {
    try {
      await _post(ApiEndpoints.notificationsReadAll);
    } catch (_) {}
  }

  @override
  Future<int> getUnreadNotificationCount() async {
    try {
      final response = await _get(ApiEndpoints.notificationsUnreadCount);
      return response['count'] ?? 0;
    } catch (_) {
      return 0;
    }
  }
}
