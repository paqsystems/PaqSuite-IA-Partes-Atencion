# Partes-Atención — mobile Flutter

Cliente nativo **MONO** para Partes v1 mobile: carga individual, consulta detallada (kardex), paquete de horas.

## Requisitos

- Flutter SDK estable (≥ 3.5)
- Backend Laravel del repo (`backend/`) en ejecución local

## Desarrollo local

```powershell
cd mobile
flutter pub get
flutter run
```

### Emulador Android → API en el PC

- URL en configuración (engranaje): `http://10.0.2.2:8010` (sin `/api/v1`)
- Tenant en login: código instalación (ej. `DEV`)
- Health y APIs usan header `X-Paq-Cliente`

## Comandos

| Comando | Uso |
|---------|-----|
| `flutter analyze` | Análisis estático |
| `flutter test` | Unit tests |
| `flutter test integration_test` | Smoke integración |

## Alcance scaffold

- `core/`: envelope GEN-20, HTTP, auth, config, secure storage
- `features/`: login tenant-first, config + health, menú filtrado v1, placeholders de proceso
- Sin `@paqsuite/react-core`

Runbook: `docs/06-operacion/runbook-smoke-mobile-flutter-partes.md`