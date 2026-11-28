import '../../core/api/envelope.dart';
import '../../core/api/paqsuite_api_client.dart';
import 'models/partes_tarea.dart';
import 'models/partes_user_profile.dart';

class PartesApi {
  PartesApi(this._client);

  final PaqsuiteApiClient _client;

  Future<int> fetchDuracionTramo() async {
    final envelope = await _client.getJson('/api/v1/partes/parametros/duracion-tramo');
    _ensureSuccess(envelope);
    return _asIntFromMap(envelope.resultado, 'tramoMinutos', fallback: 15);
  }

  Future<List<Map<String, dynamic>>> listCatalogo(
    String path, {
    String query = '',
  }) async {
    final envelope = await _client.getJson(
      '/api/v1/partes/catalogos/$path$query',
    );
    _ensureSuccess(envelope);
    return _items(envelope);
  }

  Future<PartesUserProfile?> fetchMeProfile() async {
    final envelope = await _client.getJson('/api/v1/auth/me');
    if (!envelope.isSuccess) return null;
    return PartesUserProfile.fromLoginResultado(envelope.resultado);
  }

  Future<List<PartesTarea>> listTareas({
    required String fechaDesde,
    required String fechaHasta,
    String estadoCerrado = 'todas',
    int? clienteId,
    int? usuarioId,
    int pageSize = 50,
  }) async {
    final params = <String, String>{
      'fechaDesde': fechaDesde,
      'fechaHasta': fechaHasta,
      'estadoCerrado': estadoCerrado,
      'page': '1',
      'pageSize': '$pageSize',
    };
    if (clienteId != null) params['clienteId'] = '$clienteId';
    if (usuarioId != null) params['usuarioId'] = '$usuarioId';
    final qs = Uri(queryParameters: params).query;
    final envelope = await _client.getJson('/api/v1/partes/tareas?$qs');
    _ensureSuccess(envelope);
    return _items(envelope).map(PartesTarea.fromJson).toList();
  }

  Future<PartesTarea> saveTarea(Map<String, dynamic> body, {int? id}) async {
    final envelope = id == null
        ? await _client.postJson('/api/v1/partes/tareas', body: body)
        : await _client.putJson('/api/v1/partes/tareas/$id', body: body);
    _ensureSuccess(envelope);
    final item = envelope.resultado['item'];
    if (item is! Map) {
      throw FormatException('saveTarea sin item');
    }
    return PartesTarea.fromJson(Map<String, dynamic>.from(item));
  }

  Future<void> deleteTarea(int id, String rowVersion) async {
    final envelope = await _client.deleteJson(
      '/api/v1/partes/tareas/$id',
      body: {'rowVersion': rowVersion},
    );
    _ensureSuccess(envelope);
  }

  Future<List<Map<String, dynamic>>> fetchInformeTareas(
    Map<String, String> query,
  ) async {
    final qs = Uri(queryParameters: query).query;
    final envelope =
        await _client.getJson('/api/v1/partes/informes/tareas?$qs');
    _ensureSuccess(envelope);
    return _items(envelope);
  }

  Future<PaqueteHorasResponse> fetchPaqueteHoras({
    required String fechaDesde,
    required String fechaHasta,
    int? clienteId,
    int? tipoClienteId,
  }) async {
    final params = <String, String>{
      'fechaDesde': fechaDesde,
      'fechaHasta': fechaHasta,
    };
    if (clienteId != null) params['clienteId'] = '$clienteId';
    if (tipoClienteId != null) params['tipoClienteId'] = '$tipoClienteId';
    final qs = Uri(queryParameters: params).query;
    final envelope =
        await _client.getJson('/api/v1/partes/informes/paquete-horas?$qs');
    _ensureSuccess(envelope);
    final r = envelope.resultado;
    return PaqueteHorasResponse(
      items: _itemsFromMap(r),
      total: _asIntFromMap(r, 'total'),
      saldoInicial: _asIntFromMap(r, 'saldoInicial'),
    );
  }

  static void _ensureSuccess(ApiEnvelope envelope) {
    if (!envelope.isSuccess) {
      throw Exception(envelope.respuesta);
    }
  }

  static List<Map<String, dynamic>> _items(ApiEnvelope envelope) {
    final raw = envelope.resultado['items'];
    if (raw is! List) return [];
    return raw
        .whereType<Map>()
        .map((e) => Map<String, dynamic>.from(e))
        .toList();
  }

  static List<Map<String, dynamic>> _itemsFromMap(Map<String, dynamic> r) {
    final raw = r['items'];
    if (raw is! List) return [];
    return raw
        .whereType<Map>()
        .map((e) => Map<String, dynamic>.from(e))
        .toList();
  }

  static int _asIntFromMap(
    Map<String, dynamic> json,
    String key, {
    int fallback = 0,
  }) {
    final value = json[key];
    if (value is int) return value;
    if (value is num) return value.toInt();
    return int.tryParse('$value') ?? fallback;
  }
}

class PaqueteHorasResponse {
  PaqueteHorasResponse({
    required this.items,
    required this.total,
    required this.saldoInicial,
  });

  final List<Map<String, dynamic>> items;
  final int total;
  final int saldoInicial;
}