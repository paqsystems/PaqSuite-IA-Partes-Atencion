import 'package:flutter/material.dart';

import '../kardex/kardex_row.dart';
import '../menu/menu_models.dart';
import '../menu/partes_mobile_menu_policy.dart';

/// Textos e íconos de los tres procesos mobile v1 (sin rutas en UI).
class PartesV1ProcessCatalog {
  static const Map<String, _ProcessMeta> _metaByRoute = {
    '/partes/carga': _ProcessMeta(
      title: 'Carga individual',
      summary: 'Ver y cargar tareas del día.',
      icon: Icons.edit_note_outlined,
    ),
    '/partes/informes/consulta-detallada': _ProcessMeta(
      title: 'Consulta detallada',
      summary: 'Tareas por período en tarjetas; abrí una fila para el detalle.',
      icon: Icons.view_list_outlined,
    ),
    '/partes/informes/paquete-horas': _ProcessMeta(
      title: 'Paquete de horas',
      summary: 'Saldo del paquete y desglose por período.',
      icon: Icons.schedule_outlined,
    ),
  };

  static List<MenuNode> collectOrderedV1Processes(List<MenuNode> tree) {
    final byRoute = <String, MenuNode>{};
    void walk(List<MenuNode> nodes) {
      for (final node in nodes) {
        final route = node.route;
        if (node.isLeaf &&
            route != null &&
            PartesMobileMenuPolicy.isV1ProcessImplemented(route)) {
          final normalized = PartesMobileMenuPolicy.normalizeRoute(route);
          byRoute.putIfAbsent(
            normalized,
            () => MenuNode(
              menuKey: node.menuKey,
              title: displayTitle(node, normalized),
              route: normalized,
              children: const [],
            ),
          );
        }
        walk(node.children);
      }
    }

    walk(tree);
    final ordered = <MenuNode>[];
    for (final route in PartesMobileMenuPolicy.v1ProcessRoutes) {
      final node = byRoute[route];
      if (node != null) ordered.add(node);
    }
    return ordered;
  }

  static String displayTitle(MenuNode node, String normalizedRoute) {
    return _metaByRoute[normalizedRoute]?.title ??
        _humanizeTitle(node.title);
  }

  static KardexRow kardexRow(MenuNode node) {
    final route = PartesMobileMenuPolicy.normalizeRoute(node.route ?? '');
    final meta = _metaByRoute[route];
    return KardexRow(
      id: route,
      primary: meta?.title ?? displayTitle(node, route),
      summary: meta?.summary ?? '',
    );
  }

  static IconData iconForRoute(String? route) {
    if (route == null) return Icons.apps_outlined;
    final normalized = PartesMobileMenuPolicy.normalizeRoute(route);
    return _metaByRoute[normalized]?.icon ?? Icons.chevron_right;
  }

  static String _humanizeTitle(String raw) {
    final t = raw.trim();
    if (t.isEmpty) return 'Proceso';
    if (t.startsWith('menu.')) {
      final parts = t.split('.');
      final last = parts.isNotEmpty ? parts.last : t;
      return last.replaceAll('_', ' ');
    }
    return t;
  }
}

class _ProcessMeta {
  const _ProcessMeta({
    required this.title,
    required this.summary,
    required this.icon,
  });

  final String title;
  final String summary;
  final IconData icon;
}