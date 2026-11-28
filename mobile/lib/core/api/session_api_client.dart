import '../auth/auth_service.dart';
import '../config/config_service.dart';
import 'paqsuite_api_client.dart';

Future<PaqsuiteApiClient?> buildSessionApiClient({
  required ConfigService configService,
  required AuthSession session,
}) async {
  final baseUrl = await configService.getApiBaseUrl();
  if (baseUrl == null || baseUrl.isEmpty) {
    return null;
  }
  return PaqsuiteApiClient(
    baseUrl: baseUrl,
    tenantCode: session.tenantCode,
    accessToken: session.accessToken,
    companyId: session.companyId,
  );
}