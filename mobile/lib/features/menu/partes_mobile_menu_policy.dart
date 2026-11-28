import 'menu_models.dart';

/// Paridad con `frontend/src/features/partes/mobile/partesMobilePolicy.ts`.
class PartesMobileMenuPolicy {
  static const v1ProcessRoutes = <String>[
    '/partes/carga',
    '/partes/informes/consulta-detallada',
    '/partes/informes/paquete-horas',
  ];

  static const v1AllowlistRoutes = <String>[
    '/partes/carga',
    '/partes/informes/consulta-detallada',
    '/partes/informes/paquete-horas',
    '/change-password',
    '/select-empresa',
  ];

  static const excludedRoutePrefixes = <String>[
    '/admin',
    '/pivots',
    '/excel-import',
  ];

  static const partesWebOnlyPatterns = <String>[
    '/archivos/partes',
    '/archivos/partes/',
    '/partes/proceso-masivo',
    '/partes/carga-diaria',
    '/partes/carga-paquetes',
    '/partes/informes/consultas-agrupadas',
    '/emisiones',
    '/parametros',
    '/tareas-programadas',
  ];

  static String normalizeRoute(String route) {
    var r = route.trim();
    if (r.isEmpty) return r;
    while (r.length > 1 && r.endsWith('/')) {
      r = r.substring(0, r.length - 1);
    }
    return r;
  }

  /// Paridad `frontend/.../mapNativeMenuRoute.ts` (menú SQL: `/partes/carga-diaria`).
  static String mapNativeMenuRoute(String? route) {
    if (route == null || route.trim().isEmpty) return '';
    final r = normalizeRoute(route);
    if (r == '/partes/carga-diaria' || r.startsWith('/partes/carga-diaria/')) {
      return '/partes/carga';
    }
    return r;
  }

  static MenuNode _remapMenuNode(MenuNode node) {
    return MenuNode(
      menuKey: node.menuKey,
      title: node.title,
      route: node.route != null ? mapNativeMenuRoute(node.route) : null,
      children: node.children.map(_remapMenuNode).toList(),
    );
  }

  static List<MenuNode> _remapMenuTree(List<MenuNode> nodes) {
    return nodes.map(_remapMenuNode).toList();
  }

  static bool isGenExcluded(String? route) {
    if (route == null || route.trim().isEmpty) return false;
    final normalized = normalizeRoute(route).toLowerCase();
    for (final prefix in excludedRoutePrefixes) {
      if (normalized.startsWith(prefix)) return true;
    }
    return false;
  }

  static bool isPartesWebOnlyExcluded(String? route) {
    if (route == null || route.trim().isEmpty) return false;
    final normalized = normalizeRoute(route).toLowerCase();
    for (final pattern in partesWebOnlyPatterns) {
      final p = pattern.toLowerCase();
      if (p.endsWith('/')) {
        if (normalized.startsWith(p)) return true;
      } else if (normalized == p || normalized.startsWith('$p/')) {
        return true;
      }
    }
    return false;
  }

  static bool isRouteExcludedForMobile(String? route) {
    return isGenExcluded(route) || isPartesWebOnlyExcluded(route);
  }

  static bool isRouteAllowed(String? route) {
    if (route == null || route.trim().isEmpty) return false;
    return v1AllowlistRoutes.contains(normalizeRoute(route));
  }

  static bool isV1ProcessImplemented(String? route) {
    if (route == null) return false;
    return v1ProcessRoutes.contains(normalizeRoute(route));
  }

  static List<MenuNode> filterMenuTree(List<MenuNode> nodes) {
    final remapped = _remapMenuTree(nodes);
    final afterExclusions = _filterExclusions(remapped);
    return _filterAllowlistLeaves(afterExclusions);
  }

  static List<MenuNode> fallbackV1Menu() {
    return [
      MenuNode(
        menuKey: 'partes_carga',
        title: 'Carga individual',
        route: '/partes/carga',
        children: const [],
      ),
      MenuNode(
        menuKey: 'partes_consulta_detallada',
        title: 'Consulta detallada',
        route: '/partes/informes/consulta-detallada',
        children: const [],
      ),
      MenuNode(
        menuKey: 'partes_paquete_horas',
        title: 'Paquete de horas',
        route: '/partes/informes/paquete-horas',
        children: const [],
      ),
    ];
  }

  static bool _hasAllowedLeaf(MenuNode node) {
    if (node.isLeaf) {
      return isRouteAllowed(node.route);
    }
    return node.children.any(_hasAllowedLeaf);
  }

  static List<MenuNode> _filterExclusions(List<MenuNode> nodes) {
    final filtered = <MenuNode>[];
    for (final node in nodes) {
      final children = _filterExclusions(node.children);
      final routeExcluded = isRouteExcludedForMobile(node.route);
      if (routeExcluded && children.isEmpty) {
        continue;
      }
      if (routeExcluded && children.isNotEmpty) {
        filtered.addAll(children);
        continue;
      }
      filtered.add(
        MenuNode(
          menuKey: node.menuKey,
          title: node.title,
          route: node.route,
          children: children,
        ),
      );
    }
    return filtered;
  }

  static List<MenuNode> _filterAllowlistLeaves(List<MenuNode> nodes) {
    final filtered = <MenuNode>[];
    for (final node in nodes) {
      final children = _filterAllowlistLeaves(node.children);
      if (node.isLeaf) {
        if (isRouteAllowed(node.route)) {
          filtered.add(
            MenuNode(
              menuKey: node.menuKey,
              title: node.title,
              route: node.route,
              children: const [],
            ),
          );
        }
        continue;
      }
      if (children.isEmpty && !_hasAllowedLeaf(node)) {
        continue;
      }
      filtered.add(
        MenuNode(
          menuKey: node.menuKey,
          title: node.title,
          route: node.route,
          children: children,
        ),
      );
    }
    return filtered;
  }
}