# HU-009 – Eliminar registros en grillas ABM

## Epica
000 – Generalidades

## Clasificacion
MUST-HAVE

## Rol
Usuario que opera con pantallas ABM en grillas

## Narrativa
Como usuario que opera con pantallas ABM quiero poder eliminar registros desde la grilla para mantener el catalogo actualizado, con confirmacion y respetando permisos y reglas de negocio.

## Contexto / Objetivo
En cada pantalla que presenta una grilla tipo ABM (listado con acciones por fila), debe existir la opcion de eliminar.

## Criterios de aceptacion

### AC1 – Disponibilidad del boton/accion Eliminar
- La grilla ABM muestra un control "Eliminar" por registro (habitualmente en la columna/area de acciones).
- La opcion "Eliminar" esta habilitada solo si el usuario tiene permiso de baja (`Permiso_Baja` asociado a la opcion de menu del ABM).
- Si el registro no es eliminable por estado/regla de negocio (ej. ya inactivo, dependencia, etc.), el control debe estar deshabilitado u oculto segun el caso.
- El control utiliza `data-testid` siguiendo la convencion de tests definida por cada feature (patron consistente con `docs/frontend/features/features-structure.md`), para que pueda automatizarse.
- La eliminacion se ejecuta sobre el registro correspondiente a la fila "activa" donde el usuario dispara la accion (es decir, el que corresponde al boton de eliminar de esa fila).

### AC2 – Confirmacion
- Al intentar eliminar, se muestra un modal de confirmacion.
- El modal muestra al menos una referencia identificadora del registro (ej. codigo y/o nombre) para evitar eliminaciones erroneas.
- Botones: "Cancelar" y "Confirmar".

### AC3 – Ejecucion de la eliminacion
- Al confirmar, el frontend llama al endpoint correspondiente del recurso (el mismo que usa la pantalla de ABM).
- Luego de un exito:
  - la grilla se refresca o se remueve la fila eliminada,
  - se muestra feedback (ej. toast/snackbar de eliminacion exitosa).
- La eliminacion respeta la politica del recurso:
  - si el negocio usa baja logica (ej. `activo=0`), el registro pasa a estado inactivo,
  - si el negocio usa borrado fisico, el registro desaparece.

### AC4 – Errores y restricciones
- Si el backend responde sin permiso (403/401) el control no debe aparecer o debe fallar con mensaje claro.
- Si el backend rechaza por restricciones de integridad (ej. dependencias), debe responder con un error consistente (ej. 422 con codigo/mensaje) y el frontend debe:
  - mostrar el mensaje al usuario,
  - no refrescar ni modificar la grilla en forma incorrecta.

### AC5 – Escenarios (Gherkin)
```gherkin
Feature: Eliminacion de registros en grillas ABM

  Scenario: Usuario con permiso elimina un registro
    Given el usuario esta autenticado
    And esta en una pantalla ABM con grilla que lista registros
    And el usuario tiene permiso de baja para esa opcion de menu
    When el usuario hace clic en "Eliminar" de un registro existente
    And confirma en el modal de confirmacion
    Then la eliminacion se ejecuta correctamente
    And el registro deja de estar visible o queda inactivo segun politica del recurso

  Scenario: Usuario sin permiso no puede eliminar
    Given el usuario esta autenticado
    And el usuario no tiene permiso de baja para esa opcion de menu
    When intenta ver la grilla ABM
    Then el control "Eliminar" no esta disponible (oculto o deshabilitado)

  Scenario: Restriccion de negocio impide eliminar
    Given el usuario esta autenticado
    And el registro tiene dependencias que impiden la eliminacion
    When el usuario hace clic en "Eliminar" y confirma
    Then se muestra el error devuelto por el backend
    And el registro permanece en la grilla
```

## Reglas de negocio
- La opcion de eliminar se controla por permisos de baja del usuario (`Permiso_Baja`).
- La grilla debe respetar las reglas de eliminabilidad del recurso (estado y restricciones).
- La UI debe manejar confirmacion, errores y refresco de grilla de forma consistente.

## Dependencias
- `.cursor/rules/24-devextreme-grid-standards.md` (Acciones CRUD por fila).
- Seguridad por permisos (`PQ_RolAtributo.Permiso_Baja`, asociado a `pq_menus`/menu ABM).
- Endpoints existentes de baja/eliminacion por recurso (ya definidos en cada modulo).

## Especificación (Open-Spec)

- [SPEC-009 – Eliminar registros en grillas ABM](../../05-open-spec/000-Generalidades/SPEC-009-eliminar-registros-grillas-abm.md)
