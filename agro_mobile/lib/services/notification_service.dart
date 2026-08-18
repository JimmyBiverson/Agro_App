import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

/// Plays a synthesized professional chime using a pure Dart tone via
/// HapticFeedback (no native plugin required). For richer audio, swap
/// AudioPlayer when the package is added to pubspec.yaml.
class NotificationService {
  static final NotificationService _instance = NotificationService._internal();
  factory NotificationService() => _instance;
  NotificationService._internal();

  OverlayEntry? _overlayEntry;
  final GlobalKey<_NotificationHostState> _hostKey = GlobalKey();

  void init() {}

  /// Shows an animated banner notification at the top of the screen.
  void show({
    required BuildContext context,
    required String title,
    required String message,
    NotificationStyle style = NotificationStyle.info,
    Duration duration = const Duration(seconds: 4),
    VoidCallback? onTap,
  }) {
    _playHaptic(style);

    final entry = _NotificationEntry(
      title: title,
      message: message,
      style: style,
      duration: duration,
      onTap: onTap,
    );

    // Insert or update the overlay
    if (_overlayEntry == null) {
      _overlayEntry = OverlayEntry(
        builder: (_) => _NotificationHost(key: _hostKey),
      );
      Overlay.of(context).insert(_overlayEntry!);
    }

    _hostKey.currentState?.addNotification(entry);
  }

  void _playHaptic(NotificationStyle style) {
    SystemSound.play(SystemSoundType.alert);
    switch (style) {
      case NotificationStyle.success:
        HapticFeedback.lightImpact();
        Future.delayed(
          const Duration(milliseconds: 120),
          () => HapticFeedback.lightImpact(),
        );
        break;
      case NotificationStyle.error:
        HapticFeedback.heavyImpact();
        break;
      default:
        HapticFeedback.selectionClick();
    }
  }
}

enum NotificationStyle { info, success, warning, error, order }

class _NotificationEntry {
  final String title;
  final String message;
  final NotificationStyle style;
  final Duration duration;
  final VoidCallback? onTap;

  _NotificationEntry({
    required this.title,
    required this.message,
    required this.style,
    required this.duration,
    this.onTap,
  });
}

class _NotificationHost extends StatefulWidget {
  const _NotificationHost({super.key});

  @override
  State<_NotificationHost> createState() => _NotificationHostState();
}

class _NotificationHostState extends State<_NotificationHost> {
  final List<_NotificationEntry> _active = [];

  void addNotification(_NotificationEntry entry) {
    if (!mounted) return;
    setState(() {
      _active.add(entry);
    });
    Future.delayed(entry.duration, () => _remove(entry));
  }

  void _remove(_NotificationEntry entry) {
    if (!mounted) return;
    setState(() {
      _active.remove(entry);
    });
  }

  @override
  Widget build(BuildContext context) {
    return Positioned(
      top: MediaQuery.of(context).padding.top + 8,
      left: 12,
      right: 12,
      child: Column(
        children: _active
            .map(
              (e) => _NotificationBanner(entry: e, onDismiss: () => _remove(e)),
            )
            .toList(),
      ),
    );
  }
}

class _NotificationBanner extends StatefulWidget {
  final _NotificationEntry entry;
  final VoidCallback onDismiss;

  const _NotificationBanner({required this.entry, required this.onDismiss});

  @override
  State<_NotificationBanner> createState() => _NotificationBannerState();
}

class _NotificationBannerState extends State<_NotificationBanner>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<Offset> _slide;
  late Animation<double> _fade;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 380),
    );
    _slide = Tween<Offset>(
      begin: const Offset(0, -1.2),
      end: Offset.zero,
    ).animate(CurvedAnimation(parent: _controller, curve: Curves.easeOutBack));
    _fade = CurvedAnimation(parent: _controller, curve: Curves.easeOut);
    _controller.forward();
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  Color get _backgroundColor {
    switch (widget.entry.style) {
      case NotificationStyle.success:
        return const Color(0xFF1B5E20);
      case NotificationStyle.error:
        return const Color(0xFFC62828);
      case NotificationStyle.warning:
        return const Color(0xFFE65100);
      case NotificationStyle.order:
        return const Color(0xFF0D47A1);
      default:
        return const Color(0xFF212121);
    }
  }

  IconData get _icon {
    switch (widget.entry.style) {
      case NotificationStyle.success:
        return Icons.check_circle_rounded;
      case NotificationStyle.error:
        return Icons.error_rounded;
      case NotificationStyle.warning:
        return Icons.warning_rounded;
      case NotificationStyle.order:
        return Icons.shopping_bag_rounded;
      default:
        return Icons.notifications_rounded;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: SlideTransition(
        position: _slide,
        child: FadeTransition(
          opacity: _fade,
          child: Material(
            elevation: 8,
            borderRadius: BorderRadius.circular(14),
            shadowColor: _backgroundColor.withAlpha(80),
            child: InkWell(
              onTap: () {
                widget.entry.onTap?.call();
                widget.onDismiss();
              },
              borderRadius: BorderRadius.circular(14),
              child: Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 12,
                ),
                decoration: BoxDecoration(
                  color: _backgroundColor,
                  borderRadius: BorderRadius.circular(14),
                  gradient: LinearGradient(
                    colors: [_backgroundColor, _backgroundColor.withAlpha(220)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                ),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: Colors.white.withAlpha(35),
                        shape: BoxShape.circle,
                      ),
                      child: Icon(_icon, color: Colors.white, size: 20),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            widget.entry.title,
                            style: const TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.w700,
                              fontSize: 13.5,
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            widget.entry.message,
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                            style: TextStyle(
                              color: Colors.white.withAlpha(210),
                              fontSize: 12,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 8),
                    GestureDetector(
                      onTap: widget.onDismiss,
                      child: Icon(
                        Icons.close_rounded,
                        color: Colors.white.withAlpha(160),
                        size: 18,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

/// A convenient global helper for showing notifications from anywhere in the app.
class AppNotify {
  static final NotificationService _svc = NotificationService();

  static void success(
    BuildContext context,
    String title,
    String message, {
    VoidCallback? onTap,
  }) => _svc.show(
    context: context,
    title: title,
    message: message,
    style: NotificationStyle.success,
    onTap: onTap,
  );

  static void error(BuildContext context, String title, String message) =>
      _svc.show(
        context: context,
        title: title,
        message: message,
        style: NotificationStyle.error,
        duration: const Duration(seconds: 6),
      );

  static void warning(BuildContext context, String title, String message) =>
      _svc.show(
        context: context,
        title: title,
        message: message,
        style: NotificationStyle.warning,
      );

  static void orderUpdate(
    BuildContext context,
    String message, {
    VoidCallback? onTap,
  }) => _svc.show(
    context: context,
    title: '📦 Order Update',
    message: message,
    style: NotificationStyle.order,
    onTap: onTap,
  );

  static void info(BuildContext context, String title, String message) =>
      _svc.show(context: context, title: title, message: message);
}
