import i18n from '../../i18n/i18n'

const authMessageMap: Record<string, string> = {
  'auth.invalidCredentials': 'Usuario o contraseña incorrectos.',
  'auth.emailNotFound': 'El mail no existe.',
  'auth.sessionExpired': 'Su sesión ha vencido por inactividad.',
  'auth.password.policyUnsafe': 'La contraseña no cumple la política de seguridad.',
  'auth.password.tooShort': 'La contraseña es demasiado corta.',
  'auth.password.mismatch': 'Las contraseñas no coinciden.',
  'auth.password.sameAsCurrent': 'La nueva contraseña debe ser distinta a la actual.',
  'auth.password.currentInvalid': 'La contraseña actual no es válida.',
  'auth.resetTokenInvalid': 'El enlace de restablecimiento no es válido o expiró.',
  'tenant.invalid': 'Instalación o tenant no válido.',
  'partes.auth.noFunctionalProfile':
    'No tiene perfil funcional de Partes habilitado. Contacte al administrador.',
  'partes.auth.inconsistentProfile':
    'Su perfil Partes es inconsistente. Contacte al administrador.',
  'partes.maestros.forbidden': 'No tiene permiso para administrar maestros de Partes.',
  'partes.maestros.tipoDefaultNoInhabilitar':
    'No se puede inhabilitar el tipo de tarea marcado como default.',
  'partes.maestros.tipoGenericoNoAsignable':
    'No se puede asignar un tipo de tarea genérico a un cliente.',
  'partes.maestros.userIdRequired': 'Debe seleccionar un usuario Framework.',
  'partes.maestros.userIdExclusive':
    'El usuario ya está vinculado a otro perfil Partes (asistente o cliente).',
  'partes.maestros.hasReferences':
    'No se puede eliminar: el registro tiene referencias. Inhabilítelo.',
  'partes.maestros.deleteConReferencias':
    'No se puede eliminar: el registro tiene referencias. Inhabilítelo.',
  'partes.maestros.notFound': 'Registro no encontrado.',
  'partes.maestros.codeRequired': 'El código es obligatorio.',
  'partes.maestros.codeDuplicate': 'Ya existe un registro con ese código.',
  'partes.maestros.clienteIdRequired': 'Debe indicar el cliente.',
  'partes.tarea.fechasRequeridas': 'Debe indicar fecha desde y fecha hasta.',
  'partes.tarea.duracionInvalida':
    'La duración debe ser un múltiplo del tramo configurado, mayor a 0 y hasta 1440.',
  'partes.tarea.fechaFuturaConfirmacion': 'Confirme para registrar una fecha futura.',
  'partes.tarea.conflictoVersion':
    'La tarea fue modificada por otro usuario. Refresque e intente nuevamente.',
  'partes.tarea.cerradaNoEditable': 'No se puede editar una tarea cerrada.',
  'partes.tarea.cerradaNoEliminable': 'No se puede eliminar una tarea cerrada.',
  'partes.tarea.soloSupervisor': 'Solo un supervisor puede cerrar o reabrir tareas.',
  'partes.tarea.forbiddenOwner': 'No puede operar sobre tareas de otro asistente.',
  'partes.tarea.forbidden': 'No tiene permiso para operar carga de Partes.',
  'partes.tarea.observacionRequerida': 'La observación es obligatoria.',
  'partes.tarea.camposObligatorios': 'Complete los campos obligatorios.',
  'partes.tarea.tipoFueraUniverso': 'El tipo de tarea no pertenece al universo del cliente.',
  'partes.tarea.tipoNoUsable': 'El tipo de tarea no está usable.',
  'partes.tarea.clienteNoUsable': 'El cliente no está usable.',
  'partes.tarea.asistenteNoUsable': 'El asistente no está usable.',
  'partes.tarea.notFound': 'Tarea no encontrada.',
  'partes.consulta.empty': 'No hay datos para el filtro indicado.',
  'partes.consulta.ejeInvalido': 'Eje de agrupación no válido.',
  'partes.consulta.granularidadRequerida': 'Indique granularidad día o mes para el eje fecha.',
  'mobile.routeExcluded': 'Esta función no está disponible en la app móvil.',
  'partes.masivo.forbidden': 'Solo un supervisor puede ejecutar el proceso masivo.',
  'partes.masivo.emptySelection': 'Seleccione al menos una tarea.',
  'partes.masivo.topeExcedido': 'La selección supera el tope configurado de proceso masivo.',
  'partes.masivo.loteDemasiadoGrande':
    'El lote supera el límite técnico de 5000 registros. Refine el filtro.',
  'partes.masivo.conflictoVersion':
    'Alguna tarea fue modificada por otro usuario. Refresque e intente nuevamente.',
  'partes.masivo.idInexistente': 'Algún identificador del lote no existe. No se aplicaron cambios.',
  'partes.masivo.accionInvalida': 'Acción de proceso masivo no válida.',
  'partes.masivo.itemInvalido': 'Ítem de lote inválido.',
  'partes.masivo.atributoInvalido':
    'Atributo o valor no válido para el lote. No se aplicaron cambios.',
  'partes.masivo.noEsTarea':
    'El lote incluye registros que no son tareas de carga. No se aplicaron cambios.',
  'roles.delete.hasPermisos':
    'No se puede eliminar el rol: está asignado a uno o más permisos.',
  'shell.blockedNoCompany': 'No tiene empresas habilitadas.',
  'infra.transport': 'Error de conexión. Intente nuevamente.',
  'infra.unexpected': 'Ocurrió un error inesperado. Intente nuevamente.',
  'mailEngine.sendFailed':
    'No se pudo enviar el correo de recuperación. Verifique la configuración de mail del servidor.',
  'mailEngine.configMissing': 'Falta configuración de correo en el servidor.',
  ok: 'Operación exitosa.',
}

export function resolveAuthMessage(respuesta: string, fallback?: string): string {
  if (i18n.exists(respuesta)) {
    return i18n.t(respuesta)
  }
  if (authMessageMap[respuesta]) {
    return authMessageMap[respuesta]
  }
  if (fallback) {
    return fallback
  }
  return respuesta
}

export function passwordPolicyHint(mode: 'simple' | 'segura' = 'simple'): string {
  if (mode === 'segura') {
    return 'Mínimo 8 caracteres, mayúscula, minúscula, dígito y signo autorizado.'
  }
  return 'Mínimo 8 caracteres.'
}
