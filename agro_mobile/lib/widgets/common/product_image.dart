import 'package:flutter/material.dart';
import '../../core/theme/app_colors.dart';

class ProductImage extends StatelessWidget {
  final String? imageUrl;
  final String? productName;
  final double? width;
  final double? height;
  final BoxFit fit;
  final double borderRadius;
  final Color? backgroundColor;

  const ProductImage({
    super.key,
    this.imageUrl,
    this.productName,
    this.width,
    this.height,
    this.fit = BoxFit.contain,
    this.borderRadius = 0,
    this.backgroundColor,
  });

  @override
  Widget build(BuildContext context) {
    final url = imageUrl;
    final hasImage = url != null && url.isNotEmpty;

    final fallback = Container(
      color: AppColors.primaryGreen.withAlpha(26),
      child: Center(
        child: Icon(
          Icons.science_outlined,
          size: (height ?? 48) * 0.4,
          color: AppColors.primaryGreen.withAlpha(140),
        ),
      ),
    );

    Widget child;
    if (!hasImage) {
      child = fallback;
    } else {
      child = Image.network(
        url,
        width: width,
        height: height,
        fit: fit,
        errorBuilder: (context, error, stackTrace) => fallback,
        loadingBuilder: (context, child, loadingProgress) {
          if (loadingProgress == null) return child;
          return Container(
            color: AppColors.backgroundLight,
            child: Center(
              child: SizedBox(
                width: (height ?? 48) * 0.3,
                height: (height ?? 48) * 0.3,
                child: CircularProgressIndicator(
                  strokeWidth: 2,
                  color: AppColors.primaryGreen.withAlpha(120),
                ),
              ),
            ),
          );
        },
      );
    }

    if (backgroundColor != null) {
      child = Container(color: backgroundColor, child: child);
    }

    if (borderRadius > 0) {
      return ClipRRect(
        borderRadius: BorderRadius.circular(borderRadius),
        child: child,
      );
    }
    return child;
  }
}
