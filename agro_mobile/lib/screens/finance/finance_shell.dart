import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/theme/app_colors.dart';
import '../../core/refresh/auto_refresh_mixin.dart';
import '../../providers/auth_provider.dart';
import '../../providers/payment_provider.dart';
import '../../providers/notification_provider.dart';
import '../../widgets/common/logout_dialog.dart';
import 'finance_dashboard.dart';
import 'payments/payment_list_screen.dart';
import '../shared/notifications/notification_screen.dart';

class FinanceShell extends StatefulWidget {
  const FinanceShell({super.key});

  @override
  State<FinanceShell> createState() => _FinanceShellState();
}

class _FinanceShellState extends State<FinanceShell>
    with AutoRefreshMixin<FinanceShell> {
  int _currentTab = 0;
  late final ValueNotifier<int> _tabNotifier;

  @override
  void initState() {
    super.initState();
    _tabNotifier = ValueNotifier(_currentTab);
    startAutoRefresh();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadInitialData();
    });
  }

  void _loadInitialData() {
    context.read<PaymentProvider>().loadFinanceDashboard();
    context.read<PaymentProvider>().loadFinancePayments();
    context.read<PaymentProvider>().loadPendingFinancePayments();
    context.read<NotificationProvider>().loadNotifications();
  }

  @override
  void onAutoRefresh() {
    if (!mounted) return;
    context.read<PaymentProvider>().loadFinanceDashboard(silent: true);
    context.read<PaymentProvider>().loadFinancePayments(silent: true);
    context.read<PaymentProvider>().loadPendingFinancePayments(silent: true);
    context.read<NotificationProvider>().loadNotifications(silent: true);
    context.read<NotificationProvider>().refreshUnreadCount();
  }

  @override
  void dispose() {
    _tabNotifier.dispose();
    super.dispose();
  }

  void _switchTab(int index) {
    setState(() => _currentTab = index);
    _tabNotifier.value = index;
    _refreshTabData(index);
  }

  void _refreshTabData(int index) {
    if (!mounted) return;
    if (index == 1) {
      context.read<PaymentProvider>().loadPendingFinancePayments(silent: true);
    } else if (index == 2) {
      context.read<PaymentProvider>().loadFinancePayments(silent: true);
    }
  }

  @override
  Widget build(BuildContext context) {
    return FinanceTabScope(
      tabNotifier: _tabNotifier,
      onSwitchTab: _switchTab,
      child: Scaffold(
        appBar: AppBar(
          title: Text(_getAppBarTitle()),
          actions: [
            Stack(
              children: [
                IconButton(
                  icon: const Icon(Icons.notifications_outlined),
                  onPressed: () => _switchTab(3),
                ),
                Positioned(
                  right: 8,
                  top: 8,
                  child: Consumer<NotificationProvider>(
                    builder: (_, notifier, child) {
                      if (notifier.unreadCount == 0) return const SizedBox.shrink();
                      return Container(
                        padding: const EdgeInsets.all(4),
                        decoration: const BoxDecoration(
                          color: AppColors.error,
                          shape: BoxShape.circle,
                        ),
                        child: Text(
                          notifier.unreadCount > 9 ? '9+' : '${notifier.unreadCount}',
                          style: const TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.w600),
                        ),
                      );
                    },
                  ),
                ),
              ],
            ),
          ],
        ),
        body: _buildBody(),
        bottomNavigationBar: BottomNavigationBar(
          currentIndex: _currentTab,
          onTap: _switchTab,
          type: BottomNavigationBarType.fixed,
          selectedItemColor: AppColors.primaryGreen,
          unselectedItemColor: AppColors.textLight,
          selectedLabelStyle: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600),
          unselectedLabelStyle: const TextStyle(fontSize: 11),
          items: const [
            BottomNavigationBarItem(
              icon: Icon(Icons.dashboard_outlined),
              activeIcon: Icon(Icons.dashboard),
              label: 'Home',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.payments_outlined),
              activeIcon: Icon(Icons.payments),
              label: 'Payments',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.receipt_long_outlined),
              activeIcon: Icon(Icons.receipt_long),
              label: 'History',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.notifications_outlined),
              activeIcon: Icon(Icons.notifications),
              label: 'Alerts',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.person_outline),
              activeIcon: Icon(Icons.person),
              label: 'Profile',
            ),
          ],
        ),
      ),
    );
  }

  String _getAppBarTitle() {
    switch (_currentTab) {
      case 0:
        return 'Finance Dashboard';
      case 1:
        return 'Pending Payments';
      case 2:
        return 'Payment History';
      case 3:
        return 'Notifications';
      case 4:
        return 'Profile';
      default:
        return 'Finance Department';
    }
  }

  Widget _buildBody() {
    switch (_currentTab) {
      case 0:
        return const FinanceDashboard();
      case 1:
        return PaymentListScreen(statusFilter: 'pending');
      case 2:
        return const PaymentListScreen();
      case 3:
        return const NotificationScreen();
      case 4:
        return const _FinanceProfileTab();
      default:
        return const FinanceDashboard();
    }
  }
}

class FinanceTabScope extends InheritedWidget {
  final ValueNotifier<int> tabNotifier;
  final void Function(int) onSwitchTab;

  const FinanceTabScope({
    super.key,
    required this.tabNotifier,
    required this.onSwitchTab,
    required super.child,
  });

  static FinanceTabScope? of(BuildContext context) {
    return context.dependOnInheritedWidgetOfExactType<FinanceTabScope>();
  }

  @override
  bool updateShouldNotify(FinanceTabScope oldWidget) {
    return tabNotifier != oldWidget.tabNotifier;
  }
}

class _FinanceProfileTab extends StatelessWidget {
  const _FinanceProfileTab();

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;
    return SingleChildScrollView(
      padding: const EdgeInsets.all(24),
      child: Column(
        children: [
          const SizedBox(height: 16),
          CircleAvatar(
            radius: 40,
            backgroundColor: AppColors.primaryGreen.withAlpha(26),
            child: Text(
              user?.initials ?? '?',
              style: const TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.w700,
                color: AppColors.primaryGreen,
              ),
            ),
          ),
          const SizedBox(height: 12),
          Text(
            user?.name ?? '',
            style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w700),
          ),
          const SizedBox(height: 4),
          Text(
            user?.email ?? '',
            style: const TextStyle(color: AppColors.textSecondary, fontSize: 13),
          ),
          const SizedBox(height: 8),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
            decoration: BoxDecoration(
              color: AppColors.info.withAlpha(26),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Text(
              user?.role.displayName ?? '',
              style: const TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w600,
                color: AppColors.info,
              ),
            ),
          ),
          const SizedBox(height: 32),
          _buildMenuItem(context, Icons.person_outline, 'Edit Profile', () {}),
          _buildMenuItem(context, Icons.lock_outline, 'Change Password', () {}),
          _buildMenuItem(context, Icons.help_outline, 'Help & Support', () {}),
          const SizedBox(height: 16),
          SizedBox(
            width: double.infinity,
            child: OutlinedButton.icon(
              onPressed: () async {
                final confirmed = await confirmLogout(context);
                if (!confirmed || !context.mounted) return;
                context.read<AuthProvider>().logout();
                Navigator.of(context).pushReplacementNamed('/login');
              },
              icon: const Icon(Icons.logout, color: AppColors.error),
              label: const Text('Logout', style: TextStyle(color: AppColors.error)),
              style: OutlinedButton.styleFrom(
                side: const BorderSide(color: AppColors.error),
                padding: const EdgeInsets.symmetric(vertical: 14),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMenuItem(BuildContext context, IconData icon, String label, VoidCallback onTap) {
    return ListTile(
      leading: Icon(icon, color: AppColors.textSecondary),
      title: Text(label),
      trailing: const Icon(Icons.chevron_right, color: AppColors.textLight),
      onTap: onTap,
      contentPadding: const EdgeInsets.symmetric(horizontal: 8),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
    );
  }
}
