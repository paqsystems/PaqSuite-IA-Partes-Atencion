import '../../kardex/kardex_row.dart';
import '../models/partes_tarea.dart';
import '../partes_duration.dart';
import '../partes_paquete_aggregate.dart';

KardexRow mapConsultaDetalladaRow(Map<String, dynamic> row) {
  final clienteCode = '${row['clienteCode'] ?? ''}';
  final clienteNombre = '${row['clienteNombre'] ?? ''}';
  final title = clienteNombre.isNotEmpty && clienteCode.isNotEmpty
      ? '$clienteCode — $clienteNombre'
      : clienteCode.isNotEmpty
          ? clienteCode
          : clienteNombre.isNotEmpty
              ? clienteNombre
              : '${row['id'] ?? ''}';
  final minutos = (row['duracionMinutos'] as num?)?.toInt() ?? 0;
  final fecha = '${row['fecha'] ?? ''}'.length >= 10
      ? '${row['fecha']}'.substring(0, 10)
      : '';
  final cerrado = row['cerrado'] == true;
  return KardexRow(
    id: '${row['id'] ?? '$clienteCode-$fecha-$minutos'}',
    primary: title,
    summary:
        '${row['tipoTareaDescripcion'] ?? row['tipoTareaCode'] ?? ''} · $fecha · ${formatMinutosAsHhMm(minutos)}',
    statusLabel: cerrado ? 'Cerrada' : 'Abierta',
  );
}

List<String> consultaDetalladaDetailLines(Map<String, dynamic> row) {
  final minutos = (row['duracionMinutos'] as num?)?.toInt() ?? 0;
  return [
    'Observación: ${row['observacion'] ?? ''}',
    'Duración: ${formatMinutosAsHhMm(minutos)}',
    'Sin cargo: ${row['sinCargo'] == true ? 'Sí' : 'No'}',
    'Presencial: ${row['presencial'] == true ? 'Sí' : 'No'}',
  ];
}

KardexRow mapPartesTareaRow(PartesTarea row) {
  final clienteNombre = row.clienteNombre?.trim() ?? '';
  final title = clienteNombre.isNotEmpty
      ? '${row.clienteCode ?? ''} — $clienteNombre'.trim()
      : '${row.clienteCode ?? row.clienteId}';
  return KardexRow(
    id: '${row.id}',
    primary: title,
    summary:
        '${row.tipoTareaDescripcion ?? row.tipoTareaCode ?? ''} · ${formatMinutosAsHhMm(row.duracionMinutos)}',
    statusLabel: row.cerrado ? 'Cerrada' : 'Abierta',
  );
}

KardexRow mapPaqueteDesglose(PaqueteHorasDesgloseItem row) {
  return KardexRow(
    id: row.id,
    primary: row.title,
    summary:
        '${formatMinutosAsHhMm(row.totalMinutos)} · ${row.cantidad} movimientos',
    statusLabel: null,
  );
}