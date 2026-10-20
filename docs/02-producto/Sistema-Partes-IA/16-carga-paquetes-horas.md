# Carga de paquetes de horas

Jira: PIAPARTES-43

## Objetivo

Definir, en lenguaje de producto, el proceso de **alta, edición y baja de compras / movimientos de paquete de horas** dentro del módulo Partes de Atención.

Este documento es la **definición conceptual de producto**. No es SPEC, HU ni TR; alimenta la posterior generación Open-Spec (flujo SDD / Partes A→C).

Un movimiento de paquete **no es una tarea de trabajo**. Es un crédito de horas contratadas por un cliente. Se persiste en la misma tabla de registros (`PQ_PARTES_REGISTRO_TAREA`) con **`es_tarea = false`**.

El **informe Paquete de Horas** (cuenta corriente) ya está definido en [`06-consultas-dashboard-y-navegacion.md`](./06-consultas-dashboard-y-navegacion.md). Este proceso es la **carga operativa** que alimenta esos movimientos. Sin él, el informe solo ve consumo (tareas) y no créditos.

---

## 1. Qué es y qué no es

| Capacidad | Propósito | Relación con este doc |
|-----------|-----------|------------------------|
| **Carga de paquetes de horas** (este doc) | ABM de compras/créditos de horas (`es_tarea = false`) | **Alcance** |
| **Informe Paquete de Horas** (`06`) | Lectura de cuenta corriente (saldo inicial + movimientos) | Consume estos registros; **no** los crea |
| **Carga diaria** (`05`) | Alta/edición/baja de **tareas** (`es_tarea = true`) | Circuito distinto; no se mezclan listados |
| **Smart Capture** (GEN-03 / `14`) | Asistente operativo en el **modal** (texto, audio, imagen) | **Sí — mismo patrón que Carga diaria**, sobre el formulario de paquete |
| **Asistente IA chat documental** (`12`) | Explicar el sistema desde el avatar | Distinto canal; **no** graba paquetes |
| **Importación Excel** (`13`) | Alta masiva de **tareas** | No importa compras de horas |
| **Proceso masivo** (`05`) | Cambios en lote sobre **tareas** ya cargadas | No opera sobre paquetes |
| **Emisión GEN-15** (`15`) | Documentos desde Consulta detallada | Fuera de este alcance |

Anti-patrones:

- Dar de alta un paquete desde Carga diaria (ese proceso **fuerza** `es_tarea = true`).
- Tratar un paquete como “tarea sin cargo” o “tarea de tipo especial”.
- Duplicar una tabla de saldos: el saldo nace del informe, no de un acumulado persistido.
- Montar Smart Capture en la **grilla** del listado, o usar el chat del avatar para registrar compras.

---

## 2. Dónde vive el proceso

### 2.1 Menú / acceso

Proceso **propio** en la rama **Carga de Partes**, hermano de Carga diaria (no embebido en su toolbar).

- No pertenece a Informes (el informe de cuenta corriente sigue en Informes).
- No pertenece a Seguridad / administración técnica.
- Visible según permiso de menú del proceso (no reutilizar el permiso de Carga diaria).

Etiqueta orientativa: **Paquetes de horas** / **Carga de paquetes**.

### 2.2 Forma de la pantalla

ABM clásico del módulo: **grilla de listado** filtrada + **alta/edición en modal** (Popup sobre el listado). Controles DevExtreme; chrome `ProcessPageLayout`; grilla `ProcessDataGrid`.

Filtros de contexto (Must):

- **Fecha desde / fecha hasta** (periodo de la compra).
- **Cliente** (selector de catálogo).

La grilla lista **solo** registros con `es_tarea = false`.

### 2.3 Smart Capture (asistencia IA) — Must

**D-PH-11.** El alta y la edición de un paquete montan **Smart Capture** (GEN-03, `SmartCapturePanel`) **igual que Carga diaria**: **dentro del Popup**, **debajo** del formulario. El formulario sigue siendo la fuente de verdad; SC **propone** y el usuario **revisa** y **confirma** (botón Guardar o keyword de grabación GEN).

Norma de producto del panel, modalidades, BYOK, keywords y confirmación de fecha futura: [`14-smart-capture.md`](./14-smart-capture.md). Este proceso **no** reimplementa el motor; especializa el **contrato de dominio**.

| Tema | En este proceso |
|------|-----------------|
| Dónde | Solo modal alta/edición de **paquete**; no en la grilla ni en el informe |
| Quién | El mismo gate que el proceso: **solo supervisor** |
| Campos que SC puede completar | **Cliente**, **fecha**, **duración**, **observación** |
| Campos que SC **no** pide ni pisa | Tipo de tarea, asistente, presencial, sin cargo, cerrado (los completa el sistema al grabar, §4.2) |
| Ambigüedad | Lista numerada si hay **más de un cliente** coincidente (mismo patrón D-SC-05; sin lookup de asistente/tipo) |
| Fecha futura | Misma confirmación en el hilo antes de aplicar al draft (D-SC-07 / D-SC-16) |
| Hint inicial | Texto i18n **de este proceso** (paquete / compra de horas), no el hint de tarea |
| Persistencia | Action `save` → **mismo API** que Guardar del modal de paquetes (`es_tarea = false`) |
| Mobile | No (D-PH-08 + D-SC-10) |
| Chat documental (`12`) | No opera sobre este formulario |

Sin credencial LLM (BYOK): el panel muestra el gate GEN «Ir a Preferencias»; no se envían turnos. El ABM manual sigue disponible.

### 2.4 Mobile

**Fuera de alcance en mobile v1.** Coherente con exclusiones de ABM operativo no incluido en el menú MVP mobile (`10-mobile.md`). El **informe** Paquete de Horas sí está en mobile; la **carga** de compras no.

---

## 3. Quién puede usarlo

| Actor | ¿Puede cargar / editar / eliminar paquetes? |
|-------|---------------------------------------------|
| Supervisor | **Sí** |
| Asistente (no supervisor) | **No** |
| Cliente | **No** |

Es un acto comercial / de administración de contrato, no parte de la carga diaria del asistente. El cliente solo **consulta** el efecto en el informe, si su perfil lo permite.

---

## 4. Qué se registra

El movimiento reutiliza la entidad de registro, con semántica distinta.

### 4.1 Campos visibles (formulario)

| Campo de negocio | Semántica | Obligatorio |
|------------------|-----------|-------------|
| Cliente | Cliente que contrata el paquete (selector código + descripción) | **Sí** |
| Fecha | Fecha funcional de la compra / vigencia del crédito | **Sí** |
| Duración | Horas compradas, en tramos `hh:mm` (mismo parámetro GRAL que la carga de tareas; default 15 min) | **Sí** (positiva) |
| Observación | Texto libre (referencia de contrato, nro. de orden, nota) | **Sí** |

No se muestran: tipo de tarea, asistente propietario, presencial, sin cargo, cerrado.

### 4.2 Campos persistidos (regla de producto)

La tabla exige FKs que este proceso **no** pide al usuario. El sistema las completa así:

| Campo técnico | Valor al grabar desde este proceso |
|---------------|-------------------------------------|
| `es_tarea` | **Siempre `false`** |
| `tipo_tarea_id` | Tipo de tarea **por defecto** del sistema (`isDefault`) |
| `usuario_id` | Asistente funcional del **supervisor que registra** (auditoría de quién cargó, no “quién trabajó”) |
| `sin_cargo` | `false` |
| `presencial` | `false` |
| `cerrado` | `false` (el circuito de cierre de **tareas** no aplica a paquetes) |

**D-PH-01.** Grabar desde este proceso **fuerza** `es_tarea = false`. Un paquete nunca se convierte en tarea editando el flag a mano.

**D-PH-02.** El tipo de tarea por defecto es un **detalle técnico de integridad**, no un dato de negocio del paquete. No se elige en UI.

**D-PH-03.** Duración = horas **compradas** (crédito). El signo en el informe (resta de minutos cuando `es_tarea = false`) ya está definido en `06`; este proceso no redefine la cuenta corriente.

### 4.3 Fecha

Misma noción que en carga diaria: fecha **funcional** del movimiento, no timestamp técnico. Fecha futura: advertencia, no bloqueo (salvo que SPEC posterior lo endurezca).

### 4.4 Cliente

- Selector de clientes **habilitados**.
- Un cliente inhabilitado no se ofrece para **nuevas** compras; un movimiento histórico del cliente se sigue viendo en el listado filtrado.

---

## 5. Listado y ciclo de vida

### 5.1 Columnas de grilla (orientativo)

Cliente (descripción; código opcional en column chooser), fecha, duración (`hh:mm`), observación, quién registró (asistente del supervisor), auditoría de alta.

No exponer `es_tarea` ni tipo de tarea al usuario.

### 5.2 Edición y baja

| Acción | Quién | Condición |
|--------|-------|-----------|
| Alta | Supervisor | Formulario válido |
| Edición | Supervisor | El registro es un paquete (`es_tarea = false`) |
| Eliminación | Supervisor | Confirmación explícita; el registro es un paquete |

No hay estado `cerrado` que bloquee edición: un error de carga se corrige editando o eliminando el movimiento. El informe refleja el cambio al consultar de nuevo.

**D-PH-04.** Este proceso **no** edita ni elimina tareas (`es_tarea = true`). Si el id no es un paquete, la API rechaza.

### 5.3 Errores de validación

Al guardar desde el Popup, el error se muestra **dentro del modal** (no solo detrás del overlay). Al cerrar/cancelar, se limpia el error del formulario.

---

## 6. Relación con el informe Paquete de Horas

1. El usuario supervisor carga un paquete (este proceso).
2. El mismo (u otro) usuario abre **Informes → Paquete de horas**.
3. El informe **no filtra** por `es_tarea`: mezcla tareas (consumo) y paquetes (crédito).
4. Convención ya vigente (`06`): tarea **suma** minutos; paquete **resta** minutos; fila **Saldo inicial** + columna **Saldo** en grilla; pivot **sin** Saldo.

Este documento **no** cambia columnas, filtros ni signos del informe.

---

## 7. Framework (adoptar, no reinventar)

No hay GEN de “paquetes de horas”: es **dominio Partes**.

| Pieza | Uso |
|-------|-----|
| GEN-01 `ProcessPageLayout` | Chrome de proceso |
| GEN-11 `ProcessDataGrid` | Listado + layouts |
| GEN-07 menú | Ítem con `labelKey`; `id` jerárquico en `pq_menus` |
| GEN-20 envelope | API `{ error, respuesta, resultado }` |
| Regla 74 | Persistencia de negocio por **SP** (no Eloquent CRUD nuevo) |
| GEN-03 `SmartCapturePanel` | **Must** en el modal (mismo patrón que Carga diaria; contrato de campos §2.3) |
| GEN-16 BYOK | Misma credencial LLM que chat / SC de tareas |
| GEN-14 Excel | **No** aplica (no importar paquetes en v1) |
| GEN-15 Emitir | **No** montar en esta pantalla en v1 |

---

## 8. Decisiones de producto (resumen)

| ID | Decisión |
|----|----------|
| **D-PH-01** | Alta/edición desde este proceso fuerza `es_tarea = false`. |
| **D-PH-02** | `tipo_tarea_id` = tipo por defecto; no se muestra en UI. |
| **D-PH-03** | Duración = horas compradas; el informe conserva la convención de signo de `06`. |
| **D-PH-04** | El proceso solo opera registros-paquete; no toca tareas. |
| **D-PH-05** | Solo **supervisor**; asistente y cliente no cargan paquetes. |
| **D-PH-06** | Ítem de menú propio en Carga de Partes; no embebido en Carga diaria. |
| **D-PH-07** | ABM grilla + modal; filtros periodo y cliente. |
| **D-PH-08** | Fuera de mobile v1. |
| **D-PH-09** | Sin importación Excel de paquetes en este alcance. |
| **D-PH-10** | Sin circuito `cerrado`; edición y baja con confirmación. |
| **D-PH-11** | Smart Capture **Must** en el modal de paquete (GEN-03); mismos modalidades/BYOK/keywords que `14`; campos solo cliente / fecha / duración / observación. |

---

## 9. Fuera de alcance (este documento)

- Recalcular o persistir un “saldo actual” en tabla propia.
- Facturación, costeo u órdenes de compra ERP.
- Importación masiva de paquetes.
- Permitir al asistente no supervisor cargar créditos.
- Cambiar la fórmula del informe Paquete de Horas.
- Emitir PDF/mail del paquete (GEN-15).
- Mobile.

---

## 10. Impacto en el set conceptual

Al cerrar este documento:

- [`07-fuera-de-alcance-y-evolucion.md`](./07-fuera-de-alcance-y-evolucion.md): la **alta de compras** deja de ser “proceso a definir”.
- [`06-consultas-dashboard-y-navegacion.md`](./06-consultas-dashboard-y-navegacion.md): el informe deja de depender de un flujo inexistente.
- [`11-checklist-temas-definidos-del-modulo.md`](./11-checklist-temas-definidos-del-modulo.md): marcar la carga de paquetes como definida a nivel producto (SPEC/HU/TR posteriores).

Siguiente paso SDD: Parte **A** (SPEC) a partir de este archivo.

---

## Resultado esperado

Queda cerrado, en lenguaje de producto:

- qué es un movimiento de paquete frente a una tarea;
- quién lo carga y desde qué pantalla;
- qué datos pide el usuario y cuáles completa el sistema;
- que el modal monta **Smart Capture** como en Carga diaria, sobre el contrato de paquete;
- cómo se relaciona con el informe de cuenta corriente;
- qué queda fuera hasta un update posterior.
