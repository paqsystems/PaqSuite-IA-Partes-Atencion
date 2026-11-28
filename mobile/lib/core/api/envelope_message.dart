import 'envelope.dart';
import '../../features/partes/partes_messages.dart';

String resolveEnvelopeUserMessage(ApiEnvelope envelope, {String? fallback}) {
  if (envelope.isSuccess) {
    return envelope.respuesta;
  }
  final code = envelope.error;
  if (code != 0) {
    return '${envelope.respuesta} (código $code)';
  }
  return resolvePartesMessage(fallback ?? envelope.respuesta);
}

String resolveCaughtErrorMessage(Object error) {
  final text = error.toString();
  if (text.startsWith('Exception: ')) {
    return text.substring('Exception: '.length);
  }
  if (text.contains('Connection refused') ||
      text.contains('Failed host lookup') ||
      text.contains('SocketException')) {
    return 'Error de conexión. Intente nuevamente.';
  }
  return resolvePartesMessage(text);
}