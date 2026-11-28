import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';

import '../core/auth/auth_service.dart';
import '../core/auth/session_scope.dart';
import '../core/config/config_service.dart';
import '../core/storage/secure_store.dart';
import '../features/config/config_page.dart';
import '../features/home/home_page.dart';
import '../features/login/login_page.dart';

class PartesApp extends StatefulWidget {
  const PartesApp({super.key, required this.secureStore});

  final SecureStore secureStore;

  @override
  State<PartesApp> createState() => _PartesAppState();
}

class _PartesAppState extends State<PartesApp> {
  final GlobalKey<NavigatorState> _navigatorKey = GlobalKey<NavigatorState>();
  late final ConfigService _configService;
  late final AuthService _authService;
  AuthSession? _session;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _configService = ConfigService(secureStore: widget.secureStore);
    _authService = AuthService(secureStore: widget.secureStore);
    _bootstrap();
  }

  Future<void> _bootstrap() async {
    final session = await _authService.loadSession();
    if (!mounted) return;
    setState(() {
      _session = session;
      _loading = false;
    });
  }

  Future<void> _onLoggedIn(AuthSession session) async {
    setState(() => _session = session);
  }

  Future<void> _onLogout() async {
    await _authService.logout();
    setState(() => _session = null);
  }

  void _openConfig({String? tenantCode}) {
    final tenant = tenantCode ?? _session?.tenantCode;
    _navigatorKey.currentState?.push<void>(
      MaterialPageRoute(
        builder: (context) => ConfigPage(
          configService: _configService,
          tenantCodeForHealth: tenant,
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      navigatorKey: _navigatorKey,
      title: 'Partes-Atención Mobile',
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: const Color(0xFF1565C0)),
        useMaterial3: true,
      ),
      localizationsDelegates: GlobalMaterialLocalizations.delegates,
      supportedLocales: const [
        Locale('es', 'AR'),
        Locale('en'),
      ],
      locale: const Locale('es', 'AR'),
      home: _loading
          ? const Scaffold(body: Center(child: CircularProgressIndicator()))
          : SessionScope(
              session: _session,
              child: _session == null
                  ? LoginPage(
                      configService: _configService,
                      authService: _authService,
                      onLoggedIn: _onLoggedIn,
                      onOpenConfig: ({String? tenantCode}) =>
                          _openConfig(tenantCode: tenantCode),
                    )
                  : HomePage(
                      session: _session!,
                      configService: _configService,
                      onLogout: _onLogout,
                      onOpenConfig: () => _openConfig(),
                    ),
            ),
    );
  }
}