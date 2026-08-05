import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';

class LocalStorageService {
  static const _keyToken = 'auth_token';
  static const _keyRefreshToken = 'auth_refresh_token';
  static const _keyUser = 'user_data';
  static const _keyTheme = 'theme_mode';
  static const _keyLanguage = 'language';
  static const _keyFirstLaunch = 'first_launch';

  late SharedPreferences _prefs;

  Future<void> initialize() async {
    _prefs = await SharedPreferences.getInstance();
  }

  // Token management
  Future<void> saveToken(String token) async {
    await _prefs.setString(_keyToken, token);
  }

  String? getToken() {
    return _prefs.getString(_keyToken);
  }

  Future<void> saveRefreshToken(String token) async {
    await _prefs.setString(_keyRefreshToken, token);
  }

  String? getRefreshToken() {
    return _prefs.getString(_keyRefreshToken);
  }

  // User data
  Future<void> saveUser(Map<String, dynamic> userData) async {
    final encoded = jsonEncode(userData);
    await _prefs.setString(_keyUser, encoded);
  }

  Map<String, dynamic>? getUser() {
    final raw = _prefs.getString(_keyUser);
    if (raw == null) return null;
    try {
      return jsonDecode(raw) as Map<String, dynamic>;
    } catch (_) {
      return null;
    }
  }

  // Theme
  Future<void> saveThemeMode(String mode) async {
    await _prefs.setString(_keyTheme, mode);
  }

  String? getThemeMode() {
    return _prefs.getString(_keyTheme);
  }

  // Language
  Future<void> saveLanguage(String lang) async {
    await _prefs.setString(_keyLanguage, lang);
  }

  String? getLanguage() {
    return _prefs.getString(_keyLanguage);
  }

  // First launch
  bool get isFirstLaunch {
    return _prefs.getBool(_keyFirstLaunch) ?? true;
  }

  Future<void> setFirstLaunchDone() async {
    await _prefs.setBool(_keyFirstLaunch, false);
  }

  // Clear all data
  Future<void> clearAll() async {
    await _prefs.clear();
  }

  Future<void> clearAuthData() async {
    await _prefs.remove(_keyToken);
    await _prefs.remove(_keyRefreshToken);
    await _prefs.remove(_keyUser);
  }
}
