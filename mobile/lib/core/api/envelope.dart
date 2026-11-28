/// Parser mínimo envelope GEN-20.
class ApiEnvelope {
  ApiEnvelope({
    required this.error,
    required this.respuesta,
    required this.resultado,
  });

  final int error;
  final String respuesta;
  final Map<String, dynamic> resultado;

  bool get isSuccess => error == 0;

  static ApiEnvelope fromJson(Map<String, dynamic> json) {
    final dynamic errorRaw = json['error'];
    final dynamic respuestaRaw = json['respuesta'];
    final dynamic resultadoRaw = json['resultado'];

    if (errorRaw is! int) {
      throw FormatException('envelope.error debe ser entero');
    }
    if (respuestaRaw is! String) {
      throw FormatException('envelope.respuesta debe ser String');
    }
    if (resultadoRaw == null) {
      throw FormatException('envelope.resultado no puede ser null');
    }
    if (resultadoRaw is! Map) {
      throw FormatException('envelope.resultado debe ser objeto JSON');
    }

    return ApiEnvelope(
      error: errorRaw,
      respuesta: respuestaRaw,
      resultado: Map<String, dynamic>.from(resultadoRaw),
    );
  }
}