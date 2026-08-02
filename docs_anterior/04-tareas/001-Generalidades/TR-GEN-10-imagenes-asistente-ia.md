# TR-GEN-10-imagenes-asistente-ia — Adjuntos de imágenes en el chat

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-10-imagenes-asistente-ia](../../03-historias-usuario/001-Generalidades/HU-GEN-10-imagenes-asistente-ia.md) |
| **SPEC relacionada** | [SPEC-001-10-chat-asistente-ia](../../05-open-spec/001-Generalidades/SPEC-001-10-chat-asistente-ia.md) |
| **Épica** | 001-Generalidades |
| **Prioridad** | Should |
| **Dependencias** | TR-GEN-10-configuracion-asistente-ia; TR-GEN-10-chat-documental |
| **Estado** | Pendiente |
| **Última actualización** | 2026-05-30 |

**Origen:** [HU-GEN-10-imagenes-asistente-ia](../../03-historias-usuario/001-Generalidades/HU-GEN-10-imagenes-asistente-ia.md)  
**Referencia SPEC:** [SPEC-001-10-chat-asistente-ia](../../05-open-spec/001-Generalidades/SPEC-001-10-chat-asistente-ia.md)  
**Normas transversales:** [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) (**obligatorio**)

---

## 1) HU Refinada (resumen)

### Título
Adjuntos de imágenes en el chat.

### Narrativa
Como usuario autenticado con un proveedor compatible quiero adjuntar imágenes a mi consulta del chat para explicar mejor errores, pantallas o documentos relevantes del sistema.

### In scope / Out of scope
- **In scope:** adjuntar imágenes a una consulta, interacciones con texto/solo imágenes/combinadas, validación de formatos `png`, `jpg`, `jpeg`, `webp`, máximo `5 MB` por archivo, máximo `4` imágenes por interacción, mensajes controlados cuando la capacidad no esté disponible.
- **Out of scope:** persistencia de imágenes en el portal, edición de imágenes en UI, garantías de OCR o interpretación perfecta, tipos de archivo distintos a los definidos.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: Un usuario con proveedor compatible puede adjuntar imágenes válidas.
- **AC-02**: El sistema admite interacciones con texto, solo imágenes o combinación de ambos.
- **AC-03**: El sistema rechaza formatos no admitidos con error controlado.
- **AC-04**: El sistema rechaza imágenes que exceden `5 MB` con error controlado.
- **AC-05**: El sistema rechaza más de `4` imágenes por interacción.
- **AC-06**: Si el proveedor/modelo no soporta visión, la UI informa indisponibilidad.
- **AC-07**: Las imágenes no se persisten como histórico del portal.

### Escenarios Gherkin

```gherkin
Feature: Adjuntos de imágenes en el chat

  Scenario: Enviar consulta con texto e imágenes
    Given un usuario autenticado con proveedor compatible con imágenes
    When adjunta una o más imágenes válidas
    And envía una consulta
    Then la consulta es procesada correctamente

  Scenario: Proveedor sin soporte de visión
    Given un usuario autenticado con proveedor sin soporte de imágenes
    When intenta adjuntar una imagen
    Then ve un mensaje de indisponibilidad
    And el chat no se rompe

  Scenario: Exceder límites de imágenes
    Given un usuario autenticado
    When intenta adjuntar un archivo inválido o exceder los límites
    Then recibe un error controlado
    And la interacción no se envía
```

---

## 3) Reglas de Negocio

1. **RN-01**: Solo se permiten imágenes si el proveedor/modelo activo soporta visión.
2. **RN-02**: La interacción puede tener texto, imágenes o ambos.
3. **RN-03**: Cada archivo no puede superar `5 MB`.
4. **RN-04**: El máximo por interacción es `4` imágenes.
5. **RN-05**: Las imágenes se envían al proveedor externo configurado y se descartan tras el análisis.
6. **RN-06**: Un formato inválido o un exceso de tamaño no debe romper la aplicación.
7. **RN-07**: La disponibilidad de adjuntos se decide con la configuración activa del usuario (`supportsVision` resuelto server-side) y no solo con el catálogo declarado.
8. **RN-08**: En Fase 1 el transporte de adjuntos se resuelve en el mismo endpoint del chat mediante payload JSON con `contentBase64`.
9. **RN-09**: La interacción es válida si contiene texto, imágenes o ambos; si no hay texto, debe existir al menos una imagen válida.
10. **RN-10**: El límite de `5 MB` se evalúa sobre el tamaño real del archivo o bytes decodificados, no sobre la longitud del string base64.

---

## 3.1) Informe C1 — Revisión de ambigüedad (2026-05-30)

**Fuentes revisadas:** HU-GEN-10-imagenes-asistente-ia, SPEC-001-10-chat-asistente-ia, TR-GEN-10-chat-documental, TR-GEN-10-configuracion-asistente-ia, TR-GEN-10-catalogo-proveedores-ia, `docs/04-tareas/_NORMAS-TRANSVERSALES-TR.md`, `docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md`, contrato base `POST /api/v1/chat-assistant/messages` y resoluciones C1 previas del bloque GEN-10.

### Resultado general

- **Estado:** Apto
- **Puede pasar a D1:** **Sí**, con resoluciones C1 aceptadas para cerrar la fuente de `supportsVision`, el transporte del adjunto y el manejo no persistente del archivo.

### Aceptación stakeholder (2026-05-30)

Se aceptan los supuestos detectados y las recomendaciones de ajuste de esta revisión. El slice queda cerrado con:

- fuente explícita de `supportsVision` como parte de la configuración activa del usuario;
- contrato JSON definitivo con `contentBase64` para esta fase;
- semántica explícita de request válido para interacciones con solo imágenes;
- validación del límite de `5 MB` sobre tamaño real del archivo/bytes decodificados.

### Ambigüedades críticas

| ID | Tema | Riesgo | Resolución propuesta (→ D1) |
|----|------|--------|------------------------------|
| AMB-C01 | **Fuente efectiva de `supportsVision` no cerrada en esta TR** | La HU habla de proveedor/modelo activo y el catálogo solo declara capacidad; sin una fuente final, frontend y backend pueden permitir o bloquear distinto. | **D1-1:** usar como fuente operativa la configuración personal resuelta del usuario (`supportsVision` devuelto por `TR-GEN-10-configuracion-asistente-ia`); el catálogo solo informa capacidad declarada inicial. |
| AMB-C02 | **Transporte del archivo no decidido definitivamente** | La TR muestra `contentBase64`, pero también deja abierta la puerta a `multipart/form-data`; dos implementaciones distintas romperían el contrato del endpoint compartido. | **D1-2:** para Fase 1 mantener el mismo endpoint `POST /api/v1/chat-assistant/messages` con payload JSON y `contentBase64`; no abrir multipart ni endpoint separado en este slice. |
| AMB-C03 | **No persistencia insuficientemente operacionalizada** | “No guardar imágenes” puede terminar igual dejando archivos temporales en disco, logs o colas si no se detalla el ciclo técnico. | **D1-3:** procesar adjuntos en memoria dentro de la request; prohibido persistirlos como histórico, storage local o referencia durable. Si una librería exige temporalidad técnica, debe eliminarse dentro del mismo request y sin exponer ruta. |
| AMB-C04 | **Regla de “solo imágenes” vs `message` obligatorio** | La HU admite interacciones solo imágenes, pero el slice base del chat venía con `message` obligatorio; sin coordinación, una capa permitirá y otra rechazará. | **D1-4:** al activarse imágenes, el request es válido si trae texto, imágenes o ambos; `message` puede quedar vacío solo cuando exista al menos una imagen válida. |
| AMB-C05 | **Cálculo exacto del límite `5 MB` no especificado** | Validar por tamaño del archivo seleccionado, por longitud base64 o por bytes decodificados puede producir rechazos distintos. | **D1-5:** validar por tamaño real del archivo/bytes decodificados; la longitud del base64 no es la fuente de verdad funcional. |
| AMB-C06 | **403 no justificado por el patrón actual** | Igual que en el resto del bloque personal, no hay policy adicional explicitada; documentar 403 como obligatorio induciría una regla nueva fuera de TR. | **D1-6:** `401` y `422` quedan obligatorios; `403` no aplica en MVP actual salvo incorporación explícita de policy. |

### Ambigüedades menores

| ID | Tema | Resolución propuesta (→ D1) |
|----|------|------------------------------|
| AMB-M01 | Remoción/reorden | La UI permite quitar imágenes antes de enviar; reordenarlas queda fuera de este slice. |
| AMB-M02 | MIME vs extensión | Backend valida tipo real/MIME permitido además de la extensión visible. |
| AMB-M03 | Límite de texto combinado | Cuando existan imágenes, se aplica el máximo de `1.000` caracteres de texto definido en el slice de chat. |
| AMB-M04 | Previsualización | Mostrar preview básica en cliente sin persistir ni subir el archivo antes del submit. |
| AMB-M05 | Errores controlados | Los mensajes de formato/tamaño/límite deben impedir el envío antes de invocar la API cuando sea posible. |

### Contradicciones TR ↔ HU ↔ slices hermanos

| Contradicción | Resolución |
|---------------|------------|
| La HU permite consultas con solo imágenes, pero el chat base venía modelado alrededor de `message` obligatorio | **Cerrado:** con imágenes activas, el request puede tener texto, imágenes o ambos; no se exige texto si hay al menos una imagen válida. |
| El catálogo declara `supportsVision`, pero el SPEC aclara que también depende del modelo configurado | **Cerrado:** la fuente operativa es la configuración activa del usuario, no solo el catálogo. |
| La TR deja abierta la posibilidad de multipart/endpoint separado, aunque el objetivo funcional es no persistir y mantener un único flujo | **Cerrado:** Fase 1 usa el endpoint existente con JSON + `contentBase64`. |

### Supuestos detectados

- El proveedor externo acepta imágenes en un formato que puede derivarse del payload JSON construido por backend.
- La validación principal de formato/tamaño puede hacerse en frontend y repetirse en backend.
- La previsualización de imágenes en UI no implica upload anticipado ni almacenamiento remoto.

### Preguntas para decisión humana

- Ninguna adicional. Las decisiones bloqueantes quedan cerradas con la aceptación stakeholder de esta revisión.

### Recomendaciones de ajuste de la TR

- **Aplicadas en esta revisión.**

### Veredicto C1

**Apta para D1 — C1 definitivamente cerrado.** El slice queda implementable con fuente de capacidad visual cerrada, contrato de adjunto explícito y procesamiento efímero sin persistencia.

---

## 3.2) Resoluciones C1 — pre-D1 (2026-05-30)

| # | Tema | Decisión |
|---|------|----------|
| R-C1-01 | Fuente `supportsVision` | La disponibilidad de adjuntos se decide con la configuración activa del usuario (`supportsVision` resuelto server-side), no solo con el catálogo. |
| R-C1-02 | Endpoint | Las imágenes se integran en `POST /api/v1/chat-assistant/messages`; no se crea endpoint nuevo en esta fase. |
| R-C1-03 | Transporte | El payload usa JSON con `images[].fileName`, `images[].mimeType`, `images[].contentBase64`. |
| R-C1-04 | Validez del request | La interacción puede contener texto, solo imágenes o ambos; si no hay texto, debe existir al menos una imagen válida. |
| R-C1-05 | Validación de tamaño | El máximo `5 MB` se calcula sobre el tamaño real del archivo/bytes decodificados. |
| R-C1-06 | No persistencia | Los adjuntos se procesan de forma efímera y no quedan guardados como histórico, archivo local permanente ni referencia durable. |
| R-C1-07 | Validación de tipos | Se admiten solo `png`, `jpg`, `jpeg`, `webp`, validando extensión visible y MIME/tipo real. |
| R-C1-08 | Seguridad | `401` y `422` son obligatorios; `403` no aplica en MVP actual salvo policy nueva. |
| R-C1-09 | UX | La UI permite remover imágenes antes del envío; reordenar queda fuera de alcance. |

---

## 3.3) Plan D1 — Implementación (2026-05-30)

### Alcance entendido

Extender el chat documental para aceptar adjuntos de imágenes válidas cuando la configuración activa del usuario soporte visión, manteniendo el mismo endpoint del chat y procesamiento efímero sin persistencia. El slice cubre selección/previsualización cliente, validaciones de formato/tamaño/cantidad y adaptación backend del payload JSON con `contentBase64`. **Fuera:** storage permanente, edición/reordenamiento avanzado, OCR garantizado y formatos distintos de `png`, `jpg`, `jpeg`, `webp`.

### Fuentes leídas

- SPEC: `docs/05-open-spec/001-Generalidades/SPEC-001-10-chat-asistente-ia.md`
- HU: `docs/03-historias-usuario/001-Generalidades/HU-GEN-10-imagenes-asistente-ia.md`
- TR: `docs/04-tareas/001-Generalidades/TR-GEN-10-imagenes-asistente-ia.md`
- TR hermanas: `TR-GEN-10-chat-documental`, `TR-GEN-10-configuracion-asistente-ia`, `TR-GEN-10-catalogo-proveedores-ia`
- Código: `frontend/src/shared/http/client.ts`, `frontend/src/app/router/protectedRoutes.tsx`, `backend/routes/api.php`, `backend/app/OpenApi/OpenApiSchemas.php`, `backend/tests/Feature/OpenApiDocumentationTest.php`

### Impacto esperado

#### Base de datos

- Sin tabla nueva.
- Lectura de la configuración activa para resolver `supportsVision`.
- Sin persistencia de imágenes ni histórico binario en base de datos.

#### Backend

- Extensión del endpoint `POST /api/v1/chat-assistant/messages` y de su request validator para soportar `images[]`.
- Adaptación del service del chat para validar soporte de visión, cantidad, tipo y tamaño.
- Procesamiento en memoria y descarte al finalizar la request.
- Actualización de OpenAPI y tests feature del chat.

#### Frontend

- Selector de imágenes y previsualización básica dentro del composer.
- Validador cliente para cantidad máxima, tipos permitidos y `5 MB`.
- Ajuste del payload del chat para enviar `images[].fileName`, `images[].mimeType`, `images[].contentBase64`.
- Bloqueo visible cuando `supportsVision=false`.

#### Tests

- Unit frontend para validadores y request válido con solo imágenes.
- Integration backend para payload con imágenes válidas e inválidas.
- E2E de formatos, tamaño, cantidad, indisponibilidad y caso “solo imágenes”.

#### Documentación

- Confirmar que la fila de `POST /api/v1/chat-assistant/messages` cubre adjuntos en `matriz-permisos-mvp.md`.
- Documentar el payload extendido en OpenAPI sin crear endpoint nuevo.

#### DevOps

- Sin cambios de infraestructura.
- Vigilar el tamaño del payload JSON en entornos reales por el uso de `contentBase64`.

### Decisiones D1 (cerradas en C1)

| ID | Tema | Decisión |
|----|------|----------|
| D1-1 | Fuente `supportsVision` | La UI y backend toman como referencia la configuración activa del usuario, no solo el catálogo. |
| D1-2 | Endpoint | Se reutiliza `POST /api/v1/chat-assistant/messages`; no se crea endpoint nuevo ni multipart en Fase 1. |
| D1-3 | Payload | `images[].fileName`, `images[].mimeType`, `images[].contentBase64` dentro del body JSON. |
| D1-4 | Validez request | La interacción puede ser texto, imágenes o ambos; con imágenes activas, `message` puede ir vacía si hay al menos una imagen válida. |
| D1-5 | Validaciones | Máximo `4` imágenes, tipos `png/jpg/jpeg/webp`, `5 MB` medidos sobre tamaño real/bytes decodificados. |
| D1-6 | No persistencia | Procesamiento efímero en memoria; prohibido storage durable o histórico binario. |
| D1-7 | Seguridad | 401 y 422 obligatorios; 403 no aplica en MVP actual salvo policy nueva. |

### Orden de trabajo

1. Extender modelo/request del chat para aceptar adjuntos JSON con imágenes.
2. Implementar validación backend de soporte, tamaño, formato y cantidad.
3. Implementar picker/previews y validaciones cliente en el composer.
4. Ajustar el submit del chat para casos texto+imagen y solo imágenes.
5. Añadir tests unit/integration/E2E y actualizar OpenAPI/matriz.

### Archivos previstos

| Capa | Archivos |
|------|----------|
| Backend | `backend/app/Http/Controllers/ChatAssistantMessageController.php`, `backend/app/Http/Requests/SendChatAssistantMessageRequest.php`, `backend/app/Services/ChatAssistant/ChatAssistantMessageService.php`, `backend/app/Support/ChatAssistantErrorCodes.php`, `backend/routes/api.php`, `backend/app/OpenApi/OpenApiSchemas.php`, `backend/tests/Feature/ChatAssistantMessageTest.php`, `backend/tests/Feature/OpenApiDocumentationTest.php` |
| Frontend | `frontend/src/features/chatAssistant/components/ChatAssistantImagePicker.tsx`, `frontend/src/features/chatAssistant/components/ChatAssistantImageValidationMessage.tsx`, `frontend/src/features/chatAssistant/components/ChatAssistantComposer.tsx`, `frontend/src/features/chatAssistant/utils/validateChatAssistantImages.ts`, `frontend/src/features/chatAssistant/model/chatAssistantImage.ts`, `frontend/src/features/chatAssistant/api/sendChatAssistantMessage.ts` |
| E2E | `frontend/tests/e2e/chat-assistant.spec.ts` |
| Docs | `docs/04-tareas/001-Generalidades/matriz-permisos-mvp.md` |

### Tests a ejecutar

- Frontend unit: formato, tamaño, cantidad, `supportsVision` y request válido con solo imágenes.
- Backend: `ChatAssistantMessageTest` con 200/401/422, caso sin soporte de visión, formato inválido, `>5 MB`, `>4` imágenes y `message` vacía con imagen válida.
- Backend: `OpenApiDocumentationTest`.
- E2E: adjunto válido, rechazo por formato/tamaño/cantidad, indisponibilidad por proveedor y flujo solo imágenes.

### Dudas / bloqueos

- Verificar durante D el impacto práctico del tamaño total del JSON con `contentBase64` sobre el límite actual del stack HTTP; funcionalmente el slice queda cerrado.

### Confirmación de alcance

- Sin cambio funcional fuera de SPEC/HU/TR: **Sí**. El plan amplía únicamente el chat para adjuntar imágenes válidas dentro del mismo flujo existente, sin introducir storage, endpoints nuevos ni capacidades OCR extra no documentadas.

## 4) Impacto en Datos

### Tablas afectadas
- Sin tabla nueva obligatoria en este slice.
- Lectura indirecta de `PQ_PARTES_ASISTENTE_IA_CREDENCIALES` y/o catálogo para resolver soporte de visión.

### Seed mínimo para tests
- Usuario autenticado con configuración válida y proveedor/modelo con soporte de visión.
- Usuario autenticado con configuración válida y proveedor/modelo sin soporte de visión.
- Archivos de prueba válidos en formatos admitidos.
- Archivo de prueba que exceda `5 MB`.
- Caso de interacción válida con solo imágenes y `message` vacío.

---

## 5) Contratos de API y OpenAPI

> **Norma transversal:** cumplir [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) §1–§2. Código, matriz y OpenAPI deben coincidir. Envelope: [`docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md`](../../00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md).

### 5.1 Endpoints del slice

No introduce endpoint exclusivo si los adjuntos se resuelven dentro de `POST /api/v1/chat-assistant/messages`.

En esta fase el slice queda cerrado sobre el endpoint existente `POST /api/v1/chat-assistant/messages` con transporte JSON + `contentBase64`.

Si una fase posterior necesitara multipart/form-data, endpoint separado o storage temporal explícito, deberá abrirse ajuste controlado del slice sin ampliar alcance funcional.

### 5.2 Integración con el endpoint del chat

El request del chat debe poder transportar una estructura equivalente a:

```json
{
  "message": "Necesito ayuda con esta pantalla",
  "images": [
    {
      "fileName": "captura.png",
      "mimeType": "image/png",
      "contentBase64": "..."
    }
  ]
}
```

Validaciones funcionales mínimas:

- hasta `4` imágenes por interacción;
- formatos `png`, `jpg`, `jpeg`, `webp`;
- máximo `5 MB` por archivo medido sobre tamaño real/bytes decodificados;
- rechazo controlado si `supportsVision` no aplica para la configuración activa.
- el request es válido con texto, solo imágenes o combinación de ambos; si `message` está vacía, debe existir al menos una imagen válida.

**OpenAPI (L5-Swagger):**

- [ ] Payload documentado en controller/DTO del slice de chat.
- [ ] Restricciones de cantidad, formato y tamaño documentadas.
- [ ] `security` declarado.
- [ ] Header `X-Paq-Cliente` documentado.
- [ ] Respuestas 401 y 422 documentadas.
- [ ] 403 documentado solo si en una oleada posterior se agrega policy específica.
- [ ] Envelope documentado.
- [ ] Verificado en `/api/documentation`.

### 5.3 Actualización matriz permisos

- [ ] Si no hay endpoint nuevo, confirmar que la fila de `POST /api/v1/chat-assistant/messages` cubre adjuntos de imágenes.
- [ ] Si hubiera endpoint separado, agregar fila correspondiente en `matriz-permisos-mvp.md`.

---

## 6) Cambios Frontend

### Pantallas / componentes
- `frontend/src/features/chatAssistant/components/ChatAssistantImagePicker.tsx` (nuevo): selección y previsualización básica.
- `frontend/src/features/chatAssistant/components/ChatAssistantImageValidationMessage.tsx` (nuevo o ajuste): errores de formato/tamaño/límite.
- `frontend/src/features/chatAssistant/components/ChatAssistantComposer.tsx` (ajuste): soporte texto + imágenes o solo imágenes.
- `frontend/src/features/chatAssistant/utils/validateChatAssistantImages.ts` (nuevo): validación de cantidad, formatos y tamaño.
- `frontend/src/features/chatAssistant/model/chatAssistantImage.ts` (nuevo): tipado del adjunto.
- `frontend/src/features/chatAssistant/api/sendChatAssistantMessage.ts` (ajuste): payload con imágenes cuando aplique.

### Reglas de integración

- La UI decide disponibilidad de adjuntos usando `supportsVision` de la configuración activa del usuario y no solo el catálogo.
- La previsualización en cliente no implica upload anticipado ni almacenamiento remoto.
- Los errores de formato, tamaño o cantidad deben impedir el envío antes de invocar la API cuando sea posible.
- La UI permite remover imágenes antes del envío; reordenarlas queda fuera de alcance de este slice.

### data-testid sugeridos
- `chatAssistantImagePicker`
- `chatAssistantImagePreview`
- `chatAssistantImageValidationMessage`
- `chatAssistantImageUnsupportedProviderMessage`
- `chatAssistantImageRemoveButton`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Frontend | Añadir selección y remoción de imágenes en el composer | Hasta 4 imágenes visibles |
| T2 | Frontend | Validar formato, tamaño y límite de cantidad | Mensajes de error controlados |
| T3 | Frontend | Bloquear adjuntos cuando proveedor/modelo no soporte visión | Mensaje claro de indisponibilidad |
| T4 | Backend | Aceptar adjuntos de imágenes dentro del flujo del chat y validarlos | Sin persistencia local y con JSON + `contentBase64` |
| T5 | Tests | Unit validadores + E2E formatos/límites/indisponibilidad | AC verdes, caso solo imágenes cubierto |
| T6 | Docs | OpenAPI y trazabilidad de restricciones de imágenes | Coherencia documental |

---

## 8) Estrategia de Tests

- **Unit:** validador de formatos, tamaño máximo, cantidad máxima, request válido con solo imágenes y disponibilidad por soporte de visión.
- **Integration:** validación de request del chat con imágenes válidas e inválidas; 401 y 422 obligatorios, 403 solo si aplica policy específica.
- **E2E:**  
  - adjuntar imagen válida y enviar consulta;  
  - intentar adjuntar formato inválido;  
  - intentar exceder `5 MB`;  
  - intentar exceder `4` imágenes;  
  - proveedor sin soporte de visión muestra indisponibilidad;  
  - interacción con solo imágenes se procesa correctamente.

---

## 9) Riesgos y Edge Cases

- Detección de soporte de visión inconsistente entre proveedor y modelo activo.
- Archivos grandes o múltiples que degraden la UX antes de validar.
- Diferencias de MIME real vs extensión del archivo.
- Navegadores con comportamientos distintos al seleccionar múltiples imágenes.
- Crecimiento del payload JSON por uso de `contentBase64` si no se controla bien el límite real del archivo.

---

## 10) Checklist final

### Checklist del slice
- [ ] AC cumplidos
- [ ] Backend + frontend + tests según plan
- [ ] Imágenes no persistidas como histórico del portal
- [ ] Validaciones de formato/tamaño/cantidad verificadas

### Checklist normas transversales

- [ ] Endpoints nuevos/modificados con policy en código
- [ ] Matriz endpoint ↔ permiso actualizada
- [ ] OpenAPI en /api/documentation coherente con código y matriz
- [ ] 401 y 422 documentados; 403 solo si aplica policy específica
- [ ] Envelope JSON respetado
- [ ] X-Paq-Cliente documentado donde aplique
- [ ] Tests API incluyen 401 (y 403 solo si aplica)
- [ ] Sin ampliación de alcance fuera de SPEC/HU/TR










