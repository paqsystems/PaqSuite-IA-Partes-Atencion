import '../api/envelope.dart';
import '../api/paqsuite_api_client.dart';
import '../storage/secure_store.dart';

typedef PaqsuiteApiClientFactory = PaqsuiteApiClient Function({
  required String baseUrl,
  String? tenantCode,
  String? accessToken,
  int? companyId,
});

class AuthSession {
  AuthSession({
    required this.tenantCode,
    required this.accessToken,
    this.companyId = 1,
  });

  final String tenantCode;
  final String accessToken;
  final int companyId;
}

class AuthService {
  AuthService({
    required SecureStore secureStore,
    PaqsuiteApiClientFactory? apiClientFactory,
  })  : _secureStore = secureStore,
        _apiClientFactory = apiClientFactory ?? _defaultApiClientFactory;

  final SecureStore _secureStore;
  final PaqsuiteApiClientFactory _apiClientFactory;

  static PaqsuiteApiClient _defaultApiClientFactory({
    required String baseUrl,
    String? tenantCode,
    String? accessToken,
    int? companyId,
  }) {
    return PaqsuiteApiClient(
      baseUrl: baseUrl,
      tenantCode: tenantCode,
      accessToken: accessToken,
      companyId: companyId,
    );
  }

  Future<AuthSession?> loadSession() async {
    final tenant = await _secureStore.read(SecureStoreKeys.tenantCode);
    final token = await _secureStore.read(SecureStoreKeys.accessToken);
    if (tenant == null || token == null || tenant.isEmpty || token.isEmpty) {
      return null;
    }
    return AuthSession(tenantCode: tenant, accessToken: token);
  }

  Future<ApiEnvelope> login({
    required String baseUrl,
    required String tenantCode,
    required String username,
    required String password,
  }) async {
    final client = _apiClientFactory(
      baseUrl: baseUrl,
      tenantCode: tenantCode,
    );
    final envelope = await client.postJson(
      '/api/v1/auth/login',
      body: {'usuario': username, 'password': password},
    );
    if (!envelope.isSuccess) {
      return envelope;
    }
    final token = _extractToken(envelope);
    if (token == null || token.isEmpty) {
      throw FormatException('login sin token en resultado');
    }
    await _secureStore.write(SecureStoreKeys.tenantCode, tenantCode.trim());
    await _secureStore.write(SecureStoreKeys.accessToken, token);
    return envelope;
  }

  Future<void> logout() async {
    await _secureStore.delete(SecureStoreKeys.accessToken);
    await _secureStore.delete(SecureStoreKeys.tenantCode);
  }

  static String? _extractToken(ApiEnvelope envelope) {
    final resultado = envelope.resultado;
    final dynamic token =
        resultado['token'] ?? resultado['access_token'] ?? resultado['accessToken'];
    return token is String ? token : null;
  }
}