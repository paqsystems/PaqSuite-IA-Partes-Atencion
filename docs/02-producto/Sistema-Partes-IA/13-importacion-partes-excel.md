# Importación de partes desde Excel

## Objetivo

Definir, en lenguaje de producto, el proceso de **importación masiva de partes (tareas) desde un archivo Excel** dentro del módulo Partes de Atención.

Este documento es la **definición conceptual de producto**. No es SPEC, HU ni TR; alimenta la posterior generación Open-Spec (flujo SDD / Partes A→C).

La importación **crea registros** en la tabla de partes (misma entidad que la carga diaria). No es edición masiva ni auditoría con mails.

---

## 1. Dónde vive el proceso

### 1.1 Menú / acceso

El proceso **no** agrega un ítem de menú propio en el MVP: se usa **dentro de Carga diaria** (toolbar embebida).

Coherente con la rama operativa de **Carga de Partes** / Carga diaria.

- No pertenece a Informes.
- No pertenece a Seguridad / administración técnica.
- No reemplaza el **proceso masivo** de supervisión (ese actúa sobre tareas ya existentes).

Etiqueta orientativa en toolbar: acciones GEN **Descargar plantilla** / **Importar** (i18n Framework).

### 1.2 Relación con la carga diaria

| Capacidad | Propósito |
|-----------|-----------|
| **Carga diaria** | Alta/edición/baja interactiva en grilla filtrada |
| **Importación Excel** (este doc) | Alta masiva desde plantilla Excel, con validación previa |
| **Proceso masivo** | Cambios en lote sobre tareas ya cargadas |

La importación debe respetar las **mismas reglas de dominio** de una tarea de carga (cliente, tipo, duración en tramos, marcas, etc.), con las particularidades de archivo y lote descritas abajo.

### 1.3 Mobile

**Fuera de alcance en mobile.** Coherente con exclusiones ya definidas (`10-mobile.md`: operaciones con Excel / cargas masivas). Solo web.

---

## 2. Quién puede usarlo

| Actor | ¿Puede importar? |
|-------|------------------|
| Asistente (no supervisor) | Sí |
| Supervisor | Sí |
| Cliente | **No** (coherente con `02-actores-identidad-y-acceso.md`: el cliente no opera carga) |

La importación no está pensada como circuito de solo lectura.

---

## 3. Diseño del archivo Excel

### 3.1 Columnas (títulos de cabecera sugeridos)

La primera fila del archivo es cabecera. Los nombres canónicos (entre paréntesis) son los que el sistema debe reconocer; la etiqueta visible puede localizarse más adelante sin cambiar el contrato.

| Campo de negocio | Título sugerido en Excel | Formato / semántica | ¿Obligatorio en el archivo? |
|------------------|--------------------------|---------------------|----------------------------|
| Código de cliente | `cliente` | Código del maestro de clientes | **Sí** (siempre) |
| Código de asistente | `asistente` | Código del maestro de asistentes | **Según actor** (ver §3.2) |
| Tipo de tarea | `tipo_tarea` | Código del tipo de tarea | **Sí** |
| Fecha | `fecha` | Fecha de trabajo (fecha funcional del parte) | **Sí** |
| Duración | `duracion` | Tiempo en formato **`hh:mm`** | **Sí** |
| Sin cargo | `sin_cargo` | Booleano **verdadero / falso** | **Sí** |
| Presencial | `presencial` | Booleano **verdadero / falso** | **Sí** |
| Descripción de la tarea | `descripcion` | Texto libre (observación del trabajo) | **Sí** |

### 3.2 Identidad en archivo vs. sesión (criterio adoptado)

Criterio de producto (**omitir / forzar desde sesión**, no “obligar y validar igualdad” como regla principal):

Es más simple para el usuario, reduce errores de plantilla y alinea la importación con la carga diaria (la propiedad del asistente no supervisor ya sale de la sesión).

#### Cliente (columna `cliente`)

- Quien importa es siempre **asistente o supervisor**, nunca el actor cliente.
- Por eso la columna **`cliente` es siempre obligatoria** en el Excel: hay que indicar **para qué cliente** es cada parte.
- No hay “forzar cliente de sesión” en este proceso (el asistente/supervisor no tiene un único cliente de identidad).

#### Asistente (columna `asistente`) — **D-IMP-04**

| Quién importa | Columna `asistente` en Excel | Al grabar |
|---------------|------------------------------|-----------|
| **Asistente no supervisor** | **No obligatoria.** Puede omitirse o dejarse vacía. Si viene informada, **debe coincidir** con el código del asistente de la sesión (si difiere → fila inválida). | Se **fuerza** el asistente de la sesión como propietario. |
| **Supervisor** | **Obligatoria.** Debe ser un asistente válido y habilitado. | Se graba el asistente indicado en la fila (el supervisor puede cargar por terceros). |

Así se evita pedir al no-supervisor que repita su propio código en cada fila, sin abrir la puerta a “importar como otro asistente”.

### 3.3 Convenciones de valor

- **`duracion`:** formato visible **`hh:mm`** (igual criterio de presentación que carga diaria). La persistencia sigue en minutos enteros. Debe respetar el **tramo** parametrizable del módulo (default 15 minutos) y ser duración positiva válida.
- **`sin_cargo` / `presencial`:** valores canónicos de producto **`verdadero`** y **`falso`** (sin distinguir mayúsculas en la validación de implementación, a definir en TR).
- **`fecha`:** fecha de trabajo del parte. En el **Excel** se informa según el **formato de fecha configurado / vigente de la aplicación** (mismo criterio de presentación que la UI de carga para el locale del usuario); el sistema **parsea** ese formato. La UI de la app sigue mostrando fechas según locale (`DateBox`), con persistencia interna aparte. Si el motor entrega fecha nativa Excel, también es válida.
- **Códigos** (`cliente`, `asistente` cuando aplica, `tipo_tarea`): deben existir en maestros, estar habilitados para uso y respetar reglas de negocio (p. ej. tipo disponible para el cliente: genéricos + asignados).

---

## 4. Comportamiento de la importación

### 4.1 Resultado sobre datos

Cada fila **válida** que se confirme para grabación genera un **alta** en la tabla de partes (registro de tarea).

- **D-IMP-01 — `es_tarea` forzado a verdadero:** toda fila importada se persiste con **`es_tarea = true`** (tarea de carga). Esta importación **no** crea compras de horas (`es_tarea = false`).
- El estado inicial esperado es coherente con un alta de carga (p. ej. no cerrada), salvo que el SPEC detalle otra regla.
- No es un “staging eterno”: el objetivo es **grabar partes reales** tras validar.

### 4.2 Flujo esperado (lenguaje natural)

1. El usuario está en **Carga diaria** y usa la toolbar de importación Excel.
2. Selecciona / sube el archivo Excel con la plantilla acordada.
3. El sistema **lee y valida** todas las filas de datos (sin grabar todavía el lote completo a ciegas).
4. Presenta un **resultado de validación**: cuántas filas son válidas, cuántas tienen error, y el detalle de errores por fila (mensaje entendible).
5. Según el resultado:
   - **Todas válidas:** permite confirmar y grabar el lote.
   - **Todas con error:** no graba; el usuario corrige el archivo y reintenta.
   - **Mezcla de válidas y con error (D-IMP-02):** el sistema **pregunta** si desea **grabar solo las filas válidas** (descartando las erróneas de este lote) o **cancelar** sin grabar nada.
6. Tras grabar, las tareas quedan disponibles en carga diaria / consultas como cualquier otra tarea con `es_tarea = true`.
7. **D-IMP-08 — Refresco de grilla:** al completar una importación con filas grabadas (lote completo o solo válidas tras confirmación), el sistema debe **refrescar la grilla de carga diaria** aplicando los **filtros vigentes** en esa pantalla (no limpiar ni sustituir el contexto de filtro del usuario). Si la importación se cancela o no graba ninguna fila, no es obligatorio alterar la grilla.

### 4.3 Validación de negocio (orientativa para SPEC)

Sin bajar a contrato técnico aún, una fila es inválida si, entre otras:

- falta un campo obligatorio según el actor (§3.1–§3.2);
- el código de cliente / tipo (y asistente cuando aplica) no existe o no está habilitado;
- asistente no supervisor envió `asistente` distinto al de su sesión;
- el tipo no aplica al cliente de la fila;
- la duración no es `hh:mm` válido o no respeta el tramo;
- `sin_cargo` / `presencial` no son verdadero/falso reconocibles;
- la descripción está vacía.

Los mensajes deben ser **por fila** (y, si aplica, por columna), para que el usuario pueda corregir el Excel.

---

## 5. Relación con el Framework

La capacidad genérica de **importación Excel** del Framework (si el host ya dispone del canal GEN de plantilla / validación / commit) debe **adoptarse y configurarse**, no reinventarse.

Partes declara:

- la **plantilla de columnas** de este documento;
- las **reglas de dominio** Partes (incluye forzar `es_tarea = true` y propietario asistente según sesión);
- la **política de commit parcial** (preguntar al usuario ante mezcla válida/error);
- la **ubicación de menú** bajo Carga de Partes;
- la **exclusión mobile**.

El detalle de endpoints, staging, envelope y UI del wizard GEN se baja en SPEC/HU/TR.

---

## 6. Decisiones de producto cerradas

| ID | Decisión |
|----|----------|
| **D-IMP-01** | Cada alta importada fuerza **`es_tarea = true`**. |
| **D-IMP-02** | Si hay filas válidas y filas con error, **preguntar** al usuario si quiere grabar solo las válidas; si cancela, no se graba ninguna de ese intento. |
| **D-IMP-03** | El proceso se ofrece **embebido en Carga diaria** (web); sin pantalla hermana ni menú aparte en MVP. |
| **D-IMP-04** | **Asistente no supervisor:** columna `asistente` no obligatoria; al grabar se **fuerza** el asistente de sesión. Si el Excel trae `asistente`, debe ser el mismo código de sesión. **Supervisor:** `asistente` obligatorio en archivo. |
| **D-IMP-05** | Plantilla de columnas: `cliente`, `asistente`, `tipo_tarea`, `fecha`, `duracion` (`hh:mm`), `sin_cargo`, `presencial`, `descripcion`. |
| **D-IMP-06** | Campos de negocio obligatorios en archivo salvo la excepción de `asistente` en D-IMP-04. **`cliente` siempre obligatorio** en archivo. |
| **D-IMP-07** | El actor **cliente no importa**. Solo asistente y supervisor. |
| **D-IMP-08** | Tras una importación que **graba** al menos una fila, **refrescar la grilla de carga diaria** con los **filtros vigentes** (sin resetear el contexto de filtro). |
| **D-IMP-09** | Columna `fecha` en Excel según **formato de fecha configurado de la app** (locale/presentación vigente); la app distingue presentación UI vs parseo del archivo. |

---

## 7. Fuera de alcance de esta definición

- Importar compras de horas (`es_tarea = false`).
- Usar la importación para **editar** o **eliminar** partes existentes.
- Envío de mails / auditoría ampliada (sigue siendo línea distinta en `07-fuera-de-alcance-y-evolucion.md`).
- Importación desde mobile.
- Smart Capture / IA rellenando el Excel (evolución aparte).
- Definir aquí el contrato OpenAPI, nombres de SP o pantallas DX al detalle (eso es SPEC/TR).

---

## 8. Ambigüedades / puntos a cerrar en SDD

1. **Tamaño máximo de archivo / tope de filas** y si el commit parcial es atómico por lote o fila a fila (criterio de falla a mitad de grabación).
2. **Plantilla descargable** oficial desde la pantalla (Should recomendado); conviene una variante “asistente” (sin exigir `asistente`) y una “supervisor”.
3. **Sinónimos de booleano** (`true`/`false`, `1`/`0`, `sí`/`no`) además de verdadero/falso: ¿Must o solo Should?

*(Cerrado: actor cliente no importa; criterio de `asistente` por sesión vs. supervisor — D-IMP-04 / D-IMP-07.)*

---

## 9. Impacto esperado en la carpeta producto

Al adoptar esta definición:

- deja de ser “evolución abierta” genérica la **carga masiva desde Excel** de partes de tarea;
- la **auditoría con Excel + mails** sigue siendo evolución distinta;
- checklist y `07-fuera-de-alcance-y-evolucion.md` deben apuntar a este documento.

Siguiente paso metodológico: **D implementado** (2026-08-02); continuar **E (tests/smoke) → F1**.

Trazabilidad OpenSpec:

| Artefacto | Enlace |
|-----------|--------|
| SPEC-009 | [`SPEC-009-importacion-partes-excel.md`](../../05-open-spec/100-SistemaPartes/SPEC-009-importacion-partes-excel.md) |
| HU-009 | [`HU-009-importacion-partes-excel.md`](../../03-historias-usuario/100-SistemaPartes/HU-009-importacion-partes-excel.md) |
| TR-009 | [`TR-009-importacion-partes-excel.md`](../../04-tareas/100-SistemaPartes/TR-009-importacion-partes-excel.md) |
