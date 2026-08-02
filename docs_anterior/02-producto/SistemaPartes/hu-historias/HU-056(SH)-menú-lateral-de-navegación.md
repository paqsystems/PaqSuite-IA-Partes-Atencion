# HU-056 – Menú lateral de navegación

## Épica
Épica 10: Dashboard / Navegación


**Rol:** Empleado / Empleado Supervisor / Cliente  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como usuario quiero acceder a cada proceso (informes, tareas, clientes, empleados, etc.) desde un menú lateral izquierdo en lugar de botones en el dashboard, para tener una navegación más clara y constante en todas las pantallas.

**Criterios de aceptación:**
- Existe un menú lateral fijo en la parte izquierda de la pantalla (junto al header común), visible en todas las pantallas autenticadas.
- Las opciones que hoy están como botones en la pantalla de dashboard se reubican como ítems del menú lateral (enlaces o botones que navegan a la ruta correspondiente).
- Cada ítem del menú muestra el nombre del proceso o pantalla destino (ej. "Consulta Detallada", "Tareas por Cliente", "Mis Tareas", "Proceso Masivo", etc.).
- Las opciones del menú se muestran u ocultan según el rol del usuario (empleado, supervisor, cliente), de la misma forma que actualmente en el dashboard (ej. solo supervisores ven "Tareas por Empleado", "Clientes", "Proceso Masivo", etc.).
- El menú lateral es colapsable o adaptable en pantallas pequeñas (responsive), según diseño (ej. icono hamburguesa que despliega el menú).
- La opción o sección correspondiente a la pantalla actual puede resaltarse visualmente (estado activo) para indicar dónde está el usuario.
- La pantalla de dashboard deja de mostrar el bloque de botones de acceso rápido (welcome-card-actions con todos los enlaces); el dashboard se centra en el resumen ejecutivo (KPIs, período, gráficos o listas resumen). El enlace "Panel" o "Inicio" puede permanecer en el header o como primera opción del menú lateral.
- Se mantiene la trazabilidad de `data-testid` en los enlaces del menú para pruebas E2E (o equivalentes).

**Notas de reglas de negocio:**
- Los destinos y permisos por rol son los mismos que en el dashboard actual (HU-051 y rutas existentes).
- El orden de presentación de los ítems en el menú lateral puede definirse en refinamiento o en un criterio de aceptación posterior; si no se especifica, el equipo propondrá un orden lógico (ej. Inicio, Perfil, Informes, Tareas, Gestión [Clientes, Empleados, Tipos], Proceso Masivo).

**Dependencias:** HU-051 (dashboard y rutas actuales).

---

## Orden de presentación

opció de inicio
separador
opciones de archivos : clientes, empleados, tipos de clientes, tipos de tareas
separador
opciones de partes : carga de tareas, mis tareas, procesamiento masivo
separador
opciones de informes : todos los informes definidos
