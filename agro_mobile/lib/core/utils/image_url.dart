import '../constants/api_endpoints.dart';

/// Resolves a server-relative image path (e.g. `products/x.png`,
/// `storage/avatars/y.png`) to a full URL based on the configured API host.
/// Absolute `http(s)://` values are returned untouched.
String resolveImageUrl(dynamic raw) {
  if (raw == null) return '';
  final value = raw.toString().trim();
  if (value.isEmpty) return '';
  if (value.startsWith('http://') || value.startsWith('https://')) return value;
  final host = ApiEndpoints.baseUrl.replaceFirst(RegExp(r'/api/?$'), '');
  final path = value.startsWith('/') ? value : '/storage/$value';
  return '$host$path';
}
