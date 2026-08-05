import 'package:flutter_test/flutter_test.dart';
import 'package:agro_app/core/utils/image_url.dart';

void main() {
  group('resolveImageUrl', () {
    test('returns absolute URLs unchanged', () {
      expect(
        resolveImageUrl('https://cdn.example.com/p.png'),
        'https://cdn.example.com/p.png',
      );
      expect(
        resolveImageUrl('http://localhost/x.png'),
        'http://localhost/x.png',
      );
    });

    test('builds full URL for relative storage paths', () {
      expect(
        resolveImageUrl('products/SEED-001.png'),
        'http://localhost/Agro_app/agro_web/public/storage/products/SEED-001.png',
      );
    });

    test('preserves leading-slash paths', () {
      expect(
        resolveImageUrl('/storage/avatars/a.png'),
        'http://localhost/Agro_app/agro_web/public/storage/avatars/a.png',
      );
    });

    test('handles null and empty values', () {
      expect(resolveImageUrl(null), '');
      expect(resolveImageUrl(''), '');
      expect(resolveImageUrl('   '), '');
    });
  });
}
