import '../../core/api/paqsuite_api_client.dart';
import 'menu_models.dart';

class MenuService {
  MenuService({required PaqsuiteApiClient apiClient}) : _apiClient = apiClient;

  final PaqsuiteApiClient _apiClient;

  Future<List<MenuNode>> fetchAuthorizedMenu() async {
    final envelope = await _apiClient.getJson('/api/v1/user/menu');
    return parseMenuItemsFromEnvelope(envelope);
  }
}