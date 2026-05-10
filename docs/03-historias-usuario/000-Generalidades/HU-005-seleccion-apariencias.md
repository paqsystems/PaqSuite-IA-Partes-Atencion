# HU-005 – Selección de apariencia (look & feel) por usuario

## Épica

000 – Generalidades

## Alcance de instalación (**MONO**)

En **MONO** no hay **`PQ_Empresa`** ni tema por compañía. La apariencia es una **preferencia individual**: **cada usuario** elige y conserva **su** tema DevExtreme; no existe un tema único impuesto a toda la instalación.

## Clasificación

SHOULD-HAVE

## Rol

**Usuario autenticado** (cada uno gestiona la propia apariencia, análogo a idioma en HU-004).

## Narrativa

Como usuario autenticado quiero **elegir la apariencia (look & feel)** del sistema desde el **menú del avatar**, usando los temas predefinidos de DevExtreme, para que la interfaz se vea como prefiero **en mis sesiones** sin afectar la elección de otros usuarios.

## Contexto / Objetivo

- **Persistencia por usuario** en backend (p. ej. columna en `users` — ver § Impacto en datos).
- El **punto de acceso** es una **opción del menú desplegable del avatar** (misma zona que idioma, pestañas, cerrar sesión — ver HU-003, HU-004).
- Lista cerrada de temas precompilados DevExtreme. Carga dinámica del CSS al aplicar el cambio, al iniciar sesión o al hidratar el perfil.
- Tras login, el cliente obtiene el tema del usuario (payload de login, `GET /me` u homólogo) y aplica el ThemeLoader antes posible del layout.

## Suposiciones explícitas

- Frontend con DevExtreme React.
- Shell post-login con **menú de usuario** bajo el avatar (`docs/01-arquitectura/ui/01_MainLayout_PostLogin_Specification.md`, mockup M01 UserMenu si aplica).
- **No** depende de CRUD de empresas (no aplica en MONO).

## In scope

- **Ítem en el menú del avatar:** etiqueta clara (p. ej. «Apariencia», «Tema visual» o clave i18n).
- Al elegir la opción: **selector de tema** (lista cerrada) en **popup**, **submenú** o **modal** ligero.
- Lista cerrada de temas (Generic, Material, Fluent, etc.).
- **Guardado** del valor elegido **solo para el usuario autenticado** (API que actualice el registro del usuario derivado del token).
- ThemeLoader / ThemeProvider: aplicar CSS; fallback si el valor es nulo o inválido.
- **Visibilidad:** cualquier usuario autenticado puede ver y usar la opción (salvo decisión explícita de producto de restringir por permiso — documentar en ese caso).

## Out of scope

- ThemeBuilder o temas arbitrarios fuera de la lista cerrada.
- **Un solo tema fijo para toda la instalación** (política global que ignore la preferencia del usuario).
- Variación por **empresa** (requeriría modelo MULTI / `PQ_Empresa`).

---

## Criterios de aceptación

### AC1 – Lista de temas

- Lista cerrada de temas DevExtreme predefinidos y nombres legibles en el selector.

### AC2 – Preferencia por usuario

- Cada usuario puede fijar **su** tema desde el flujo iniciado en el menú del avatar.
- Si el usuario **no tiene valor** persistido (NULL / vacío), se usa el tema por defecto de producto (p. ej. `material.blue.light`).

### AC3 – Aplicación y sesiones

- Tras guardar, el tema se aplica **de inmediato** en la sesión actual y queda almacenado **en su perfil**.
- En el **próximo acceso** (otro dispositivo o nueva sesión), se carga el tema guardado de **ese** usuario.
- Reducir «flash» de estilos cuando sea posible.

### AC4 – Ubicación en UI (**obligatorio**)

- La funcionalidad es accesible como **opción en el menú del usuario / avatar** en el header.
- Clic en ítem → **popover/modal** o **submenú** con la lista de temas.

### AC5 – Persistencia y contrato API

- El valor vive en el **modelo del usuario** (migración acordada — ver abajo).
- El backend expone lectura en login/`me` y **actualización** (p. ej. `PATCH` usuario actual o endpoint de preferencias) de forma que **solo se modifique el usuario del token** (no se puede cambiar el tema de otro usuario sin autorización de administración).

### AC6 – Seguridad

- Cualquier operación de escritura valida que el **subject** sea el usuario autenticado (o rol admin si en el futuro se permite editar preferencias de terceros — fuera del alcance mínimo).

---

## Reglas de negocio

1. La apariencia efectiva es **por usuario**, no por instalación ni por empresa.
2. Solo valores de la lista cerrada (validación 422 ante valor inválido).
3. Si **falla** la carga del CSS, usar tema por defecto.
4. El menú avatar mantiene **coherencia** con otras preferencias (idioma, pestañas, logout).

---

## Impacto en datos (**MONO**)

- **No** se usa `PQ_Empresa` para el tema en este producto.
- **Recomendado:** columna nullable en **`users`**, p. ej. `theme` / `dx_theme` (`varchar`, NULL = usar default). Alinear migración y modelo con el equipo (equivalencia conceptual al legado multi donde el tema podía figurar en otras tablas).
- Documentar convención en migración/API junto con `locale` y `menu_abrir_nueva_pestana`.

---

## Dependencias

- HU-001 (Login) y payload o recurso que exponga preferencias del usuario.
- Layout con **menú de usuario** post-login.

## Especificación (Open-Spec)

- [SPEC-005 – Apariencia / tema por usuario](../../05-open-spec/000-Generalidades/SPEC-005-seleccion-apariencia.md)

## Referencias

- `docs/01-arquitectura/ui/01_MainLayout_PostLogin_Specification.md` – Shell, header, menú usuario.
- `docs/ui/mockups/mockup-spec-mainlayout.md` – M01 UserMenu (si existe).
- `docs/03-historias-usuario/000-Generalidades/HU-004-seleccion-idioma.md` – Patrón preferencia en menú usuario / `users`.
- `docs/modelo-datos/md-seguridad.md` – Esquema `users` (locale, etc.); ampliar con tema por usuario según esta HU.

### Nota sobre versión **MULTI** (histórico)

En modelos ERP multi-empresa el tema a veces se asocia a **empresa** (`PQ_Empresa.theme`). En **MONO** esta HU prioriza **`users.theme`** (preferencia personal), no tenant.
