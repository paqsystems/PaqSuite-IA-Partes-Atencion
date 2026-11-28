import 'package:flutter/material.dart';

import '../kardex/kardex_card.dart';
import '../menu/menu_models.dart';
import 'partes_v1_process_catalog.dart';

/// Acceso rápido a procesos en la pantalla principal (patrón kardex).
class HomeProcessKardex extends StatelessWidget {
  const HomeProcessKardex({
    super.key,
    required this.processes,
    required this.onProcessTap,
  });

  final List<MenuNode> processes;
  final void Function(String route) onProcessTap;

  @override
  Widget build(BuildContext context) {
    if (processes.isEmpty) {
      return const Center(
        key: Key('homeProcessKardexEmpty'),
        child: Text('No hay procesos disponibles en mobile v1.'),
      );
    }

    return ListView.separated(
      key: const Key('homeProcessKardexList'),
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.only(bottom: 24),
      itemCount: processes.length,
      separatorBuilder: (_, __) => const SizedBox(height: 8),
      itemBuilder: (context, index) {
        final node = processes[index];
        final route = node.route ?? '';
        final row = PartesV1ProcessCatalog.kardexRow(node);
        final icon = PartesV1ProcessCatalog.iconForRoute(route);

        return Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: const EdgeInsets.only(top: 8, right: 8),
              child: CircleAvatar(
                radius: 22,
                backgroundColor: Theme.of(context).colorScheme.primaryContainer,
                child: Icon(
                  icon,
                  color: Theme.of(context).colorScheme.onPrimaryContainer,
                ),
              ),
            ),
            Expanded(
              child: KardexCard(
                row: row,
                onTap: () => onProcessTap(route),
              ),
            ),
          ],
        );
      },
    );
  }
}