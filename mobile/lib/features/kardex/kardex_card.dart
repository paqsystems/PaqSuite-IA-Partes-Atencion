import 'package:flutter/material.dart';

import 'kardex_row.dart';

class KardexCard extends StatelessWidget {
  const KardexCard({
    super.key,
    required this.row,
    required this.onTap,
  });

  final KardexRow row;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Card(
      key: Key('kardexCard-${row.id}'),
      child: InkWell(
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                row.primary,
                style: Theme.of(context).textTheme.titleMedium,
              ),
              const SizedBox(height: 4),
              Text(row.summary),
              if (row.statusLabel != null) ...[
                const SizedBox(height: 8),
                Chip(
                  label: Text(row.statusLabel!),
                  visualDensity: VisualDensity.compact,
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}