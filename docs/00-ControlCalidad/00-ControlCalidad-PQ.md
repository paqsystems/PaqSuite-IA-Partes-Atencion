# Control de Calidad — PQ

| Campo | Valor |
|-------|--------|
| **ID archivo** | `00-ControlCalidad-PQ` |
| **Responsable** | Pablo Quarracino (PQ) |
| **Alcance** | Hallazgos de pruebas manuales y mejoras solicitadas por cliente en PaqSuite Partes de Atención |
| **Metodología** | Open-Spec / SDD — [`_OPEN-SPEC-METODOLOGIA.md`](../_base/_OPEN-SPEC-METODOLOGIA.md) |
| **Dispatcher** | Parte **G** (volcado), **H** (cierre opcional), ciclo **G → D → E → F → I** |

## Propósito

Registro operativo de **incidencias y mejoras** detectadas fuera del flujo automatizado de tests. Cada sesión de control se numera secuencialmente y conserva trazabilidad hasta su derivación a **SPEC-update**, **HU-update** y **TR-update** en `docs/.../updates/`.

Este archivo **no sustituye** SPEC, HU ni TR: es la **entrada** del circuito de correcciones (Parte G).

## Convenciones

| Tema | Regla |
|------|-------|
| **Fecha** | Formato `dd/MM/yyyy` en todo el documento |
| **Bloques** | `## Control de Calidad #N` — numeración incremental |
| **Ítems** | Preferir `### HU-XXX-slug` cuando la HU sea identificable |
| **Marcas de gestión** | `*Procesado*` tras volcado G; `*Sugerencia: HU-…*` si aún no hay HU asociable |
| **Comando de volcado** | `Corrige los errores del dd/MM/yyyy de PQ` (o *Realiza las mejoras…* / *Procesa las solicitudes…*) |

## Estados del bloque (*Referencia del control*)

| Estado | Significado |
|--------|-------------|
| **Pendiente** | Control registrado; ítems sin volcar a `updates/` |
| **Con Sugerencias** | Volcado parcial: quedan ítems con sugerencia de HU sin archivo generado |
| **A Programar** | Todas las entradas marcadas `*Procesado*`; pendiente cierre formal G/H |
| **Especificado** | Parte G (o H) cerró el bloque: volcado documental completo; cola activa en `docs/.../updates/` — **no** implica código implementado ni **`Finalizado`** en metadatos de HU/TR |

> El **Estado** bajo *Referencia del control* es independiente del **Estado** en metadatos de HU/TR ([`07-estado-hu-tr.md`](../../.cursor/rules/base/00-arquitectura/07-estado-hu-tr.md)). Tras **Parte I**, los originales unificados quedan en **Finalizado**; el CC permanece como referencia histórica.

## Flujo tras registrar un control

1. Registrar hallazgos en el bloque `#N` con estado **Pendiente**.
2. Ejecutar **Parte G** (`Corrige… dd/MM/yyyy de PQ`): §0 SPEC-update si cambia el alcance; HU-update / TR-update.
3. Implementar (**D**), tests (**E**), verificación (**F**).
4. Marcar updates **`Finalizado`** (manual) y **Parte I** (unificar).

## Índice de controles

| # | Fecha | Estado | Resumen |
|---|-------|--------|---------|
| 1 | 31/07/2026 | Especificado | `es_tarea` + Paquete de Horas — F1/F Aprobado · **Parte I unificada** ([D-VERIFICACION](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-01-2026-07-31.md)) |
| 2 | 01/08/2026 | Especificado | ERP clientes + informes — F1/F Aprobado · **Parte I unificada** ([D-VERIFICACION](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-02-2026-08-01.md)) |
| 3 | 09/08/2026 | Especificado | Masivo tilde + carga decimal/Excel/plantillas/tipo default + Smart Capture controles — **F1/F Aprobado 16/09/2026** · **Parte I unificada** ([D-VERIFICACION](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-03-2026-09-16.md)) |
| 4 | 04/10/2026 | Especificado | ABM asistentes/clientes + Paquete de Horas tipo cliente — **F1/F Aprobado 11/10/2026** · **Parte I unificada** ([D-VERIFICACION](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-04-2026-10-04.md)) |
| 5 | 05/10/2026 | Especificado | Errores de validación en modal — **F1/F Aprobado 11/10/2026** · **Parte I unificada** ([D-VERIFICACION](../04-tareas/updates/001-Generalidades/D-VERIFICACION-CC-PQ-05-2026-10-05.md)) |
| 6 | 11/10/2026 | Especificado | Deuda cableado SDK — PA-01…14 **F+I** (PA-03/04/14: 12/10/2026, pin 2.4.25-beta.1) |
| 7 | 19/10/2026 | Especificado | Mail acceso ABM Usuarios + informar clientes desde Proceso masivo (GEN Mail Engine; cola updates 002/005) |
| 8 | 25/10/2026 | Especificado | Carga de paquetes desde 1 hora + destaque de paquetes + cálculo de saldo por renglón · [PIAPARTES-52](https://paqsystems.atlassian.net/browse/PIAPARTES-52) |
| 9 | 30/10/2026 | Especificado | **Hito FRAMEWORK** — compatibilizar tablas de seguridad MONO vs MULTI (`users`, `pq_empresa`, roles, permisos) + GEN-34 |

---

## Control de Calidad #9

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 30/10/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Especificado |
| **Proyecto de ejecución** | **FRAMEWORK** (`PaqSuite-IA-FRAMEWORK` / `paqsuite/laravel-core`) — hosts MONO (Partes) consumen el contrato unificado |
| **Origen** | Deploy Forge 30/10/2026: `paqsuite:installation:update` falla en `pq_sp_admin_empresa_list` (`Invalid column name 'IDEmpresa'`) por desalineación dictionary TANGO vs esquema MONO del host |
| **Referencias host** | [`integracion-framework-sdk.md`](../01-arquitectura/integracion-framework-sdk.md) § huecos · [`mono-abm-empresas-gen06.md`](../01-arquitectura/mono-abm-empresas-gen06.md) · [`md-seguridad.md`](../modelo-datos/md-seguridad.md) (MONO vs legado MULTI) · GEN-34 [`RELEASE-PARTES.md`](../06-operacion/RELEASE-PARTES.md) |

### Hallazgos

Existen **dos contratos físicos** para el mismo dominio de seguridad bajo nombres parecidos:

| Ámbito | MULTI / dictionary (Framework, Tango) | MONO / host Partes (unified DB) |
|--------|----------------------------------------|----------------------------------|
| Empresa | `pq_empresa`: `IDEmpresa`, `NombreEmpresa`, `Habilita`, `NombreBD`, ERP… | `pq_empresa`: `id`, `nombre`, `activo`, `theme` |
| SP admin empresas | `pq_sp_admin_empresa_*` (singular) en manifiesto Framework | `pq_sp_admin_empresas_*` (plural) en host |
| Usuarios / roles / permisos | Legado `Pq_Rol`, `Pq_Permiso`, casing ERP | Laravel: `users`, `pq_roles`, `pq_permisos`, `pq_rol_atributos` |

El pipeline **GEN-34** ejecuta SQL del Framework **sin bifurcar** por `PAQSUITE_TENANCY=single`, lo que **rompe deploy** (y puede **eliminar** SP del host antes del error).

### Hito solicitado (FRAMEWORK)

**Compatibilizar estructuras de tablas de seguridad entre MULTI y MONO** con contrato explícito, versionado y aplicable en `installation:update` / manifiesto merge.

Entidades en alcance (nombres lógicos; unificar DDL + SP + DTO API):

| Entidad | Notas |
|---------|--------|
| **`users`** | Campos auth, locale, flags operativos; alinear naming MULTI/MONO donde el SDK expone admin |
| **`pq_empresa`** | Superset documentado o perfiles `single` / `multi`; columnas ERP/BD opcionales en MONO |
| **`pq_rol`** | En host físico `pq_roles`; roles, `acceso_total`, códigos |
| **`pq_rolatributo`** | En host físico `pq_rol_atributos`; atributos de menú/proceso por rol |
| **`pq_permiso`** | En host físico `pq_permisos`; usuario ↔ rol (y empresa solo donde aplique MULTI) |

**Entregables esperados del hito (Framework):**

1. **Decisión de arquitectura** publicada (SPEC Framework): modelo canónico, columnas obligatorias/opcionales por tenancy, mapeo Tango ↔ Laravel.
2. **Scripts SP únicos o ramificados** (`PAQSUITE_TENANCY` / target manifiesto) — **no** desplegar `pq_sp_admin_empresa_*` dictionary sobre BD MONO incompatible.
3. **Manifiesto GEN-34**: exclusión, reemplazo host, o paso condicionado para hosts `single` (merge no solo “append”).
4. **Migraciones / DDL idempotente** dictionary y guía de adopción para hosts existentes (Partes).
5. Pin SDK release + nota en `RELEASE-FRAMEWORK-MANIFEST` y checklist host.

**Fuera de alcance inmediato:** pantallas de dominio Partes; ABM empresas MONO (alta ERP) ya prohibido en producto.

### Reparto Framework vs Partes

| Responsable | Qué corresponde | Por qué |
|-------------|----------------|---------|
| **FRAMEWORK** (principal) | Contrato canónico MONO/MULTI; SP y manifiesto GEN-34; `installation:update` condicionado a `PAQSUITE_TENANCY`; adapters SDK; release `laravel-core` | El fallo de deploy nace en SQL/manifiesto **del core**, no en reglas de negocio Partes |
| **Partes** (adopción) | Pin del SDK; manifiesto host; migraciones/SP **solo si** el SPEC Framework lo exige para alinear columnas; smoke Forge; retirar parches duplicados cuando el core unifique | El host **consume** el contrato; no redefine el modelo de plataforma en silencio |

Regla práctica: si la pregunta es *«¿qué DDL/SP debe existir para todos los hosts?»* → **Framework**. Si es *«¿cómo aplicamos el release en esta BD ya poblada?»* → **Partes** (TR de adopción, sin cambiar semántica de datos).

### Partes — preservación de datos en cambios de esquema

Cualquier migración o script SQL en **Partes-Atención** que toque tablas de seguridad debe ser **evolutivo e idempotente** y **no alterar valores existentes** salvo corrección documentada en TR.

| Restricción | Detalle |
|-------------|---------|
| **Identidad** | No reasignar ni regenerar **`id`** (PK/FK): `users.id`, `pq_empresa.id`, `pq_roles.id`, filas de `pq_permisos` / `pq_rol_atributos` |
| **Login** | **`users.codigo`** (código de usuario) y **`password` / `password_hash`**: sin reset masivo, sin rehash salvo migración explícita acordada; credenciales vigentes siguen válidas post-deploy |
| **Denominación** | **`nombre`** (y equivalentes visibles: `name_user`, `pq_empresa.nombre`, nombres de rol): solo cambios de **columna** (rename/add), con **copia 1:1** del valor, nunca sustitución por defaults |
| **Permisos** | Matriz usuario–rol–empresa: mismos vínculos lógicos tras el cambio (mismos `user_id` / `rol_id` / `empresa_id` o mapeo 1:1 documentado) |
| **Prohibido** | `migrate:fresh`, truncates, seeds destructivos sobre seguridad en **preview/producción**; renumeración masiva de IDs |

Validación mínima post-cambio en Partes: conteo de filas estable, login con usuario conocido, menú y permisos equivalentes al pre-deploy.

### Criterios de aceptación preliminares

- [ ] **CA-FW-01** Documento SPEC (Framework) fija contrato único o dual explícito (`single` / `multi`) para las cinco entidades.
- [ ] **CA-FW-02** `paqsuite:installation:update` en instalación **MONO** de Partes **no falla** por columnas Tango inexistentes.
- [ ] **CA-FW-03** SP de admin empresas/seguridad invocados por SDK y host comparten **misma familia de nombres** o adapter documentado sin `DROP` cruzado.
- [ ] **CA-FW-04** Manifiesto permite al host **omitir o sustituir** pasos Framework incompatibles con `single`.
- [ ] **CA-FW-05** Smoke post-deploy: migrate + installation update + `deploy-sp` + login + menú + ABM empresas (MONO).
- [ ] **CA-FW-06** Host Partes actualizado (migración/SP/manifiesto) según guía del release Framework.
- [ ] **CA-FW-07** En Partes, migraciones de adopción **preservan** `id`, `codigo`, `nombre` y hashes de contraseña; smoke de login y permisos sin pérdida de accesos.

### Derivación SDD

*Procesado* (Parte G 30/10/2026) → Framework ([repo `PaqSuite-IA-FRAMEWORK`](https://github.com/paqsystems/PaqSuite-IA-FRAMEWORK)): [SPEC-001-06-update](https://github.com/paqsystems/PaqSuite-IA-FRAMEWORK/blob/develop/docs/05-open-spec/updates/001-Generalidades/SPEC-001-06-update-cc-pq-9-2026-10-30-seguridad-mono-multi.md) · [SPEC-001-34-update](https://github.com/paqsystems/PaqSuite-IA-FRAMEWORK/blob/develop/docs/05-open-spec/updates/001-Generalidades/SPEC-001-34-update-cc-pq-9-2026-10-30-install-single-tenancy.md) · [HU-GEN-06-update](https://github.com/paqsystems/PaqSuite-IA-FRAMEWORK/blob/develop/docs/03-historias-usuario/updates/001-Generalidades/HU-GEN-06-seguridad-mono-multi-update.md) · [HU-GEN-34-update](https://github.com/paqsystems/PaqSuite-IA-FRAMEWORK/blob/develop/docs/03-historias-usuario/updates/001-Generalidades/HU-GEN-34-install-single-tenancy-update.md) · [TR-GEN-06-update](https://github.com/paqsystems/PaqSuite-IA-FRAMEWORK/blob/develop/docs/04-tareas/updates/001-Generalidades/TR-GEN-06-seguridad-mono-multi-update.md) · [TR-GEN-34-update](https://github.com/paqsystems/PaqSuite-IA-FRAMEWORK/blob/develop/docs/04-tareas/updates/001-Generalidades/TR-GEN-34-install-single-tenancy-update.md) · Partes host: [TR-adopcion-host-cc-pq-9](../04-tareas/updates/001-Generalidades/TR-adopcion-host-cc-pq-9-seguridad-mono-multi-update.md)

*Sugerencia:* ítem Jira / epic Framework «Seguridad MONO-MULTI unificación» enlazado desde Partes tras crear ticket.

---

## Control de Calidad #8

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 25/10/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Especificado |
| **Origen** | Prueba manual de Paquete de horas e indicación en [`18-obtencion-saldo-por-renglon.md`](../02-producto/Sistema-Partes-IA/18-obtencion-saldo-por-renglon.md) |
| **Jira** | [PIAPARTES-52](https://paqsystems.atlassian.net/browse/PIAPARTES-52) |

### Hallazgos

En la prueba manual del informe **Paquete de horas** se detectaron tres ajustes:

1. El rango de duración de la **carga de paquetes de horas** debe ser de **1 a 240 horas enteras, inclusive**. La definición anterior que comenzaba en 8 horas queda corregida.
2. En el informe **Paquete de horas**, los registros con `es_tarea = 0` deben distinguirse visualmente en la columna **Duración**, mediante una presentación negativa, color rojo u otro destaque equivalente.
3. La columna **Saldo** no está calculándose correctamente.

### Mejoras solicitadas

#### Carga de paquetes — duración de 1 a 240 horas

*Procesado* (Parte G 30/10/2026) → [SPEC-013-update](../05-open-spec/updates/100-SistemaPartes/SPEC-013-update-cc-pq-8-2026-10-25-duracion-paquetes.md) · [HU-013-update](../03-historias-usuario/updates/100-SistemaPartes/HU-013-update-cc-pq-8-2026-10-25-duracion-paquetes.md) · [TR-013-update](../04-tareas/updates/100-SistemaPartes/TR-013-update-cc-pq-8-2026-10-25-duracion-paquetes.md)

- Ajustar el rango permitido de duración para la carga de paquetes para aceptar horas enteras de **1 a 240**, inclusive.
- Alinear validación frontend, backend/SP, Smart Capture, mensajes i18n y tests.

#### Informe Paquete de horas — destaque de paquetes

*Procesado* (Parte G 30/10/2026) → [SPEC-006-update](../05-open-spec/updates/100-SistemaPartes/SPEC-006-update-cc-pq-8-2026-10-25-paquete-horas-informe.md) · [HU-006-update](../03-historias-usuario/updates/100-SistemaPartes/HU-006-update-cc-pq-8-2026-10-25-paquete-horas-informe.md) · [TR-006-update](../04-tareas/updates/100-SistemaPartes/TR-006-update-cc-pq-8-2026-10-25-paquete-horas-informe.md)

- Para cada fila con `es_tarea = 0`, destacar el valor de la columna **Duración**.
- El resultado puede expresarse como duración negativa y/o color rojo, siempre que sea visible y consistente en grilla, exportación si corresponde e informe.
- Las filas con `es_tarea = 1` deben conservar la presentación normal.

#### Informe Paquete de horas — saldo por renglón

*Procesado* (Parte G 30/10/2026) → mismo volcado SPEC/HU/TR-006-update (ítem saldo + orden fecha/id).

- Calcular el saldo acumulado por cliente y mostrarlo en la columna **Saldo**.
- Aplicar la convención indicada en [`18-obtencion-saldo-por-renglon.md`](../02-producto/Sistema-Partes-IA/18-obtencion-saldo-por-renglon.md): los registros con `es_tarea = 1` suman duración y los registros con `es_tarea = 0` restan duración.
- El saldo inicial debe considerar los movimientos anteriores al inicio del período (`fecha < @fechaDesde`) y cada renglón posterior debe continuar el acumulado del cliente.
- Definir y respetar el orden de cálculo cuando existan varias filas del mismo cliente en la misma fecha.

### Criterios de aceptación preliminares

- [ ] **CA-01** La carga de paquetes acepta duraciones enteras de 1 a 240 horas y rechaza valores fuera del rango.
- [ ] **CA-02** La regla de duración es consistente en UI, API/SP, Smart Capture, i18n y tests.
- [ ] **CA-03** Las filas con `es_tarea = 0` muestran la duración destacada en el informe Paquete de horas.
- [ ] **CA-04** Las filas con `es_tarea = 1` mantienen la presentación normal de duración.
- [ ] **CA-05** El saldo inicial se obtiene sumando/restando los movimientos anteriores al período por cliente.
- [ ] **CA-06** El saldo de cada renglón continúa el acumulado del cliente según `es_tarea`.
- [ ] **CA-07** La solución funciona para clientes distintos sin mezclar sus saldos.

### Derivación SDD

Parte G **30/10/2026** — cola `updates/` lista para **D → E → F** (PIAPARTES-52).

---

## Control de Calidad #7

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 19/10/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Especificado |

### Hallazgos

Envios de mails a clientes y asistentes antes dos eventos : 1) aviso desde ABM de usuarios para proveedor link del sitio y credenciales iniciales de acceso. 2) Anuncio de novedades a los clientes sobre partes ingresados por los asistentes y aprobado por el supervisor

### Errores encontrados - Mejoras solicitadas

#### Envio mail a usuario para acceder al sitio

*Procesado* (Parte G 20/10/2026) → [SPEC-002-update](../05-open-spec/updates/100-SistemaPartes/SPEC-002-identidad-funcional-y-acceso-update.md) · [HU-002-update](../03-historias-usuario/updates/100-SistemaPartes/HU-002-identidad-funcional-y-acceso-update.md) · [TR-002-update](../04-tareas/updates/100-SistemaPartes/TR-002-identidad-funcional-y-acceso-update.md)

en el Abm de Usuarios, agregar un botón "Notificación", por el cual : 
- se envia un mail al usuario (en ALTA; se habilita después de haber ingresado un mail)
- EL mismo notifica en Asunto : Codigo de la Empresa y algo así como " - ingreso a nuestro sistema de Partes de Atención".
- En el cuerpo del mensaje, hacer mención que se ofrece al receptor el link para poder controlar los servicios que se prestaron a su empresa, aclarando el link de acceso (hipervínculo para eso), y las credenciales de acceso : usuario y contraseña
- La contraseña se define en el momento con un número random que cumpla con las normas de seguridad parametrizadas (simple o segura, longitud de la clave)
- se graba en el usuario que es el primer login (para que lo modifique en el proximo acceso)

#### Envio mail a clientes para informar existencia de novedades

*Procesado* (Parte G 20/10/2026) → [SPEC-005-update](../05-open-spec/updates/100-SistemaPartes/SPEC-005-supervision-proceso-masivo-update.md) · [HU-005-update](../03-historias-usuario/updates/100-SistemaPartes/HU-005-supervision-proceso-masivo-update.md) · [TR-005-update](../04-tareas/updates/100-SistemaPartes/TR-005-supervision-proceso-masivo-update.md)

En el proceso de "Partes Masivos":
- habilitar un ícono con la imagen de mail, y tooltip "Informar a clientes", con el cual.
- se determinan todos los clientes que figuran en la grilla en ese momento, y que tengan asociado un usuario.
- Enviar un mail a todos los usuarios recolectados.
- Asunto : Codigo de la empresa y algo así como : "Existen novedades de atención a su empresa".
- Mensaje : informar que hubo novedades de servicios prestados en el periodod (desde y hasta con se filtró el procesó), recordando la URL de aviso y el codigo de usuario para acceder, y que si no recuerda su contraseña podrá solicitar una nueva en el login.

---

## Control de Calidad #6

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 11/10/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Especificado |
| **F1/F** | PA-01…14: F Aprobado (PA-03/04/14: 12/10/2026). |
| **Parte I** | PA-01 (012); PA-02…14 unificados en SPEC/HU/TR-002 originales. Updates 002 **eliminados** 12/10/2026. |
| **Cierre updates** | Familia 002 **Finalizado**. F1+F+I 12/10/2026. |
| **Origen** | [deuda-cableado-sdk-por-gen.md](../06-operacion/deuda-cableado-sdk-por-gen.md) · relevamiento Framework 11/10/2026 |
| **Pin de referencia** | `@paqsuite/react-core` **2.4.29-beta.1** · `paqsuite/laravel-core` **1.3.15-beta.1** |

### Hallazgos

Cerrar la **deuda de cableado SDK** del host Partes-Atención: mala invocación de exports GEN listos, forks de pantallas/motores que el SDK ya exporta, o contratos que el SDK ya cubre (headers, cliente, envelope, tema, i18n GEN).

**Fuera de alcance de este control:** pantallas de dominio (carga diaria, maestros, informes); gaps de producto no ofrecidos (GEN-13, GEN-17, GEN-23, GEN-32).

**Qué no pedir (ya está bien):** `registerGridI18nResources`; `MenuSidebar` con `t`/`locale`/`labels`/`openInNewTab`/`onItemsLoaded`; `UserAvatarMenu`; `DashboardContainer`; `EmpresaSelectorPage`; `ParametrosGeneralesPage`; `ProcessDataGrid` (salvo permisos); `ConsultaGrillaPivotShell`; `ExcelImportToolbar`; `SmartCapturePanel`; `ChatAssistantPage`; `LlmPreferencesPanel`; `ShellLayout` + CSS SDK; `EmpresasAdminPage` con `tenancyMode="single"` y preview sin reload.

**Criterio de aceptación (por ítem, al resolver vía SDD):** se invoca el **export** del SDK (sin pantalla/motor GEN duplicado); i18n GEN con `register*I18nResources` y el mismo namespace (sin JSON/mapper host de las mismas claves); envelope `{ error: 0, respuesta, resultado }`; `bootstrapClienteFromWindow` antes del Router y headers con `buildPlatformHeaders`; smoke login + una pantalla GEN tocada (empresas o usuarios/permisos) en ES y EN/FR.

**Orden sugerido de resolución (Parte G / D):** P0 PA-02 → P1 GEN-06 (PA-03, PA-04, PA-05, PA-06, PA-14) → P1 GEN-19 (PA-09, PA-10, PA-08) → P1 GEN-07 PA-07 → P2 PA-11, PA-12, PA-13 → GEN-35 PA-01 al pasar de labo a Cloudsmith en CI/Forge.

*Procesado* (Parte G 11/10/2026) → [SPEC-002](../05-open-spec/001-Generalidades/SPEC-002-adopcion-cableado-sdk.md) · [HU-002](../03-historias-usuario/001-Generalidades/HU-002-adopcion-cableado-sdk.md) · [TR-002](../04-tareas/001-Generalidades/TR-002-adopcion-cableado-sdk.md) · PA-01 **F+I** → [SPEC-012](../05-open-spec/100-SistemaPartes/SPEC-012-adopcion-gen-35-cloudsmith.md)

### Errores encontrados - Mejoras solicitadas

#### GEN-20 — Envelope API (PA-02, P0)

*Procesado* (Parte G 11/10/2026) → [SPEC-002](../05-open-spec/001-Generalidades/SPEC-002-adopcion-cableado-sdk.md) · [HU-002](../03-historias-usuario/001-Generalidades/HU-002-adopcion-cableado-sdk.md) · [TR-002](../04-tareas/001-Generalidades/TR-002-adopcion-cableado-sdk.md)

- `ApiEnvelope::ok` serializa `'error' => null`. Contrato SDK: `'error' => 0` y `resultado` objeto, no `null`. Rompe clientes que usan `isEnvelope` / `error === 0`.
- Dónde: `backend/app/Support/ApiEnvelope.php`, `AuthController.php`.
- Pedido: eliminar `ApiEnvelope` o alinearlo a `PaqSuite\LaravelCore` `ApiResponse`. Auth (y status) deben usar el envelope canónico.

#### GEN-06 — Seguridad admin (PA-03, PA-04, PA-05, PA-06, PA-14)

*Procesado* (Parte G 11/10/2026) → [SPEC-002](../05-open-spec/001-Generalidades/SPEC-002-adopcion-cableado-sdk.md) · [HU-002](../03-historias-usuario/001-Generalidades/HU-002-adopcion-cableado-sdk.md) · [TR-002](../04-tareas/001-Generalidades/TR-002-adopcion-cableado-sdk.md) · regla 44 / 36

- **PA-03 P1:** el SDK exporta `UsuariosAdminPage`; el host reimplementa listado, popup, alta/edición e inhabilitación (`UsuariosAdminPage.tsx`). Pedido: wrapper fino del export SDK. Dominio solo: token, `platform`, permisos, menú.
- **PA-04 P1:** el SDK exporta `PermisosAdminPage`; el host monta tres `DataGrid` DX y lógica batch propia (`PermisosAdminPage.tsx`). Pedido: montar `PermisosAdminPage` del SDK. No DataGrid crudo de proceso.
- **PA-05 P1:** wrapper de empresas **no pasa** `i18nNamespace="common"` (init usa `defaultNS: 'common'`). Pasa `t={translateSdk}` (`securityEmpresasSdkI18n`) **y** `registerEmpresasAdminI18nResources` **y** `empresasAdminHostFallback` con overwrite. Triple fuente de `admin.empresas.*` / `appearance.paqsuite.*`. Pedido: `registerEmpresasAdminI18nResources(i18n, 'common')` + `i18nNamespace="common"`. Quitar mapper y fallback host de claves GEN.
- **PA-06 P1:** `SecurityRolesPage` recibe `t` que mapea a claves host. **No** se llama `registerSecurityRolesI18nResources`. Pedido: registrar recursos SDK y no mapear claves GEN.
- **PA-14 P2:** existen `registerUsuariosAdminI18nResources` / `registerPermisosAdminI18nResources`; el host no los registra (coherente con el fork de pantallas). Pedido: cerrar junto con PA-03 / PA-04.

#### GEN-07 + GEN-02 — Menú / i18n GEN (PA-07, PA-11)

*Procesado* (Parte G 11/10/2026) → [SPEC-002](../05-open-spec/001-Generalidades/SPEC-002-adopcion-cableado-sdk.md) · [HU-002](../03-historias-usuario/001-Generalidades/HU-002-adopcion-cableado-sdk.md) · [TR-002](../04-tareas/001-Generalidades/TR-002-adopcion-cableado-sdk.md)

- **PA-07 P1:** `shellI18n.ts` duplica chrome `menu.*`. Pedido: labels del sidebar vía `t('menu.*')` / helper SDK (`menuSidebarLabelsFromT`). Catálogo host solo claves **producto**.
- **PA-11 P2:** `main.tsx` hace `syncDevExtremeLocale('es')` y `i18n.ts` sincroniza `getGuestLocale()`. Arranque EN/FR puede pintar DX en ES un tick. Pedido: un solo `syncDevExtremeLocale` con el locale real (guest/sesión), no forzar `'es'` al boot.

#### GEN-19 — Shell / tenancy / tema / `{cliente}` (PA-08, PA-09, PA-10)

*Procesado* (Parte G 11/10/2026) → [SPEC-002](../05-open-spec/001-Generalidades/SPEC-002-adopcion-cableado-sdk.md) · [HU-002](../03-historias-usuario/001-Generalidades/HU-002-adopcion-cableado-sdk.md) · [TR-002](../04-tareas/001-Generalidades/TR-002-adopcion-cableado-sdk.md) · regla 34

- **PA-08 P1:** con pin ≥ 2.4.20 el SDK exporta `normalizeThemeFromApi`, `mapThemeForApiPersistence`, `paqsuiteDxStockBridge`, `useEmpresaAppearanceBridge`. El host mantiene `sdkEmpresaThemeCompat.ts` y switcher DX propio. Pedido: un solo puente SDK. Preview/restore sin reload de página.
- **PA-09 P1:** `main.tsx` llama `bootstrapPlatformCliente()` **antes** del Router (orden OK) pero **no** `bootstrapClienteFromWindow`. Reimplementa query/cookie/session/Vercel/landing. Pedido: delegar a `bootstrapClienteFromWindow`. Conservar wrap de landing Vercel **solo** si el SoT de resolución host lo admite.
- **PA-10 P1:** `buildAuthPlatformHeaders()` arma `{ cliente, companyId, tenancy }` a mano. Pedido: usar `buildPlatformHeaders`. MONO: `X-Paq-Cliente` ≠ id de empresa.

#### GEN-04 — Auth / cambio de clave (PA-12, P2)

*Procesado* (Parte G 11/10/2026) → [SPEC-002](../05-open-spec/001-Generalidades/SPEC-002-adopcion-cableado-sdk.md) · [HU-002](../03-historias-usuario/001-Generalidades/HU-002-adopcion-cableado-sdk.md) · [TR-002](../04-tareas/001-Generalidades/TR-002-adopcion-cableado-sdk.md)

- `ChangePasswordPage` sin `AuthCardLayout` / `AuthPageLayout`, literales en español.
- Pedido: layouts SDK + i18n (mismo patrón que login/forgot/reset, que ya están bien).

#### GEN-01 — Layouts de proceso (PA-13, P2)

*Procesado* (Parte G 11/10/2026) → [SPEC-002](../05-open-spec/001-Generalidades/SPEC-002-adopcion-cableado-sdk.md) · [HU-002](../03-historias-usuario/001-Generalidades/HU-002-adopcion-cableado-sdk.md) · [TR-002](../04-tareas/001-Generalidades/TR-002-adopcion-cableado-sdk.md)

- Procesos de dominio no usan `ProcessPageLayout` (div/h2 propios) en varias páginas `features/partes/**`.
- Pedido: adoptar `ProcessPageLayout`. Título desde menú localizado (`resolveMenuCaption`) o `t()` de producto — no literal ES.

#### GEN-35 — Distribución SDK (PA-01, lab / ops)

*Procesado* (Parte G 11/10/2026) · **F+I 11/10/2026** → [SPEC-012](../05-open-spec/100-SistemaPartes/SPEC-012-adopcion-gen-35-cloudsmith.md) · [HU-012](../03-historias-usuario/100-SistemaPartes/HU-012-adopcion-gen-35-cloudsmith.md) · [TR-012](../04-tareas/100-SistemaPartes/TR-012-adopcion-gen-35-cloudsmith.md) · [F PA-01](../04-tareas/100-SistemaPartes/F-TR-012-adopcion-gen-35-cloudsmith-PA-01.md)

**F+I 11/10/2026:** path quitado; lock Cloudsmith `/basic/`; Forge rechaza path/Satis. F: [F PA-01](../04-tareas/100-SistemaPartes/F-TR-012-adopcion-gen-35-cloudsmith-PA-01.md) **Aprobado con observaciones**. SPEC/HU/TR-012-update fusionados en originales (**Finalizado**). El ítem #6 de la tabla sigue abierto por PA-02…PA-14.

---

## Control de Calidad #5

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 05/10/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Especificado |
| **F1/F** | Aprobado 11/10/2026 → [D-VERIFICACION](../04-tareas/updates/001-Generalidades/D-VERIFICACION-CC-PQ-05-2026-10-05.md) |
| **Parte I** | Unificado 11/10/2026 → originales [SPEC/HU/TR-001](../05-open-spec/001-Generalidades/SPEC-001-ui-errores-validacion-contexto-carga.md) |
| **Cierre updates** | 11/10/2026 — Parte I ejecutada; originales en **Finalizado** |

### Hallazgos

Alta de usuario (ABM Usuarios): si la contraseña no cumple la política, el mensaje aparece como texto en la **pantalla principal** (bajo el título), no dentro del modal «Nuevo usuario».

*Procesado* (unificado Parte I 11/10/2026) → [SPEC-001](../05-open-spec/001-Generalidades/SPEC-001-ui-errores-validacion-contexto-carga.md) · [HU-001](../03-historias-usuario/001-Generalidades/HU-001-ui-errores-validacion-contexto-carga.md) · [TR-001](../04-tareas/001-Generalidades/TR-001-ui-errores-validacion-contexto-carga.md) · Regla BASE [36-ui-errores-validacion-contexto-carga](../../.cursor/rules/base/20-frontend/36-ui-errores-validacion-contexto-carga.mdc)

### Errores encontrados - Mejoras solicitadas

#### ABM Usuarios (GEN seguridad)

- Los errores de validación al guardar deben mostrarse **en el modal de carga** o en un **popup modal**, no en el layout de la página de fondo.
- Aplicar el mismo criterio a **todos los procesos de carga en modal** del producto (regla transversal).

---

## Control de Calidad #4

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 04/10/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Especificado |
| **F1/F** | Aprobado 11/10/2026 → [D-VERIFICACION](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-04-2026-10-04.md) |
| **Parte I** | Unificado 11/10/2026 → originales [SPEC/HU/TR-003](../05-open-spec/100-SistemaPartes/SPEC-003-maestros-y-catalogos.md), [006](../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) |
| **Cierre updates** | 11/10/2026 — Parte I ejecutada; originales en **Finalizado** |

### Hallazgos

Mejoras y correcciones tras control de calidad manual y completo

*Procesado* (unificado Parte I 11/10/2026) → [SPEC-003](../05-open-spec/100-SistemaPartes/SPEC-003-maestros-y-catalogos.md) · [HU-003](../03-historias-usuario/100-SistemaPartes/HU-003-maestros-y-catalogos.md) · [TR-003](../04-tareas/100-SistemaPartes/TR-003-maestros-y-catalogos.md) · [SPEC-006](../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) · [HU-006](../03-historias-usuario/100-SistemaPartes/HU-006-consultas-dashboard-navegacion.md) · [TR-006](../04-tareas/100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md)

### Errores encontrados - Mejoras solicitadas

#### ABM Asistentes

*Procesado* (unificado Parte I 11/10/2026) → [SPEC-003](../05-open-spec/100-SistemaPartes/SPEC-003-maestros-y-catalogos.md) · [HU-003](../03-historias-usuario/100-SistemaPartes/HU-003-maestros-y-catalogos.md) · [TR-003](../04-tareas/100-SistemaPartes/TR-003-maestros-y-catalogos.md)

- No aparece el codigo de usuario asociado.
- Verificar que no se duplique el usuario asignado. (ni en asistente ni en clientes)
- limitar la lista de usuario a elegir, a los activos, habilitados y no asignados previamente.

#### ABM Clientes

*Procesado* (unificado Parte I 11/10/2026) → [SPEC-003](../05-open-spec/100-SistemaPartes/SPEC-003-maestros-y-catalogos.md) · [HU-003](../03-historias-usuario/100-SistemaPartes/HU-003-maestros-y-catalogos.md) · [TR-003](../04-tareas/100-SistemaPartes/TR-003-maestros-y-catalogos.md)

- Verificar que no se duplique el usuario asignado. (ni en asistente ni en clientes)
- limitar la lista de usuario a elegir, a los activos, habilitados y no asignados previamente.

#### Paquete de Horas

*Procesado* (unificado Parte I 11/10/2026) → [SPEC-006](../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) · [HU-006](../03-historias-usuario/100-SistemaPartes/HU-006-consultas-dashboard-navegacion.md) · [TR-006](../04-tareas/100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md)

-  Agregar filtro "Tipo de Cliente".
- Agregar columna "Tipo de Cliente" (codigo y descripcion)

---

## Control de Calidad #3

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 09/08/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Especificado |
| **F1/F** | Aprobado 16/09/2026 → [D-VERIFICACION](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-03-2026-09-16.md) |
| **Parte I** | Unificado 16/09/2026 → originales [SPEC/HU/TR-004](../05-open-spec/100-SistemaPartes/SPEC-004-operacion-carga-diaria.md), [005](../05-open-spec/100-SistemaPartes/SPEC-005-supervision-proceso-masivo.md), [010](../05-open-spec/100-SistemaPartes/SPEC-010-smart-capture-carga-diaria.md) |
| **Cierre updates** | 16/09/2026 — Parte I ejecutada; originales en **Finalizado** |

### Hallazgos

Mejoras y correcciones tras pruebas manuales

*Procesado* (unificado Parte I 16/09/2026) → [SPEC-004](../05-open-spec/100-SistemaPartes/SPEC-004-operacion-carga-diaria.md) · [HU-004](../03-historias-usuario/100-SistemaPartes/HU-004-operacion-carga-diaria.md) · [TR-004](../04-tareas/100-SistemaPartes/TR-004-operacion-carga-diaria.md) · [SPEC-005](../05-open-spec/100-SistemaPartes/SPEC-005-supervision-proceso-masivo.md) · [HU-005](../03-historias-usuario/100-SistemaPartes/HU-005-supervision-proceso-masivo.md) · [TR-005](../04-tareas/100-SistemaPartes/TR-005-supervision-proceso-masivo.md) · [SPEC-010](../05-open-spec/100-SistemaPartes/SPEC-010-smart-capture-carga-diaria.md) · [HU-010](../03-historias-usuario/100-SistemaPartes/HU-010-smart-capture-carga-diaria.md) · [TR-010](../04-tareas/100-SistemaPartes/TR-010-smart-capture-carga-diaria.md)

### Errores encontrados - Mejoras solicitadas

#### proceso masivo : tildo fila y se borra el tilde

*Procesado* (unificado Parte I) → [HU-005](../03-historias-usuario/100-SistemaPartes/HU-005-supervision-proceso-masivo.md) · [TR-005](../04-tareas/100-SistemaPartes/TR-005-supervision-proceso-masivo.md) (sin SPEC-update: bug vs SPEC-005 §4.3)

- si en la grilla tildo registros para procesarlos masivamente (incluso el tilde que marca todos los registros), se borran inmediatamente.

#### Carga Diaria : Mostrar (agregado) duración en formato decimal

*Procesado* (unificado Parte I) → [SPEC-004](../05-open-spec/100-SistemaPartes/SPEC-004-operacion-carga-diaria.md) · [HU-004](../03-historias-usuario/100-SistemaPartes/HU-004-operacion-carga-diaria.md) · [TR-004](../04-tareas/100-SistemaPartes/TR-004-operacion-carga-diaria.md)

- en la grilla de carga diaria, (que además sea exportable a Excel), expresar también la duración en formato decimal (mm/60). Ejemplos : 02:15 -> 2.25, 15:30 -> 15.5, 14.45 -> 14.75

#### Plantillas de grillas : diseños de otros usuarios.

*Procesado* (unificado Parte I) → [SPEC-004](../05-open-spec/100-SistemaPartes/SPEC-004-operacion-carga-diaria.md) · [HU-004](../03-historias-usuario/100-SistemaPartes/HU-004-operacion-carga-diaria.md) · [TR-004](../04-tareas/100-SistemaPartes/TR-004-operacion-carga-diaria.md) (GEN-11 compartido; **no** hay parámetro para ocultar ajenas)

- Dos usuarios que guardan plantillas personalizadas de grillas, sólo ven las suyas, no las de otros usuarios. desconozco si hay un atributo en la instalación o en parámetros generales para optar por verlos o no. Ocurre al menos en Cargas de Partes Diarios.

#### Cargas de Partes Diarios - Implementar IA

*Procesado* (unificado Parte I) → [SPEC-010](../05-open-spec/100-SistemaPartes/SPEC-010-smart-capture-carga-diaria.md) · [HU-010](../03-historias-usuario/100-SistemaPartes/HU-010-smart-capture-carga-diaria.md) · [TR-010](../04-tareas/100-SistemaPartes/TR-010-smart-capture-carga-diaria.md) · F1/F → [D-VERIFICACION](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-03-2026-09-16.md) (slice IA: [D-VERIFICACION-TR-010](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-TR-010-update-CC-PQ-03-2026-09-16.md))

- implementar bien la asistencia IA. cargo la información, pero no actualiza los datos en los controles. (Ver imagen CC#3-PQ-2026-09-16.png)

#### Cargas de Partes Diarios - inicializar tipo de tarea

*Procesado* (unificado Parte I) → [HU-004](../03-historias-usuario/100-SistemaPartes/HU-004-operacion-carga-diaria.md) · [TR-004](../04-tareas/100-SistemaPartes/TR-004-operacion-carga-diaria.md) (sin cambio SPEC: ya está en SPEC-004 §4.4)

- al generar un parte nuevo, no aparece en Tipo de Tarea por defecto el registro marcado como tal.



---

## Control de Calidad #2

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 01/08/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Especificado |
| **F1/F** | Aprobado 01/08/2026 → [D-VERIFICACION-CC-PQ-02](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-02-2026-08-01.md) |
| **Parte I** | Unificado 01/08/2026 → originales [SPEC/HU/TR-001](../05-open-spec/100-SistemaPartes/SPEC-001-modelo-datos-modulo.md), [003](../05-open-spec/100-SistemaPartes/SPEC-003-maestros-y-catalogos.md), [006](../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) |

### Hallazgos

Incorporar en los clientes dos atributos referidos al sistema ERP, para obtener informes para facturación.

*Procesado* (unificado Parte I) → [SPEC-001](../05-open-spec/100-SistemaPartes/SPEC-001-modelo-datos-modulo.md) · [HU-001](../03-historias-usuario/100-SistemaPartes/HU-001-modelo-datos-modulo.md) · [TR-001](../04-tareas/100-SistemaPartes/TR-001-modelo-datos-modulo.md) · [SPEC-003](../05-open-spec/100-SistemaPartes/SPEC-003-maestros-y-catalogos.md) · [HU-003](../03-historias-usuario/100-SistemaPartes/HU-003-maestros-y-catalogos.md) · [TR-003](../04-tareas/100-SistemaPartes/TR-003-maestros-y-catalogos.md)

### Errores encontrados - Mejoras solicitadas

#### Agregar atributo al maestro de Clientes

*Procesado* (unificado Parte I) → [SPEC-001](../05-open-spec/100-SistemaPartes/SPEC-001-modelo-datos-modulo.md) · [HU-001](../03-historias-usuario/100-SistemaPartes/HU-001-modelo-datos-modulo.md) · [TR-001](../04-tareas/100-SistemaPartes/TR-001-modelo-datos-modulo.md) · [SPEC-003](../05-open-spec/100-SistemaPartes/SPEC-003-maestros-y-catalogos.md) · [HU-003](../03-historias-usuario/100-SistemaPartes/HU-003-maestros-y-catalogos.md) · [TR-003](../04-tareas/100-SistemaPartes/TR-003-maestros-y-catalogos.md)

Agregar dos atributos Erp_Cliente y Erp_Articulo, string de 15 caracteres cada uno, para poder referenciar atributos del ERP con el cual después se puedan hacer informes de facturación.

#### Incorporar Atributos a Informes

*Procesado* (unificado Parte I) → [SPEC-006](../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) · [HU-006](../03-historias-usuario/100-SistemaPartes/HU-006-consultas-dashboard-navegacion.md) · [TR-006](../04-tareas/100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md)

Incorporar ambos atributos en la consulta detallada y la consulta agrupada, tango en la grilla como en el pivot.


---

## Control de Calidad #1

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 31/07/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Especificado |
| **F1/F** | Aprobado 01/08/2026 → [D-VERIFICACION-CC-PQ-01](../04-tareas/updates/100-SistemaPartes/D-VERIFICACION-CC-PQ-01-2026-07-31.md) |
| **Parte I** | Unificado 01/08/2026 → originales [SPEC/HU/TR-001](../05-open-spec/100-SistemaPartes/SPEC-001-modelo-datos-modulo.md), [004](../05-open-spec/100-SistemaPartes/SPEC-004-operacion-carga-diaria.md), [005](../05-open-spec/100-SistemaPartes/SPEC-005-supervision-proceso-masivo.md), [006](../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) |

### Hallazgos

Poder llevar una cuenta corriente de horas para aquellos clientes que abonan por paquete anticipado

*Procesado* (unificado Parte I) → [SPEC-006](../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) · [HU-006](../03-historias-usuario/100-SistemaPartes/HU-006-consultas-dashboard-navegacion.md) · [TR-006](../04-tareas/100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md) (incluido en rehacer Paquete de Horas)

### Errores encontrados - Mejoras solicitadas

#### Agregar atributo booleano "EsTarea"

*Procesado* (unificado Parte I) → [SPEC-001](../05-open-spec/100-SistemaPartes/SPEC-001-modelo-datos-modulo.md) · [HU-001](../03-historias-usuario/100-SistemaPartes/HU-001-modelo-datos-modulo.md) · [TR-001](../04-tareas/100-SistemaPartes/TR-001-modelo-datos-modulo.md)

Agregar en la tabla de tareas un atributo booleano denominado "EsTarea"
True : es una tarea (cargada del proceso "Carga de Partes").
False : Es una compra de horas (en un proceso a definir)

#### Carga de Partes y Proceso Masivo - Asignar True a "EsTarea"

*Procesado* (unificado Parte I) → [SPEC-004](../05-open-spec/100-SistemaPartes/SPEC-004-operacion-carga-diaria.md) · [HU-004](../03-historias-usuario/100-SistemaPartes/HU-004-operacion-carga-diaria.md) · [TR-004](../04-tareas/100-SistemaPartes/TR-004-operacion-carga-diaria.md) · [SPEC-005](../05-open-spec/100-SistemaPartes/SPEC-005-supervision-proceso-masivo.md) · [HU-005](../03-historias-usuario/100-SistemaPartes/HU-005-supervision-proceso-masivo.md) · [TR-005](../04-tareas/100-SistemaPartes/TR-005-supervision-proceso-masivo.md)

- Solo traer registros donde este atributo es "true"
- Al grabar un parte (nuevo/Edicion), asignar "true" a este atributo

#### Informes "Carga detallada" y "Carga agrupada" y Dashboard

*Procesado* (unificado Parte I) → [SPEC-006](../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) · [HU-006](../03-historias-usuario/100-SistemaPartes/HU-006-consultas-dashboard-navegacion.md) · [TR-006](../04-tareas/100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md)

- Filtrar los registros para que consideren unicamente los que este atributo es "true"

#### Informe "Paquete de Horas" : Rehacer

*Procesado* (unificado Parte I) → [SPEC-006](../05-open-spec/100-SistemaPartes/SPEC-006-consultas-dashboard-navegacion.md) · [HU-006](../03-historias-usuario/100-SistemaPartes/HU-006-consultas-dashboard-navegacion.md) · [TR-006](../04-tareas/100-SistemaPartes/TR-006-consultas-dashboard-navegacion.md)

el objetivo de este informe es poder llevar una cuenta corriente de horas para aquellos clientes que contratan paquetes de horas anticipadas
este informe debe ser tipo grilla/pivot con las siguientes características:
- seleccionar como filtros fecha desde, fecha hasta, cliente (cuando el usuario es asistente o supervisor).
- presentar los mismos atributos que "Carga Detallada".
- este informe NO debe filtrar por el campo "Estarea"
- debe agregar un registro "Saldo inicial" con la suma/resta de minutos hasta la fecha desde solicitada (exclusive). Si "Estarea"=true, suma, si es false, resta.
- agregar una columna Saldo, donde en el primer registro "saldo inicial" se coloca la sumatoria antedicha, y cada registro agrega el saldo anterior más sus minutos (sumando/restando)
- en el PIVOT, no incluir este atributo de Saldo.
