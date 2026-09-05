/// <reference types="vite/client" />

interface ImportMetaEnv {
  readonly VITE_APP_VERSION: string
  readonly VITE_DEVEXTREME_LICENSE?: string
  /** Clave LCP DX 26.1 (canónica). El JWT eyJ de 25.2 no se usa. */
  readonly VITE_DEVEXPRESS_LICENSE_KEY?: string
  readonly VITE_API_PROXY_TARGET: string
  readonly VITE_DX_REPORTING_HOST?: string
  /**
   * URL absoluta del backend (Forge) en builds Vercel / multidominio.
   * Ejemplo: https://backend.partesatencion.paqsystems.com
   * Vacío o relativo → same-origin (proxy Vite local).
   */
  readonly VITE_API_BASE_URL?: string
}

interface ImportMeta {
  readonly env: ImportMetaEnv
}
