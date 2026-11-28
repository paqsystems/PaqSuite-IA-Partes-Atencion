import '../../../core/api/session_api_client.dart';
import '../../../core/auth/auth_service.dart';
import '../../../core/config/config_service.dart';
import '../partes_api.dart';

Future<PartesApi?> loadPartesApi({
  required ConfigService configService,
  required AuthSession session,
}) async {
  final client = await buildSessionApiClient(
    configService: configService,
    session: session,
  );
  if (client == null) return null;
  return PartesApi(client);
}