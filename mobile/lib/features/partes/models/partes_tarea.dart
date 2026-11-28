class PartesTarea {
  PartesTarea({
    required this.id,
    required this.usuarioId,
    required this.clienteId,
    required this.tipoTareaId,
    required this.fecha,
    required this.duracionMinutos,
    required this.sinCargo,
    required this.presencial,
    required this.observacion,
    required this.cerrado,
    required this.rowVersion,
    this.usuarioCode,
    this.usuarioNombre,
    this.clienteCode,
    this.clienteNombre,
    this.tipoTareaCode,
    this.tipoTareaDescripcion,
  });

  final int id;
  final int usuarioId;
  final int clienteId;
  final int tipoTareaId;
  final String fecha;
  final int duracionMinutos;
  final bool sinCargo;
  final bool presencial;
  final String observacion;
  final bool cerrado;
  final String rowVersion;
  final String? usuarioCode;
  final String? usuarioNombre;
  final String? clienteCode;
  final String? clienteNombre;
  final String? tipoTareaCode;
  final String? tipoTareaDescripcion;

  static PartesTarea fromJson(Map<String, dynamic> json) {
    return PartesTarea(
      id: _asInt(json['id']),
      usuarioId: _asInt(json['usuarioId']),
      clienteId: _asInt(json['clienteId']),
      tipoTareaId: _asInt(json['tipoTareaId']),
      fecha: json['fecha']?.toString() ?? '',
      duracionMinutos: _asInt(json['duracionMinutos']),
      sinCargo: json['sinCargo'] == true,
      presencial: json['presencial'] == true,
      observacion: json['observacion']?.toString() ?? '',
      cerrado: json['cerrado'] == true,
      rowVersion: json['rowVersion']?.toString() ?? '',
      usuarioCode: json['usuarioCode']?.toString(),
      usuarioNombre: json['usuarioNombre']?.toString(),
      clienteCode: json['clienteCode']?.toString(),
      clienteNombre: json['clienteNombre']?.toString(),
      tipoTareaCode: json['tipoTareaCode']?.toString(),
      tipoTareaDescripcion: json['tipoTareaDescripcion']?.toString(),
    );
  }

  static int _asInt(Object? value) {
    if (value is int) return value;
    if (value is num) return value.toInt();
    return int.tryParse('$value') ?? 0;
  }
}