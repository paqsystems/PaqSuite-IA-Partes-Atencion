# Verificación F1 — TR-007 Mobile Capacitor

| Campo | Valor |
|-------|-------|
| **TR / HU / SPEC** | [TR-007](../../100-SistemaPartes/TR-007-mobile-capacitor.md) · [HU-007](../../../03-historias-usuario/100-SistemaPartes/HU-007-mobile-capacitor.md) · [SPEC-007](../../../05-open-spec/100-SistemaPartes/SPEC-007-mobile-capacitor.md) |
| **Fecha verificación** | 2026-09-24 |
| **Entorno** | Windows 10 · Node.js 24.11.1 · frontend repo-lab |
| **Resultado F1** | **Pendiente de revisión manual** |

## Evidencia ejecutada

| Criterio | Comando / evidencia | Resultado |
|----------|---------------------|-----------|
| Build mobile | `npm run build:mobile` | OK — Vite built en 37.45 s |
| Sincronización Capacitor | `npx cap sync` | OK — Android actualizado; plugin Preferences detectado |
| Policy y mapper mobile | `npx vitest run src/features/partes/mobile src/features/auth/partesMenuI18n.test.ts src/features/auth/partesMenuSidebar.test.ts` | OK — 2 archivos, 6 tests |
| SDK / i18n de grilla | `@paqsuite/react-core` 2.4.14 local; `registerGridI18nResources(i18n, 'common')` y `syncDevExtremeLocale` presentes | OK |

## Smoke manual de emulador

No verificado en esta sesión. `adb` no está disponible en el PATH y no se detectó un emulador Android accesible desde el entorno.

Quedan sin evidencia manual:

1. Configuración de URL, health y persistencia.
2. Login con empresa (`loginTenant`) y header `X-Paq-Cliente`.
3. Dashboard, kardex, alta/edición y lectura de tarea cerrada.
4. Informe Paquete de Horas con gráfico.
5. Menú native filtrado y chat in-app.

## Observaciones técnicas

- `npm run typecheck` no es verde en el repo-lab: reporta errores del checkout Framework, duplicación de tipos `i18next`/DevExtreme entre ambos repositorios y errores host preexistentes. No se modificó código del Framework para ocultarlos.
- La suite completa `npm run test` no finalizó; fue detenida tras permanecer bloqueada. La corrida focalizada mobile/i18n sí finalizó correctamente.
- La eliminación de `frontend/src/features/auth/shellI18n.ts` queda pendiente hasta que `react-core` publique exports equivalentes a `createAppTranslator` y `buildMenuSidebarLabels`.

## Estado recomendado

Mantener TR-007 y HU-007 en **Pendiente de Revisión** hasta ejecutar el humo manual en emulador o dispositivo. No marcar F1 como aprobado ni pasar los documentos a `Finalizado` en esta sesión.
