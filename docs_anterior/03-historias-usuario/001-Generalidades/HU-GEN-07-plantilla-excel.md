# HU-GEN-07-plantilla-excel — Plantilla modelo por proceso

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-07-plantilla-excel |
| **SPEC origen** | [SPEC-001-07-importar-excel.md](../../05-open-spec/001-Generalidades/SPEC-001-07-importar-excel.md) |
| **TR** | [TR-GEN-07-plantilla-excel.md](../../04-tareas/001-Generalidades/TR-GEN-07-plantilla-excel.md) |
| **MONO** | [PQ_EXCEL_Documento_Conceptual_Funcional_v3.md](../../00-contexto/_mono/importar-excel/PQ_EXCEL_Documento_Conceptual_Funcional_v3.md) §12 |
| **Épica** | 001 — Generalidades / Importar Excel |
| **Prioridad** | Could |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-06-11) |
| **Última actualización** | 2026-06-16 |
| **Dependencias** | HU-GEN-02-autorizacion-menu-api; tablas `PQ_EXCEL_PROCESOS`, `PQ_EXCEL_PROCESOS_CAMPOS` |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Flujo paso 2: exportar plantilla modelo | Narrativa, CA-01 |
| Diseño fijo por proceso (sin mapeo manual) | RN-01, RN-02 |
| Encabezados exactos sin tildes/símbolos | RN-03, CA-03 |
| Inventario tablas `PQ_EXCEL_PROCESOS*` | Alcance |
| Fuera de alcance MVP portal | Metadatos, veredicto B1 |

## Narrativa

Como **usuario autorizado en un proceso de importación**,  
quiero **descargar la plantilla Excel oficial del proceso**,  
para **completar el archivo con la estructura exacta que el sistema espera**.

## Contexto funcional

Cada proceso de importación tiene diseño fijo en `PQ_EXCEL_PROCESOS_CAMPOS`. La plantilla se genera desde esa definición: fila 1 con `NombreColumnaExcel`, orden según `OrdenCampo`, formato de columna según `TipoDato`, validaciones de celda según `LargoMaximo`/`CantidadDecimales`, comentarios en encabezado con `Observaciones` y línea **`OBLIGATORIO`** cuando `EsColumnaObligatoriaEstructural = 1`. En la pantalla del proceso, el botón **Descargar plantilla modelo** permanece siempre visible si `GeneraPlantilla = 1` (default catálogo). Solo procesos excepcionales con `GeneraPlantilla = 0` ocultan la acción.

## Alcance incluido

- Botón **Descargar plantilla modelo** **siempre visible** en la barra del proceso de importación (cuando `GeneraPlantilla = 1`; default catálogo).
- Generación `.xlsx` con **todas** las columnas activas del proceso en orden `OrdenCampo`.
- Fila 1: encabezados = `NombreColumnaExcel` exactos (fondo azul `#4472C4`, letras blancas).
- **Comentario Excel** en cada celda de encabezado:
  - línea `OBLIGATORIO` si `EsColumnaObligatoriaEstructural = 1`;
  - texto de `Observaciones` si no está vacío (en línea adicional o única si no es obligatorio).
- **Formato de columna** según `TipoDato` (`texto`, `codigo`, `entero`, `decimal`, `fecha`, `booleano`) en filas de datos (desde fila 2).
- Validación de celda Excel: `LargoMaximo`, `CantidadDecimales`; lista desplegable en `booleano` según `FormatoBooleanoPlantilla`.
- Plantilla sin filas de ejemplo: solo encabezados (usuario completa desde fila 2).
- Nombre de archivo sugerido: `{CodigoProceso}_plantilla.xlsx` (fijo; ver patrón UI embebida).

## Fuera de alcance

- Edición web de definición de campos (`PQ_EXCEL_PROCESOS_CAMPOS`).
- Múltiples variantes de plantilla por proceso.
- Importación del archivo (otras HU-GEN-07).
- MVP portal SistemaPartes (`INDICE-Sistema-Partes.md`).

## Reglas de negocio

1. La plantilla refleja **exactamente** los `NombreColumnaExcel` activos del proceso; sin alias ni corrección tipográfica.
2. Encabezados solo con letras sin tildes, números y espacios (contexto §4).
3. Si `GeneraPlantilla = 0`, la acción no se muestra.
4. Permisos: mismo criterio que ejecutar el proceso de importación (definir en TR por `CodigoProceso` / menú).
5. La plantilla exportada es la referencia normativa para validación estructural en carga.
6. El título del encabezado **no** incluye marcas de obligatoriedad; `OBLIGATORIO` va solo en el **comentario** de la celda.
7. `GeneraPlantilla` default en catálogo = `1`; valor `0` solo para procesos excepcionales sin modelo descargable.

## Decisiones cerradas (producto / B1)

| Tema | Decisión |
|------|----------|
| Fuente de columnas | `PQ_EXCEL_PROCESOS_CAMPOS` orden `OrdenCampo`, solo `Activo = 1` |
| Estilo encabezado | Fondo azul `#4472C4`, texto blanco (contexto §12.2) |
| Comentario obligatorio | `EsColumnaObligatoriaEstructural = 1` → línea `OBLIGATORIO` en comentario (§12.3) |
| Formato por tipo | Tabla §12.4 conceptual; booleano según `FormatoBooleanoPlantilla` |
| MVP portal | **Fuera** — epic posterior |

## Criterios de aceptación

- [ ] **CA-01:** Usuario con permiso ve botón **Descargar plantilla modelo** siempre en toolbar del proceso con `GeneraPlantilla = 1`.
- [ ] **CA-02:** Archivo `.xlsx` con fila 1 = todos los `NombreColumnaExcel` activos en orden `OrdenCampo`.
- [ ] **CA-03:** Encabezados coinciden carácter a carácter con catálogo (sin tildes ni símbolos prohibidos).
- [ ] **CA-04:** Columnas inactivas no aparecen en la plantilla.
- [ ] **CA-05:** Comentario con `Observaciones` cuando el campo tiene valor.
- [ ] **CA-06:** Campo con `EsColumnaObligatoriaEstructural = 1` tiene comentario con línea `OBLIGATORIO`.
- [ ] **CA-07:** Columnas `decimal`, `fecha`, `entero`, `booleano`, `texto`/`codigo` con formato Excel acorde a `TipoDato` (filas ≥ 2).
- [ ] **CA-08:** Proceso con `GeneraPlantilla = 0` no muestra el botón.
- [ ] **CA-09:** i18n en etiqueta del botón y mensajes de error; `data-testid` estable (`excelTemplateDownload`).

## Escenarios Gherkin

```gherkin
Feature: Plantilla modelo Excel por proceso

  Scenario: Descargar plantilla oficial
    Given un usuario con permiso en el proceso ARTICULOS_ALTA
    And el proceso tiene GeneraPlantilla activo
    When descarga la plantilla modelo
    Then obtiene un archivo xlsx
    And la fila 1 contiene exactamente los encabezados definidos en PQ_EXCEL_PROCESOS_CAMPOS

  Scenario: Sin generación de plantilla
    Given un proceso con GeneraPlantilla deshabilitado
    When el usuario abre la pantalla del proceso
    Then no ve la acción Descargar plantilla

  Scenario: Comentario con obligatorio y observaciones
    Given un campo Codigo con EsColumnaObligatoriaEstructural activo
    And Observaciones "Debe venir como texto"
    When descarga la plantilla
    Then el comentario de la celda Codigo contiene la linea OBLIGATORIO
    And el comentario contiene "Debe venir como texto"

  Scenario: Formato de columna decimal
    Given un campo Precio con TipoDato decimal y CantidadDecimales 2
    When descarga la plantilla
    Then la columna Precio tiene formato numerico con 2 decimales en filas de datos
```

## Supuestos explícitos

- Tablas `PQ_EXCEL_*` creadas según `PQ_EXCEL_SQL_Server_Tablas_y_Create.md`.
- Librería de generación Excel reutiliza patrones de exportación transversal (`HU-GEN-03-exportaciones`) donde aplique.

## Preguntas abiertas

| ID | Pregunta | Propuesta default |
|----|----------|-------------------|
| AMB-Q-07-01 | ¿Permiso dedicado `Permiso_Importar` vs permiso del proceso host? | Mismo permiso que la pantalla que invoca la importación (TR por proceso) |

## Veredicto B1

**Lista para TR** — epic importar Excel posterior al MVP portal. Resolver AMB-Q-07-01 en TR.










