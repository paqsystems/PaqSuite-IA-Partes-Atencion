# SPEC-001-10 - Chat Asistente IA

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionadas** | `HU-GEN-10-*` (a generar) |
| **Estado** | Especificado |
| **Revisión A1** | Apto con observaciones (2026-06-21) |

## Objetivo

Definir el marco funcional y técnico inicial para incorporar un chat asistente IA en SistemaPartes, abierto en nueva pestaña, con consumo a cargo del cliente o del usuario mediante esquema `BYOK`, soporte documental propio y capacidad opcional de imágenes.

## Estado de ejecución

Preparación funcional y técnica. No reemplaza la ayuda externa simple ya documentada; define una evolución del producto para apertura posterior de HU y TR específicas.

## Decisiones humanas

| Tema | Decisión |
|------|----------|
| Esquema de consumo | `BYOK` (`bring your own key`) a cargo del cliente o del usuario |
| Modalidad inicial de configuración | Solo por usuario |
| Persistencia de credenciales | Tabla separada de `users`: `PQ_PARTES_ASISTENTE_IA_CREDENCIALES` |
| Catálogo de proveedores | Tabla dedicada + catálogo editable en Markdown |
| Recomendación inicial | Priorizar `Ollama` cuando el cliente disponga de infraestructura propia o administrada |
| Soporte de imágenes | Sí, opcional según proveedor/modelo (`supportsVision`) |
| Persistencia de adjuntos | No se guardan en el sistema; se descartan luego del análisis |
| Punto de entrada | Menú avatar |
| Etiqueta inicial en UI | `Chat Asistente IA` |
| Configuración funcional | Cada usuario configura su proveedor/modelo/credencial desde su perfil |
| Apertura de experiencia | Nueva pestaña |
| Sin configuración válida | Se informa que falta configurar el chat y se muestra CTA de configuración |
| Corpus inicial Fase 1 | `99-manual-usuario` + documentación operativa funcional estable aprobada |
| Corpus excluido en Fase 1 | SPEC/HU/TR, documentación técnica de implementación, borradores/no aprobados y documentos conceptuales amplios de producto |
| Textos de experiencia | Mensaje inicial y cierre a soporte editables desde archivos Markdown |
| Regla de cierre a soporte | Solo cuando la IA no tenga confianza suficiente en la respuesta |
| Adjuntos de imágenes | En consultas con o sin texto, hasta 4 imágenes por interacción si el proveedor/modelo lo soporta |
| Soporte de proveedores del catálogo inicial | Todos entran como soportados en la primera HU |

## Fuente de verdad de producto

- `docs/00-contexto/_mono/01-experiencia-base/asistente-ia-byok.md`
- `docs/00-contexto/_mono/01-experiencia-base/asistente-ia-proveedores.md`
- `docs/00-contexto/_mono/01-experiencia-base/asistente-ia-mensaje-inicial.md`
- `docs/00-contexto/_mono/01-experiencia-base/asistente-ia-mensaje-cierre-soporte.md`
- `docs/02-producto/SistemaPartes/modelo-datos.md`
- `docs/05-open-spec/001-Generalidades/SPEC-001-01-experiencia-base.md`
- `docs/05-open-spec/001-Generalidades/SPEC-001-04-configuracion-global.md`
- `docs/05-open-spec/001-Generalidades/SPEC-001-07-importar-excel.md`

## Fuentes (contexto MONO)

Subcarpeta: `docs/00-contexto/_mono/01-experiencia-base/`

- `asistente-ia-byok.md`
- `asistente-ia-proveedores.md`
- `asistente-ia-mensaje-inicial.md`
- `asistente-ia-mensaje-cierre-soporte.md`
- `ayuda-externa-asistente.md` (como antecedente funcional)
- `menu-avatar.md`

## Alcance

- Definir el asistente como capacidad post-login, abierta en nueva pestaña desde una superficie estable del portal.
- Permitir consultas textuales apoyadas en documentación propia del sistema.
- Permitir configuración `BYOK` por usuario en la primera versión y evolución futura a variantes compartidas.
- Definir catálogo inicial de proveedores soportables y onboarding asociado.
- Definir experiencia conversacional base, incluyendo mensaje inicial y cierre orientado a soporte.
- Definir soporte opcional de imágenes para proveedores/modelos que lo permitan.
- Definir reglas de privacidad, transparencia y no persistencia de adjuntos.
- Dejar trazado el modelo de datos mínimo para proveedores y credenciales.

## Fuera de alcance

- Automatización autónoma de acciones dentro del sistema.
- Promesa de resolución infalible de errores o problemas operativos.
- Persistencia histórica de imágenes o archivos adjuntos en el portal.
- Envío automático a soporte desde el chat en esta primera definición.
- Reemplazo del flujo de ayuda externa simple ya documentado.

## Principio operativo de costos

La inferencia no debe ejecutarse con credenciales del proveedor del producto.

Regla base:

- la llamada al proveedor externo se hace con credenciales aportadas por el cliente o el usuario;
- el backend del portal media técnicamente la invocación;
- la facturación y consumo quedan asociados a la cuenta externa configurada.

## Actores y permisos funcionales

### Usuario autenticado

- accede al asistente desde el menú avatar mediante la opción `Chat Asistente IA`;
- realiza consultas textuales;
- adjunta imágenes cuando el proveedor/modelo configurado lo soporta;
- configura su propio proveedor, endpoint, modelo y credencial desde el flujo de perfil.

### Perfil del usuario

El flujo de perfil del usuario es la superficie funcional inicial para:

- alta de configuración del asistente;
- edición de proveedor/modelo/credencial;
- consulta del estado de habilitación;
- acceso a ayuda de onboarding por proveedor.

En esta primera versión no se define configuración compartida por tenant ni administración centralizada por otro actor.

## Configuración funcional mínima

La configuración del asistente no debe quedar hardcodeada en frontend.

Configuración mínima conceptual:

| Clave | Propósito |
|------|-----------|
| `providerId` | Proveedor o familia de integración |
| `baseUrl` | Endpoint base |
| `apiKey` | Credencial externa |
| `modelId` | Modelo seleccionado |
| `supportsVision` | Capacidad declarada de imágenes |
| `isEnabled` | Habilitación lógica |

La UI de configuración debe ayudar al operador a comprender:

- qué proveedor elegir;
- dónde obtener la credencial;
- qué valor usar para endpoint y modelo;
- si el plan o modelo admite imágenes.

La URL de onboarding y ayuda de configuración (`supportUrl`) debe resolverse desde el catálogo de proveedores, no desde la configuración sensible del usuario.

## Punto de entrada y comportamiento sin configuración

La entrada inicial del asistente debe ubicarse en el menú avatar.

Reglas de experiencia:

- la opción visible se denomina `Chat Asistente IA`;
- debe incluir icono distintivo respecto de otras opciones del avatar;
- convive con el acceso de ayuda externa simple mientras ambos flujos existan;
- al activarse abre una nueva pestaña del portal destinada al chat;
- al abrirse sin configuración válida no debe ocultarse ni fallar;
- debe mostrarse una superficie vacía indicando que falta configurar el chat y una CTA visible para ir a configuración de perfil.

El CTA debe orientar al usuario a completar al menos:

- proveedor;
- credencial;
- modelo;
- endpoint cuando el proveedor lo requiera.

## Modelo de datos base

### Tabla de proveedores

El producto debe contar con una tabla dedicada para catálogo funcional de proveedores:

- `PQ_PARTES_ASISTENTE_IA_PROVEEDORES`

Objetivo:

- centralizar nombre visible, URLs y capacidades declaradas;
- desacoplar catálogo funcional de la credencial concreta del usuario;
- facilitar seeds iniciales y desactivación controlada por despliegue.

### Tabla de credenciales

La persistencia sensible no debe resolverse en `users`.

El producto debe contar con una tabla dedicada:

- `PQ_PARTES_ASISTENTE_IA_CREDENCIALES`

Reglas mínimas:

- una configuración activa por usuario en primera versión;
- referencia al proveedor mediante `providerId`;
- credencial siempre cifrada;
- cifrado antes de persistir y descifrado solo al invocar proveedor;
- clave o mecanismo de descifrado fuera de la base de datos;
- sin exposición de credenciales completas en UI, logs, respuestas API ni errores.

## Catálogo inicial recomendado

El catálogo funcional inicial puede sembrarse con:

- `ollama`
- `openai`
- `anthropic`
- `googleGemini`
- `azureOpenAi`
- `openRouter`
- `groq`
- `mistral`

La fuente editable inicial del catálogo es:

- `docs/00-contexto/_mono/01-experiencia-base/asistente-ia-proveedores.md`

## Experiencia de usuario

El asistente debe presentarse como ayuda operativa contextual y no como soporte definitivo.

Comportamiento esperado:

- acceso desde una entrada visible y estable del portal;
- acceso inicial desde el menú avatar;
- apertura en nueva pestaña del portal;
- conservación de la pantalla actual;
- consultas libres en lenguaje natural;
- referencias a documentación fuente cuando sea posible;
- degradación controlada si la capacidad no está disponible.

### Límites iniciales de longitud de consulta

Límites recomendados para la primera versión:

- consulta de **texto solo**: hasta `2.000` caracteres;
- consulta de **texto + imágenes**: hasta `1.000` caracteres.

Estos límites buscan equilibrar:

- contexto suficiente para una duda operativa real;
- control de costo y complejidad en primera fase;
- prevención de consultas excesivamente largas o copiadas sin síntesis.

Sugerencias de UX asociadas:

- mostrar contador visible de caracteres;
- bloquear el envío al exceder el límite;
- mostrar mensaje claro indicando el máximo permitido;
- sugerir resumir el caso o dividirlo en varias consultas cuando se supere el máximo.

### Mensajes editables

El contenido textual de arranque y cierre no debe quedar hardcodeado en lógica.

Archivos de referencia:

- `docs/00-contexto/_mono/01-experiencia-base/asistente-ia-mensaje-inicial.md`
- `docs/00-contexto/_mono/01-experiencia-base/asistente-ia-mensaje-cierre-soporte.md`

Reglas:

- el mensaje inicial se muestra al abrir el asistente o iniciar una conversación nueva;
- el cierre se agrega solo cuando la IA no tenga confianza suficiente en la respuesta o indique que la orientación puede no ser suficiente;
- el mensaje de soporte debe admitir un placeholder o mecanismo equivalente para el dato de contacto real.

## Resultado esperado de las respuestas

La respuesta del asistente debe posicionarse como orientación útil y controlada.

Debe priorizar:

- explicación funcional del problema;
- pasos sugeridos;
- aclaración de validaciones, campos o restricciones;
- referencia al manual o documento aplicable;
- recomendación de contacto a soporte cuando la respuesta no resulte suficiente.

No debe asumirse que la IA reemplaza soporte humano, validación funcional ni decisión de negocio.

## Fuentes de conocimiento

La base principal debe ser documentación propia del producto.

Fuentes incluidas en Fase 1:

- `99-manual-usuario`;
- documentación operativa funcional estable aprobada por el equipo.

Fuentes excluidas en Fase 1:

- SPEC, HU y TR;
- documentación técnica de implementación de backend, frontend o infraestructura;
- borradores, documentos no aprobados o material transitorio;
- documentos conceptuales amplios de producto usados para definición estratégica.

La solución debería privilegiar respuestas con referencia documental antes que respuestas puramente generativas sin trazabilidad.

La ampliación del corpus a importación Excel, documentos funcionales de negocio u otras fuentes debe abrirse en fases posteriores mediante decisión explícita.

## Consulta con imágenes

La capacidad de adjuntar imágenes es opcional y depende de `supportsVision` y del modelo configurado.

Usos permitidos previstos:

- capturas de pantallas del sistema;
- imágenes de planillas Excel usadas por el sistema;
- documentos del negocio admitidos por el producto;
- mensajes visuales de error o advertencia.

Límites iniciales:

- formatos: `png`, `jpg`, `jpeg`, `webp`;
- tamaño máximo por archivo: `5 MB`;
- cantidad máxima por consulta: `4`.

La interacción puede incluir texto, solo imágenes o combinación de texto más imágenes dentro de esos límites, siempre que el proveedor/modelo activo lo soporte.

Si el proveedor o modelo no soporta visión, la UI debe informar indisponibilidad de adjuntos sin romper el flujo principal.

## Privacidad y ciclo de vida de archivos

Reglas mínimas:

- las imágenes no se guardan en el sistema;
- el archivo se usa solo para la consulta en curso;
- se envía al proveedor externo configurado;
- se descarta luego del análisis;
- no se persiste como histórico del portal.

Debe advertirse al usuario que el archivo será procesado por un proveedor externo configurado por su organización o por él mismo, según modalidad vigente.

## Seguridad y transparencia

El asistente debe operar con reglas explícitas de transparencia:

- informar que responde sobre documentación del sistema cuando corresponda;
- informar dependencia de proveedor externo configurado;
- informar indisponibilidad de una capacidad por configuración o plan;
- proteger credenciales y no exponer secretos en ninguna capa visible;
- validar y controlar llamadas desde backend.

## Evolución prevista

### Fase 1

Consultas textuales con base documental propia.

### Fase 2

Adjuntos de imágenes como apoyo a la consulta.

### Fase 3

Asistencia contextual enriquecida con combinación de conversación, pantalla actual, documentación y adjuntos.

## Entregables verificables

- SPEC base para HU y TR del chat asistente IA.
- Contexto y modelo de datos alineados en tabla de proveedores y tabla de credenciales.
- Catálogo inicial de proveedores editable en documentación.
- Mensajes inicial y final editables por archivo.
- Reglas de privacidad y límites de adjuntos sin ambigüedad.

## Criterios de aceptación medibles

- [x] Queda explícito que el consumo del modelo no depende de la cuenta del proveedor del producto.
- [x] Queda explícito que la primera versión se configura solo por usuario.
- [x] La persistencia de credenciales queda definida fuera de `users`.
- [x] Existe catálogo inicial de proveedores y fuente editable para onboarding.
- [x] La entrada inicial del asistente queda definida en menú avatar como `Chat Asistente IA`.
- [x] El chat se abre en nueva pestaña y no embebido en la pantalla actual.
- [x] Sin configuración válida se informa falta de configuración y se muestra superficie vacía con CTA de configuración, sin error fatal.
- [x] El chat define límite inicial de `2.000` caracteres para texto solo y `1.000` para texto + imágenes.
- [x] La UX muestra contador, bloqueo de envío y mensaje claro cuando se supera el límite.
- [x] El asistente define soporte opcional de imágenes con límites concretos.
- [x] El saludo inicial y el cierre de derivación a soporte quedan externalizados a archivos editables.
- [x] `supportUrl` queda definido como dato del catálogo de proveedores.
- [x] El cierre de soporte queda definido solo para respuestas sin confianza suficiente.
- [x] Todos los proveedores del catálogo inicial quedan contemplados como soportados en la primera HU.
- [x] Las reglas de no persistencia de adjuntos quedan explícitas.
- [x] El corpus inicial queda delimitado a documentación operativa estable y excluye material técnico/metodológico.
- [x] El resultado esperado de las respuestas queda delimitado como orientación y no como resolución garantizada.

## Trazabilidad HU (parte B)

| HU | TR | Foco | Orden D1 |
|----|-----|------|----------|
| [HU-GEN-10-catalogo-proveedores-ia](../../03-historias-usuario/001-Generalidades/HU-GEN-10-catalogo-proveedores-ia.md) | [TR-GEN-10-catalogo-proveedores-ia](../../04-tareas/001-Generalidades/TR-GEN-10-catalogo-proveedores-ia.md) | Catálogo inicial y onboarding | 1 |
| [HU-GEN-10-configuracion-asistente-ia](../../03-historias-usuario/001-Generalidades/HU-GEN-10-configuracion-asistente-ia.md) | [TR-GEN-10-configuracion-asistente-ia](../../04-tareas/001-Generalidades/TR-GEN-10-configuracion-asistente-ia.md) | Alta/edición proveedor, modelo y credencial | 2 |
| [HU-GEN-10-mensajes-asistente-ia](../../03-historias-usuario/001-Generalidades/HU-GEN-10-mensajes-asistente-ia.md) | [TR-GEN-10-mensajes-asistente-ia](../../04-tareas/001-Generalidades/TR-GEN-10-mensajes-asistente-ia.md) | Mensaje inicial, cierre y derivación a soporte | 3 |
| [HU-GEN-10-chat-documental](../../03-historias-usuario/001-Generalidades/HU-GEN-10-chat-documental.md) | [TR-GEN-10-chat-documental](../../04-tareas/001-Generalidades/TR-GEN-10-chat-documental.md) | Consulta textual con base documental | 4 |
| [HU-GEN-10-imagenes-asistente-ia](../../03-historias-usuario/001-Generalidades/HU-GEN-10-imagenes-asistente-ia.md) | [TR-GEN-10-imagenes-asistente-ia](../../04-tareas/001-Generalidades/TR-GEN-10-imagenes-asistente-ia.md) | Adjuntos y validaciones de imágenes | 5 |

---

## Revisión A1 — cierre (2026-06-21)

### Resultado general

| Campo | Valor |
|-------|--------|
| **Veredicto** | **Apto con observaciones** |
| **Puede pasar a HU (MVP portal)** | **No** — epic Chat Asistente IA fuera del MVP portal |
| **Puede abrir epic / HU futura** | **Sí** — contexto `_mono/01-experiencia-base/asistente-ia-*` cerrado para derivar HU |

### Checklist A1 (resumen)

| Área | Estado | Notas |
|------|--------|-------|
| Alcance / fuera de alcance | OK | BYOK, nueva pestaña, sin automatización autónoma |
| Actores / permisos | OK | Usuario autenticado; configuración solo por usuario en v1 |
| Modelo de datos | OK | Tablas `PQ_PARTES_ASISTENTE_IA_PROVEEDORES` y `PQ_PARTES_ASISTENTE_IA_CREDENCIALES` |
| Catálogo / onboarding | OK | Fuente editable `asistente-ia-proveedores.md` |
| UX entrada / sin config | OK | Menú avatar; CTA a preferencias |
| Límites texto e imágenes | OK | 2.000 / 1.000 caracteres; 4 imágenes × 5 MB |
| Privacidad adjuntos | OK | No persistencia en portal |
| Corpus Fase 1 | OK | `99-manual-usuario` + docs operativas; excluye SPEC/HU/TR |
| APIs | Pendiente TR | Contratos REST en TR-GEN-10-* (cerrados en C1) |
| Criterios aceptación | OK | Medibles y trazables a fuentes MONO |

### Ambigüedades menores (resueltas en C1)

| ID | Tema | Resolución |
|----|------|------------|
| AMB-M-10-01 | Deshabilitar vs eliminar credencial | `PATCH /status` solo cambia `isEnabled`; no borra secreto (TR configuración R-C1-04) |
| AMB-M-10-02 | Heurística baja confianza | Backend expone `requiresSupportFollowup`; slice mensajes solo renderiza (TR mensajes R-C1-05) |
| AMB-M-10-03 | Sincronización catálogo doc vs seed | Seed 1:1 con `asistente-ia-proveedores.md` (TR catálogo R-C1-07) |

### Veredicto

**Apto con observaciones** para cierre **A1 documental**. **Autoriza parte B** (HU derivadas).

---

## Parte B — cierre (2026-05-30)

### Resultado general

| Campo | Valor |
|-------|--------|
| **Veredicto B1** | **Cerrado** — 5 HU enriquecidas |
| **¿Puede pasar a parte C (TR) en MVP portal?** | **No** — epic posterior |
| **¿Listo para parte C cuando se priorice epic?** | **Sí** |

### Entregables parte B

| Entregable | Estado |
|------------|--------|
| `HU-GEN-10-catalogo-proveedores-ia` | Enriquecida |
| `HU-GEN-10-configuracion-asistente-ia` | Enriquecida |
| `HU-GEN-10-mensajes-asistente-ia` | Enriquecida |
| `HU-GEN-10-chat-documental` | Enriquecida |
| `HU-GEN-10-imagenes-asistente-ia` | Enriquecida |
| Índice HU README 001-Generalidades | Actualizado (2026-06-21) |

### Veredicto

**B1 cerrado** para SPEC-001-10. **C generada (2026-05-30).** Al priorizar el epic, ejecutar TR en orden catálogo → configuración → mensajes → chat → imágenes.

---

## Parte C — cierre (2026-06-21)

### Resultado general

| Campo | Valor |
|-------|--------|
| **Veredicto C1** | **Cerrado** — 5 TR generadas; aptas para D1 |
| **¿Puede pasar a parte D en MVP portal?** | **No** — epic posterior |
| **¿Listo para parte D cuando se priorice epic?** | **Sí** — ver [F-GEN-10-cierre-c1](../../04-tareas/001-Generalidades/F-GEN-10-cierre-c1.md) |

### Entregables parte C

| Entregable | Estado |
|------------|--------|
| `TR-GEN-10-catalogo-proveedores-ia` | Generada; C1 cerrado |
| `TR-GEN-10-configuracion-asistente-ia` | Generada; C1 cerrado |
| `TR-GEN-10-mensajes-asistente-ia` | Generada; C1 cerrado |
| `TR-GEN-10-chat-documental` | Generada; C1 cerrado |
| `TR-GEN-10-imagenes-asistente-ia` | Generada; C1 cerrado |
| `F-GEN-10-cierre-c1` | C1 formalizado |
| Índice TR README 001-Generalidades | Actualizado |

### Veredicto

**C1 cerrado** para SPEC-001-10. Epic **listo para D**; plan D1 ya incorporado en §3.3 de cada TR (2026-05-30).








