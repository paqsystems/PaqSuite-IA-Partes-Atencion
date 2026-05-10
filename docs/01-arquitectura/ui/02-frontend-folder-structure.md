# Frontend Folder Structure (React + DevExtreme)

Objetivo: separar claramente Shell/UI, páginas, servicios, y estado global.

## Estructura propuesta

/src
  /app
    App.tsx
    routes.tsx
    providers/
      AuthProvider.tsx
      CompanyProvider.tsx
      I18nProvider.tsx
      ThemeProvider.tsx            # carga dinámica del CSS DevExtreme (Opción A)
      QueryProvider.tsx            # si se usa react-query
    store/
      auth.store.ts
      company.store.ts
      ui.store.ts                  # preferencias: sidebar, etc.
  /layouts
    MainLayout/
      MainLayout.tsx               # compone TopBar + Drawer/Sidebar + Content + Footer
      MainLayout.types.ts
      MainLayout.css               # si hace falta (mínimo)
  /components
    /shell
      TopBar/
        TopBar.tsx
        TopBar.types.ts
      Sidebar/
        Sidebar.tsx
        Sidebar.types.ts
      FooterBar/
        FooterBar.tsx
      UserMenu/
        UserMenu.tsx
      LanguageSwitcher/
        LanguageSwitcher.tsx
    /common
      LoadingScreen.tsx
      ErrorState.tsx
      EmptyState.tsx
      ConfirmDialog.tsx
  /pages
    /home
      DesktopHome.tsx              # dashboard container (solo desktop/tablet)
      MobileHome.tsx               # quick access (sin dashboard)
    /auth
      Login.tsx
      SelectCompany.tsx
    /process
      ProcessHost.tsx              # pantalla contenedora al abrir un proceso
  /features
    /company
      components/
        CompanySwitcherPopup.tsx
      api/
        company.api.ts
    /user
      components/
        ProfilePopup.tsx
        ChangePasswordPopup.tsx
      api/
        user.api.ts
    /menu
      api/
        menu.api.ts                # obtiene menú permitido por usuario/empresa
      types/
        menu.types.ts
  /services
    http/
      httpClient.ts                # axios/fetch wrapper
      interceptors.ts
    ui-settings/
      uiSettings.service.ts        # get settings + apply theme
      themeLoader.ts               # inyecta <link id="dx-theme-link">
    auth/
      auth.service.ts
    telemetry/                     # opcional futuro
  /assets
    /themes
      material.blue.light.css
      material.blue.dark.css
      ...
    /images
      logo.svg
  /types
    common.ts
  /utils
    breakpoints.ts
    format.ts
    guards.ts

## Reglas
- Shell: todo lo que es TopBar/Sidebar/Footer vive en `/components/shell`.
- Layout: composición general en `/layouts`.
- Pages: pantallas navegables (route-level).
- Features: lógica por dominio (company/user/menu) con api + components.
- Services: infraestructura y acceso a backend.
- Theme: assets versionados en `/assets/themes`.

## Puntos clave para Cursor
- La pantalla principal post-login es `MainLayout`.
- En mobile, Home es `MobileHome` (quick access, sin dashboard).
- La aplicación del theme ocurre en `ThemeProvider` antes de montar layout.
