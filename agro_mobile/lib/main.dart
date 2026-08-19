import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'app.dart';
import 'providers/auth_provider.dart';
import 'providers/product_provider.dart';
import 'providers/order_provider.dart';
import 'providers/inventory_provider.dart';
import 'providers/payment_provider.dart';
import 'providers/customer_provider.dart';
import 'providers/notification_provider.dart';
import 'services/api/api_service.dart';
import 'services/api/http_api_service.dart';
import 'providers/site_settings_provider.dart';
import 'services/storage/local_storage_service.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  final localStorage = LocalStorageService();
  await localStorage.initialize();

  final apiService = HttpApiService();
  await apiService.initialize();

  // Restore saved token if any
  final savedToken = localStorage.getToken();
  if (savedToken != null) {
    apiService.setToken(savedToken);
  }

  runApp(
    MultiProvider(
      providers: [
        Provider<ApiService>.value(value: apiService),
        ChangeNotifierProvider(
          create: (_) =>
              AuthProvider(apiService: apiService, storage: localStorage),
        ),
        ChangeNotifierProvider(
          create: (_) =>
              SiteSettingsProvider(apiService: apiService)..loadSettings(),
        ),
        ChangeNotifierProvider(
          create: (_) => ProductProvider(apiService: apiService),
        ),
        ChangeNotifierProvider(
          create: (_) => OrderProvider(apiService: apiService),
        ),
        ChangeNotifierProvider(
          create: (_) => InventoryProvider(apiService: apiService),
        ),
        ChangeNotifierProvider(
          create: (_) => PaymentProvider(apiService: apiService),
        ),
        ChangeNotifierProvider(
          create: (_) => CustomerProvider(apiService: apiService),
        ),
        ChangeNotifierProvider(
          create: (_) => NotificationProvider(apiService: apiService),
        ),
      ],
      child: const FarmmantraApp(),
    ),
  );
}
