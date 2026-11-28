class PartesUserProfile {
  const PartesUserProfile({
    required this.tipoFuncional,
    required this.esSupervisor,
    this.asistenteId,
    this.clienteId,
  });

  final String tipoFuncional;
  final bool esSupervisor;
  final int? asistenteId;
  final int? clienteId;

  bool get isCliente => tipoFuncional == 'cliente';

  static PartesUserProfile? fromLoginResultado(Map<String, dynamic> resultado) {
    final partes = resultado['partes'];
    if (partes is! Map) return null;
    final map = Map<String, dynamic>.from(partes);
    final tipo = map['tipoFuncional']?.toString() ?? '';
    if (tipo.isEmpty) return null;
    return PartesUserProfile(
      tipoFuncional: tipo,
      esSupervisor: map['esSupervisor'] == true,
      asistenteId: _optionalInt(map['asistenteId']),
      clienteId: _optionalInt(map['clienteId']),
    );
  }

  static int? _optionalInt(Object? value) {
    if (value == null) return null;
    if (value is int) return value;
    if (value is num) return value.toInt();
    return int.tryParse('$value');
  }
}