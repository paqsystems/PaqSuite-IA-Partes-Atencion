/**
 * Fallback GEN-06 ABM empresas cuando el SDK instalado no exporta
 * `registerEmpresasAdminI18nResources` (p. ej. react-core anterior a 2.4.17 con pin desfasado).
 * Si el SDK registra el catálogo, estas claves se sobrescriben (overwrite true).
 */
export const empresasAdminHostFallback: Record<string, Record<string, string>> = {
  es: {
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
  },
  en: {
    'admin.empresas.title': 'Companies',
    'admin.empresas.empty': 'No companies',
    'admin.empresas.loadError': 'Could not load companies',
    'admin.empresas.loadCandidatosError': 'Could not load ERP candidates',
    'admin.empresas.list.codigo': 'Code',
    'admin.empresas.list.nombre': 'Name',
    'admin.empresas.list.nombreBd': 'Database',
    'admin.empresas.list.habilitada': 'Enabled',
    'admin.empresas.list.theme': 'Appearance',
    'admin.empresas.action.add': 'New company',
    'admin.empresas.action.edit': 'Edit',
    'admin.empresas.form.createTitle': 'New company',
    'admin.empresas.form.editTitle': 'Edit company',
    'admin.empresas.form.candidato': 'ERP company',
    'admin.empresas.form.codigo': 'Code',
    'admin.empresas.form.erpId': 'ERP ID',
    'admin.empresas.form.nombreBd': 'Database',
    'admin.empresas.form.nombre': 'Name',
    'admin.empresas.form.habilitada': 'Enabled',
    'admin.empresas.form.theme': 'Appearance',
    'admin.empresas.form.save': 'Save',
    'admin.empresas.form.cancel': 'Cancel',
    'admin.empresas.form.loading': 'Loading…',
    'admin.empresas.form.saveError': 'Could not save the company',
    'admin.empresas.applyTheme': 'Apply',
    'admin.empresas.applyThemeHint':
      'Preview active (not saved). Cancel restores the previous appearance.',
    'admin.empresas.monoNote':
      'Single-company installation: no create or delete allowed, only editing name and status.',
    'admin.empresas.validation.nombreRequired': 'Name is required',
    'admin.empresas.validation.candidatoRequired': 'Select an ERP candidate',
    'admin.empresas.validation.invalid':
      'Check the form data (name, status, and appearance).',
    'admin.empresas.validation.themeInvalid':
      'The selected appearance is not valid for this installation.',
  },
}

empresasAdminHostFallback.pt = {
  ...empresasAdminHostFallback.en,
  'admin.empresas.title': 'Empresas',
  'admin.empresas.empty': 'Não há empresas',
  'admin.empresas.loadError': 'Não foi possível carregar as empresas',
  'admin.empresas.list.nombre': 'Nome',
  'admin.empresas.form.nombre': 'Nome',
  'admin.empresas.form.habilitada': 'Habilitada',
  'admin.empresas.form.save': 'Guardar',
  'admin.empresas.form.cancel': 'Cancelar',
  'admin.empresas.form.editTitle': 'Editar empresa',
  'admin.empresas.applyTheme': 'Aplicar',
}

empresasAdminHostFallback.fr = {
  ...empresasAdminHostFallback.en,
  'admin.empresas.title': 'Sociétés',
  'admin.empresas.list.nombre': 'Nom',
  'admin.empresas.form.nombre': 'Nom',
  'admin.empresas.form.save': 'Enregistrer',
  'admin.empresas.form.cancel': 'Annuler',
  'admin.empresas.form.editTitle': 'Modifier la société',
  'admin.empresas.applyTheme': 'Appliquer',
}

empresasAdminHostFallback.it = {
  ...empresasAdminHostFallback.en,
  'admin.empresas.title': 'Aziende',
  'admin.empresas.list.nombre': 'Nome',
  'admin.empresas.form.nombre': 'Nome',
  'admin.empresas.form.save': 'Salva',
  'admin.empresas.form.cancel': 'Annulla',
  'admin.empresas.form.editTitle': 'Modifica azienda',
  'admin.empresas.applyTheme': 'Applica',
}
