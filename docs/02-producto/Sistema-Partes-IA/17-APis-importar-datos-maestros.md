# APIs para importar datos maestros

Jira: PIAPARTES-46

## Objetivo

Definir, en lenguaje de producto, las **APIs de integración** para **alta y actualización masiva de maestros** del módulo Partes de Atención, alimentadas por un sistema externo (ERP, CRM u otro origen).

Este documento es la **definición conceptual de producto**. No es SPEC, HU ni TR; alimenta la posterior generación Open-Spec (flujo SDD / Partes A→C).

Las APIs **crean o actualizan** las mismas entidades que el ABM de Archivos. No reemplazan las pantallas ABM. No importan partes (tareas) ni paquetes de horas.

[Proceso] de [host Partes]: **APIs de dominio** de upsert de maestros. UI/motor GEN-14 **no** aplica (eso es Excel de **tareas** en `13`). Envelope = GEN-20. Acceso datos = SP (regla 74). Host: contratos REST, claves naturales `code`, permiso de integración, catálogo y menú no obligatorio.

SoT de reglas de maestro: [`04-maestros-y-catalogos.md`](./04-maestros-y-catalogos.md) y SPEC-003 (ABM). Este doc **no** reabre el modelo; declara **otro canal de persistencia**.

---

## 1. Qué es y qué no es

| Capacidad | Propósito | Relación con este doc |
|-----------|-----------|------------------------|
| **APIs importar maestros** (este doc) | Upsert masivo de catálogos por integración | **Alcance** |
| **ABM maestros** (`04` / SPEC-003) | Alta/edición humana en grilla + modal | Misma entidad; canal distinto |
| **Importación Excel de partes** (`13` / GEN-14) | Alta masiva de **tareas** desde plantilla | **No** es este proceso |
| **Carga diaria / masivo / paquetes** (`05`, `16`) | Operación sobre registros de tiempo | Fuera de alcance |
| **Referencias ERP en cliente** (`erpCliente` / `erpArticulo`) | Datos de maestro para informes | **Sí** se pueden informar en el upsert de cliente; **no** hay sync bidireccional con el ERP |

Anti-patrones:

- Reutilizar GEN-14 / `ExcelImportToolbar` para maestros.
- Inventar un ABM paralelo o un parser Excel propio de catálogos.
- Crear usuarios Sanctum (`users`) desde estas APIs.
- Importar filas de `PQ_PARTES_REGISTRO_TAREA`.
- Usar `WHERE IN` con listas de tamaño desconocido en el backend (regla 74/77).

---

## 2. Dónde vive

### 2.1 Canal

**Solo API REST** autenticada (Sanctum + headers de plataforma `X-Paq-Cliente`). No hay pantalla de menú en el MVP. El operador humano sigue usando Archivos.

Opcional post-MVP: un proceso de menú que invoque las mismas APIs no forma parte de esta definición.

### 2.2 Mobile

**Fuera de alcance.** No hay UI. Las exclusiones mobile de ABM / Excel no cambian.

---

## 3. Quién puede usarlo

| Actor | ¿Puede llamar las APIs? |
|-------|-------------------------|
| Integración / usuario técnico con **permiso de proceso** dedicado | Sí |
| Supervisor u operador de Archivos **sin** ese permiso | No (sigue el ABM) |
| Cliente funcional | No |
| Anónimo | No |

El permiso es **distinto** del de ABM de maestros. Un token de integración no implica poder abrir Archivos, y viceversa.

---

## 4. Entidades y clave natural

Upsert por **`code`** (único en cada maestro). El `id` interno no se usa como clave de integración.

| Recurso | Tabla | Clave | Campos que el origen puede enviar (conceptual) |
|---------|--------|-------|-----------------------------------------------|
| Tipos de cliente | `PQ_PARTES_TIPOS_CLIENTE` | `code` | `descripcion`, estado usable (`activo` / `inhabilitado`) |
| Tipos de tarea | `PQ_PARTES_TIPOS_TAREA` | `code` | `descripcion`, `isGenerico`, `isDefault`, estado usable |
| Clientes | `PQ_PARTES_CLIENTES` | `code` | `nombre`, `tipoCliente` (code del tipo), `email`, `erpCliente`, `erpArticulo`, estado usable |
| Asignación cliente–tipo | `PQ_PARTES_CLIENTE_TIPO_TAREA` | par (`cliente.code`, `tipoTarea.code`) | alta o baja de la relación |
| Asistentes | `PQ_PARTES_USUARIOS` | `code` | `nombre`, `email`, `supervisor`, estado usable, **`usuario` Framework ya existente** |

Orden de aplicación cuando un lote trae varias entidades: **tipos de cliente → tipos de tarea → clientes → asignaciones → asistentes**. El origen puede enviar un lote de un solo recurso.

### 4.1 Qué no viaja en el import (Must)

- **Alta de `users`.** El vínculo autenticable de un asistente exige un `users.id` **ya existente**, identificado por el código de usuario Framework (`usuario`), no por el `code` de Partes.
- **Habilitar acceso de cliente** (`user_id` en cliente): **fuera de este canal** en v1. El cliente importado queda como entidad de negocio **sin** acceso; el acceso se habilita en el ABM (SPEC-003 §4.3).
- **Eliminación física** de maestros referenciados. El import solo puede **inhabilitar** (flag). Delete físico, si aplica, sigue en ABM cuando no hay historial.

### 4.2 Asistentes y `user_id`

El modelo vigente **exige** `user_id` en asistentes. Por eso el upsert de asistente **Must** incluir el código de usuario Framework ya dado de alta en seguridad GEN.

- Si el `usuario` no existe, o ya está vinculado a **otro** asistente o a un cliente: la fila **falla** (no se crea un asistente huérfano).
- Si el asistente ya existe por `code` y el `usuario` coincide con el vínculo actual: se actualizan nombre, email, supervisor y estado.
- Si el asistente ya existe y el origen pide **otro** `usuario`: **falla** en v1 (el cambio de vínculo autenticable queda en el ABM, con confirmación).

---

## 5. Comportamiento del lote

### 5.1 Contrato de llamada

- Envelope GEN-20: éxito con `error: 0`.
- Cuerpo: lista de ítems del recurso (o un sobre con secciones por recurso).
- Respuesta: conteo de aceptados / rechazados y **detalle por fila** (índice o `code` + clave i18n de error). Sin prosa de negocio en el JSON.
- Persistencia de negocio **solo por SP**.

### 5.2 Validación y commit (D-IM-01)

Misma semántica que la importación de partes (`13`): **validar todo el lote**, **grabar las filas válidas**, **no abortar el lote entero** por una fila mala. El llamador recibe el informe y reintenta solo los rechazos.

Excepción: reglas que exigen unicidad global en el mismo lote (p. ej. dos ítems con el mismo `code`, o dos `isDefault = true`) → **fallan esas filas** (o el segundo default); no se inventa un ganador silencioso.

### 5.3 Idempotencia (D-IM-02)

Reenviar el mismo `code` con los mismos datos es un no-op efectivo (update idéntico). Reenviar con datos distintos **actualiza**. No hay modo “solo alta” ni “solo update” en v1: siempre upsert.

### 5.4 Reglas de dominio reutilizadas (D-IM-03)

No hay un segundo reglamento. Aplica lo ya definido para ABM:

- Unicidad de `code`.
- Tipo de cliente del cliente: debe existir y ser usable (o venir en el mismo lote **antes**, §4).
- `erpCliente` / `erpArticulo`: opcionales, máx. 15; exceso → fila rechazada.
- Tipos genéricos: **no** se asignan a cliente (la fila de asignación se rechaza).
- `isDefault`: a lo sumo uno usable; marcar default implica genérico y desmarca el anterior (igual que ABM).
- Inhabilitado / no activo: deja de ofrecerse en selectores de **nuevas** operaciones; el historial no se borra.
- No se puede inhabilitar el tipo default vigente sin otro default resultante (misma restricción que ABM).

### 5.5 Tamaño del lote (D-IM-04)

El SPEC bajará un máximo concreto (orden de magnitud: **cientos**, no decenas de miles por request). Superar el máximo → rechazo del request (no recorte silencioso). Lotes mayores se parten en el origen.

---

## 6. Relación con el ABM y la operatoria

- Tras un import, las pantallas de Archivos y los selectores de carga **ven** los mismos registros.
- Un operador puede corregir en ABM un dato llegado por API, y viceversa.
- La carga diaria, el masivo y los informes **no** cambian de contrato; solo se benefician de catálogos más completos.

---

## 7. Decisiones de producto

| ID | Decisión |
|----|----------|
| D-IM-01 | Commit parcial con informe por fila; no transacción “todo o nada” del lote. |
| D-IM-02 | Upsert por `code` (o par de codes en asignaciones); idempotente. |
| D-IM-03 | Mismas reglas de negocio que el ABM; sin atajos de integración. |
| D-IM-04 | Tope de tamaño por request; exceso = 422/400 de contrato, no recorte. |
| D-IM-05 | Sin UI de menú en v1; permiso de API dedicado. |
| D-IM-06 | Sin crear `users`; sin habilitar acceso cliente por este canal. |
| D-IM-07 | Asistente: `usuario` Framework obligatorio y ya existente; no se cambia el vínculo en v1. |
| D-IM-08 | Canal REST; no Excel GEN-14; no mobile. |
| D-IM-09 | Orden de dependencias §4 cuando el sobre trae varios recursos. |
| D-IM-10 | Delete físico fuera del import; solo inhabilitación por flag. |

---

## 8. Fuera de alcance (v1)

- Importación Excel / CSV de maestros (sigue fuera; no se abre GEN-14 para catálogos).
- Sync bidireccional ni webhooks hacia el ERP.
- Import de tareas, paquetes de horas o layouts.
- Jobs programados GEN-13 que disparen el import (el origen llama cuando quiera).
- Diff / “dry-run” persistido más allá de la validación del mismo request (un modo `dryRun` en el SPEC es opcional; no es Must de producto).
- Versionado de catálogo o aprobación humana del lote.

---

## 9. Dudas cerradas en esta definición

- **¿Excel o API?** API. Excel de maestros no forma parte del producto.
- **¿Reemplaza Archivos?** No.
- **¿Crea logins?** No.
- **¿Cliente con acceso automático?** No en v1.

## 10. Pendiente para el SPEC (no bloquea A0)

- Path exacto (`/api/v1/partes/integracion/...` u otro), un endpoint vs. uno por recurso.
- Número máximo de filas y timeout.
- Si `dryRun` entra en v1.
- Matriz OpenAPI y nombres de SP.

---

## Resultado esperado

Un sistema externo puede **poblar y mantener** tipos, clientes, asignaciones y asistentes (estos últimos ya autenticables en GEN) **sin operador en Archivos**, con las **mismas reglas** que el ABM, informe de filas rechazadas y sin contaminar el circuito de partes ni la seguridad de usuarios.
