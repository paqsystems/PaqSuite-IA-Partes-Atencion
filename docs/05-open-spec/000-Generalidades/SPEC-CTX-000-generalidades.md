# SPEC-CTX-000 – Épica Generalidades: capacidades transversales (antecedente a las HU)

---

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | SPEC-CTX-000 |
| Título | Capacidades transversales de producto (shell, grillas, i18n, parámetros) |
| Épica / carpeta | 000 – Generalidades |
| Rol en la metodología | **Contexto / alcance de épica** que en un flujo A→B→C precedería a la redacción de las HU (paso **B**). |
| Estado | Reconstrucción documental (ver §8) |
| Última actualización | 2026-05-09 |

---

## 1. Problema y resultado buscado

Los módulos de negocio comparten **misma carcasa** (layout, menú, preferencias) y **mismos patrones de listados** (grillas). Sin reglas transversales, cada pantalla reinvenca filtros, exportación, eliminación, idioma y configuración operativa, y el usuario pierde continuidad entre procesos.

**Resultado:** un producto **coherente** en web MONO donde toda pantalla con grilla y el shell comparten comportamiento, datos de preferencia por usuario y mecanismos operativos (parámetros por módulo, ayuda) **sin** depender de multi-empresa.

---

## 2. Contexto de instalación (decisión explícita)

- Despliegue **MONO:** una base / contexto de datos; **no** hay `PQ_Empresa` operativo ni `X-Company-Id`.
- Preferencias de usuario (idioma, pestañas, tema) son **por usuario**, no por instalación única ni por compañía.
- Referencias MULTI (p. ej. cambio de empresa activa) existen como **reserva evolutiva**, no como alcance de entrega.

---

## 3. Capacidades de épica (cómo se descompondrían las HU)

Cada bloque es la **unidad de intención** desde la que, en un flujo “bien ordenado”, se redactarían las HUs actuales. La última columna apunta al **SPEC operativo** ya existente (derivado luego para trazabilidad fina).

| Capacidad (origen lógico) | Qué debe quedar garantizado | HUs derivadas | SPEC detalle |
|---------------------------|----------------------------|---------------|--------------|
| **G1 – Identidad de grillas y vistas reutilizables** | Toda grilla conoce `proceso` y `grid_id`; el usuario reutiliza layouts sin rearmar columnas/filtros cada vez | [HU-001](../../03-historias-usuario/000-Generalidades/HU-001-layouts-grilla.md) | [SPEC-001](SPEC-001-layouts-grilla.md) |
| **G2 – Evolución multi-empresa (no entregable MONO)** | Si el producto pasara a MULTI, el usuario cambia sociedad sin re-login; aquí solo se **documenta la reserva** | [HU-002](../../03-historias-usuario/000-Generalidades/HU-002-cambio-empresa-activa.md) | [SPEC-002](SPEC-002-cambio-empresa-multi-referencia.md) |
| **G3 – Navegación acorde al trabajo del usuario** | Abrir procesos en la misma pestaña o en una nueva, persistido en servidor | [HU-003](../../03-historias-usuario/000-Generalidades/HU-003-apertura-menu-misma-o-nueva-pestana.md) | [SPEC-003](SPEC-003-apertura-menu-pestanas.md) |
| **G4 – Idioma de producto** | i18n end-to-end; preferencia persistida; selector en login **y** header | [HU-004](../../03-historias-usuario/000-Generalidades/HU-004-seleccion-idioma.md), [HU-008](../../03-historias-usuario/000-Generalidades/HU-008-multilingual-idiomas-banderas.md) | [SPEC-004](SPEC-004-seleccion-idioma.md), [SPEC-008](SPEC-008-multilingual-banderas.md) |
| **G5 – Apariencia por usuario** | Tema DevExtreme elegible por quien usa el sistema, desde menú avatar | [HU-005](../../03-historias-usuario/000-Generalidades/HU-005-seleccion-apariencias.md) | [SPEC-005](SPEC-005-seleccion-apariencia.md) |
| **G6 – Salida de datos desde grillas** | Exportación XLSX estándar con modalidades de uso (básica, formateada, pivot) | [HU-006](../../03-historias-usuario/000-Generalidades/HU-006-exportacion-excel.md) | [SPEC-006](SPEC-006-exportacion-excel-grillas.md) |
| **G7 – Configuración por módulo sin deploy** | Parámetros en `PQ_PARAMETROS_GRAL` filtrados por programa/menú; solo edición de valores | [HU-007](../../03-historias-usuario/000-Generalidades/HU-007-Parametros-generales.md) | [SPEC-007](SPEC-007-parametros-generales.md) |
| **G8 – Eliminación estándar en ABM** | Patrón único de baja en grillas con permiso, confirmación y errores | [HU-009](../../03-historias-usuario/000-Generalidades/HU-009-eliminar-registros-grillas-abm.md) | [SPEC-009](SPEC-009-eliminar-registros-grillas-abm.md) |
| **G9 – Ayuda complementaria** | Acceso global a recurso externo configurable (“Asistente IA”) | [HU-010](../../03-historias-usuario/000-Generalidades/HU-010-acceso-ayuda-externa-chat-compartido.md) | [SPEC-010](SPEC-010-ayuda-externa-asistente-ia.md) |

**Nota:** HU-004 y HU-008 son deliberadamente **dos caras** de la misma capacidad G4 (base i18n vs. extensión de catálogo y UX con banderas); en un greenfield podrían haberse fusionado en una sola HU o mantenerse como incremento; lo que importa para este SPEC-CTX es que **ambas siguen una misma decisión de producto: idioma por usuario y consistencia global de UI**.

---

## 4. Reglas transversales (épica)

- Cumplir normas de **rendimiento y obtención de datos** del README de historias y reglas `.cursor` citadas allí.
- Grillas: estándar DevExtreme del repo (ver reglas de grillas y diccionario `pq_grid_layouts`).
- Parámetros: ver modelo `PQ_PARAMETROS_GRAL` y reglas UI por tipo de valor.

---

## 5. Dependencias entre capacidades (vista “hubiese sido antes”)

Un orden **plausible** para **derivar** HUs en entregas incrementales (no es cronología real del repo):

1. **G4** (idioma mínimo) y **G3** (pestañas) y **G5** (tema) alimentan el shell junto con la épica de Seguridad (sesión).
2. **G1** y **G6** y **G9** encadenan sobre el estándar de grillas.
3. **G7** habilita módulos sin tocar código para toggles operativos.
4. **G2** queda explícito como no aplicable en MONO pero alineado a documentación MULTI.

---

## 6. Fuera de alcance de esta épica

- Lógica de negocio específica de un módulo (solo patrones transversales).
- Tenancy multi-DB / selector de empresa en MONO (salvo SPEC de referencia SPEC-002).

---

## 7. Criterios verificables a nivel épica

- [ ] Toda **capacidad G1–G9** tiene al menos una **HU** y un **SPEC** operativo enlazados.
- [ ] Las preferencias de usuario **G3, G4, G5** no contradicen modelo MONO (sin empresa activa).
- [ ] Grillas siguen un patrón común para **G1, G6, G9** en torno a identificación `proceso`/`grid_id` donde aplique.

---

## 8. Reconstrucción y límites

Este documento **no** es un artefacto histórico recuperado de un ticket archivado: es una **reconstrucción coherente** elaborada **después** de existir las HU, para aproximar el **tipo de SPEC de contexto** que habría tenido sentido **antes** del paso “HU desde SPEC”.

Si el alcance real del producto difiere, prevalece el acuerdo del equipo y los **SPEC-update**; este CTX debe actualizarse como **mapa** de la épica, no como sustituto de los SPEC operativos por HU.

---

**Lectura sugerida siguiente:** índice [README](../README.md) y, por cada HU, su SPEC-NNN en esta misma carpeta.
