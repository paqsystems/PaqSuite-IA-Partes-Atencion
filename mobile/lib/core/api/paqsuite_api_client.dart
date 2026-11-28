import 'dart:convert';

import 'package:http/http.dart' as http;

import 'envelope.dart';

/// Cliente HTTP MONO (`X-Paq-Cliente`, Bearer).
class PaqsuiteApiClient {
  PaqsuiteApiClient({
    required this.baseUrl,
    this.tenantCode,
    this.accessToken,
    this.companyId,
    http.Client? httpClient,
  }) : _httpClient = httpClient ?? http.Client();

  final String baseUrl;
  final String? tenantCode;
  final String? accessToken;
  final int? companyId;
  final http.Client _httpClient;

  static const headerCliente = 'X-Paq-Cliente';
  static const headerCompanyId = 'X-Company-Id';

  Map<String, String> buildHeaders({Map<String, String>? extra}) {
    final headers = <String, String>{
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      ...?extra,
    };
    final tenant = tenantCode?.trim();
    if (tenant != null && tenant.isNotEmpty) {
      headers[headerCliente] = tenant;
    }
    final token = accessToken?.trim();
    if (token != null && token.isNotEmpty) {
      headers['Authorization'] = 'Bearer $token';
    }
    final company = companyId;
    if (company != null && company > 0) {
      headers[headerCompanyId] = company.toString();
    }
    return headers;
  }

  Future<ApiEnvelope> getHealth() => getJson('/api/v1/health');

  Future<ApiEnvelope> getJson(String path) async {
    final uri = Uri.parse('$baseUrl$path');
    try {
      final response = await _httpClient.get(uri, headers: buildHeaders());
      return _parseBody(response);
    } on Exception catch (error) {
      throw _toTransportException(error, baseUrl);
    }
  }

  Future<ApiEnvelope> deleteJson(
    String path, {
    Map<String, dynamic>? body,
  }) async {
    final uri = Uri.parse('$baseUrl$path');
    try {
      final response = await _httpClient.delete(
        uri,
        headers: buildHeaders(),
        body: body == null ? null : jsonEncode(body),
      );
      return _parseBody(response);
    } on Exception catch (error) {
      throw _toTransportException(error, baseUrl);
    }
  }

  Future<ApiEnvelope> postJson(
    String path, {
    required Map<String, dynamic> body,
  }) async {
    final uri = Uri.parse('$baseUrl$path');
    try {
      final response = await _httpClient.post(
        uri,
        headers: buildHeaders(),
        body: jsonEncode(body),
      );
      return _parseBody(response);
    } on Exception catch (error) {
      throw _toTransportException(error, baseUrl);
    }
  }

  Future<ApiEnvelope> putJson(
    String path, {
    required Map<String, dynamic> body,
  }) async {
    final uri = Uri.parse('$baseUrl$path');
    try {
      final response = await _httpClient.put(
        uri,
        headers: buildHeaders(),
        body: jsonEncode(body),
      );
      return _parseBody(response);
    } on Exception catch (error) {
      throw _toTransportException(error, baseUrl);
    }
  }

  static Exception _toTransportException(Object error, String baseUrl) {
    final detail = error.toString();
    if (detail.contains('localhost') || detail.contains('127.0.0.1')) {
      return Exception(
        'En Android use http://10.0.2.2:<puerto> (no localhost). Configurá la URL en el engranaje.',
      );
    }
    if (detail.contains('Failed host lookup') ||
        detail.contains('Connection refused') ||
        detail.contains('SocketException') ||
        detail.contains('Network is unreachable')) {
      return Exception(
        'No se pudo conectar a $baseUrl. Verificá que el backend esté en marcha '
        '(.\\scripts\\dev\\start-dev.ps1 -All -SkipSqlSync) y la URL '
        'http://10.0.2.2:<puerto> sin /api/v1.',
      );
    }
    return Exception('Error de conexión. Intente nuevamente.');
  }

  ApiEnvelope _parseBody(http.Response response) {
    final decoded = jsonDecode(response.body);
    if (decoded is! Map<String, dynamic>) {
      throw FormatException('respuesta no es objeto JSON');
    }
    return ApiEnvelope.fromJson(decoded);
  }

  void close() => _httpClient.close();
}