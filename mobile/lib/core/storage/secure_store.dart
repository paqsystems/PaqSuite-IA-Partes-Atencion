abstract class SecureStore {
  Future<void> write(String key, String value);
  Future<String?> read(String key);
  Future<void> delete(String key);
  Future<void> deleteAll();
}

class SecureStoreKeys {
  static const apiBaseUrl = 'apiBaseUrl';
  static const tenantCode = 'tenantCode';
  static const accessToken = 'accessToken';
}