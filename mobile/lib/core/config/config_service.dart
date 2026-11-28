import '../api/envelope.dart';
import '../api/paqsuite_api_client.dart';
import '../storage/secure_store.dart';

class ConfigService {
  ConfigService({required SecureStore secureStore}) : _secureStore = secureStore;

  final SecureStore _secureStore;

  Future<String?> getApiBaseUrl() => _secureStore.read(SecureStoreKeys.apiBaseUrl);

  Future<void> saveApiBaseUrl(String rawUrl) async {
    final normalized = normalizeBaseUrl(rawUrl);
    await _secureStore.write(SecureStoreKeys.apiBaseUrl, normalized);
  }

  Future<ApiEnvelope> testHealth({
    required String rawUrl,
    String? tenantCode,
    PaqsuiteApiClient? client,
  }) async {
    final baseUrl = normalizeBaseUrl(rawUrl);
    final apiClient = client ??
        PaqsuiteApiClient(
          baseUrl: baseUrl,
          tenantCode: tenantCode,
        );
    return apiClient.getHealth();
  }

  static String normalizeBaseUrl(String raw) {
    var trimmed = raw.trim();
    const suffix = '/api/v1';
    if (trimmed.toLowerCase().endsWith(suffix)) {
      trimmed = trimmed.substring(0, trimmed.length - suffix.length);
    }
    while (trimmed.endsWith('/')) {
      trimmed = trimmed.substring(0, trimmed.length - 1);
    }
    return trimmed;
  }
}