import '../core/enums/app_enums.dart';

class Order {
  final String id;
  final String? orderNumber;
  final String franchiseId;
  final String franchiseName;
  final List<OrderItem> items;
  final double totalAmount;
  final double? adjustedAmount;
  final String status;
  final String? declineReason;
  final String? adjustmentNotes;
  final DateTime? expectedDeliveryDate;
  final DateTime? deliveredAt;
  final DateTime? createdAt;
  final DateTime? updatedAt;
  final String? staffNotes;

  const Order({
    required this.id,
    this.orderNumber,
    required this.franchiseId,
    required this.franchiseName,
    required this.items,
    required this.totalAmount,
    this.adjustedAmount,
    required this.status,
    this.declineReason,
    this.adjustmentNotes,
    this.expectedDeliveryDate,
    this.deliveredAt,
    this.createdAt,
    this.updatedAt,
    this.staffNotes,
  });

  OrderStatus get statusEnum {
    switch (status) {
      case 'pending':
        return OrderStatus.pending;
      case 'approved':
        return OrderStatus.approved;
      case 'declined':
        return OrderStatus.declined;
      case 'adjusted':
        return OrderStatus.adjusted;
      case 'delivered':
        return OrderStatus.delivered;
      case 'cancelled':
        return OrderStatus.cancelled;
      default:
        return OrderStatus.pending;
    }
  }

  static DateTime? _parseDate(dynamic value) {
    if (value == null) return null;
    if (value is DateTime) return value;
    if (value is String && value.isNotEmpty) return DateTime.tryParse(value);
    return null;
  }

  static double _toDouble(dynamic value) {
    if (value is num) return value.toDouble();
    if (value is String) return double.tryParse(value) ?? 0;
    return 0;
  }

  static int _toInt(dynamic value) {
    if (value is num) return value.toInt();
    if (value is String) return int.tryParse(value) ?? 0;
    return 0;
  }

  factory Order.fromJson(Map<String, dynamic> json) {
    final franchise = json['franchise'];
    return Order(
      id: json['id']?.toString() ?? '',
      orderNumber: json['order_number'],
      franchiseId: franchise is Map
          ? (franchise['id']?.toString() ?? json['franchise_id']?.toString() ?? '')
          : (json['franchise_id']?.toString() ?? ''),
      franchiseName: franchise is Map
          ? (franchise['name'] ?? '')
          : (json['franchise_name'] ?? ''),
      items: (json['items'] as List<dynamic>?)
              ?.map((i) => OrderItem.fromJson(i))
              .toList() ??
          [],
      totalAmount: _toDouble(json['total_amount']),
      adjustedAmount: json['adjusted_amount'] == null
          ? null
          : _toDouble(json['adjusted_amount']),
      status: json['status'] ?? 'pending',
      declineReason: json['decline_reason'],
      adjustmentNotes: json['adjustment_notes'],
      expectedDeliveryDate: _parseDate(json['expected_delivery_date']),
      deliveredAt: _parseDate(json['delivered_at'] ?? json['completed_at'] ?? json['received_at']),
      createdAt: _parseDate(json['created_at']) ?? DateTime.now(),
      updatedAt: _parseDate(json['updated_at']) ?? DateTime.now(),
      staffNotes: json['notes'] ?? json['staff_notes'],
    );
  }

  int get totalItems => items.fold(0, (sum, item) => sum + item.quantity);

  String get displayOrderNumber => orderNumber ?? id;
}

class OrderItem {
  final String id;
  final String productId;
  final String productName;
  final String categoryName;
  final int quantity;
  final double unitPrice;
  final double totalPrice;
  final int? adjustedQuantity;
  final String? notes;

  const OrderItem({
    required this.id,
    required this.productId,
    required this.productName,
    required this.categoryName,
    required this.quantity,
    required this.unitPrice,
    required this.totalPrice,
    this.adjustedQuantity,
    this.notes,
  });

  factory OrderItem.fromJson(Map<String, dynamic> json) {
    final product = json['product'];
    return OrderItem(
      id: json['id']?.toString() ?? '',
      productId: json['product_id']?.toString() ?? '',
      productName: product is Map
          ? (product['name'] ?? '')
          : (json['product_name'] ?? ''),
      categoryName: product is Map && product['category'] is Map
          ? (product['category']['name'] ?? '')
          : (json['category_name'] ?? ''),
      quantity: Order._toInt(json['quantity']),
      unitPrice: Order._toDouble(json['unit_price']),
      totalPrice: Order._toDouble(json['subtotal'] ?? json['total_price']),
      adjustedQuantity: json['adjusted_quantity'] == null
          ? null
          : Order._toInt(json['adjusted_quantity']),
      notes: json['adjustment_notes'] ?? json['notes'],
    );
  }

  int get effectiveQuantity => adjustedQuantity ?? quantity;
}
