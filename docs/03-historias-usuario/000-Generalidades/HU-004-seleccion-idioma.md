# HU-004 – Selección de idioma de la aplicación

## Épica
000 – Generalidades

## Clasificación
SHOULD-HAVE

## Rol
Usuario del sistema (antes y después del login)

## Narrativa

Como usuario quiero elegir el idioma en que se muestran los datos de la aplicación para poder usarla en mi idioma preferido, con la opción disponible desde el login y que se mantenga ante nuevo acceso en cualquier navegador o dispositivo.

## Criterios de aceptación

### Disponibilidad

- El selector de idioma está disponible en la pantalla de login (antes de autenticarse).
- El selector de idioma permanece disponible en el layout principal tras el login, usando el **control dedicado** diseñado para tal fin en el header (dropdown o grupo de botones según diseño PaqSystems).
- El selector de idioma **no** forma parte del menú de usuario; es un control independiente en la barra superior.
- El usuario puede cambiar el idioma en cualquier momento.

### Idioma inicial

- Si el usuario no tiene preferencia guardada (`users.locale` NULL o vacío), se toma el idioma del navegador (`navigator.language`).
- Si el idioma del navegador no está soportado, se usa el idioma por defecto (ej. español).
- Si el usuario tiene preferencia guardada, se aplica esa preferencia al cargar la aplicación.

### Forma de selección

- El usuario puede seleccionar el idioma mediante:
  - Iconos de banderas (uno por idioma), o
  - Lista desplegable de opciones, o
  - Lista donde cada opción muestra idioma y bandera.
- La opción elegida se aplica de inmediato (la interfaz se actualiza sin recargar la página completa).

### Persistencia

- La preferencia se persiste en el campo `users.locale` (Dictionary DB) para que se mantenga ante nuevo acceso, en el mismo navegador, otro navegador u otro dispositivo.
- Usuarios no autenticados (en login): la selección se guarda temporalmente (ej. localStorage) y al autenticarse se envía al backend para persistir en `users.locale`.
- Usuarios autenticados: al cambiar el idioma se actualiza en backend y se refleja en la UI.

### Activación de i18n

- La aplicación debe tener i18n (react-i18next o equivalente) activado y configurado.
- Todos los textos de la UI deben usar las claves de traducción (función `t()`).
- Se definen los idiomas soportados inicialmente (ej. español, inglés) con sus archivos de traducción.

## Tabla involucrada

- `users`: campo `locale` (varchar(10), ej. 'es', 'en'). NULL = usar idioma del navegador.

## Reglas de negocio

- La preferencia es por usuario (en MONO no hay contexto por empresa).
- Solo se ofrecen idiomas para los que existan archivos de traducción.
- El idioma afecta a la interfaz (labels, mensajes, formatos de fecha/número según locale). Los datos de negocio (nombres de clientes, descripciones) no se traducen automáticamente.

## Dependencias

- i18n (react-i18next) configurado en el frontend.
- Archivos de traducción para cada idioma soportado.
- Campo `users.locale` en Dictionary DB.
- API para leer/actualizar la preferencia (incluida en login o endpoint de perfil/preferencias).
- HU-001 (Login) de épica 001 – sesión activa para persistir tras autenticación.

## Especificación (Open-Spec)

- [SPEC-004 – Selección de idioma (i18n)](../../05-open-spec/000-Generalidades/SPEC-004-seleccion-idioma.md)

## Referencias

- `docs/design/paqsystems-main-shell-design.md` – Sección 4.1 Selector de idioma
- `docs/ui/mockups/mockup-spec-mainlayout.md` – TopBar Language flags
- `docs/modelo-datos/md-diccionario/md-diccionario.md` – Esquema users
- `docs/frontend/i18n.md` – Estrategia de i18n
- `frontend/src/i18n/` – Configuración actual de i18next
