import 'package:flutter_test/flutter_test.dart';
import 'package:partes_atencion_mobile/features/menu/menu_models.dart';
import 'package:partes_atencion_mobile/features/menu/partes_mobile_menu_policy.dart';

bool containsRoute(List<MenuNode> nodes, String route) {
  for (final node in nodes) {
    if (PartesMobileMenuPolicy.normalizeRoute(node.route ?? '') ==
        PartesMobileMenuPolicy.normalizeRoute(route)) {
      return true;
    }
    if (containsRoute(node.children, route)) return true;
  }
  return false;
}

void main() {
  test('allowlist v1 procesos', () {
    expect(PartesMobileMenuPolicy.isRouteAllowed('/partes/carga'), isTrue);
    expect(
      PartesMobileMenuPolicy.isRouteAllowed('/partes/informes/consulta-detallada'),
      isTrue,
    );
    expect(
      PartesMobileMenuPolicy.isRouteAllowed('/partes/carga-diaria'),
      isFalse,
    );
    expect(PartesMobileMenuPolicy.isRouteExcludedForMobile('/admin/usuarios'), isTrue);
  });

  test('mapNativeMenuRoute convierte carga-diaria a carga', () {
    expect(
      PartesMobileMenuPolicy.mapNativeMenuRoute('/partes/carga-diaria'),
      '/partes/carga',
    );
  });

  test('filterMenuTree deja solo rutas v1', () {
    final tree = [
      MenuNode(
        menuKey: 'partes',
        title: 'Partes',
        route: null,
        children: [
          MenuNode(
            menuKey: 'carga',
            title: 'Carga',
            route: '/partes/carga',
            children: [],
          ),
          MenuNode(
            menuKey: 'carga-diaria',
            title: 'Carga diaria',
            route: '/partes/carga-diaria',
            children: [],
          ),
          MenuNode(
            menuKey: 'admin',
            title: 'Admin',
            route: '/admin/usuarios',
            children: [],
          ),
        ],
      ),
    ];

    final filtered = PartesMobileMenuPolicy.filterMenuTree(tree);
    expect(containsRoute(filtered, '/partes/carga'), isTrue);
    expect(containsRoute(filtered, '/partes/carga-diaria'), isFalse);
    expect(containsRoute(filtered, '/admin/usuarios'), isFalse);
  });
}