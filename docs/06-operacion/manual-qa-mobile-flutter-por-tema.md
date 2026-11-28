# QA manual — Flutter Partes (por tema)

Usar con backend local (`start-dev.ps1 -All -SkipSqlSync`, puerto **8010**) y emulador Android.

**URL:** `http://10.0.2.2:8010` · **Tenant:** `DEV` (ajustar lab).

| Tema | Qué probar | OK |
|------|------------|-----|
| **T1 Config** | Engranaje → guardar URL → health OK con tenant en login | ☐ |
| **T2 Login** | Tenant + user + pass → home con tenant visible | ☐ |
| **T3 Menú** | Drawer: solo 3 procesos v1; sin admin/pivot | ☐ |
| **T4 Carga** | Listado del día; alta tarea mínima; error en modal si falla guardado | ☐ |
| **T5 Consulta** | Filtros fechas → tarjetas; tap → detalle | ☐ |
| **T6 Paquete** | Rango fechas → tarjetas desglose; saldo coherente | ☐ |
| **T7 Logout** | Cierra sesión → vuelve a login | ☐ |
| **T8 Sesión** | Tras login, cerrar app (swipe away) y reabrir → sigue en home sin pedir credenciales (o vuelve a login con tenant/URL recordados) | ☐ |
| **T9 Health KO** | Config: URL imposible → «Probar conexión» muestra error; no guarda si falla validación acordada | ☐ |
| **T10 Navegación** | Entrar a los 3 procesos y volver (back / drawer) sin pantalla en blanco ni crash | ☐ |
| **T11 Carga datos** | Carga: lista del día responde; si perfil tiene cliente fijo, tipos cargan | ☐ |
| **T12 Consulta datos** | Consulta: rango con datos → tarjetas; detalle abre y muestra campos clave | ☐ |

Automático previo a cada sesión manual:

```powershell
cd mobile
flutter analyze
flutter test
```

## Resultado F1 — 28/11/2026

La batería funcional fue validada por el usuario en el emulador Android Flutter.
La evidencia formal y el detalle del smoke E2E están en
[`D-VERIFICACION-TR-007-flutter-native-v1.md`](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-TR-007-flutter-native-v1.md).

Resultado automatizado: `flutter analyze` sin issues y `flutter test` con 10
tests aprobados.