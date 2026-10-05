import type { AdminEmpresa } from './adminSecurityApi'
import { EMPRESA_THEME_DEFAULT } from './empresaThemeCatalog'

export type AdminEmpresaFormState = {
  nombreEmpresa: string
  habilitada: boolean
  theme: string
}

export function adminEmpresaToFormState(row: AdminEmpresa): AdminEmpresaFormState {
  return {
    nombreEmpresa: row.nombreEmpresa ?? '',
    habilitada: row.habilitada ?? true,
    theme: row.theme || EMPRESA_THEME_DEFAULT,
  }
}
