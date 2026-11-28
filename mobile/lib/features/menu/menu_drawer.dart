import 'package:flutter/material.dart';

import 'menu_models.dart';

typedef MenuRouteSelected = void Function(String route);

class MenuDrawer extends StatelessWidget {
  const MenuDrawer({
    super.key,
    required this.items,
    required this.onRouteSelected,
    this.loadError,
  });

  final List<MenuNode> items;
  final MenuRouteSelected onRouteSelected;
  final String? loadError;

  @override
  Widget build(BuildContext context) {
    return Drawer(
      key: const Key('menuDrawer'),
      child: SafeArea(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const Padding(
              padding: EdgeInsets.all(16),
              child: Text(
                'Menú — Partes mobile v1',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.w600),
              ),
            ),
            if (loadError != null)
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                child: Text(
                  loadError!,
                  key: const Key('menuDrawerError'),
                  style: const TextStyle(color: Colors.red),
                ),
              ),
            Expanded(
              child: ListView(
                children: _buildNodes(context, items, 0),
              ),
            ),
          ],
        ),
      ),
    );
  }

  List<Widget> _buildNodes(BuildContext context, List<MenuNode> nodes, int depth) {
    final widgets = <Widget>[];
    for (final node in nodes) {
      if (node.children.isNotEmpty) {
        widgets.add(
          Padding(
            padding: EdgeInsets.only(left: 16.0 + depth * 12),
            child: Text(
              node.title,
              style: const TextStyle(fontWeight: FontWeight.bold),
            ),
          ),
        );
        widgets.addAll(_buildNodes(context, node.children, depth + 1));
        continue;
      }
      final route = node.route ?? '';
      widgets.add(
        ListTile(
          key: Key('menuItem-${node.menuKey}'),
          title: Text(node.title),
          subtitle: route.isNotEmpty ? Text(route) : null,
          onTap: route.isEmpty
              ? null
              : () {
                  Navigator.of(context).pop();
                  onRouteSelected(route);
                },
        ),
      );
    }
    return widgets;
  }
}