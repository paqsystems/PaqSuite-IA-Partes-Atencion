---
specId: SPEC-008
titulo: Asistente IA y preferencias de modelo
estado: publicado
moduloCodigo: Partes
ultimaActualizacion: 2026-10-05
openSpec: docs/02-producto/Sistema-Partes-IA/12-asistente-ia-ayuda-y-chat-documental.md
---

# Asistente IA y preferencias de modelo

> Manual de usuario — corpus Asistente IA. No incluir detalles de implementación.

## Resumen

Desde el **menú del avatar** podés abrir **Asistente IA**: un chat de **ayuda documental** sobre Partes de Atención y las generalidades de uso del portal. Responde preguntas de operatoria; **no** carga, edita ni elimina tareas. Para completar un parte con ayuda del modelo usá la **captura inteligente** dentro de **Carga diaria** (ver [SPEC-004](./SPEC-004-operacion-carga-diaria.md)).

## Funcionamiento

### Usar el Asistente IA

1. Tras iniciar sesión, abrí el **avatar** (arriba a la derecha).
2. Elegí **Asistente IA**.
3. Si no tenés un proveedor de modelo configurado, el sistema te ofrece ir a **Preferencias**.
4. Escribí tu pregunta (por ejemplo: «¿Cómo cierro partes en lote?», «¿Qué significa cerrada?»).
5. Opcionalmente podés adjuntar imágenes si tu configuración lo permite (límites indicados en pantalla).
6. Leé la respuesta; si no alcanza, reformulá la pregunta o consultá el manual por proceso en el índice [README](./README.md).

### Configurar Preferencias (credencial de modelo)

1. En el avatar, abrí **Preferencias** (credencial LLM / modelo).
2. Cargá la clave o credencial que te proporcionó tu organización (**traé tu propia clave**).
3. Elegí el proveedor y modelo activos según las opciones de la pantalla.
4. Guardá. Volvé al Asistente IA o a la captura inteligente en carga diaria.

La misma configuración suele servir para el chat del avatar y para la captura inteligente del formulario de tareas.

## Particularidades

- El asistente se basa en la **documentación aprobada** del producto (este manual); no inventa pantallas ni reglas que no estén documentadas.
- No sustituye al soporte humano cuando el caso es excepcional o no está documentado.
- En **móvil** el chat puede estar disponible según menú; la captura inteligente en carga puede no estarlo (ver [SPEC-007](./SPEC-007-mobile-capacitor.md)).
- Para **integrar** otro sistema con Partes (consultas automáticas, altas masivas externas), pedí al administrador la URL de documentación **OpenAPI** del backend (`/api/documentation` en su instalación).

### Web vs mobile

| Tema | Web | Mobile |
|------|-----|--------|
| Asistente IA (avatar) | Sí | Según menú |
| Preferencias de modelo | Sí | Sí (avatar) |
| Captura inteligente en carga | Sí | No en esta versión |

## Condiciones de uso

- Sesión iniciada con perfil Partes válido.
- Credencial de modelo configurada para enviar consultas.
- Uso responsable de la clave (política de la organización).

## Errores posibles en este proceso

Identificador de catálogo: **P10**. Detalle completo en [CATALOGO-ERRORES.md](./CATALOGO-ERRORES.md).

| Clave | Qué hacer |
|-------|-----------|
| `chatAssistant.turnError` | Reintentar; verificar red y credencial en Preferencias |
| Estado sin proveedor (`chatAssistant.emptyState`) | Ir a Preferencias y cargar credencial |
| `partes.smartCapture.sinCredencial` | Misma acción si el mensaje aparece al usar captura inteligente |
| **P00** (`infra.*`, `auth.sessionExpired`, `partes.auth.*`, `mobile.routeExcluded`) | Ver catálogo transversal |

## Preguntas frecuentes

### ¿El Asistente IA guarda mi parte?

No. Solo orienta. Para grabar usá **Carga diaria** o la app móvil.

### ¿Puedo preguntar por datos de un cliente concreto?

El asistente explica **cómo** consultar en Informes o Dashboard; no reemplaza las pantallas de datos ni muestra datos en vivo fuera de lo que vos pegues en el chat.

### ¿Dónde está la documentación que usa el chat?

En este repositorio de manual: carpeta `docs/99-manual-usuario/` más generalidades del portal que aporta la instalación.
