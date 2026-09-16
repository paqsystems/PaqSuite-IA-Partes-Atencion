# HU-004-update – Carga diaria: decimal, Excel, plantillas compartidas y tipo default

## Metadatos

| Campo | Valor |
|-------|-------|
| ID | HU-004-update |
| Título | Carga diaria — duración decimal, export Excel, plantillas GEN-11 y tipo de tarea default |
| Épica / carpeta | `100-SistemaPartes` |
| Clasificación | MUST-HAVE |
| Estado | Pendiente de Revisión |
| Última actualización | 2026-09-15 |
| SPEC origen | [SPEC-004-operacion-carga-diaria-update](../../../05-open-spec/updates/100-SistemaPartes/SPEC-004-operacion-carga-diaria-update.md) (base: [SPEC-004](../../../05-open-spec/100-SistemaPartes/SPEC-004-operacion-carga-diaria.md)) |
| TR relacionada(s) | [TR-004-operacion-carga-diaria-update](../../../04-tareas/updates/100-SistemaPartes/TR-004-operacion-carga-diaria-update.md) |
| HU base | [HU-004-operacion-carga-diaria](../../100-SistemaPartes/HU-004-operacion-carga-diaria.md) |

## Origen

| Campo | Valor |
|-------|-------|
| Fuente | `00-ControlCalidad-PQ` |
| Fecha | 09/08/2026 |
| Control | #3 |
| Ítems | Duración decimal; Plantillas de otros usuarios; Inicializar tipo de tarea |

## Estado de alcance

| Campo | Valor |
|-------|-------|
| Estado | Pendiente de Revisión |

---

## Narrativa (delta)

Como asistente o supervisor  
quiero ver la duración también en horas decimales, exportar la grilla a Excel, reutilizar plantillas de otros usuarios y que el alta preseleccione el tipo de tarea marcado como default  
para operar la carga diaria sin reprocesar formatos ni cargar el tipo a mano cada vez.

---

## Alcance incluido (solo este update)

- Columna visible de duración decimal (`minutos/60`) además de `hh:mm`; ambas en export Excel.
- Export Excel GEN de la grilla de carga diaria (Must).
- Listado de plantillas GEN-11 **compartido** (ver y aplicar las de otros; sin parámetro opt-in).
- Alta de parte nuevo: SelectBox Tipo de tarea inicia con el registro `is_default = 1` (ya en SPEC-004 §4.4; este update lo eleva a CA verificable del CC).

## Fuera de alcance

- Redefinir tramo, persistencia en minutos o universo de tipos.
- Importación Excel (SPEC-009).
- Smart Capture (HU-010-update).
- Proceso masivo (HU-005-update).

---

## Criterios de aceptación

- [ ] **CA-CC3-01** En la grilla de carga diaria se ve duración en `hh:mm` **y** en decimal. Ejemplos: `02:15` → `2.25`; `15:30` → `15.5`; `14:45` → `14.75`.
- [ ] **CA-CC3-02** La grilla permite exportar a Excel (toolbar GEN); el archivo incluye la columna decimal (numérica) además de la de reloj.
- [ ] **CA-CC3-03** Dos usuarios en la misma instalación: cada uno ve las plantillas del otro en Carga de Partes Diarios y puede aplicarlas; no puede borrar las ajenas. No hay parámetro Partes para ocultarlas.
- [ ] **CA-CC3-04** Al abrir **Nueva tarea**, Tipo de tarea muestra el tipo con `is_default` (no queda en «Seleccionar…» si existe un default usable). Al elegir cliente, se mantiene si sigue en el universo o se reasigna el default del universo.

### Gherkin (delta)

```gherkin
Scenario: Duración decimal en grilla y Excel
  Given una tarea de 135 minutos (02:15)
  When listo carga diaria
  Then veo duración "02:15" y decimal 2.25
  When exporto a Excel
  Then el archivo contiene 2.25 (o equivalente numérico)

Scenario: Plantilla ajena visible
  Given el usuario B guardó un layout "Compacta" en carga diaria
  When el usuario A abre Carga de Partes Diarios
  Then A ve "Compacta" en el selector de plantillas
  And A puede aplicarla y no puede eliminarla

Scenario: Tipo default en alta
  Given existe un tipo de tarea usable con is_default = true
  When abro Nueva tarea
  Then el selector Tipo de tarea muestra ese tipo (no placeholder vacío)
```

---

## Historial

| Fecha | Cambio |
|-------|--------|
| 2026-09-15 | Parte G CC-PQ #3 (09/08/2026). |
