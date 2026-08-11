import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/theme/app_colors.dart';
import '../../core/utils/responsive.dart';
import '../../core/utils/formatters.dart';
import '../../core/enums/app_enums.dart';
import '../../providers/auth_provider.dart';
import '../../providers/order_provider.dart';
import '../../providers/inventory_provider.dart';
import '../../widgets/common/app_card.dart';
import '../../widgets/common/section_header.dart';
import '../../widgets/common/status_badge.dart';
import '../../widgets/common/loading_view.dart';
import 'franchise_shell.dart';
import 'orders/create_order_screen.dart';
import 'sales/create_sale_screen.dart';

class FranchiseDashboard extends StatefulWidget {
  const FranchiseDashboard({super.key});

  @override
  State<FranchiseDashboard> createState() => _FranchiseDashboardState();
}

class _FranchiseDashboardState extends State<FranchiseDashboard> {
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _loadData());
  }

  Future<void> _loadData() async {
    final orderProvider = context.read<OrderProvider>();
    final invProvider = context.read<InventoryProvider>();

    try {
      await Future.wait([
        orderProvider.loadOrders(),
        invProvider.loadInventory(),
      ]);
    } catch (_) {}

    if (!mounted) return;

    setState(() {
      _isLoading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: EdgeInsets.symmetric(
        horizontal: Responsive.horizontalPadding(context),
        vertical: 16,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildWelcomeHeader(),
          const SizedBox(height: 20),
          if (_isLoading)
            const SizedBox(height: 200, child: Center(child: LoadingView()))
          else ...[
            _buildStatsGrid(context),
            const SizedBox(height: 8),
            SectionHeader(
              title: 'Recent Orders',
              actionLabel: 'View All',
              onAction: () {
                FranchiseTabScope.of(context)?.onSwitchTab(1);
              },
            ),
            _buildRecentOrders(),
            const SizedBox(height: 8),
            SectionHeader(
              title: 'Inventory Alerts',
              actionLabel: 'View All',
              onAction: () {
                FranchiseTabScope.of(context)?.onSwitchTab(3);
              },
            ),
            _buildInventoryAlerts(),
          ],
          const SizedBox(height: 24),
        ],
      ),
    );
  }

  Widget _buildWelcomeHeader() {
    final user = context.watch<AuthProvider>().user;
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            AppColors.primaryGreenDark,
            AppColors.primaryGreen,
            Color(0xFF388E3C),
          ],
        ),
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: AppColors.primaryGreen.withAlpha(50),
            blurRadius: 15,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: Colors.white.withAlpha(38),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: const Icon(
                  Icons.agriculture_rounded,
                  color: Colors.white,
                  size: 28,
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Welcome back, ${user?.name ?? 'Partner'}! 👋',
                      style: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w800,
                        color: Colors.white,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      user?.franchiseName ?? 'Farmmantra Franchise Partner',
                      style: const TextStyle(
                        fontSize: 12,
                        color: Colors.white70,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          const Divider(color: Colors.white24, height: 1),
          const SizedBox(height: 14),
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            physics: const BouncingScrollPhysics(),
            child: Row(
              children: [
                _buildQuickActionButton(
                  icon: Icons.add_shopping_cart,
                  label: 'New Order',
                  onTap: () {
                    Navigator.of(context).push(
                      MaterialPageRoute(builder: (_) => const CreateOrderScreen()),
                    );
                  },
                ),
                const SizedBox(width: 8),
                _buildQuickActionButton(
                  icon: Icons.storefront_outlined,
                  label: 'Products',
                  onTap: () {
                    FranchiseTabScope.of(context)?.onSwitchTab(2);
                  },
                ),
                const SizedBox(width: 8),
                _buildQuickActionButton(
                  icon: Icons.point_of_sale_outlined,
                  label: 'New Sale',
                  onTap: () {
                    Navigator.of(context).push(
                      MaterialPageRoute(builder: (_) => const CreateSaleScreen()),
                    );
                  },
                ),
                const SizedBox(width: 8),
                _buildQuickActionButton(
                  icon: Icons.inventory_2_outlined,
                  label: 'Stock Alerts',
                  onTap: () {
                    FranchiseTabScope.of(context)?.onSwitchTab(3);
                  },
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildQuickActionButton({
    required IconData icon,
    required String label,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
        decoration: BoxDecoration(
          color: Colors.white.withAlpha(28),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: Colors.white.withAlpha(40)),
        ),
        child: Row(
          children: [
            Icon(icon, color: Colors.white, size: 16),
            const SizedBox(width: 6),
            Text(
              label,
              style: const TextStyle(
                color: Colors.white,
                fontWeight: FontWeight.w600,
                fontSize: 12,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatsGrid(BuildContext context) {
    final pendingCount = context
        .read<OrderProvider>()
        .orders
        .where((o) => o.statusEnum.name == 'pending')
        .length;
    final inventory = context.read<InventoryProvider>().items;
    final totalInventoryValue = inventory.fold(
      0.0,
      (sum, i) => sum + i.totalValue,
    );

    final stats = [
      StatCard(
        title: 'Pending Orders',
        value: '$pendingCount',
        icon: Icons.shopping_cart_outlined,
        iconColor: AppColors.warning,
      ),
      StatCard(
        title: 'Inventory Value',
        value: Formatters.currency(totalInventoryValue),
        icon: Icons.inventory_2_outlined,
        iconColor: AppColors.info,
      ),
      StatCard(
        title: 'Inventory Items',
        value: '${inventory.length}',
        icon: Icons.inventory,
        iconColor: AppColors.primaryGreen,
      ),
      StatCard(
        title: 'Low Stock',
        value:
            '${inventory.where((i) => i.isLowStock || i.isOutOfStock).length}',
        icon: Icons.warning_amber_outlined,
        iconColor: AppColors.error,
      ),
    ];

    if (Responsive.isDesktop(context)) {
      return Row(children: stats.map((s) => Expanded(child: s)).toList());
    }

    return GridView.count(
      crossAxisCount: Responsive.isTablet(context) ? 4 : 2,
      mainAxisSpacing: 12,
      crossAxisSpacing: 12,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      childAspectRatio: 1.6,
      children: stats,
    );
  }

  Widget _buildRecentOrders() {
    final orders = context.read<OrderProvider>().orders.take(5).toList();

    if (orders.isEmpty) {
      return const AppCard(
        child: Center(
          child: Padding(
            padding: EdgeInsets.all(24),
            child: Text(
              'No orders yet',
              style: TextStyle(color: AppColors.textSecondary),
            ),
          ),
        ),
      );
    }

    return Column(
      children: orders.map((order) {
        return AppCard(
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: AppColors.primaryGreen.withAlpha(26),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Icon(
                  Icons.shopping_bag_outlined,
                  color: AppColors.primaryGreen,
                  size: 20,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      order.orderNumber ?? order.id,
                      style: const TextStyle(
                        fontWeight: FontWeight.w600,
                        fontSize: 14,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      Formatters.date(order.createdAt),
                      style: const TextStyle(
                        fontSize: 12,
                        color: AppColors.textSecondary,
                      ),
                    ),
                  ],
                ),
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text(
                    Formatters.currency(order.totalAmount),
                    style: const TextStyle(
                      fontWeight: FontWeight.w600,
                      fontSize: 13,
                    ),
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

  Widget _buildInventoryAlerts() {
    final inventory = context
        .read<InventoryProvider>()
        .items
        .where((i) => i.isLowStock || i.isOutOfStock)
        .take(5)
        .toList();

    if (inventory.isEmpty) {
      return const AppCard(
        child: Center(
          child: Padding(
            padding: EdgeInsets.all(24),
            child: Text(
              'No inventory alerts',
              style: TextStyle(color: AppColors.success),
            ),
          ),
        ),
      );
    }

    return AppCard(
      child: Column(
        children: inventory.asMap().entries.map((entry) {
          final item = entry.value;
          final detail = item.isOutOfStock
              ? 'Out of stock'
              : '${Formatters.quantity(item.quantity)} remaining';
          return Column(
            children: [
              _buildAlertItem(item.productName, detail, item.alertLevel),
              if (entry.key < inventory.length - 1) const Divider(height: 1),
            ],
          );
        }).toList(),
      ),
    );
  }

  Widget _buildAlertItem(
    String name,
    String detail,
    InventoryAlertLevel level,
  ) {
    Color color;
    IconData icon;
    switch (level) {
      case InventoryAlertLevel.outOfStock:
      case InventoryAlertLevel.critical:
        color = AppColors.error;
        icon = Icons.error_outline;
        break;
      case InventoryAlertLevel.low:
        color = AppColors.warning;
        icon = Icons.warning_amber_outlined;
        break;
      default:
        color = AppColors.success;
        icon = Icons.check_circle_outline;
    }

    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 10),
      child: Row(
        children: [
          Icon(icon, color: color, size: 20),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  name,
                  style: const TextStyle(
                    fontWeight: FontWeight.w600,
                    fontSize: 13,
                  ),
                ),
                Text(detail, style: TextStyle(fontSize: 12, color: color)),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
