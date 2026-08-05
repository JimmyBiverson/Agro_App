import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../../core/theme/app_colors.dart';
import '../../../models/notification.dart';
import '../../../providers/notification_provider.dart';
import '../../../widgets/common/app_card.dart';
import '../../../widgets/common/error_view.dart';
import '../../../widgets/common/loading_view.dart';

class NotificationScreen extends StatefulWidget {
  const NotificationScreen({super.key});

  @override
  State<NotificationScreen> createState() => _NotificationScreenState();
}

class _NotificationScreenState extends State<NotificationScreen> {
  bool _showUnreadOnly = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<NotificationProvider>().loadNotifications();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Notifications'),
        actions: [
          TextButton(
            onPressed: () {
              context.read<NotificationProvider>().markAllAsRead();
            },
            child: const Text('Mark all read'),
          ),
          PopupMenuButton<bool>(
            onSelected: (value) {
              setState(() => _showUnreadOnly = value);
            },
            itemBuilder: (context) => [
              CheckedPopupMenuItem(
                value: false,
                checked: !_showUnreadOnly,
                child: Text('All'),
              ),
              CheckedPopupMenuItem(
                value: true,
                checked: _showUnreadOnly,
                child: Text('Unread'),
              ),
            ],
          ),
        ],
      ),
      body: Consumer<NotificationProvider>(
        builder: (context, provider, _) {
          if (provider.isLoading) {
            return const LoadingView();
          }

          if (provider.error != null) {
            return ErrorView(
              message: provider.error!,
              onRetry: () => provider.loadNotifications(),
            );
          }

          final notifications = _showUnreadOnly
              ? provider.notifications.where((n) => !n.isRead).toList()
              : provider.notifications;

          if (notifications.isEmpty) {
            return EmptyView(
              message: _showUnreadOnly
                  ? 'No unread notifications'
                  : 'No notifications yet',
              icon: Icons.notifications_none,
            );
          }

          return RefreshIndicator(
            onRefresh: () => provider.loadNotifications(),
            child: ListView.builder(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              itemCount: notifications.length,
              itemBuilder: (context, index) {
                return _buildNotificationCard(notifications[index]);
              },
            ),
          );
        },
      ),
    );
  }

  Widget _buildNotificationCard(NotificationItem notification) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: AppCard(
        onTap: () {
          context.read<NotificationProvider>().markAsRead(notification.id);
          _navigateToReference(notification);
        },
        color: notification.isRead
            ? null
            : AppColors.primaryGreen.withValues(alpha: 0.3),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _buildNotificationIcon(notification.type),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Expanded(
                          child: Text(
                            notification.title,
                            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                              fontWeight: notification.isRead
                                  ? FontWeight.normal
                                  : FontWeight.bold,
                            ),
                          ),
                        ),
                        if (!notification.isRead)
                          Container(
                            width: 8,
                            height: 8,
                            decoration: const BoxDecoration(
                              color: AppColors.primaryGreen,
                              shape: BoxShape.circle,
                            ),
                          ),
                      ],
                    ),
                    const SizedBox(height: 4),
                    Text(
                      notification.message,
                      style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                        color: AppColors.textSecondary,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 8),
                    Text(
                      _timeAgo(notification.createdAt),
                      style: Theme.of(context).textTheme.bodySmall?.copyWith(
                        color: AppColors.textLight,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildNotificationIcon(String type) {
    IconData icon;
    Color color;

    switch (type) {
      case 'order':
        icon = Icons.shopping_bag;
        color = AppColors.primaryGreen;
        break;
      case 'payment':
        icon = Icons.wallet;
        color = AppColors.success;
        break;
      case 'inventory':
        icon = Icons.inventory;
        color = AppColors.warning;
        break;
      case 'system':
      default:
        icon = Icons.info;
        color = AppColors.info;
        break;
    }

    return Container(
      width: 40,
      height: 40,
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Icon(icon, color: color, size: 20),
    );
  }

  String _timeAgo(DateTime dateTime) {
    final difference = DateTime.now().difference(dateTime);
    if (difference.inSeconds < 60) return 'Just now';
    if (difference.inMinutes < 60) return '${difference.inMinutes}m ago';
    if (difference.inHours < 24) return '${difference.inHours}h ago';
    if (difference.inDays < 7) return '${difference.inDays}d ago';
    return '${(difference.inDays / 7).floor()}w ago';
  }

  void _navigateToReference(NotificationItem notification) {
    if (notification.referenceId == null || notification.referenceType == null) {
      return;
    }

    switch (notification.referenceType) {
      case 'order':
        Navigator.pushNamed(
          context,
          '/staff/orders/detail',
          arguments: notification.referenceId,
        );
        break;
      case 'payment':
        Navigator.pushNamed(
          context,
          '/payments/detail',
          arguments: notification.referenceId,
        );
        break;
      default:
        break;
    }
  }
}
