import 'package:flutter/material.dart';

import 'kardex_card.dart';
import 'kardex_row.dart';

/// Lista vertical kardex (scaffold). Datos demo hasta conectar API de consulta.
class KardexPlaceholderPage extends StatelessWidget {
  const KardexPlaceholderPage({
    super.key,
    required this.title,
    required this.demoRows,
  });

  final String title;
  final List<KardexRow> demoRows;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(title)),
      body: ListView.separated(
        padding: const EdgeInsets.all(12),
        itemCount: demoRows.length,
        separatorBuilder: (_, __) => const SizedBox(height: 8),
        itemBuilder: (context, index) {
          final row = demoRows[index];
          return KardexCard(
            row: row,
            onTap: () {
              showModalBottomSheet<void>(
                context: context,
                showDragHandle: true,
                builder: (context) => Padding(
                  padding: const EdgeInsets.all(16),
                  child: Text(
                    'Detalle (scaffold): ${row.primary}',
                    key: Key('kardexDetail-${row.id}'),
                  ),
                ),
              );
            },
          );
        },
      ),
    );
  }
}