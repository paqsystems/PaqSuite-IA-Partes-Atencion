# Smoke — Flutter mobile Partes-Atención

Cliente: `mobile/` (Flutter nativo). Backend: mismo Laravel del repo.

## Prerrequisitos

- Flutter SDK estable
- Stack local: `.\scripts\dev\start-dev.ps1 -All -SkipSqlSync` (o equivalente)
- Puerto backend habitual en lab: **8010** (ajustar según `.env`)

## Emulador Android

| Campo | Valor |
|-------|--------|
| URL API (engranaje) | `http://10.0.2.2:8010` |
| Backend local | `start-dev.ps1` con `--host=0.0.0.0` en puerto 8010 (accesible desde emulador) |
| Tenant (login) | Código instalación (ej. `DEV`) |
| Usuario / clave | Usuario SQL de la instalación |

El cliente agrega `/api/v1` en las rutas. No escribir `/api/v1` en la URL guardada.

## Comandos

**Emulador + app Partes (release, sin smoke):**

```powershell
.\scripts\dev\run-mobile-emulador-partes.ps1
```

Debug con hot reload:

```powershell
.\scripts\dev\run-mobile-emulador-partes.ps1 -Mode debug
```

Manual:

```powershell
cd mobile
flutter pub get
flutter analyze
flutter test
flutter run --release -d emulator-5554
```

APK debug:

```powershell
flutter build apk --debug
```

## Smoke manual (mínimo)

1. Config → guardar URL → probar conexión (con tenant en login para health con header).
2. Login tenant + usuario + contraseña.
3. Menú: solo procesos v1 (carga, consulta detallada, paquete horas).
4. Abrir cada proceso y validar datos reales: carga individual, consulta
   detallada kardex y paquete de horas.

## Resultado F1 — 28/11/2026

- Smoke ejecutado en emulador Android con Flutter nativo.
- Carga individual: nueva tarea, catálogo de asistentes y selección de
  `PQ — PaqSystems` verificados.
- El APK release fue compilado e instalado correctamente.
- Tests automatizados: `flutter analyze` sin issues y `flutter test` con 10
  tests aprobados.

## iOS / dispositivo físico

- Dispositivo físico: IP LAN del PC, ej. `http://192.168.x.x:8010`.
- Cleartext HTTP: solo desarrollo; release debe usar HTTPS.