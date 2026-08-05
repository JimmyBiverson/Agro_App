import 'dart:async';
import 'dart:convert';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:agro_app/core/exceptions/app_exception.dart';
import 'package:agro_app/services/api/http_api_service.dart';

void main() {
  group('HttpApiService.getOrders', () {
    test('parses paginated orders response without error', () async {
      final api = HttpApiService(
        client: MockClient((request) async {
          return http.Response(
            jsonEncode(_ordersResponse),
            200,
            headers: {'content-type': 'application/json'},
          );
        }),
      );
      api.setToken('test-token');
      api.setUserRole('franchisePartner');

      final orders = await api.getOrders();

      expect(orders, isNotEmpty);
      expect(orders.first.id, '33');
      expect(orders.first.status, 'approved');
      expect(orders.first.createdAt, isA<DateTime>());
      expect(orders.first.updatedAt, isA<DateTime>());
      expect(orders.first.expectedDeliveryDate, isA<DateTime>());
      expect(orders.first.items.first.productName, 'Thunder 145-SE');
      expect(orders.first.items.first.categoryName, 'Insecticides');
      expect(orders.first.items.first.quantity, 8);
    });
  });

  group('HttpApiService.getPayments', () {
    test('parses paginated payments response without error', () async {
      final api = HttpApiService(
        client: MockClient((request) async {
          return http.Response(
            jsonEncode(_paymentsResponse),
            200,
            headers: {'content-type': 'application/json'},
          );
        }),
      );
      api.setToken('test-token');
      api.setUserRole('franchisePartner');

      final payments = await api.getPayments();

      expect(payments, isNotEmpty);
      expect(payments.first.id, '34');
      expect(payments.first.amount, 50000);
      expect(payments.first.status, 'accepted');
      expect(payments.first.franchiseName, 'Kampala Franchise');
    });
  });

  group('HttpApiService.getInventory', () {
    test('parses paginated inventory response without error', () async {
      final api = HttpApiService(
        client: MockClient((request) async {
          return http.Response(
            jsonEncode(_inventoryResponse),
            200,
            headers: {'content-type': 'application/json'},
          );
        }),
      );
      api.setToken('test-token');
      api.setUserRole('franchisePartner');

      final items = await api.getInventory();

      expect(items, isNotEmpty);
      expect(items.first.id, '1');
      expect(items.first.productName, 'Roundup PowerMax');
      expect(items.first.categoryName, 'Herbicides');
      expect(items.first.quantity, 9);
      expect(items.first.unitOfMeasure, 'Litres');
      expect(items.first.reorderLevel, 7);
    });
  });

  group('errorMessageOf', () {
    test('surfaces AppException message', () {
      expect(
        errorMessageOf(
          const AppException(message: 'Order exceeds credit limit.'),
          'Failed to create order',
        ),
        'Order exceeds credit limit.',
      );
    });

    test('uses fallback for unknown exceptions', () {
      expect(
        errorMessageOf(Exception('boom'), 'Failed to load orders'),
        'Failed to load orders',
      );
    });

    test('maps network client errors to a friendly message', () {
      expect(
        errorMessageOf(http.ClientException('Connection refused'), 'Failed'),
        'Network error. Please check your connection and try again.',
      );
    });

    test('maps timeouts to a friendly message', () {
      expect(
        errorMessageOf(TimeoutException('timed out'), 'Failed'),
        'The request timed out. Please try again.',
      );
    });
  });
}

final _ordersResponse = {
  'data': [
    {
      'id': 33,
      'order_number': 'ORD-202607-0033',
      'franchise_id': 1,
      'ordered_by': 4,
      'status': 'approved',
      'received_at': null,
      'served_at': null,
      'completed_at': null,
      'notes': null,
      'expected_delivery_date': '2026-07-25T21:00:00.000000Z',
      'approved_by': 2,
      'approved_at': '2026-07-22T09:00:00.000000Z',
      'declined_by': null,
      'declined_at': null,
      'decline_reason': null,
      'total_amount': '1045000.00',
      'created_at': '2026-07-22T05:00:00.000000Z',
      'updated_at': '2026-07-22T11:22:50.000000Z',
      'items': [
        {
          'id': 90,
          'order_id': 33,
          'product_id': 2,
          'quantity': '8.00',
          'unit_price': '35000.00',
          'adjusted_quantity': null,
          'adjustment_notes': null,
          'original_unit_price': '35000.00',
          'subtotal': '280000.00',
          'notes': null,
          'rejection_reason': null,
          'status': 'pending',
          'created_at': '2026-07-22T11:22:50.000000Z',
          'updated_at': '2026-07-22T11:22:50.000000Z',
          'product': {
            'id': 2,
            'name': 'Thunder 145-SE',
            'sku': 'INSE-001',
            'category': {'id': 2, 'name': 'Insecticides'},
          },
        },
      ],
    },
  ],
};

final _paymentsResponse = {
  'data': [
    {
      'id': 34,
      'payment_number': 'PAY-202607-0034',
      'franchise_id': 1,
      'amount': '50000.00',
      'payment_method': 'cash',
      'transaction_reference': 'E2E-FINAL-001',
      'bank_name': null,
      'proof_of_payment_path': null,
      'status': 'accepted',
      'submitted_at': '2026-07-22T13:52:49.000000Z',
      'verified_by': 3,
      'verified_at': '2026-07-22T13:52:49.000000Z',
      'rejected_by': null,
      'rejected_at': null,
      'rejection_reason': null,
      'finance_notes': null,
      'verified_amount': '50000.00',
      'created_at': '2026-07-22T13:52:49.000000Z',
      'updated_at': '2026-07-22T13:52:49.000000Z',
      'franchise': {'id': 1, 'name': 'Kampala Franchise'},
    },
  ],
};

final _inventoryResponse = {
  'data': [
    {
      'id': 1,
      'franchise_id': 1,
      'product_id': 1,
      'quantity': '9.00',
      'reorder_level': '7.00',
      'total_value': '405000.00',
      'created_at': '2026-07-22T11:22:50.000000Z',
      'updated_at': '2026-07-22T11:29:04.000000Z',
      'product': {
        'id': 1,
        'name': 'Roundup PowerMax',
        'sku': 'HERB-001',
        'category_id': 1,
        'unit_of_measure': 'Litres',
        'packaging_details': '1L, 5L, 20L',
        'selling_price': '45000.00',
        'standard_price': '45000.00',
        'image': 'products/HERB-001.png',
        'is_active': true,
        'category': {'id': 1, 'name': 'Herbicides'},
      },
    },
  ],
};
