# SPEC-009 – Importación de partes desde Excel

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-009 |
| Título | Importación de partes (tareas) desde Excel bajo Carga de Partes |
| Épica / carpeta | `100-SistemaPartes` |
| Estado | Pendiente |
| Última actualización | 2026-08-02 |
| HU relacionada(s) | [HU-009-importacion-partes-excel](../../03-historias-usuario/100-SistemaPartes/HU-009-importacion-partes-excel.md) |
| TR relacionada(s) | [TR-009-importacion-partes-excel](../../04-tareas/100-SistemaPartes/TR-009-importacion-partes-excel.md) |
| Depende de | [SPEC-001](./SPEC-001-modelo-datos-modulo.md), [SPEC-002](./SPEC-002-identidad-funcional-y-acceso.md), [SPEC-003](./SPEC-003-maestros-y-catalogos.md), [SPEC-004](./SPEC-004-operacion-carga-diaria.md) (grilla/filtros/validaciones de tarea) |
| Fuentes | [`13-importacion-partes-excel.md`](../../02-producto/Sistema-Partes-IA/13-importacion-partes-excel.md) (D-IMP-01…08); [`05-operacion-diaria-y-supervision.md`](../../02-producto/Sistema-Partes-IA/05-operacion-diaria-y-supervision.md); Framework [`14-importaciones-excel.md`](../../../PaqSuite-IA-FRAMEWORK/docs/02-producto/14-importaciones-excel.md) / SPEC-001-14 |

---

## 1. Resumen ejecutivo

- **Problema:** registrar muchas tareas de partes una a una en la grilla es costoso cuando el dato ya está en planillas.
- **Resultado esperado:** en **Carga de Partes** (web), el asistente/supervisor puede **importar altas** desde Excel con plantilla fija, validación por fila, política de **procesamiento parcial** y grabación real en `PQ_PARTES_REGISTRO_TAREA` con **`es_tarea = true`**, alineado al motor GEN de importaciones Excel y a las reglas de carga diaria (SPEC-004).

---

## 2. Alcance

### 2.1 En alcance

- Adopción del **motor GEN** de importaciones Excel (`ExcelImportToolbar` / modal / plantilla / staging / process) **embebido únicamente** en la pantalla de **carga diaria** (misma ruta/pantalla SPEC-004; **sin** pantalla hermana ni ítem de menú aparte en este MVP).
- Catálogo de proceso importable Partes (seed/SQL): código de proceso **`partes.tareas.import`** (TR-009), columnas D-IMP-05, **`permiteProcesamientoParcial = true`**.
- Handler de producto que valida filas y, al procesar, **inserta** tareas con las mismas reglas de dominio que un alta de SPEC-004 (códigos → ids, tramo de duración, universo de tipos, etc.).
- Forzar **`es_tarea = 1`** en toda fila grabada (D-IMP-01).
- Reglas de columna **`asistente`** según actor (D-IMP-04).
- Columna **`cliente` siempre obligatoria** en archivo (D-IMP-06).
- Actor **cliente no importa** (D-IMP-07); API/menú deniegan.
- Tras grabar ≥1 fila: **refrescar la grilla de carga diaria con filtros vigentes** (D-IMP-08).
- Solo **web**; exclusión mobile (D-IMP-03 / SPEC-007).
- i18n (`excelImport.*` GEN + claves Partes de validación de fila) + `data-testid` GEN `excelImport.*`.

### 2.2 Fuera de alcance

- Importar compras / movimientos con `es_tarea = false`.
- Editar o eliminar partes existentes vía Excel.
- Auditoría con mails / consulta auditora ampliada (`07-fuera-de-alcance…`).
- Mobile / Capacitor (exclusión Excel / cargas masivas).
- Smart Capture / IA rellenando el archivo.
- ABM web del catálogo GEN de procesos Excel.
- Reemplazar o redefinir el motor GEN-14 (solo adoptar + handler Partes).
- Proceso masivo de supervisión (SPEC-005).

---

## 3. Actores y contexto

| Actor | Importación Excel |
|-------|-------------------|
| Asistente (`esSupervisor = false`) | Sí |
| Supervisor (`esSupervisor = true`) | Sí |
| Cliente | **No** (menú/ruta no expuestos; API deniega) |

Precondiciones: sesión Partes usable (SPEC-002); maestros usables (SPEC-003); pantalla de carga diaria operativa (SPEC-004); capacidad GEN Excel habilitada en la instalación (`ExcelImportEnabled` / equivalente).

---

## 4. Comportamiento funcional

### 4.1 Ubicación UX (Must)

1. La importación **solo** se ofrece **embebida en Carga diaria** (toolbar en la misma pantalla SPEC-004).
2. **No** hay pantalla hermana ni entrada de menú adicional dedicada en este MVP (el usuario llega por Carga diaria).
3. La UI canónica es el **componente embebido GEN** (`ExcelImportToolbar`: Descargar plantilla | Importar) en **fila exclusiva** (norma Framework 14: no compartir fila con filtros de la grilla).
4. Flujo: plantilla → subir `.xlsx` → validar/staging → ver errores por fila → **Procesar** (si hay válidas según política parcial) → `onComplete` en el host → refresco §4.6.

### 4.2 Plantilla de columnas (Must — D-IMP-05)

Cabecera canónica (primera fila):

| Columna Excel | Semántica | Obligatorio en archivo |
|---------------|-----------|------------------------|
| `cliente` | Código cliente | Sí (siempre) |
| `asistente` | Código asistente | Según §4.3 |
| `tipo_tarea` | Código tipo de tarea | Sí |
| `fecha` | Fecha de trabajo | Sí — ver §4.4.1 |
| `duracion` | `hh:mm` (ej. `00:30`) o minutos enteros (ej. `30`); media hora = `00:30` o `30`; múltiplo del tramo (p. ej. 15) | Sí |
| `sin_cargo` | `verdadero` / `falso` | Sí |
| `presencial` | `verdadero` / `falso` | Sí |
| `descripcion` | Texto observación | Sí |

Persistencia al grabar: mismos campos de negocio que SPEC-004 (`cliente_id`, `usuario_id` propietario, `tipo_tarea_id`, `fecha`, `duracion_minutos`, `sin_cargo`, `presencial`, `observacion`, `cerrado = 0` en alta, **`es_tarea = 1`**).

### 4.3 Identidad archivo vs. sesión (Must — D-IMP-04 / D-IMP-07)

| Quién importa | Columna `asistente` | Al procesar / grabar |
|---------------|---------------------|----------------------|
| Asistente no supervisor | No obligatoria (omitida o vacía OK). Si viene valor ≠ código de sesión → **fila inválida**. | **Fuerza** `usuario_id` = `asistenteId` de sesión. |
| Supervisor | **Obligatoria**; asistente usable (activo / no inhabilitado). | Graba el asistente de la fila. |
| Cliente | N/A — no usa el proceso. | — |

Columna `cliente`: siempre obligatoria; resuelve a cliente usable; tipo debe pertenecer al universo del cliente (SPEC-003 / SPEC-004).

### 4.4 Validación de fila (Must)

Una fila es inválida si, entre otras:

- falta campo obligatorio según §4.2–§4.3;
- código de cliente / tipo / asistente (cuando aplica) inexistente o no usable;
- tipo ∉ universo del cliente de la fila;
- `fecha` no interpretable según §4.4.1;
- `duracion` no parseable como `hh:mm` ni como minutos enteros (ni serial de hora Excel), ≤ 0, no múltiplo del tramo (`PQ_PARAMETROS_GRAL`, default 15) o > 1440 minutos equivalentes;
- `sin_cargo` / `presencial` no reconocibles como verdadero/falso (Must mínimo: esos literales, case-insensitive; sinónimos adicionales = Should §7);
- `descripcion` vacía o solo whitespace;
- asistente no supervisor con `asistente` distinto al de sesión.

Errores: **por fila** (nº de fila + mensaje i18n), según UX GEN de grilla de errores.

Filas Excel **totalmente vacías** (sin valores en columnas de la plantilla): se **ignoran** (no cuentan como error ni como válida).

Filas con el mismo contenido que otra ya existente o repetida en el archivo: **se permiten** como altas independientes (no hay deduplicación Must).

#### 4.4.1 Formato de `fecha` en Excel (Must — cierre A1)

Hay que distinguir:

| Ámbito | Formato |
|--------|--------|
| **App (UI carga diaria)** | Presentación según locale / `DateBox` (p. ej. `dd/MM/yyyy` en `es`); persistencia interna ISO. |
| **Excel (columna `fecha`)** | El usuario carga la fecha en el **formato de fecha configurado / vigente de la aplicación** (mismo criterio de presentación que la UI de carga para el locale activo del usuario que importa). El handler **parsea** según ese formato configurado. |

Además:

- Si el motor GEN entrega la celda ya como **fecha nativa** (serial Excel / tipo fecha), se acepta sin exigir texto con un único patrón ISO.
- **No** es Must exigir solo `yyyy-MM-dd` en el archivo.
- Fecha futura: misma política que SPEC-004 (**no bloqueo duro**); sin confirmación interactiva por fila en MVP.

### 4.5 Procesamiento y parcial (Must — D-IMP-01 / D-IMP-02)

1. Validación de lote **antes** de grabar el conjunto.
2. Flag de proceso **`permiteProcesamientoParcial = true`** (allowPartial):
   - `validRows = 0` → no se puede Procesar;
   - solo errores → no graba;
   - todas válidas → Procesar graba todas las válidas;
   - **mezcla válidas + errores** → el usuario puede **Procesar** (acción explícita = confirmación de grabar solo válidas). Cancelar / cerrar sin Procesar → **no graba** ninguna fila de ese intento.
3. Cada fila procesada inserta un registro de tarea; **`es_tarea = 1`** siempre.
4. Acceso de negocio vía **SP** (MUST BASE); no Eloquent CRUD de dominio en API nueva.
5. Si el Procesar falla a mitad del lote de válidas: el host **no** debe mostrar éxito silencioso; preferible **transacción atómica** del conjunto de válidas del batch (detalle en TR). Si GEN solo permite commit fila a fila, documentar en TR el criterio de compensación / mensaje de error parcial.

### 4.6 Post-importación (Must — D-IMP-08)

Si el procesamiento **graba al menos una fila**, el host debe **volver a cargar** el listado de la grilla de carga diaria con los **filtros vigentes** (fechas, cliente, asistente, estado, etc. — sin resetear el contexto). Si no hubo grabación, no es obligatorio alterar la grilla.

### 4.7 Reglas numeradas

| ID | Regla |
|----|--------|
| R-IMP-01 | Importación = altas de tarea vía Excel **embebida en Carga diaria**; adopta GEN-14, no lo reimplementa. Sin pantalla hermana ni menú aparte (MVP). |
| R-IMP-02 | Cliente funcional no importa; API/menú deniegan. |
| R-IMP-03 | Plantilla fija §4.2; `cliente` siempre obligatorio en archivo. |
| R-IMP-04 | Asistente no supervisor: `asistente` opcional; fuerza sesión; si informado debe coincidir. Supervisor: `asistente` obligatorio. |
| R-IMP-05 | Toda fila grabada persiste `es_tarea = 1`. |
| R-IMP-06 | Validaciones de dominio alineadas a SPEC-004 (tramo, universo tipos, observación, usabilidad maestros). |
| R-IMP-07 | `permiteProcesamientoParcial = true`; Procesar con errores + válidas = grabar solo válidas; sin Procesar = cero grabados. |
| R-IMP-08 | Tras grabar ≥1 fila → refrescar grilla carga diaria con filtros vigentes. |
| R-IMP-09 | Solo web; no mobile. |
| R-IMP-10 | Toolbar Excel en fila exclusiva; i18n + testids GEN. |
| R-IMP-11 | Persistencia de negocio vía SP (MUST). |
| R-IMP-12 | `fecha` en Excel según **formato de fecha configurado de la app** (locale / presentación vigente); parseo en handler; fecha nativa Excel aceptada si GEN la entrega tipada. |
| R-IMP-13 | Filas Excel totalmente vacías se ignoran; no hay deduplicación Must entre filas/importaciones. |

---

## 5. Criterios verificables

- [ ] Menú / entrada bajo Carga de Partes; cliente no ve ni puede invocar el proceso.
- [ ] Toolbar GEN (plantilla + importar) en fila propia **en Carga diaria** (sin ruta/menú aparte).
- [ ] Plantilla descarga columnas canónicas §4.2.
- [ ] Fechas en Excel aceptadas según formato de fecha configurado de la app (y/o fecha nativa Excel); UI de carga sigue mostrando según locale.
- [ ] Asistente no supervisor importa sin columna `asistente` (o vacía) y las tareas quedan a su nombre.
- [ ] Asistente no supervisor con `asistente` ≠ sesión → filas en error; no se graban esas filas.
- [ ] Supervisor requiere `asistente` válido por fila.
- [ ] `cliente` faltante → error de fila.
- [ ] Filas grabadas tienen `es_tarea = true` y aparecen en carga diaria / consultas de tareas.
- [ ] Lote mixto: sin Procesar → 0 altas; con Procesar → solo válidas.
- [ ] Tras Procesar con altas > 0, la grilla se refresca manteniendo filtros previos.
- [ ] Mobile: proceso no expuesto / policy excluye Excel.
- [ ] Feature/API o tests de handler cubren al menos: OK mínimo, error de código, parcial, fuerza `es_tarea`, fuerza propietario no-supervisor.

---

## 6. Impacto técnico (visión para TR)

| Capa | Impacto |
|------|---------|
| DB / seed | Proceso + campos en catálogo Excel GEN (`PQ_EXCEL_*` o equivalente host); scripts SP de insert tarea reutilizando familia carga diaria si existe |
| Backend | Registro handler Partes; endpoints GEN Excel ya del paquete; validación fila → staging; process → insert SP; gate roles |
| Frontend | Montar `ExcelImportToolbar` **solo** en `CargaDiariaPage`; `onComplete` → `load()` filtros actuales; i18n; sin ítem menú extra |
| Mobile | Exclusión en policy / menú |
| Config | Flag capacidad Excel import de instalación |
| Docs | Manual usuario: cómo importar; enlace producto 13 |

`processCode` definitivo (TR-009): **`partes.tareas.import`**. Authz: permiso menú **`partes_carga_diaria`**.

---

## 7. Riesgos y supuestos

| Tema | Tratamiento |
|------|-------------|
| Motor GEN disponible vía `@paqsuite/react-core` / `laravel-core` | Supuesto: path packages Framework ya en el host (como chat/LLM). |
| Confirmación “preguntar” vs. botón Procesar GEN | Must: acción explícita Procesar con `allowPartial=true` cumple D-IMP-02; dialog extra = Should. |
| Tope de filas / async | Umbrales GEN; si el lote es grande puede ir `queued` — host debe manejar `onComplete` queued/partial/done sin romper refresco (detalle TR). |
| Plantilla distinta asistente vs supervisor | Should: una plantilla con `asistente` opcional documentada; o dos descargas. |
| Sinónimos booleano (`sí`/`1`/`true`) | Should; Must = `verdadero`/`falso` case-insensitive. |
| Fecha en Excel | Must: formato de presentación vigente de la app (locale) + fecha nativa si GEN tipifica; UI app independiente en presentación. |
| Fecha futura en lote | No bloqueo duro (SPEC-004); sin confirmación por fila en MVP. |

---

## 8. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-08-02 | Parte A: SPEC-009 desde `13-importacion-partes-excel.md` (D-IMP-01…08) + adopción GEN-14. |
| 2026-08-02 | A1: embebida solo en Carga diaria; `fecha` Excel = formato configurado de app (+ nativa GEN); filas vacías ignoradas; sin dedupe Must. |
| 2026-08-02 | Parte B: enlazada HU-009. |
| 2026-08-02 | Parte B1: HU-009 enriquecida. |
| 2026-08-02 | Parte C: enlace TR-009. |
