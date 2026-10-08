/** Mapeo claves SDK GEN-06 (`admin.empresas.*`) → catálogo Partes (claves legacy). */
export const empresasAdminSdkToHost: Record<string, string> = {
  'admin.empresas.form.editTitle': 'admin.empresas.editTitle',
  'admin.empresas.list.nombre': 'admin.empresas.field.nombre',
  'admin.empresas.form.nombre': 'admin.empresas.field.nombre',
  'admin.empresas.list.theme': 'admin.empresas.field.theme',
  'admin.empresas.form.theme': 'admin.empresas.field.theme',
  'admin.empresas.list.codigo': 'admin.empresas.field.codigo',
  'admin.empresas.form.codigo': 'admin.empresas.field.codigo',
  'admin.empresas.applyTheme': 'admin.empresas.applyTheme',
  'admin.empresas.applyThemeHint': 'admin.empresas.applyThemeHint',
  'admin.empresas.monoNote': 'admin.empresas.monoNote',
  'admin.empresas.title': 'admin.empresas.title',
  'admin.empresas.validation.invalid': 'admin.empresas.validation.invalid',
  'admin.empresas.validation.themeInvalid': 'admin.empresas.validation.themeInvalid',
}

/** Fallback ES si i18n aún no tiene la clave (pin SDK / deploy desfasado). */
export const empresasAdminSdkFallbackEs: Record<string, string> = {
  'admin.empresas.title': 'Empresas',
  'admin.empresas.empty': 'No hay empresas',
  'admin.empresas.loadError': 'No se pudieron cargar las empresas',
  'admin.empresas.loadCandidatosError': 'No se pudieron cargar los candidatos ERP',
  'admin.empresas.list.codigo': 'Código',
  'admin.empresas.list.nombre': 'Nombre',
  'admin.empresas.list.nombreBd': 'Base de datos',
  'admin.empresas.list.habilitada': 'Habilitada',
  'admin.empresas.list.theme': 'Apariencia',
  'admin.empresas.action.add': 'Nueva empresa',
  'admin.empresas.action.edit': 'Editar',
  'admin.empresas.form.createTitle': 'Alta de empresa',
  'admin.empresas.form.editTitle': 'Editar empresa',
  'admin.empresas.form.candidato': 'Empresa ERP',
  'admin.empresas.form.codigo': 'Código',
  'admin.empresas.form.erpId': 'ID ERP',
  'admin.empresas.form.nombreBd': 'Base de datos',
  'admin.empresas.form.nombre': 'Nombre',
  'admin.empresas.form.habilitada': 'Habilitada',
  'admin.empresas.form.theme': 'Apariencia',
  'admin.empresas.form.save': 'Guardar',
  'admin.empresas.form.cancel': 'Cancelar',
  'admin.empresas.form.loading': 'Cargando…',
  'admin.empresas.form.saveError': 'No se pudo guardar la empresa',
  'admin.empresas.applyTheme': 'Aplicar',
  'admin.empresas.applyThemeHint':
    'Vista previa activa (sin grabar). Cancelar restaura la apariencia anterior.',
  'admin.empresas.monoNote':
    'Instalación de una sola empresa: no se permite alta ni baja, solo edición de nombre y estado.',
  'admin.empresas.validation.nombreRequired': 'El nombre es obligatorio',
  'admin.empresas.validation.candidatoRequired': 'Seleccioná un candidato ERP',
  'admin.empresas.validation.invalid':
    'Revise los datos del formulario (nombre, estado y apariencia).',
  'admin.empresas.validation.themeInvalid':
    'La apariencia seleccionada no es válida para esta instalación.',
}

/**
 * Traduce claves del SDK EmpresasAdmin.
 * El SDK hace `t ?? ((key) => key)`: sin esta prop se muestran claves crudas.
 */
export function translateEmpresasAdminSdk(
  key: string,
  t: (key: string) => string,
): string {
  if (!key.startsWith('admin.empresas.') && !key.startsWith('appearance.paqsuite.')) {
    return t(key)
  }

  const hostKey = empresasAdminSdkToHost[key]
  if (hostKey) {
    const mapped = t(hostKey)
    if (mapped !== hostKey) {
      return mapped
    }
  }

  const direct = t(key)
  if (direct !== key) {
    return direct
  }

  return empresasAdminSdkFallbackEs[key] ?? key
}
