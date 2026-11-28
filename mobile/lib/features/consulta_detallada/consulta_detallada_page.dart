import 'package:flutter/material.dart';

import '../../core/api/envelope_message.dart';
import '../../core/auth/auth_service.dart';
import '../../core/config/config_service.dart';
import '../kardex/kardex_card.dart';
import '../kardex/kardex_row.dart';
import '../partes/mappers/kardex_mappers.dart';
import '../partes/models/partes_user_profile.dart';
import '../partes/partes_date_format.dart';
import '../partes/widgets/partes_api_loader.dart';
import '../partes/widgets/partes_date_field.dart';

class ConsultaDetalladaPage extends StatefulWidget {
  const ConsultaDetalladaPage({
    super.key,
    required this.configService,
    required this.session,
    this.profile,
  });

  final ConfigService configService;
  final AuthSession session;
  final PartesUserProfile? profile;

  @override
  State<ConsultaDetalladaPage> createState() => _ConsultaDetalladaPageState();
}

class _ConsultaDetalladaPageState extends State<ConsultaDetalladaPage> {
  late String fechaDesdeIso;
  late String fechaHastaIso;
  String estadoCerrado = 'todas';
  bool loading = false;
  String? listError;
  List<Map<String, dynamic>> rawRows = const [];

  @override
  @override
  void initState() {
    super.initState();
    final hoy = todayIsoDate();
    fechaDesdeIso = hoy;
    fechaHastaIso = hoy;
    _load();
  }

  Future<void> _load() async {
    setState(() {
      loading = true;
      listError = null;
    });
    try {
      final api = await loadPartesApi(
        configService: widget.configService,
        session: widget.session,
      );
      if (api == null) throw StateError('Configurá la URL de la API');
      final query = <String, String>{
        'fechaDesde': fechaDesdeIso,
        'fechaHasta': fechaHastaIso,
        'estadoCerrado': estadoCerrado,
        'pageSize': '0',
      };
      final profile = widget.profile;
      if (profile != null && profile.isCliente && profile.clienteId != null) {
        query['clienteId'] = '${profile.clienteId}';
      }
      final items = await api.fetchInformeTareas(query);
      if (!mounted) return;
      setState(() {
        rawRows = items;
        if (items.isEmpty) {
          listError = 'Sin registros para los filtros indicados.';
        }
      });
    } catch (error) {
      if (!mounted) return;
      setState(() => listError = resolveCaughtErrorMessage(error));
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  List<KardexRow> get kardexRows =>
      rawRows.map(mapConsultaDetalladaRow).toList();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Consulta detallada'),
        actions: [
          IconButton(
            key: const Key('consultaDetalladaRefresh'),
            onPressed: loading ? null : _load,
            icon: const Icon(Icons.refresh),
          ),
        ],
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(12),
            child: Column(
              children: [
                PartesDateField(
                  fieldKey: const Key('consultaFechaDesde'),
                  label: 'Fecha desde',
                  isoValue: fechaDesdeIso,
                  enabled: !loading,
                  onIsoChanged: (iso) => setState(() => fechaDesdeIso = iso),
                ),
                PartesDateField(
                  fieldKey: const Key('consultaFechaHasta'),
                  label: 'Fecha hasta',
                  isoValue: fechaHastaIso,
                  enabled: !loading,
                  onIsoChanged: (iso) => setState(() => fechaHastaIso = iso),
                ),
                const SizedBox(height: 8),
                DropdownButtonFormField<String>(
                  key: const Key('consultaEstadoCerrado'),
                  value: estadoCerrado,
                  decoration: const InputDecoration(labelText: 'Estado'),
                  items: const [
                    DropdownMenuItem(value: 'todas', child: Text('Todas')),
                    DropdownMenuItem(value: 'abiertas', child: Text('Abiertas')),
                    DropdownMenuItem(value: 'cerradas', child: Text('Cerradas')),
                  ],
                  onChanged: loading
                      ? null
                      : (value) {
                          if (value == null) return;
                          setState(() => estadoCerrado = value);
                        },
                ),
                FilledButton(
                  onPressed: loading ? null : _load,
                  child: const Text('Buscar'),
                ),
                if (listError != null)
                  Padding(
                    padding: const EdgeInsets.only(top: 8),
                    child: Text(
                      listError!,
                      key: const Key('consultaListError'),
                      style: const TextStyle(color: Colors.red),
                    ),
                  ),
              ],
            ),
          ),
          if (loading) const LinearProgressIndicator(),
          Expanded(
            child: ListView.separated(
              padding: const EdgeInsets.all(12),
              itemCount: kardexRows.length,
              separatorBuilder: (_, __) => const SizedBox(height: 8),
              itemBuilder: (context, index) {
                final row = kardexRows[index];
                final raw = rawRows[index];
                return KardexCard(
                  row: row,
                  onTap: () {
                    showModalBottomSheet<void>(
                      context: context,
                      showDragHandle: true,
                      builder: (context) => Padding(
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              row.primary,
                              style: Theme.of(context).textTheme.titleMedium,
                            ),
                            const SizedBox(height: 8),
                            ...consultaDetalladaDetailLines(raw).map(Text.new),
                          ],
                        ),
                      ),
                    );
                  },
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}