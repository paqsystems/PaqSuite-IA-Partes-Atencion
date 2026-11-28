import 'package:flutter/material.dart';

import '../../core/api/envelope_message.dart';
import '../../core/auth/auth_service.dart';
import '../../core/config/config_service.dart';

class LoginPage extends StatefulWidget {
  const LoginPage({
    super.key,
    required this.configService,
    required this.authService,
    required this.onLoggedIn,
    required this.onOpenConfig,
  });

  final ConfigService configService;
  final AuthService authService;
  final void Function(AuthSession session) onLoggedIn;
  final void Function({String? tenantCode}) onOpenConfig;

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final _tenantController = TextEditingController();
  final _usernameController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _busy = false;
  String? _formError;

  Future<void> _submit() async {
    setState(() {
      _busy = true;
      _formError = null;
    });
    try {
      final baseUrl = await widget.configService.getApiBaseUrl();
      if (baseUrl == null || baseUrl.isEmpty) {
        throw StateError('Configurá la URL de la API (engranaje)');
      }
      final envelope = await widget.authService.login(
        baseUrl: baseUrl,
        tenantCode: _tenantController.text.trim(),
        username: _usernameController.text.trim(),
        password: _passwordController.text,
      );
      if (!envelope.isSuccess) {
        setState(() => _formError = resolveEnvelopeUserMessage(envelope));
        return;
      }
      final session = await widget.authService.loadSession();
      if (session != null) {
        widget.onLoggedIn(session);
      }
    } catch (error) {
      setState(() => _formError = resolveCaughtErrorMessage(error));
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  void _openConfig() {
    widget.onOpenConfig(tenantCode: _tenantController.text.trim());
  }

  @override
  void dispose() {
    _tenantController.dispose();
    _usernameController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Login — Partes'),
        actions: [
          IconButton(
            key: const Key('mobileConfigOpen'),
            icon: const Icon(Icons.settings),
            onPressed: _openConfig,
            tooltip: 'Configuración API',
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            TextField(
              key: const Key('loginTenant'),
              controller: _tenantController,
              decoration: const InputDecoration(labelText: 'Cliente (tenant)'),
              textInputAction: TextInputAction.next,
            ),
            const SizedBox(height: 12),
            TextField(
              key: const Key('loginUsername'),
              controller: _usernameController,
              decoration: const InputDecoration(labelText: 'Usuario'),
              textInputAction: TextInputAction.next,
            ),
            const SizedBox(height: 12),
            TextField(
              key: const Key('loginPassword'),
              controller: _passwordController,
              decoration: const InputDecoration(labelText: 'Contraseña'),
              obscureText: true,
              onSubmitted: (_) => _busy ? null : _submit(),
            ),
            if (_formError != null) ...[
              const SizedBox(height: 12),
              Text(
                _formError!,
                key: const Key('loginFormError'),
                style: const TextStyle(color: Colors.red),
              ),
            ],
            const SizedBox(height: 8),
            OutlinedButton.icon(
              key: const Key('loginOpenConfig'),
              onPressed: _busy ? null : _openConfig,
              icon: const Icon(Icons.settings_outlined),
              label: const Text('Configurar URL del servidor'),
            ),
            const SizedBox(height: 16),
            FilledButton(
              key: const Key('loginSubmit'),
              onPressed: _busy ? null : _submit,
              child: _busy
                  ? const SizedBox(
                      height: 18,
                      width: 18,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  : const Text('Ingresar'),
            ),
          ],
        ),
      ),
    );
  }
}