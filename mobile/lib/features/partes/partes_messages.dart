/// Mensajes de producto (claves envelope / respuesta API → español mobile v1).
const Map<String, String> _partesMessageCatalog = {
  'partes.informe.clienteRequerido':
      'Seleccioná un cliente para consultar el paquete de horas.',
};

String resolvePartesMessage(String text) {
  final trimmed = text.trim();
  if (trimmed.isEmpty) return text;
  return _partesMessageCatalog[trimmed] ?? trimmed;
}