# HU-GEN-07-carga-staging-excel — Carga de archivo y staging

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-07-carga-staging-excel |
| **SPEC origen** | [SPEC-001-07-importar-excel.md](../../05-open-spec/001-Generalidades/SPEC-001-07-importar-excel.md) |
| **TR** | [TR-GEN-07-carga-staging-excel.md](../../04-tareas/001-Generalidades/TR-GEN-07-carga-staging-excel.md) |
| **MONO** | [PQ_EXCEL_Documento_Conceptual_Funcional_v3.md](../../00-contexto/_mono/importar-excel/PQ_EXCEL_Documento_Conceptual_Funcional_v3.md) §5–§7 |
| **Épica** | 001 — Generalidades / Importar Excel |
| **Prioridad** | Could |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-06-11) |
| **Última actualización** | 2026-06-11 |
| **Dependencias** | HU-GEN-07-plantilla-excel (recomendada); HU-GEN-02-login-sesion; HU-GEN-02-autorizacion-menu-api |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Flujo pasos 4–9: archivo, hoja, validación estructural, lote, staging | CA conjunto |
| Solo `.xlsx`; fila 1 encabezado fija | RN-01, RN-02 |
| Columnas extra ignoradas; obligatorias faltantes = error estructural | RN-03, RN-04 |
| Normalizaciones trim / caracteres (§7) | RN-05, RN-06 |
| `FilaAjustadaAutomaticamente` solo auditoría | RN-07, decisión SPEC |
| Multiusuario: lote independiente | RN-08 |
| Estados lote hasta `lista_para_procesar` | RN-09 |

## Narrativa

Como **usuario que importa datos**,  
quiero **subir un archivo Excel, elegir la hoja y ver las filas cargadas en staging con sus validaciones**,  
para **revisar el contenido antes de confirmar el procesamiento final**.

## Contexto funcional

Tras seleccionar un `.xlsx`, el sistema lista hojas, valida estructura (encabezados exactos, sin duplicados ni celdas combinadas) y, si es válida, crea un lote en `PQ_EXCEL_IMPORTACIONES` y vuelca filas a `PQ_EXCEL_IMPORTACIONES_FILAS`. Validaciones de formato corren en frontend; validaciones de negocio en backend vía reglas del proceso y `HandlerBackend`. Errores estructurales bloquean antes del staging (`con_error_estructura`).

## Alcance incluido

- Selector de archivo `.xlsx` (DevExtreme `FileUploader` o equivalente).
- Listado de hojas del libro; elección de **una** hoja.
- Validación estructural: encabezado fila 1, columnas obligatorias, duplicados, encabezados vacíos, celdas combinadas.
- Creación de lote (`GuidImportacion`, usuario, terminal, archivo, hoja).
- Lectura de filas: ignorar filas totalmente vacías (contabilizar descartadas); completar `NULL` si faltan columnas al final.
- Normalización según `MantenerEspaciosEnBlanco` / `MantenerCaracteresEspeciales` (defaults del proceso, override por lote si la TR lo define).
- Persistencia staging + errores detallados (`PQ_EXCEL_IMPORTACIONES_FILAS_ERRORES`).
- Validación formato (tipos, largos) y negocio (handler) tras carga.
- Modo asíncrono para archivos grandes (`EsAsincronica`).
- Cancelación del lote antes del procesamiento final (`PuedeCancelar`, estado `cancelada`).

## Fuera de alcance

- Confirmación de procesamiento al destino (`HU-GEN-07-grilla-procesamiento-excel`).
- Grilla DevExtreme de resultados (misma HU hermana; API compartida).
- Historial de importaciones (`HU-GEN-07-historial-importaciones`).
- Detección automática de fila de encabezado distinta a 1.
- Control de duplicados dentro del Excel.
- MVP portal SistemaPartes.

## Reglas de negocio

1. Solo se acepta extensión `.xlsx` válida; otro formato → rechazo con mensaje i18n claro.
2. Encabezado **siempre** fila 1; coincidencia **exacta** con `NombreColumnaExcel` (sin alias).
3. Columnas adicionales en el archivo se **ignoran**; columnas obligatorias estructurales faltantes → error de archivo, sin staging útil.
4. Filas completamente vacías (todas las obligatorias vacías) se descartan y se contabilizan.
5. Tipo incorrecto, texto que supera `LargoMaximo` → error **por fila** (`TieneError = 1`), no error estructural.
6. Fórmulas: leer valor resultante; filas/columnas ocultas: procesar normalmente.
7. `FilaAjustadaAutomaticamente = true` si hubo trim (§7.1) o limpieza de no imprimibles (§7.2); **no** se expone en UI en esta etapa.
8. Cada ejecución es un lote aislado; ejecuciones paralelas no mezclan datos.
9. Estados de lote relevantes: `pendiente` → `validando` → `validada` o `con_error_estructura` → `lista_para_procesar` (si hay filas válidas o sin error estructural según reglas).

## Decisiones cerradas (producto / B1)

| Tema | Decisión |
|------|----------|
| Fila ajustada en UI | **No mostrar** al usuario (solo BD) — SPEC-001-07 |
| Errores estructurales vs fila | Estructural bloquea lote; fila permite política parcial en HU hermana |
| `PermiteSoloValidar` | Si `1`, el flujo termina en validación sin habilitar procesamiento (TR define UI) |
| Encoding encabezados | Sin tildes ni símbolos — validar en carga estructural |

## Criterios de aceptación

- [ ] **CA-01:** Usuario selecciona `.xlsx` y ve listado de hojas del libro.
- [ ] **CA-02:** Tras elegir hoja válida, se crea lote en `PQ_EXCEL_IMPORTACIONES` con metadatos de archivo y usuario.
- [ ] **CA-03:** Encabezado inválido (tildes, columna faltante, duplicado, combinadas) → estado `con_error_estructura` y mensaje explícito.
- [ ] **CA-04:** Filas vacías descartadas; contador `CantidadFilasDescartadas` actualizado.
- [ ] **CA-05:** Errores por fila persistidos en staging y tabla de errores detallados.
- [ ] **CA-06:** Normalización trim/caracteres según flags del lote; `FilaAjustadaAutomaticamente` en BD sin indicación UI.
- [ ] **CA-07:** Archivo grande puede procesarse asíncrono con notificación al finalizar (ver HU notificaciones).
- [ ] **CA-08:** Cancelación permitida antes de procesamiento final → estado `cancelada`.
- [ ] **CA-09:** i18n y `data-testid` en selector de archivo y hoja (`excelFileUpload`, `excelSheetSelect`).

## Escenarios Gherkin

```gherkin
Feature: Carga de Excel a staging

  Scenario: Carga estructural válida
    Given un archivo xlsx con encabezados exactos del proceso
    When el usuario selecciona la hoja correcta
    Then se crea un lote en PQ_EXCEL_IMPORTACIONES
    And las filas se persisten en PQ_EXCEL_IMPORTACIONES_FILAS
    And el lote queda lista_para_procesar o validada según reglas

  Scenario: Columna obligatoria faltante
    Given un archivo sin la columna obligatoria "Codigo"
    When intenta cargar el archivo
    Then el lote queda en con_error_estructura
    And no hay filas procesables en staging

  Scenario: Fila con error de tipo
    Given un archivo con una fila con fecha inválida en columna fecha
    When completa la validación
    Then la fila tiene TieneError = 1
    And el error queda registrado para la grilla

  Scenario: Trim automático sin aviso UI
    Given MantenerEspaciosEnBlanco = false
    And una celda con espacios al inicio y fin
    When se carga la fila
    Then FilaAjustadaAutomaticamente = true en BD
    And el usuario no ve indicación de ajuste en pantalla
```

## Supuestos explícitos

- Parser asume fila 1 como encabezado sin configuración adicional.
- `HandlerBackend` del proceso existe para validación de negocio post-formato.

## Preguntas abiertas

| ID | Pregunta | Propuesta default |
|----|----------|-------------------|
| AMB-Q-07-02 | ¿Override por lote de `MantenerEspaciosEnBlanco` / `MantenerCaracteresEspeciales`? | Usar defaults del proceso en MVP del motor; override en TR si se requiere |

## Veredicto B1

**Lista para TR** — epic posterior al MVP portal. Resolver AMB-Q-07-02 en TR.










