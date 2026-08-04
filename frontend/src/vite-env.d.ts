/// <reference types="vite/client" />

interface ImportMetaEnv {
  readonly VITE_APP_VERSION: string
  readonly VITE_DEVEXTREME_LICENSE: string
  readonly VITE_API_PROXY_TARGET: string
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
