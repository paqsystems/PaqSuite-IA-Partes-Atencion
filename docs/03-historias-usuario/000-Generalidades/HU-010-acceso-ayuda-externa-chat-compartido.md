# HU-010 – Acceso a ayuda externa mediante chat compartido

## Épica
000 – Generalidades

## Clasificación
SHOULD-HAVE

## Rol
Usuario del sistema / Soporte funcional

## Narrativa

Como usuario que opera el sistema quiero disponer de una opción visible dentro del menú avatar que me lleve a un chat compartido con documentación operativa para consultar rápidamente cómo usar los procesos, resolver dudas frecuentes y obtener asistencia funcional sin salir del circuito habitual de trabajo.

## Contexto / Objetivo

La aplicación ya concentra documentación funcional y manuales de usuario. Como complemento, se desea ofrecer un acceso directo desde la interfaz hacia un chat compartido, especialmente útil para consultas operativas generales sobre los manuales almacenados en `docs/99-Manual-Usuario`.

La solución debe contemplar tres criterios definidos para este alcance:

1. **Entrada visible en el menú avatar** para abrir la ayuda desde cualquier pantalla del sistema.
2. **Redirección interna** como patrón preferido para no hardcodear directamente en la UI la URL de destino.
3. **URL configurable** para poder reemplazar el recurso de ayuda sin modificar el frontend.

El acceso inicial previsto para esta capacidad se llamará **Asistente IA** y utilizará inicialmente el siguiente chat compartido:

`https://chatgpt.com/g/g-68c834a4112c81918a16923a31fb3311-programacion-paqsuite-2-0-paqsystems`

## Criterios de aceptación

### AC1 – Opción visible en el menú avatar

- El acceso a la ayuda se expone dentro del **menú avatar** del usuario.
- El nombre visible de la opción es **`Asistente IA`**.
- La opción puede incluir un **ícono distintivo** que refuerce visualmente que se trata de un acceso a asistencia o consulta.
- La opción debe ser clara para el usuario y no confundirse con acciones de configuración personal o cierre de sesión.
- La opción debe poder automatizarse mediante `data-testid` o convención equivalente.

### AC2 – Apertura del recurso en nueva pestaña o ventana

- Al activar la opción **Asistente IA**, el sistema abre el recurso externo en una nueva pestaña o ventana del navegador.
- El usuario no debe perder la pantalla actual del proceso.
- La sesión del sistema debe permanecer intacta en la pestaña original.

### AC3 – Redirección interna como patrón preferido

- La aplicación no debe depender de una URL hardcodeada directamente en el componente visual del botón.
- Debe existir una ruta o mecanismo interno de redirección que actúe como intermediario entre la UI y el recurso externo.
- El usuario accede al recurso desde la app mediante esa ruta o acción interna.
- Si por limitaciones del navegador o del servicio externo la URL final resulta visible en la pestaña de destino, esto no se considera defecto mientras la navegación principal se resuelva mediante el patrón interno.

### AC4 – URL configurable

- La URL del recurso externo debe poder configurarse sin cambiar el código del frontend.
- La configuración debe permitir reemplazar el enlace si el chat compartido cambia, se regenera o se decide apuntar a otro recurso.
- La configuración puede ser global, por módulo o por recurso de ayuda, según la arquitectura elegida.
- Debe existir un valor inicial para el caso de Acopios con el enlace compartido definido en esta historia.

### AC5 – Comportamiento ante configuración faltante o inválida

- Si el recurso de ayuda no está configurado, el botón no debe producir una navegación rota.
- El sistema debe ocultar la acción o informar claramente que la ayuda no está disponible.
- Si la URL configurada es inválida o el recurso no responde, debe mostrarse un mensaje claro para el usuario y el proceso principal debe continuar operativo.

### AC6 – Alcance inicial

- La primera implementación se aplica como acceso **global** del sistema y no como ayuda exclusiva de un módulo puntual.
- El contenido del chat compartido cubre los manuales almacenados en `docs/99-Manual-Usuario`.
- El diseño debe permitir cambiar el recurso o ampliar su alcance sin modificar la experiencia del usuario.

### AC7 – Escenarios (Gherkin)

```gherkin
Feature: Acceso a ayuda externa desde la app

  Scenario: Usuario abre la ayuda desde el menú avatar
    Given el usuario está autenticado y visualiza el menú avatar
    And existe una URL configurada para el recurso de ayuda
    When hace clic en la opción "Asistente IA"
    Then el sistema abre una nueva pestaña o ventana
    And el usuario mantiene abierta la pantalla original del sistema

  Scenario: Recurso no configurado
    Given el usuario está autenticado y visualiza el menú avatar
    And no existe una URL configurada para el recurso de ayuda
    When intenta usar la opción "Asistente IA"
    Then el sistema no abre una navegación inválida
    And informa que la ayuda no está disponible o no muestra la opción

  Scenario: Cambio del enlace de ayuda sin modificar frontend
    Given el recurso de ayuda utiliza una URL configurable
    When administración actualiza el enlace configurado
    Then el botón de ayuda sigue funcionando
    And la UI no requiere cambios de código para usar el nuevo destino
```

## Reglas de negocio

- La ayuda externa es un complemento funcional y no debe bloquear la operatoria principal del sistema.
- El acceso visible para el usuario se resuelve desde el **menú avatar** bajo la opción **Asistente IA**.
- La navegación al recurso de ayuda debe abrirse fuera del flujo principal de la SPA.
- La URL real del destino externo no debe depender de un valor fijo embebido en el botón del frontend.
- La solución debe permitir cambiar el recurso de ayuda sin modificar la experiencia del usuario.
- El alcance funcional inicial es global para los manuales incluidos en `docs/99-Manual-Usuario`.

## Dependencias

- HU-003 – Apertura de opción de menú en misma o nueva pestaña, como referencia de comportamiento de navegación en nueva pestaña.
- Mecanismo de configuración disponible en la arquitectura del sistema (configuración backend, parámetros o equivalente).
- Menú avatar o componente equivalente de acciones globales del usuario.

## Especificación (Open-Spec)

- [SPEC-010 – Asistente IA / ayuda externa](../../05-open-spec/000-Generalidades/SPEC-010-ayuda-externa-asistente-ia.md)

## Referencias

- `docs/99-Manual-Usuario/modulo-acopios.md`
- `docs/99-Manual-Usuario/uso-de-grillas-y-layouts.md`
- `frontend/src/features/user/components/UserMenu.tsx` – ubicación sugerida en el menú avatar
- `frontend/src/app/Sidebar.tsx` – patrón actual de apertura en nueva pestaña
