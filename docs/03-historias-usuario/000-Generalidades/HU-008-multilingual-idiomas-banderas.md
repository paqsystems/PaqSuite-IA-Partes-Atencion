# HU-008 – Multilingual con varios idiomas y banderas

## Épica
000 – Generalidades

## Clasificación
SHOULD-HAVE

## Estado de alcance
**Estado:** En Control Calidad — pendiente cierre de [HU-008-update-03](../updates/000-Generalidades/HU-008-multilingual-idiomas-banderas-update-03.md) (CC PQ 04/04/2026).

## Rol
Usuario del sistema (antes y después del login)

## Narrativa

Como usuario quiero que la aplicación ofrezca varios idiomas con indicadores visuales (banderas de país) para poder elegir mi idioma preferido de forma intuitiva y que el cambio se aplique correctamente en toda la interfaz.

## Criterios de aceptación

### Idiomas soportados

- La aplicación debe soportar al menos los siguientes idiomas (orden inicial sugerido):
  1. **Español** (es) – bandera Argentina 🇦🇷
  2. **English** (en) – bandera Inglaterra 🇬🇧
  3. **Português** (pt) – bandera Brasil 🇧🇷
  4. **Français** (fr) – bandera Francia 🇫🇷
  5. **Italiano** (it) – bandera Italia 🇮🇹

### Selector de idioma con banderas

- El selector de idioma debe mostrar cada opción con:
  - **Bandera de país** junto al texto del idioma (emojis 🇦🇷 🇬🇧 🇧🇷 🇫🇷 🇮🇹 o iconos SVG según diseño).
  - **Nombre del idioma** en su propio idioma (Español, English, Português, Français, Italiano).
- El control puede ser un dropdown, grupo de botones o lista según diseño PaqSystems.
- La opción seleccionada se indica visualmente (resaltado, check, etc.).

### Aplicación del cambio

- Al seleccionar un idioma, la interfaz se actualiza **de inmediato** sin recargar la página.
- Todos los textos de la UI que usan `t()` reflejan el nuevo idioma.
- Los formatos de fecha y número se ajustan al locale seleccionado.

### Persistencia

- La preferencia se persiste en `users.locale` (Dictionary DB).
- Usuarios no autenticados: la selección se guarda en localStorage y al autenticarse se envía al backend.
- Usuarios autenticados: al cambiar el idioma se actualiza en backend vía API de preferencias.

### Disponibilidad

- El selector está disponible en la pantalla de login y en el layout principal (barra superior).
- Si el idioma del navegador no está soportado, se usa español como fallback.

## Tabla involucrada

- `users`: campo `locale` (varchar(10), ej. 'es', 'en', 'pt', 'fr', 'it'). NULL = usar idioma del navegador.

## Reglas de negocio

- Solo se ofrecen idiomas para los que existan archivos de traducción completos.
- La preferencia es por usuario (en MONO no hay contexto por empresa).
- El idioma afecta a la interfaz; los datos de negocio (nombres de clientes, descripciones) no se traducen automáticamente.

## Dependencias

- HU-004 (Selección de idioma) – base conceptual; esta HU extiende con más idiomas y banderas.
- i18n (react-i18next) configurado en el frontend.
- Archivos de traducción para cada idioma: `frontend/src/i18n/locales/es.json`, `en.json`, `pt.json`, `fr.json`, `it.json`.
- Campo `users.locale` en Dictionary DB.
- API de preferencias de usuario para persistir `locale`.

## Especificación (Open-Spec)

- [SPEC-008 – Multilingual e iconografía banderas](../../05-open-spec/000-Generalidades/SPEC-008-multilingual-banderas.md)

## Referencias

- `docs/03-historias-usuario/000-Generalidades/HU-004-seleccion-idioma.md` – Selección de idioma (base)
- `docs/frontend/i18n.md` – Estrategia de i18n
- `frontend/src/i18n/` – Configuración actual de i18next
- `frontend/src/shared/components/LanguageSelector.tsx` – Componente actual (2 idiomas)
