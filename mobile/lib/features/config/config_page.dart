import 'package:flutter/material.dart';

import '../../core/api/envelope.dart';
import '../../core/api/envelope_message.dart';
import '../../core/config/config_service.dart';

class ConfigPage extends StatefulWidget {
  const ConfigPage({
    super.key,
    required this.configService,
    this.tenantCodeForHealth,
  });

  final ConfigService configService;
  final String? tenantCodeForHealth;

  @override
  State<ConfigPage> createState() => _ConfigPageState();
}

class _ConfigPageState extends State<ConfigPage> {
  final _urlController = TextEditingController();
  bool _busy = false;
  String? _message;
  bool _isError = false;

  @override
  void initState() {
    super.initState();
    _loadUrl();
  }

  Future<void> _loadUrl() async {
    final saved = await widget.configService.getApiBaseUrl();
    if (saved != null) {
      _urlController.text = saved;
    }
  }

  Future<void> _save() async {
    await widget.configService.saveApiBaseUrl(_urlController.text);
    if (!mounted) return;
    setState(() {
      _message = 'URL guardada';
      _isError = false;
    });
  }

  Future<void> _testHealth() async {
    setState(() {
      _busy = true;
      _message = null;
    });
    try {
      final tenant = widget.tenantCodeForHealth?.trim();
      final ApiEnvelope envelope = await widget.configService.testHealth(
        rawUrl: _urlController.text,
        tenantCode: tenant != null && tenant.isNotEmpty ? tenant : null,
      );
      if (!mounted) return;
      setState(() {
        _message = resolveEnvelopeUserMessage(envelope);
        _isError = !envelope.isSuccess;
      });
    } catch (error) {
      if (!mounted) return;
      setState(() {
        _message = resolveCaughtErrorMessage(error);
        _isError = true;
      });
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  void dispose() {
    _urlController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Configuración API')),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            TextField(
              key: const Key('mobileConfigApiUrl'),
              controller: _urlController,
              decoration: const InputDecoration(
                labelText: 'URL base del servidor',
                hintText: 'http://10.0.2.2:8010',
                helperText: 'Sin /api/v1 — el cliente lo agrega',
              ),
              keyboardType: TextInputType.url,
            ),
            const SizedBox(height: 12),
            FilledButton(
              key: const Key('mobileConfigSave'),
              onPressed: _busy ? null : _save,
              child: const Text('Guardar'),
            ),
            const SizedBox(height: 8),
            OutlinedButton(
              key: const Key('mobileConfigTestConnection'),
              onPressed: _busy ? null : _testHealth,
              child: _busy
                  ? const SizedBox(
                      height: 18,
                      width: 18,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  : const Text('Probar conexión'),
            ),
            if (widget.tenantCodeForHealth == null ||
                widget.tenantCodeForHealth!.trim().isEmpty)
              const Padding(
                padding: EdgeInsets.only(top: 8),
                child: Text(
                  'Health con tenant: ingresá cliente en login antes de probar, o abrí config desde home.',
                  style: TextStyle(fontSize: 12),
                ),
              ),
            if (_message != null) ...[
              const SizedBox(height: 16),
              Text(
                _message!,
                key: const Key('mobileConfigFormMessage'),
                style: TextStyle(color: _isError ? Colors.red : Colors.green),
              ),
            ],
          ],
        ),
      ),
    );
  }
}