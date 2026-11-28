import '../../core/api/envelope.dart';

class MenuNode {
  MenuNode({
    required this.menuKey,
    required this.title,
    required this.route,
    required this.children,
  });

  final String menuKey;
  final String title;
  final String? route;
  final List<MenuNode> children;

  bool get isLeaf => children.isEmpty;

  static MenuNode fromJson(Map<String, dynamic> json) {
    final rawChildren = json['children'];
    final children = rawChildren is List
        ? rawChildren
            .whereType<Map>()
            .map((c) => MenuNode.fromJson(Map<String, dynamic>.from(c)))
            .toList()
        : <MenuNode>[];

    return MenuNode(
      menuKey: json['menuKey']?.toString() ?? '',
      title: _resolveTitle(json),
      route: json['routeName']?.toString() ?? json['ruta']?.toString(),
      children: children,
    );
  }

  static String _resolveTitle(Map<String, dynamic> json) {
    return json['text']?.toString() ??
        json['titulo']?.toString() ??
        json['labelKey']?.toString() ??
        json['menuKey']?.toString() ??
        '';
  }
}

List<MenuNode> parseMenuItemsFromEnvelope(ApiEnvelope envelope) {
  if (!envelope.isSuccess) {
    throw FormatException(envelope.respuesta);
  }
  final rawItems = envelope.resultado['items'];
  if (rawItems is! List) {
    throw FormatException('resultado.items debe ser lista');
  }
  return rawItems
      .whereType<Map>()
      .map((item) => MenuNode.fromJson(Map<String, dynamic>.from(item)))
      .toList();
}