import 'package:flutter/material.dart';

import '../../core/api/envelope_message.dart';
import '../../core/api/session_api_client.dart';
import '../../core/auth/auth_service.dart';
import '../../core/config/config_service.dart';
import '../carga/carga_page.dart';
import '../consulta_detallada/consulta_detallada_page.dart';
import '../partes/models/partes_user_profile.dart';
import '../partes/widgets/partes_api_loader.dart';
import '../menu/menu_models.dart';
import '../menu/menu_service.dart';
import '../menu/partes_mobile_menu_policy.dart';
import '../paquete_horas/paquete_horas_page.dart';
import 'home_process_kardex.dart';
import 'partes_v1_process_catalog.dart';

class HomePage extends StatefulWidget {
  const HomePage({
    super.key,
    required this.session,
    required this.configService,
    required this.onLogout,
    required this.onOpenConfig,
  });

  final AuthSession session;
  final ConfigService configService;
  final Future<void> Function() onLogout;
  final VoidCallback onOpenConfig;

  @override
  State<HomePage> createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  List<MenuNode> _menuItems = [];
  String? _menuError;
  bool _menuLoading = true;
  PartesUserProfile? _partesProfile;

  @override
  void initState() {
    super.initState();
    _loadMenu();
    _loadPartesProfile();
  }

  Future<void> _loadPartesProfile() async {
    try {
      final api = await loadPartesApi(
        configService: widget.configService,
        session: widget.session,
      );
      if (api == null) return;
      final profile = await api.fetchMeProfile();
      if (!mounted) return;
      setState(() => _partesProfile = profile);
    } catch (_) {
      // Perfil Partes opcional en scaffold
    }
  }

  Future<void> _loadMenu() async {
    setState(() {
      _menuLoading = true;
      _menuError = null;
    });
    try {
      final client = await buildSessionApiClient(
        configService: widget.configService,
        session: widget.session,
      );
      if (client == null) {
        throw StateError('Configurá la URL de la API');
      }
      final menuService = MenuService(apiClient: client);
      final items = await menuService.fetchAuthorizedMenu();
      if (!mounted) return;
      var filtered = PartesMobileMenuPolicy.filterMenuTree(items);
      if (!_hasProcessLeaves(filtered)) {
        filtered = PartesMobileMenuPolicy.fallbackV1Menu();
      }
      setState(() => _menuItems = filtered);
    } catch (error) {
      if (!mounted) return;
      setState(() {
        _menuError = resolveCaughtErrorMessage(error);
        _menuItems = PartesMobileMenuPolicy.fallbackV1Menu();
      });
    } finally {
      if (mounted) setState(() => _menuLoading = false);
    }
  }

  bool _hasProcessLeaves(List<MenuNode> nodes) {
    for (final node in nodes) {
      if (node.isLeaf &&
          PartesMobileMenuPolicy.isV1ProcessImplemented(node.route)) {
        return true;
      }
      if (_hasProcessLeaves(node.children)) return true;
    }
    return false;
  }

  void _onMenuRouteSelected(String route) {
    final normalized = PartesMobileMenuPolicy.normalizeRoute(route);
    if (!PartesMobileMenuPolicy.isV1ProcessImplemented(normalized)) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          key: const Key('menuRouteNotImplemented'),
          content: Text('Ruta no disponible en mobile v1: $normalized'),
        ),
      );
      return;
    }

    final args = (
      configService: widget.configService,
      session: widget.session,
      profile: _partesProfile,
    );

    late final Widget page;
    switch (normalized) {
      case '/partes/carga':
        page = CargaPage(
          configService: args.configService,
          session: args.session,
          profile: args.profile,
        );
      case '/partes/informes/consulta-detallada':
        page = ConsultaDetalladaPage(
          configService: args.configService,
          session: args.session,
          profile: args.profile,
        );
      case '/partes/informes/paquete-horas':
        page = PaqueteHorasPage(
          configService: args.configService,
          session: args.session,
          profile: args.profile,
        );
      default:
        return;
    }

    Navigator.of(context).push<void>(
      MaterialPageRoute(builder: (context) => page),
    );
  }

  List<MenuNode> get _v1Processes =>
      PartesV1ProcessCatalog.collectOrderedV1Processes(_menuItems);

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Partes-Atención'),
        actions: [
          if (_menuLoading)
            const Padding(
              padding: EdgeInsets.all(12),
              child: SizedBox(
                width: 20,
                height: 20,
                child: CircularProgressIndicator(strokeWidth: 2),
              ),
            ),
          IconButton(
            key: const Key('homeMenuRefresh'),
            tooltip: 'Actualizar menú',
            icon: const Icon(Icons.refresh),
            onPressed: _menuLoading ? null : _loadMenu,
          ),
          IconButton(
            key: const Key('mobileConfigOpen'),
            icon: const Icon(Icons.settings),
            onPressed: widget.onOpenConfig,
          ),
          PopupMenuButton<String>(
            key: const Key('homeOverflowMenu'),
            onSelected: (value) {
              if (value == 'logout') {
                void onLogout() => widget.onLogout();
                onLogout();
              }
            },
            itemBuilder: (context) => [
              const PopupMenuItem(
                value: 'logout',
                child: Text('Cerrar sesión'),
              ),
            ],
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _loadMenu,
        child: Padding(
          padding: const EdgeInsets.fromLTRB(16, 8, 16, 16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Instalación · ${widget.session.tenantCode}',
                key: const Key('homeTenant'),
                style: theme.textTheme.titleMedium,
              ),
              const SizedBox(height: 8),
              Text(
                'Elegí un proceso',
                style: theme.textTheme.labelLarge?.copyWith(
                  color: theme.colorScheme.primary,
                ),
              ),
              if (_menuError != null) ...[
                const SizedBox(height: 8),
                Material(
                  color: theme.colorScheme.errorContainer,
                  borderRadius: BorderRadius.circular(8),
                  child: Padding(
                    padding: const EdgeInsets.all(12),
                    child: Row(
                      children: [
                        Icon(
                          Icons.cloud_off_outlined,
                          color: theme.colorScheme.onErrorContainer,
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            'Menú desde servidor no disponible; mostrando catálogo v1.',
                            key: const Key('menuDrawerError'),
                            style: TextStyle(
                              color: theme.colorScheme.onErrorContainer,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
              const SizedBox(height: 12),
              Expanded(
                child: HomeProcessKardex(
                  processes: _v1Processes,
                  onProcessTap: _onMenuRouteSelected,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}