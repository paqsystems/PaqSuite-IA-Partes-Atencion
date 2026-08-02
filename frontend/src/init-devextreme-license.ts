import config from 'devextreme/core/config'

/**
 * Licencia DevExtreme — MUST ejecutarse antes de montar widgets (mismo patrón PedidosWeb/TANGO).
 * Variable: `VITE_DEVEXTREME_LICENSE` en `frontend/.env`.
 */
const licenseKey = String(import.meta.env.VITE_DEVEXTREME_LICENSE ?? '')
  .trim()
  .replace(/^['"]|['"]$/g, '')
  .replace(/[\r\n\t]/g, '')

if (import.meta.env.PROD && licenseKey === '') {
  throw new Error(
    '[DevExtreme] Licencia faltante en produccion. Configure VITE_DEVEXTREME_LICENSE para generar un build valido.',
  )
}

config({ licenseKey })
