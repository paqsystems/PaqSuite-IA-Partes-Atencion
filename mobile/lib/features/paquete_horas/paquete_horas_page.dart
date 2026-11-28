import 'package:flutter/material.dart';

import '../../core/api/envelope_message.dart';
import '../../core/auth/auth_service.dart';
import '../../core/config/config_service.dart';
import '../kardex/kardex_card.dart';
import '../kardex/kardex_row.dart';
import '../partes/mappers/kardex_mappers.dart';
import '../partes/models/partes_user_profile.dart';
import '../partes/partes_date_format.dart';
import '../partes/partes_duration.dart';
import '../partes/partes_messages.dart';
import '../partes/partes_paquete_aggregate.dart';
import '../partes/widgets/partes_api_loader.dart';
import '../partes/widgets/partes_catalog_dropdown.dart';
import '../partes/widgets/partes_date_field.dart';

class PaqueteHorasPage extends StatefulWidget {
  const PaqueteHorasPage({
    super.key,
    required this.configService,
    required this.session,
    this.profile,
  });

  final ConfigService configService;
  final AuthSession session;
  final PartesUserProfile? profile;

  @override
  State<PaqueteHorasPage> createState() => _PaqueteHorasPageState();
}

class _PaqueteHorasPageState extends State<PaqueteHorasPage> {
  late String fechaDesdeIso;
  late String fechaHastaIso;
  int? clienteId;
  int? tipoClienteId;
  int saldoInicial = 0;
  bool loading = false;
  bool catalogLoading = false;
  String? error;
  String? listError;
  List<Map<String, dynamic>> rawRows = const [];
  List<Map<String, dynamic>> clientes = const [];
  List<Map<String, dynamic>> tiposCliente = const [];
  String eje = 'cliente';

  bool get esCliente => widget.profile?.isCliente == true;

  bool get needsClienteSelection =>
      !esCliente && (clienteId == null || clienteId! <= 0);

  @override
  void initState() {
    super.initState();
    final range = currentCalendarMonthRangeIso();
    fechaDesdeIso = range.fechaDesde;
    fechaHastaIso = range.fechaHasta;
    final profile = widget.profile;
    if (profile != null && profile.isCliente && profile.clienteId != null) {
      clienteId = profile.clienteId;
      _load();
    }
    _loadCatalogs();
  }

  Future<void> _loadCatalogs() async {
    setState(() => catalogLoading = true);
    try {
      final api = await loadPartesApi(
        configService: widget.configService,
        session: widget.session,
      );
      if (api == null) return;
      if (!esCliente) {
        clientes = await api.listCatalogo('clientes');
      }
      tiposCliente = await api.listCatalogo('tipos-cliente');
    } catch (_) {
      // Catálogos se reintentan al buscar
    } finally {
      if (mounted) setState(() => catalogLoading = false);
    }
  }

  Future<void> _load() async {
    if (needsClienteSelection) {
      setState(() {
        error = resolvePartesMessage('partes.informe.clienteRequerido');
        rawRows = const [];
        saldoInicial = 0;
      });
      return;
    }

    setState(() {
      loading = true;
      error = null;
      listError = null;
    });
    try {
      final api = await loadPartesApi(
        configService: widget.configService,
        session: widget.session,
      );
      if (api == null) throw StateError('Configurá la URL de la API');
      final response = await api.fetchPaqueteHoras(
        fechaDesde: fechaDesdeIso,
        fechaHasta: fechaHastaIso,
        clienteId: esCliente ? widget.profile?.clienteId : clienteId,
        tipoClienteId: tipoClienteId,
      );
      if (!mounted) return;
      setState(() {
        rawRows = response.items;
        saldoInicial = response.saldoInicial;
        if (response.items.isEmpty) {
          listError = 'Sin registros para los filtros indicados.';
        }
      });
    } catch (e) {
      if (!mounted) return;
      setState(() => error = resolveCaughtErrorMessage(e));
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  List<KardexRow> get desgloseRows {
    final items = aggregatePaqueteHorasDesglose(rawRows, eje);
    return items.map(mapPaqueteDesglose).toList();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Paquete de horas'),
        actions: [
          IconButton(
            key: const Key('paqueteHorasRefresh'),
            onPressed: loading || needsClienteSelection ? null : _load,
            icon: const Icon(Icons.refresh),
          ),
        ],
      ),
      body: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Padding(
            padding: const EdgeInsets.all(12),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                PartesDateField(
                  fieldKey: const Key('paqueteFechaDesde'),
                  label: 'Fecha desde',
                  isoValue: fechaDesdeIso,
                  enabled: !loading,
                  onIsoChanged: (iso) => setState(() => fechaDesdeIso = iso),
                ),
                PartesDateField(
                  fieldKey: const Key('paqueteFechaHasta'),
                  label: 'Fecha hasta',
                  isoValue: fechaHastaIso,
                  enabled: !loading,
                  onIsoChanged: (iso) => setState(() => fechaHastaIso = iso),
                ),
                if (!esCliente) ...[
                  const SizedBox(height: 8),
                  PartesCatalogDropdown(
                    key: const Key('paqueteCliente'),
                    label: 'Cliente',
                    hint: catalogLoading ? 'Cargando…' : 'Elegí cliente',
                    items: clientes,
                    value: clienteId,
                    enabled: !loading && !catalogLoading,
                    onChanged: (value) => setState(() {
                      clienteId = value;
                      error = null;
                    }),
                  ),
                ],
                const SizedBox(height: 8),
                PartesCatalogDropdown(
                  key: const Key('paqueteTipoCliente'),
                  label: 'Tipo de cliente (opcional)',
                  hint: catalogLoading ? 'Cargando…' : 'Todos',
                  items: tiposCliente,
                  value: tipoClienteId,
                  enabled: !loading && !catalogLoading,
                  onChanged: (value) => setState(() => tipoClienteId = value),
                ),
                const SizedBox(height: 8),
                Text(
                  'Saldo inicial: ${formatMinutosAsHhMm(saldoInicial)}',
                  key: const Key('paqueteSaldoInicial'),
                ),
                if (needsClienteSelection)
                  Padding(
                    padding: const EdgeInsets.only(top: 8),
                    child: Text(
                      resolvePartesMessage('partes.informe.clienteRequerido'),
                      key: const Key('paqueteClienteHint'),
                      style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                            color: Theme.of(context).colorScheme.onSurfaceVariant,
                          ),
                    ),
                  ),
                if (error != null)
                  Padding(
                    padding: const EdgeInsets.only(top: 8),
                    child: Text(
                      error!,
                      style: TextStyle(
                        color: Theme.of(context).colorScheme.error,
                      ),
                    ),
                  ),
                const SizedBox(height: 8),
                FilledButton(
                  key: const Key('paqueteBuscar'),
                  onPressed: loading ? null : _load,
                  child: const Text('Buscar'),
                ),
                const SizedBox(height: 12),
                Text(
                  'Desglose agrupado por',
                  style: Theme.of(context).textTheme.labelMedium,
                ),
                const SizedBox(height: 4),
                SegmentedButton<String>(
                  segments: const [
                    ButtonSegment(value: 'cliente', label: Text('Cliente')),
                    ButtonSegment(value: 'tipo', label: Text('Tipo tarea')),
                  ],
                  selected: {eje},
                  onSelectionChanged: loading
                      ? null
                      : (selection) {
                          setState(() => eje = selection.first);
                        },
                ),
              ],
            ),
          ),
          if (loading) const LinearProgressIndicator(),
          if (listError != null && !loading)
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 12),
              child: Text(listError!),
            ),
          Expanded(
            child: ListView.separated(
              padding: const EdgeInsets.all(12),
              itemCount: desgloseRows.length,
              separatorBuilder: (_, __) => const SizedBox(height: 8),
              itemBuilder: (context, index) {
                return KardexCard(
                  row: desgloseRows[index],
                  onTap: () {},
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}