import 'package:flutter/material.dart';

import 'app/partes_app.dart';
import 'core/storage/flutter_secure_store_adapter.dart';
import 'core/storage/secure_store.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(PartesApp(secureStore: FlutterSecureStoreAdapter()));
}

/// Bootstrap para tests (`PartesApp` con store en memoria).
class PartesMobileBootstrap {
  static Widget appForTests({required SecureStore secureStore}) {
    return PartesApp(secureStore: secureStore);
  }
}