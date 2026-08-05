import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/theme/app_colors.dart';
import '../../core/enums/app_enums.dart';
import '../../core/utils/formatters.dart';
import '../../providers/auth_provider.dart';
import '../../providers/order_provider.dart';
import '../../widgets/common/app_card.dart';
import '../../widgets/common/section_header.dart';
import '../../widgets/common/status_badge.dart';
import '../../widgets/common/loading_view.dart';
import 'staff_shell.dart';

class StaffDashboard extends StatefulWidget {
  const StaffDashboard({super.key});

  @override
  State<StaffDashboard> createState() => _StaffDashboardState();
}

class _StaffDashboardState extends State<StaffDashboard> {
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _loadData());
  }

  Future<void> _loadData() async {
    try {
      await context.read<OrderProvider>().loadOrders();
    } catch (_) {}
    if (mounted) setState(() => _isLoading = false);
  }

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildWelcomeHeader(),
          const SizedBox(height: 20),
          if (_isLoading)
            const SizedBox(height: 200, child: Center(child: LoadingView()))
          else ...[
            _buildQuickActions(),
            const SizedBox(height: 8),
            SectionHeader(
              title: 'Pending Orders',
              actionLabel: 'View All',
              onAction: () {
                StaffTabScope.of(context)?.onSwitchTab(1);
              },
            ),
            _buildPendingOrders(),
            const SizedBox(height: 24),
          ],
        ],
      ),
    );
  }

  Widget _buildWelcomeHeader() {
    final user = context.watch<AuthProvider>().user;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Hello, ${user?.name ?? 'Staff'}',
          style: const TextStyle(
            fontSize: 22,
            fontWeight: FontWeight.w700,
            color: AppColors.textPrimary,
          ),
        ),
        const SizedBox(height: 4),
        Text(
          Formatters.dateTime(DateTime.now()),
          style: const TextStyle(
            fontSize: 13,
            color: AppColors.textSecondary,
          ),
        ),
      ],
    );
  }

  Widget _buildQuickActions() {
    final allOrders = context.read<OrderProvider>().orders;
    final pendingCount = allOrders.where((o) => o.statusEnum == OrderStatus.pending).length;
    final approvedToday = allOrders.where((o) => o.statusEnum == OrderStatus.approved).length;

    return Row(
      children: [
        Expanded(
          child: _buildQuickActionCard(
            'Pending\nOrders',
            '$pendingCount',
            Icons.pending_actions,
            AppColors.warning,
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: _buildQuickActionCard(
            'Total\nOrders',
            '${allOrders.length}',
            Icons.receipt_long,
            AppColors.info,
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: _buildQuickActionCard(
            'Approved',
            '$approvedToday',
            Icons.check_circle_outline,
            AppColors.primaryGreen,
          ),
        ),
      ],
    );
  }

  Widget _buildQuickActionCard(String label, String count, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.surfaceWhite,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.divider),
      ),
      child: Column(
        children: [
          Icon(icon, color: color, size: 24),
          const SizedBox(height: 8),
          Text(
            count,
            style: TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.w700,
              color: color,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            label,
            style: const TextStyle(
              fontSize: 11,
              color: AppColors.textSecondary,
              height: 1.2,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildPendingOrders() {
    final pendingOrders = context.read<OrderProvider>().orders
        .where((o) => o.statusEnum == OrderStatus.pending)
        .take(5)
        .toList();

    if (pendingOrders.isEmpty) {
      return const AppCard(
        child: Center(
          child: Padding(
            padding: EdgeInsets.all(24),
            child: Text('No pending orders', style: TextStyle(color: AppColors.success)),
          ),
        ),
      );
    }

    return Column(
      children: pendingOrders.map((order) {
        return AppCard(
          onTap: () {},
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: AppColors.warning.withAlpha(26),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Icon(Icons.pending_actions, color: AppColors.warning, size: 20),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      order.orderNumber ?? order.id,
                      style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      '${order.franchiseName} \u2022 ${order.items.length} items',
                      style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                    ),
                  ],
                ),
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text(
                    Formatters.currency(order.totalAmount),
                    style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
                  ),
                  const SizedBox(height: 4),
                  StatusBadge.fromOrderStatus(order.status),
                ],
              ),
            ],
          ),
        );
      }).toList(),
    );
  }
}
