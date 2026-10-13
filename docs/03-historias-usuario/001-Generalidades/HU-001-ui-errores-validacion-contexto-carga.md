# HU-001 – Errores de validación visibles en el modal de carga

Jira: PIAPARTES-27


## Metadatos

| Campo | Valor |
|-------|-------|
| ID | HU-001-ui-errores-validacion-contexto-carga |
| Título | Errores de guardado en el contexto del modal de alta/edición |
| Épica / carpeta | `001-Generalidades` |
| Clasificación | MUST-HAVE |
| Estado | Finalizado |
| Última actualización | 2026-10-11 |
| SPEC origen | [SPEC-001](../../05-open-spec/001-Generalidades/SPEC-001-ui-errores-validacion-contexto-carga.md) |
| TR relacionada(s) | [TR-001](../../04-tareas/001-Generalidades/TR-001-ui-errores-validacion-contexto-carga.md) |

## Origen

| Campo | Valor |
|-------|-------|
| Control | `00-ControlCalidad-PQ` |
| Fecha | 05/10/2026 |
| Ítem | Control de Calidad #5 · ABM Usuarios |

---

## Narrativa

Como administrador que da de alta un usuario en un modal  
quiero ver los errores de validación (por ejemplo, contraseña no conforme) **dentro del mismo modal**  
para corregir los datos sin confundir el mensaje con la pantalla de fondo.

---

## Alcance incluido

- Errores de **guardado** en Popups de ABM (usuarios y demás pantallas con el anti-patrón detectado).
- Regla transversal aplicable a **todos los procesos de carga en modal** del host (ver SPEC y regla BASE 36).

## Fuera de alcance

- Cambiar reglas de política de contraseña en backend.
- Errores de carga del listado inicial.

---

## Reglas de negocio / UX

| ID | Regla |
|----|--------|
| R-UI-ERR-01 … R-UI-ERR-06 | Ver [SPEC-001](../../05-open-spec/001-Generalidades/SPEC-001-ui-errores-validacion-contexto-carga.md) §4.1 |

---

## Criterios de aceptación

- [x] **CA-U01** Crear usuario con contraseña inválida: el texto de error aparece en el Popup, con `data-testid` `adminUsuariosFormError` (o equivalente documentado en TR).
- [x] **CA-U02** El Popup no se cierra automáticamente ante error de validación.
- [x] **CA-U03** Al cancelar/cerrar el Popup, desaparece el error del formulario; un nuevo alta no muestra el error anterior.
- [x] **CA-U04** ABM maestros Partes y admin roles/permisos/empresas cumplen el mismo criterio en sus modales de guardado.
- [x] **CA-U05** Regresión: carga diaria sigue mostrando errores de tarea solo en el contexto del modal de tarea.

---

## Escenarios Gherkin

```gherkin
Scenario: Contraseña inválida al crear usuario
  Given estoy en el ABM Usuarios con el modal "Nuevo usuario" abierto
  And completo usuario, nombre y email válidos
  And ingreso una contraseña que no cumple la política
  When guardo
  Then el modal sigue abierto
  And veo el mensaje de error dentro del modal
  And no veo ese mensaje como único aviso bajo el título de la página detrás del overlay

Scenario: Error de guardado en maestro con modal abierto
  Given el modal de alta de un maestro Partes está abierto
  When el servidor rechaza el guardado con un error de envelope
  Then el mensaje se muestra dentro del modal de ese maestro
```

---

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-10-05 | Volcado Parte G desde CC-PQ #5 (HU-update). |
| 2026-10-11 | F1/F Aprobado. Parte I: promovido a original `001-Generalidades`; update eliminado. Estado → Finalizado. |
