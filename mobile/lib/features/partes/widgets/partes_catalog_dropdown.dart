import 'package:flutter/material.dart';

int? catalogItemId(Map<String, dynamic> item) {
  final id = item['id'];
  if (id is int) return id;
  if (id is num) return id.toInt();
  return int.tryParse('$id');
}

String catalogItemLabel(
  Map<String, dynamic> item, {
  String descriptionKey = 'nombre',
}) {
  final code = item['code']?.toString() ?? '';
  final desc = item[descriptionKey] ?? item['descripcion'] ?? item['nombre'] ?? '';
  final text = '$code — $desc'.trim();
  if (text == '—' || text.isEmpty) {
    return desc.toString().isNotEmpty ? desc.toString() : '—';
  }
  return text;
}

class PartesCatalogDropdown extends StatelessWidget {
  const PartesCatalogDropdown({
    super.key,
    required this.label,
    required this.items,
    required this.value,
    required this.onChanged,
    this.hint,
    this.enabled = true,
  });

  final String label;
  final List<Map<String, dynamic>> items;
  final int? value;
  final ValueChanged<int?> onChanged;
  final String? hint;
  final bool enabled;

  @override
  Widget build(BuildContext context) {
    return DropdownButtonFormField<int>(
      value: value,
      decoration: InputDecoration(labelText: label),
      isExpanded: true,
      hint: hint != null ? Text(hint!) : null,
      items: items
          .map(
            (item) => DropdownMenuItem<int>(
              value: catalogItemId(item),
              child: Text(
                catalogItemLabel(item),
                overflow: TextOverflow.ellipsis,
              ),
            ),
          )
          .toList(),
      onChanged: enabled ? onChanged : null,
    );
  }
}