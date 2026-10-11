# Verificación F1 + F — Control de Calidad PQ #5 (05/10/2026)

| Campo | Valor |
|-------|--------|
| **Control** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) · CC #5 |
| **Fecha control** | 05/10/2026 |
| **Fecha verificación** | 11/10/2026 |
| **Parte I (unificación)** | Ejecutada 11/10/2026: SPEC/HU/TR promovidos a originales bajo `001-Generalidades` y los `*-update` eliminados. Originales en **Finalizado**. |

## TRs / HUs / SPECs cubiertos

| Original (tras Parte I) | Alcance CC #5 |
|-------------------------|----------------|
| [TR-001](../../001-Generalidades/TR-001-ui-errores-validacion-contexto-carga.md) · [HU-001](../../../03-historias-usuario/001-Generalidades/HU-001-ui-errores-validacion-contexto-carga.md) · [SPEC-001](../../../05-open-spec/001-Generalidades/SPEC-001-ui-errores-validacion-contexto-carga.md) | Errores de validación/guardado visibles dentro del modal de carga (ABM Usuarios y patrón transversal) |

---

# F1 — Verificación del agente

## Resultado

- **Aprobado.** El caso CC (política de contraseña al alta de usuario) muestra el mensaje **dentro** del Popup `Nuevo usuario` (`adminUsuariosFormError`) y **no** en el listado (`adminUsuariosListError` ausente). El modal permanece abierto.

## Evidencia revisada

| AC | Evidencia | Estado |
|----|-----------|--------|
| AC-U01 | Playwright `partes-admin-usuarios-form-error.spec.ts`: POST `/admin/usuarios` con envelope `error≠0` / `auth.password.policyUnsafe` → alerta en el formulario del Popup; listado sin alerta | OK |
| AC-U01 (unit) | Vitest `UsuariosAdminPage.test.tsx`: `adminUsuariosFormError` presente, `adminUsuariosListError` ausente | OK |
| AC-U02 / AC-U03 | Código: `FormContextErrorAlert` en `MaestroCrudPage`, `ClienteTiposTareaPage`, `PermisosAdminPage` y restantes ABM del inventario TR | OK (revisión de código) |
| AC-U04 | `shouldShowPageListError(formOpen, listError)` + alerta de listado solo si el modal está cerrado | OK |
| AC-U05 | Vitest (4 tests: página + helper) + E2E Playwright del alta inválida en verde | OK |

Comandos: `npx vitest run src/features/admin/security/UsuariosAdminPage.test.tsx src/shared/ui/modalFormErrors.test.ts` (4 passed) y `npx playwright test tests/e2e/partes-admin-usuarios-form-error.spec.ts` (1 passed).

Nota: el login contra SQL Server local (RDS) no respondió en esta sesión; el smoke de UI usó sesión e interceptación de API, equivalente al contrato de pantalla del CC.

---

# F — vs SPEC / HU / TR

## Resumen ejecutivo

La implementación sigue el SPEC/HU/TR del CC #5 y la regla BASE 36: el error de guardado vive en el contexto del Popup. Completitud del ítem CC (ABM Usuarios) cubierta por E2E. El patrón transversal está cableado en los ABM del inventario TR.

## Completitud

| Ítem CC | Estado |
|---------|--------|
| Error de validación al guardar visible en el modal de carga | ✓ |
| No mostrar ese error solo en el layout de la página de fondo | ✓ |
| Mismo criterio en procesos de carga en modal (regla 36) | ✓ (código + helper compartido) |

## Cierre

- Updates marcados **Finalizado** y **Parte I** ejecutada el 11/10/2026.
- Originales: [SPEC-001](../../../05-open-spec/001-Generalidades/SPEC-001-ui-errores-validacion-contexto-carga.md) · [HU-001](../../../03-historias-usuario/001-Generalidades/HU-001-ui-errores-validacion-contexto-carga.md) · [TR-001](../../001-Generalidades/TR-001-ui-errores-validacion-contexto-carga.md).
