import 'dart:async';

import 'package:flutter/material.dart';

import '../../core/api/envelope_message.dart';
import '../../core/auth/auth_service.dart';
import '../../core/config/config_service.dart';
import '../kardex/kardex_card.dart';
import '../kardex/kardex_row.dart';
import '../partes/mappers/kardex_mappers.dart';
import '../partes/models/partes_tarea.dart';
import '../partes/models/partes_user_profile.dart';
import '../partes/partes_duration.dart';
import '../partes/widgets/catalog_dropdown.dart';
import '../partes/widgets/partes_api_loader.dart';
import '../partes/widgets/partes_date_field.dart';

class CargaPage extends StatefulWidget {
  const CargaPage({
    super.key,
    required this.configService,
    required this.session,
    this.profile,
  });

  final ConfigService configService;
  final AuthSession session;
  final PartesUserProfile? profile;

  @override
  State<CargaPage> createState() => _CargaPageState();
}

class _CargaPageState extends State<CargaPage> {
  late String fechaIso;
  List<PartesTarea> rows = const [];
  bool loading = false;
  String? listError;
  String? formError;
  String? catalogError;
  bool formOpen = false;
  PartesTarea? editing;

  final observacionController = TextEditingController();
  int? clienteId;
  int? tipoTareaId;
  int? usuarioId;
  int duracionMinutos = 15;
  int tramoMinutos = 15;
  bool sinCargo = false;
  bool presencial = false;
  String rowVersion = '';

  List<Map<String, dynamic>> clientes = const [];
  List<Map<String, dynamic>> asistentes = const [];
  List<Map<String, dynamic>> tipos = const [];

  /// Refresco del bottom sheet del formulario (dropdowns).
  VoidCallback? _formSheetTick;

  bool get readOnly => widget.profile?.isCliente == true;
  bool get esSupervisor => widget.profile?.esSupervisor == true;
  int? get asistenteId => widget.profile?.asistenteId;

  @override
  void initState() {
    super.initState();
    fechaIso = todayIsoDate();
    usuarioId = asistenteId;
    _bootstrap();
  }

  Future<void> _bootstrap() async {
    await _loadCatalogos();
    await _loadList();
  }

  Future<void> _loadCatalogos() async {
    final api = await loadPartesApi(
      configService: widget.configService,
      session: widget.session,
    );
    if (api == null) return;

    try {
      final tramo = await api.fetchDuracionTramo();
      if (mounted) {
        setState(() {
          tramoMinutos = tramo;
          duracionMinutos = tramo;
        });
      }
    } catch (_) {
      // Se conserva el tramo por defecto.
    }

    try {
      var loadedClientes = await api.listCatalogo('clientes');
      final fixedClienteId = widget.profile?.clienteId;
      if (fixedClienteId != null) {
        loadedClientes = loadedClientes
            .where((c) => catalogItemId(c) == fixedClienteId)
            .toList();
      }
      if (mounted) setState(() => clientes = loadedClientes);
    } catch (_) {
      if (mounted) setState(() => catalogError = 'No se pudo cargar clientes.');
    }

    if (!esSupervisor) return;
    try {
      final loadedAsistentes = await api.listCatalogo('asistentes');
      if (!mounted) return;
      setState(() {
        asistentes = loadedAsistentes;
        catalogError = loadedAsistentes.isEmpty
            ? 'El catálogo de asistentes llegó vacío desde la API.'
            : null;
        _alignUsuarioId();
      });
    } catch (error) {
      if (!mounted) return;
      setState(() {
        catalogError = 'No se pudo cargar asistentes: '
            '${resolveCaughtErrorMessage(error)}';
      });
    }
  }

  void _touchForm() {
    if (mounted) setState(() {});
    _formSheetTick?.call();
  }

  void _alignUsuarioId() {
    usuarioId = resolveDropdownIntValue(asistentes, usuarioId) ??
        resolveDropdownIntValue(asistentes, asistenteId) ??
        (asistentes.isNotEmpty ? catalogItemId(asistentes.first) : asistenteId);
  }

  Future<void> _loadList() async {
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
      final profile = widget.profile;
      final items = await api.listTareas(
        fechaDesde: fechaIso,
        fechaHasta: fechaIso,
        estadoCerrado: 'todas',
        clienteId: profile?.isCliente == true ? profile?.clienteId : null,
      );
      if (!mounted) return;
      setState(() => rows = items);
    } catch (error) {
      if (!mounted) return;
      setState(() => listError = resolveCaughtErrorMessage(error));
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  Future<void> _onClienteChanged(int? value) async {
    setState(() {
      clienteId = value;
      tipoTareaId = null;
      tipos = const [];
    });
    _formSheetTick?.call();
    if (value == null) return;
    final api = await loadPartesApi(
      configService: widget.configService,
      session: widget.session,
    );
    if (api == null || !mounted) return;
    final loaded = await api.listCatalogo('tipos-tarea', query: '?clienteId=$value');
    if (!mounted) return;
    int? defaultTipo;
    for (final item in loaded) {
      if (item['isDefault'] == true) {
        defaultTipo = catalogItemId(item);
        break;
      }
    }
    defaultTipo ??=
        loaded.isNotEmpty ? catalogItemId(loaded.first) : null;
    setState(() {
      tipos = loaded;
      tipoTareaId = resolveDropdownIntValue(loaded, defaultTipo);
    });
    _formSheetTick?.call();
  }

  void _resetFormFields() {
    observacionController.clear();
    clienteId = widget.profile?.clienteId;
    tipoTareaId = null;
    tipos = const [];
    usuarioId = asistenteId;
    _alignUsuarioId();
    duracionMinutos = tramoMinutos;
    sinCargo = false;
    presencial = false;
    rowVersion = '';
    editing = null;
  }

  Future<void> _openCreate() async {
    setState(() {
      formError = null;
      _resetFormFields();
    });

    // Abrir primero: la carga de catálogos no debe dejar al usuario
    // esperando después de tocar el botón "+".
    final sheetFuture = _showFormSheet();

    if (esSupervisor && asistentes.isEmpty) {
      unawaited(_loadCatalogos());
    }
    if (clienteId != null) {
      unawaited(_onClienteChanged(clienteId));
    }
    await sheetFuture;
  }

  Future<void> _openEdit(PartesTarea row) async {
    if (row.cerrado) {
      _showDetail(row);
      return;
    }
    if (readOnly) {
      _showDetail(row);
      return;
    }
    setState(() {
      editing = row;
      formError = null;
      clienteId = row.clienteId;
      tipoTareaId = row.tipoTareaId;
      usuarioId = row.usuarioId;
      duracionMinutos = row.duracionMinutos;
      sinCargo = row.sinCargo;
      presencial = row.presencial;
      rowVersion = row.rowVersion;
      observacionController.text = row.observacion;
    });
    final api = await loadPartesApi(
      configService: widget.configService,
      session: widget.session,
    );
    if (api == null || !mounted) return;
    final loaded = await api.listCatalogo(
      'tipos-tarea',
      query: '?clienteId=${row.clienteId}',
    );
    if (!mounted) return;
    setState(() {
      tipos = loaded;
      tipoTareaId = resolveDropdownIntValue(loaded, tipoTareaId);
      usuarioId = resolveDropdownIntValue(asistentes, usuarioId) ?? usuarioId;
    });
    if (!mounted) return;
    await _showFormSheet();
  }

  void _showDetail(PartesTarea row) {
    showModalBottomSheet<void>(
      context: context,
      showDragHandle: true,
      builder: (context) => Padding(
        padding: const EdgeInsets.fromLTRB(16, 0, 16, 24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              row.tipoTareaDescripcion ?? row.tipoTareaCode ?? 'Tarea',
              style: Theme.of(context).textTheme.titleMedium,
            ),
            const SizedBox(height: 8),
            Text('Cliente: ${row.clienteNombre ?? row.clienteCode ?? row.clienteId}'),
            Text('Duración: ${formatMinutosAsHhMm(row.duracionMinutos)}'),
            Text('Estado: ${row.cerrado ? 'Cerrada' : 'Abierta'}'),
            if (row.observacion.isNotEmpty) ...[
              const SizedBox(height: 8),
              Text('Observación: ${row.observacion}'),
            ],
          ],
        ),
      ),
    );
  }

  Future<bool> _confirmFechaFutura() async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Fecha futura'),
        content: const Text('¿Confirmás cargar una tarea con fecha futura?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancelar'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Confirmar'),
          ),
        ],
      ),
    );
    return ok == true;
  }

  Future<void> _save() async {
    setState(() {
      formError = null;
      loading = true;
    });
    try {
      if (clienteId == null || tipoTareaId == null) {
        throw StateError('Cliente y tipo de tarea son obligatorios.');
      }
      if (!isValidDuracionMinutos(duracionMinutos, tramoMinutos)) {
        throw StateError(
          'Duración inválida: debe ser múltiplo de $tramoMinutos min (máx. 24 h).',
        );
      }
      if (isFechaFuturaIso(fechaIso)) {
        final confirmed = await _confirmFechaFutura();
        if (!confirmed) {
          if (mounted) setState(() => loading = false);
          return;
        }
      }
      final api = await loadPartesApi(
        configService: widget.configService,
        session: widget.session,
      );
      if (api == null) throw StateError('Configurá la URL de la API');
      final body = <String, dynamic>{
        'usuarioId': usuarioId ?? asistenteId,
        'clienteId': clienteId,
        'tipoTareaId': tipoTareaId,
        'fecha': fechaIso,
        'duracionMinutos': duracionMinutos,
        'observacion': observacionController.text.trim(),
        'sinCargo': sinCargo,
        'presencial': presencial,
      };
      if (isFechaFuturaIso(fechaIso)) {
        body['confirmarFechaFutura'] = true;
      }
      if (editing != null) {
        body['rowVersion'] = rowVersion;
      }
      await api.saveTarea(body, id: editing?.id);
      if (!mounted) return;
      Navigator.of(context).maybePop();
      await _loadList();
    } catch (error) {
      if (!mounted) return;
      setState(() => formError = resolveCaughtErrorMessage(error));
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  Future<void> _deleteEditing() async {
    final row = editing;
    if (row == null || row.cerrado) return;
    final ok = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Eliminar tarea'),
        content: const Text('¿Eliminar esta tarea?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancelar'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Eliminar'),
          ),
        ],
      ),
    );
    if (ok != true || !mounted) return;
    setState(() {
      formError = null;
      loading = true;
    });
    try {
      final api = await loadPartesApi(
        configService: widget.configService,
        session: widget.session,
      );
      if (api == null) throw StateError('Configurá la URL de la API');
      await api.deleteTarea(row.id, row.rowVersion);
      if (!mounted) return;
      Navigator.of(context).maybePop();
      await _loadList();
    } catch (error) {
      if (!mounted) return;
      setState(() => formError = resolveCaughtErrorMessage(error));
      _formSheetTick?.call();
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  void _closeForm() {
    setState(() => formError = null);
    Navigator.of(context).maybePop();
  }

  Future<void> _showFormSheet() async {
    formOpen = true;
    await showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      useRootNavigator: true,
      showDragHandle: true,
      builder: (sheetContext) {
        return StatefulBuilder(
          builder: (sheetContext, setSheetState) {
            _formSheetTick = () => setSheetState(() {});
            final maxH = MediaQuery.sizeOf(sheetContext).height * 0.9;
            return Padding(
              padding: EdgeInsets.only(
                left: 16,
                right: 16,
                bottom: 16 + MediaQuery.viewInsetsOf(sheetContext).bottom,
              ),
              child: SizedBox(
                height: maxH,
                child: ListView(
                  children: [_buildFormContent()],
                ),
              ),
            );
          },
        );
      },
    );
    _formSheetTick = null;
    if (mounted) setState(() => formOpen = false);
  }

  Widget _buildFormContent() {
    final editingTitle = editing != null ? 'Editar tarea' : 'Nueva tarea';
    final asistenteItems = buildIntDropdownItems(
      asistentes,
      (a) => '${a['code'] ?? ''} — ${a['nombre'] ?? ''}',
    );
    final clienteItems = buildIntDropdownItems(
      clientes,
      (c) => '${c['code'] ?? ''} — ${c['nombre'] ?? ''}',
    );
    final tipoItems = buildIntDropdownItems(
      tipos,
      (t) => '${t['code'] ?? ''} — ${t['descripcion'] ?? ''}',
    );

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Text(
          editingTitle,
          style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w600),
        ),
        if (formError != null)
          Padding(
            padding: const EdgeInsets.only(top: 8),
            child: Text(
              formError!,
              key: const Key('cargaFormError'),
              style: const TextStyle(color: Colors.red),
            ),
          ),
        if (esSupervisor) ...[
          const SizedBox(height: 12),
          if (asistenteItems.isEmpty)
            Text(catalogError ?? 'No hay asistentes en catálogo.')
          else
            DropdownButtonFormField<int>(
              key: const Key('cargaAsistente'),
              isExpanded: true,
              value: resolveDropdownIntValue(asistentes, usuarioId),
              decoration: const InputDecoration(labelText: 'Asistente'),
              items: asistenteItems,
              onChanged: (v) {
                usuarioId = v;
                _touchForm();
              },
            ),
        ],
        DropdownButtonFormField<int>(
          key: const Key('cargaCliente'),
          isExpanded: true,
          value: resolveDropdownIntValue(clientes, clienteId),
          decoration: const InputDecoration(labelText: 'Cliente'),
          items: clienteItems,
          onChanged: widget.profile?.clienteId != null
              ? null
              : (v) => _onClienteChanged(v),
        ),
        DropdownButtonFormField<int>(
          key: const Key('cargaTipoTarea'),
          isExpanded: true,
          value: resolveDropdownIntValue(tipos, tipoTareaId),
          decoration: const InputDecoration(labelText: 'Tipo tarea'),
          items: tipoItems,
          onChanged: tipoItems.isEmpty
              ? null
              : (v) {
                  tipoTareaId = v;
                  _touchForm();
                },
        ),
        DropdownButtonFormField<int>(
          key: const Key('cargaDuracion'),
          isExpanded: true,
          value: duracionOptions.contains(duracionMinutos)
              ? duracionMinutos
              : duracionOptions.firstOrNull,
          decoration: InputDecoration(
            labelText: 'Duración',
            helperText: formatMinutosAsHhMm(duracionMinutos),
          ),
          items: duracionOptions
              .map(
                (m) => DropdownMenuItem<int>(
                  value: m,
                  child: Text('${formatMinutosAsHhMm(m)} ($m min)'),
                ),
              )
              .toList(),
          onChanged: (v) {
            if (v != null) {
              duracionMinutos = v;
              _touchForm();
            }
          },
        ),
        TextField(
          key: const Key('cargaObservacion'),
          controller: observacionController,
          decoration: const InputDecoration(labelText: 'Observación'),
          maxLines: 3,
        ),
        CheckboxListTile(
          key: const Key('cargaSinCargo'),
          value: sinCargo,
          onChanged: (v) {
            sinCargo = v ?? false;
            _touchForm();
          },
          title: const Text('Sin cargo'),
          controlAffinity: ListTileControlAffinity.leading,
          contentPadding: EdgeInsets.zero,
        ),
        CheckboxListTile(
          key: const Key('cargaPresencial'),
          value: presencial,
          onChanged: (v) {
            presencial = v ?? false;
            _touchForm();
          },
          title: const Text('Presencial'),
          controlAffinity: ListTileControlAffinity.leading,
          contentPadding: EdgeInsets.zero,
        ),
        const SizedBox(height: 8),
        Row(
          children: [
            TextButton(onPressed: _closeForm, child: const Text('Cancelar')),
            if (editing != null && !editing!.cerrado) ...[
              TextButton(
                key: const Key('cargaEliminar'),
                onPressed: loading ? null : _deleteEditing,
                child: const Text(
                  'Eliminar',
                  style: TextStyle(color: Colors.red),
                ),
              ),
            ],
            const Spacer(),
            FilledButton(
              key: const Key('cargaGuardar'),
              onPressed: loading ? null : _save,
              child: const Text('Guardar'),
            ),
          ],
        ),
      ],
    );
  }

  @override
  void dispose() {
    observacionController.dispose();
    super.dispose();
  }

  List<KardexRow> get kardexRows => rows.map(mapPartesTareaRow).toList();

  List<int> get duracionOptions => buildTramoOptions(tramoMinutos);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Carga individual'),
        actions: [
          if (!readOnly)
            IconButton(
              key: const Key('cargaNuevaTarea'),
              onPressed: formOpen ? null : () => unawaited(_openCreate()),
              icon: const Icon(Icons.add),
            ),
        ],
      ),
      body: Column(
        children: [
              Padding(
                padding: const EdgeInsets.all(12),
                child: PartesDateField(
                  fieldKey: const Key('cargaFecha'),
                  label: 'Fecha',
                  isoValue: fechaIso,
                  enabled: !loading && !formOpen,
                  onIsoChanged: (iso) {
                    setState(() => fechaIso = iso);
                    _loadList();
                  },
                ),
              ),
              if (listError != null && !formOpen)
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 12),
                  child: Text(
                    listError!,
                    style: const TextStyle(color: Colors.red),
                  ),
                ),
              if (loading) const LinearProgressIndicator(),
              Expanded(
                child: RefreshIndicator(
                  onRefresh: _loadList,
                  child: ListView.separated(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.all(12),
                    itemCount: kardexRows.isEmpty ? 1 : kardexRows.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 8),
                    itemBuilder: (context, index) {
                      if (kardexRows.isEmpty) {
                        return const Padding(
                          padding: EdgeInsets.all(24),
                          child: Center(
                            child: Text('Sin tareas para esta fecha.'),
                          ),
                        );
                      }
                      final row = rows[index];
                      return KardexCard(
                        row: kardexRows[index],
                        onTap: () => _openEdit(row),
                      );
                    },
                  ),
                ),
              ),
        ],
      ),
    );
  }
}

extension _FirstOrNull<E> on List<E> {
  E? get firstOrNull => isEmpty ? null : first;
}