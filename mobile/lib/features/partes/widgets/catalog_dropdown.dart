import 'package:flutter/material.dart';

int? catalogItemId(Map<String, dynamic> item) {
  final id = item['id'];
  if (id is int) return id;
  if (id is num) return id.toInt();
  return int.tryParse('$id');
}

Set<int> catalogItemIds(List<Map<String, dynamic>> items) {
  return items.map(catalogItemId).whereType<int>().toSet();
}

/// Evita assert de Dropdown cuando `value` no está en [items].
int? resolveDropdownIntValue(List<Map<String, dynamic>> items, int? value) {
  if (value == null || items.isEmpty) return null;
  return catalogItemIds(items).contains(value) ? value : null;
}

List<DropdownMenuItem<int>> buildIntDropdownItems(
  List<Map<String, dynamic>> items,
  String Function(Map<String, dynamic> item) label,
) {
  final seen = <int>{};
  final result = <DropdownMenuItem<int>>[];
  for (final item in items) {
    final id = catalogItemId(item);
    if (id == null || !seen.add(id)) continue;
    result.add(
      DropdownMenuItem<int>(
        value: id,
        child: Text(
          label(item),
          overflow: TextOverflow.ellipsis,
        ),
      ),
    );
  }
  return result;
}