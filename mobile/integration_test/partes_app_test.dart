import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:integration_test/integration_test.dart';
import 'package:partes_atencion_mobile/app/partes_app.dart';
import 'package:partes_atencion_mobile/core/storage/in_memory_secure_store.dart';

void main() {
  IntegrationTestWidgetsFlutterBinding.ensureInitialized();

  testWidgets('arranca en login tenant-first', (tester) async {
    await tester.pumpWidget(PartesApp(secureStore: InMemorySecureStore()));
    await tester.pumpAndSettle();

    expect(find.byKey(const Key('loginTenant')), findsOneWidget);
    expect(find.text('Login — Partes'), findsOneWidget);
  });
}