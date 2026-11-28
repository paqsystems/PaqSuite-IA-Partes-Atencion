class PaqueteHorasDesgloseItem {
  const PaqueteHorasDesgloseItem({
    required this.id,
    required this.title,
    required this.totalMinutos,
    required this.cantidad,
  });

  final String id;
  final String title;
  final int totalMinutos;
  final int cantidad;
}

int _signedMinutos(bool esTarea, int duracionMinutos) {
  return esTarea ? duracionMinutos : -duracionMinutos;
}

List<PaqueteHorasDesgloseItem> aggregatePaqueteHorasDesglose(
  List<Map<String, dynamic>> rows,
  String eje,
) {
  final map = <String, PaqueteHorasDesgloseItem>{};
  for (final row in rows) {
    if (row['esSaldoInicial'] == true) continue;
    final code = eje == 'cliente'
        ? '${row['clienteCode'] ?? ''}'
        : '${row['tipoTareaCode'] ?? ''}';
    final nombre = eje == 'cliente'
        ? '${row['clienteNombre'] ?? ''}'
        : '${row['tipoTareaDescripcion'] ?? ''}';
    final key = code.isNotEmpty
        ? code
        : '${row['clienteId'] ?? row['tipoTareaId'] ?? ''}';
    if (key.isEmpty) continue;
    final esTarea = row['esTarea'] != false;
    final minutos = _signedMinutos(esTarea, (row['duracionMinutos'] as num?)?.toInt() ?? 0);
    final current = map[key] ??
        PaqueteHorasDesgloseItem(
          id: key,
          title: nombre.isNotEmpty ? '$code — $nombre' : code,
          totalMinutos: 0,
          cantidad: 0,
        );
    map[key] = PaqueteHorasDesgloseItem(
      id: current.id,
      title: current.title,
      totalMinutos: current.totalMinutos + minutos,
      cantidad: current.cantidad + (esTarea ? 1 : 0),
    );
  }
  final list = map.values.toList()
    ..sort((a, b) => b.totalMinutos.compareTo(a.totalMinutos));
  return list;
}