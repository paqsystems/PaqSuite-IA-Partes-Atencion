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
}

export const empresasAdminSdkFallbackEs: Record<string, string> = {
  'admin.empresas.title': 'Empresas',
  'admin.empresas.empty': 'No hay empresas',
  'admin.empresas.loadError': 'No se pudieron cargar las empresas',
  'admin.empresas.loadCandidatosError': 'No se pudieron cargar los candidatos ERP',
  'admin.empresas.list.nombreBd': 'Base de datos',
  'admin.empresas.list.habilitada': 'Habilitada',
  'admin.empresas.action.add': 'Nueva empresa',
  'admin.empresas.action.edit': 'Editar',
  'admin.empresas.form.createTitle': 'Alta de empresa',
  'admin.empresas.form.candidato': 'Empresa ERP',
  'admin.empresas.list.codigo': 'Código',
  'admin.empresas.form.codigo': 'Código',
  'admin.empresas.applyTheme': 'Aplicar',
  'admin.empresas.applyThemeHint':
    'Vista previa activa (sin grabar). Cancelar restaura la apariencia anterior.',
  'admin.empresas.monoNote':
    'Instalación de una sola empresa: no se permite alta ni baja, solo edición de nombre y estado.',
  'admin.empresas.form.erpId': 'ID ERP',
  'admin.empresas.form.nombreBd': 'Base de datos',
  'admin.empresas.form.habilitada': 'Habilitada',
  'admin.empresas.form.save': 'Guardar',
  'admin.empresas.form.cancel': 'Cancelar',
  'admin.empresas.form.loading': 'Cargando…',
  'admin.empresas.form.saveError': 'No se pudo guardar la empresa',
  'admin.empresas.validation.nombreRequired': 'El nombre es obligatorio',
  'admin.empresas.validation.candidatoRequired': 'Seleccioná un candidato ERP',
  'admin.empresas.validation.invalid':
    'Revise los datos del formulario (nombre, estado y apariencia).',
  'admin.empresas.validation.themeInvalid':
    'La apariencia seleccionada no es válida para esta instalación.',
}
