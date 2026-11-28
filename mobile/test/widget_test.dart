import 'package:flutter_test/flutter_test.dart';
import 'package:partes_atencion_mobile/main.dart';
import 'package:partes_atencion_mobile/core/storage/in_memory_secure_store.dart';

void main() {
  testWidgets('bootstrap muestra login', (tester) async {
    await tester.pumpWidget(
      PartesMobileBootstrap.appForTests(secureStore: InMemorySecureStore()),
    );
    await tester.pumpAndSettle();
    expect(find.text('Login — Partes'), findsOneWidget);
  });
}