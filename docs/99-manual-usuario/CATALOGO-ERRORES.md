# Catálogo de errores — Partes de Atención

| Campo | Valor |
|-------|--------|
| **Versión** | 2026-10-05 |
| **Uso** | Corpus del **Asistente IA** (menú avatar) y referencia para manuales por proceso |
| **Idioma** | Español canónico (las pantallas pueden mostrar otros idiomas con el mismo significado) |

Este documento reúne **todos los mensajes de error catalogados** del producto Partes de Atención en esta versión. En la operatoria real, un mismo mensaje puede aparecer en más de una pantalla (por ejemplo, fallo de conexión o sesión vencida).

**Leyenda de procesos**

| Código | Proceso |
|--------|---------|
| **P00** | Transversal (casi cualquier pantalla tras login o al operar Partes) |
| **P01** | Acceso, login, recuperación de clave, perfil Partes |
| **P02** | Maestros (Archivos) |
| **P03** | Carga diaria de tareas |
| **P04** | Captura inteligente (dentro del formulario de carga) |
| **P05** | Importación Excel (Carga diaria) |
| **P06** | Proceso masivo |
| **P07** | Dashboard e informes (consultas, paquete de horas) |
| **P08** | Emisión de reportes y diseñador de emisiones |
| **P09** | App móvil |
| **P10** | Asistente IA (avatar) y preferencias de modelo |
| **P11** | Parámetros generales y administración de seguridad |

Para conectividad con otros sistemas (integraciones), el administrador debe indicar la **URL de documentación OpenAPI** del backend de su instalación (ruta habitual: `/api/documentation`).

---

## P00 — Transversal

| Clave | Qué ve el usuario (resumen) | Causa habitual | Qué hacer |
|-------|-----------------------------|----------------|-----------|
| `infra.transport` | Error de conexión | Red, servidor no disponible | Reintentar; si persiste, avisar a soporte |
| `infra.unexpected` | Error inesperado | Fallo interno | Reintentar; reportar hora y pantalla |
| `auth.sessionExpired` | Sesión vencida por inactividad | Tiempo de inactividad superado | Volver a iniciar sesión |
| `auth.sessionUnauthorized` | Sesión no válida para esta empresa | Cambio de empresa o sesión inconsistente | Cerrar sesión e ingresar de nuevo |
| `partes.auth.noFunctionalProfile` | Sin perfil Partes habilitado | Sin vínculo asistente/cliente | Contactar al administrador |
| `partes.auth.inconsistentProfile` | Perfil Partes inconsistente | Doble vínculo o datos contradictorios | Contactar al administrador |
| `mobile.routeExcluded` | Función no disponible en la app móvil | Proceso solo web | Usar la versión web |

---

## P01 — Acceso e identidad

| Clave | Qué ve el usuario (resumen) | Causa habitual | Qué hacer |
|-------|-----------------------------|----------------|-----------|
| `auth.invalidCredentials` | Usuario o contraseña incorrectos | Credenciales erróneas | Verificar datos |
| `auth.emailNotFound` | El mail no existe | Email no registrado | Mail correcto o pedir alta |
| `auth.password.mismatch` | Las contraseñas no coinciden | Confirmación distinta | Repetir la nueva clave |
| `auth.password.tooShort` / `auth.password.policyUnsafe` / `validation.failed` | La contraseña no cumple las condiciones | Política de contraseña | Cumplir longitud y reglas |
| `auth.password.fieldsRequired` | Faltan campos de contraseña | Formulario incompleto | Completar actual, nueva y confirmación |
| `auth.password.sameAsCurrent` | La nueva debe ser distinta a la actual | Misma clave | Elegir otra contraseña |
| `auth.password.currentInvalid` / `auth.password.invalidCurrent` | Contraseña actual no válida | Clave actual incorrecta | Verificar contraseña actual |
| `auth.resetTokenInvalid` / `auth.password.resetInvalid` | Enlace de restablecimiento inválido | Token vencido o usado | Solicitar nuevo enlace |
| `tenant.invalid` | Instalación o empresa no válida | Código de empresa incorrecto | Verificar con el administrador |
| `tenant.gatewayNotSupported` | Instalación no habilitada para Partes | Configuración de la instalación | Contactar al administrador |
| `shell.blockedNoCompany` | No tiene empresas habilitadas | Sin empresa en la sesión | Pedir permisos al administrador |
| `mailEngine.sendFailed` | No se pudo enviar el correo de recuperación | Correo del servidor | Avisar a administración |
| `mailEngine.configMissing` | Falta configuración de correo | Servidor sin mail configurado | Avisar a administración |

*(También aplican claves **P00** tras login en Partes.)*

---

## P02 — Maestros

| Clave | Qué ve el usuario (resumen) | Causa habitual | Qué hacer |
|-------|-----------------------------|----------------|-----------|
| `partes.maestros.codeRequired` | El código es obligatorio | Código vacío | Completar código |
| `partes.maestros.codeDuplicate` | Ya existe ese código | Código repetido | Usar otro código |
| `partes.maestros.userIdRequired` | Debe seleccionar un usuario | Falta usuario de login | Elegir usuario del sistema |
| `partes.maestros.clienteIdRequired` | Debe indicar el cliente | Falta cliente | Seleccionar cliente |
| `partes.maestros.validationFailed` | Validación de datos fallida | p. ej. Erp Cliente/Artículo largo | Corregir según el mensaje en pantalla |
| `partes.maestros.forbidden` | Sin permiso para maestros | Sin permiso ABM | Pedir permiso |
| `partes.maestros.userIdExclusive` / `partes.maestros.exclusividadUserId` | Usuario ya vinculado a otro perfil | Exclusividad asistente/cliente | Otro usuario o liberar vínculo |
| `partes.maestros.usuarioNoVinculable` | Usuario no vinculable | Inactivo o ya vinculado | Elegir usuario activo sin vínculo |
| `partes.maestros.tipoGenericoNoAsignable` | No se asigna tipo genérico | Regla de catálogo | Elegir tipo no genérico |
| `partes.maestros.tipoDefaultNoInhabilitar` | No se puede inhabilitar el default | Debe haber default usable | Marcar otro default antes |
| `partes.maestros.hasReferences` / `partes.maestros.deleteConReferencias` | No se puede eliminar | Historial de tareas | Inhabilitar en lugar de borrar |
| `partes.maestros.notFound` | Registro no encontrado | Ya no existe | Refrescar listado |

*(También **P00**.)*

---

## P03 — Carga diaria

| Clave | Qué ve el usuario (resumen) | Causa habitual | Qué hacer |
|-------|-----------------------------|----------------|-----------|
| `partes.tarea.fechasRequeridas` | Faltan fechas desde/hasta | Filtro incompleto | Completar ambas fechas |
| `partes.tarea.duracionInvalida` | Duración inválida | No múltiplo del tramo, 0 o >24 h | Elegir tramo válido en hh:mm |
| `partes.tarea.fechaInvalida` | La fecha no es válida | Fecha mal formada | Corregir fecha |
| `partes.tarea.observacionRequerida` | Observación obligatoria | Texto vacío | Completar observación |
| `partes.tarea.camposObligatorios` | Campos obligatorios incompletos | Faltan datos | Completar formulario |
| `partes.tarea.fechaFuturaConfirmacion` | Confirmar fecha futura | Fecha posterior a hoy | Confirmar o cambiar fecha |
| `partes.tarea.tipoFueraUniverso` | Tipo no válido para el cliente | Tipo no asignado / no genérico | Elegir otro tipo |
| `partes.tarea.tipoNoUsable` / `clienteNoUsable` / `asistenteNoUsable` | Registro no usable | Inhabilitado o inactivo | Elegir registro activo |
| `partes.tarea.cerradaNoEditable` / `cerradaNoEliminable` | Tarea cerrada | Estado supervisado | Pedir reapertura (supervisor) |
| `partes.tarea.soloSupervisor` | Solo supervisor cierra/reabre | Rol insuficiente | Usuario supervisor |
| `partes.tarea.forbiddenOwner` | No puede operar tareas de otro | No es dueño ni supervisor | Solo propias tareas |
| `partes.tarea.forbidden` | Sin permiso de carga | Perfil cliente u otro | Perfil correcto |
| `partes.tarea.conflictoVersion` | Modificada por otro usuario | Cambio concurrente | Refrescar e intentar de nuevo |
| `partes.tarea.notFound` | Tarea no encontrada | Ya no existe | Refrescar listado |

*(También **P00**; en mobile ver **P09**.)*

---

## P04 — Captura inteligente

| Clave | Qué ve el usuario (resumen) | Causa habitual | Qué hacer |
|-------|-----------------------------|----------------|-----------|
| `partes.smartCapture.turnError` | No se completó el turno | Fallo al procesar | Reintentar; revisar conexión y credencial |
| `partes.smartCapture.sinCredencial` | Sin credencial de modelo | Preferencias sin clave | Configurar en Preferencias (avatar) |
| `partes.smartCapture.noEntendi` | No se interpretó el mensaje | Texto ambiguo | Reformular con más detalle |
| `partes.smartCapture.clienteNoEncontrado` | Cliente no encontrado | Nombre/código no coincide | Indicar cliente válido |
| `partes.smartCapture.clienteAmbiguo` | Varios clientes posibles | Coincidencias múltiples | Elegir opción numerada |
| `partes.smartCapture.tipoNoEncontrado` | Tipo no encontrado | Descripción no coincide | Indicar tipo válido |
| `partes.smartCapture.tipoAmbiguo` | Varios tipos posibles | Coincidencias múltiples | Elegir opción numerada |
| `partes.smartCapture.asistenteNoEncontrado` | Asistente no encontrado | Nombre no coincide | Indicar asistente válido |
| `partes.smartCapture.asistenteAmbiguo` | Varios asistentes posibles | Coincidencias múltiples | Elegir opción numerada |
| `partes.smartCapture.asistenteSoloSupervisor` | Solo supervisor cambia asistente | Rol asistente | Pedir a supervisor o cargar vos |
| `partes.smartCapture.fechaInvalida` | Fecha no válida | Formato o valor incorrecto | Corregir fecha |
| `partes.smartCapture.fechaFuturaConfirmar` | Fecha futura pendiente | Requiere confirmación | Responder sí/confirmo |
| `partes.smartCapture.fechaFuturaPendiente` | Pendiente confirmación fecha | Turno anterior incompleto | Confirmar o cancelar |
| `partes.smartCapture.fechaConfirmada` | (Informativo) Fecha aplicada | Confirmación aceptada | Continuar y guardar si corresponde |
| `partes.smartCapture.duracionInvalida` | Duración no válida | No respeta tramo | Indicar duración en tramos válidos |
| `partes.smartCapture.opcionInvalida` | Opción inválida | Número fuera de lista | Elegir número de la lista |
| `partes.smartCapture.opcionAplicada` | (Informativo) Opción aplicada | Desambiguación OK | Revisar formulario |

*(También **P00** y errores de validación de **P03** al guardar.)*

---

## P05 — Importación Excel

| Clave | Qué ve el usuario (resumen) | Causa habitual | Qué hacer |
|-------|-----------------------------|----------------|-----------|
| `excelImport.error.4603` | Sin permiso para importar | Rol sin permiso | Pedir permiso |
| `excelImport.error.4604` | Importación deshabilitada | Parámetro de instalación | Contactar administrador |
| `excelImport.error.4605` | Formato no soportado | No es .xlsx | Usar plantilla .xlsx |
| `excelImport.error.4606` | Error estructural del archivo | Archivo dañado o columnas | Descargar plantilla y rearmar |
| `excelImport.error.4607` | No se puede procesar el lote | Estado del lote | Validar de nuevo |
| `excelImport.error.4608` | El lote no admite procesamiento | Lote en estado incorrecto | Cerrar y volver a importar |
| `excelImport.error.4609` | Proceso de importación no encontrado | Configuración | Contactar soporte |
| `excelImport.error.4611` | Importación no registrada | Configuración | Contactar soporte |
| `excelImport.row.required` | Falta valor obligatorio en columna | Celda vacía | Completar columna indicada |
| `excelImport.row.invalidType` | Tipo inválido en columna | Formato de celda | Corregir según plantilla |
| `excelImport.process.noValidRows` | No hay filas válidas | Todas con error | Corregir planilla |
| `excelImport.process.partialNotAllowed` | No se permite procesamiento parcial | Errores pendientes | Corregir o quitar filas erróneas |
| `partes.import.clienteRequerido` | Cliente obligatorio (fila) | Columna cliente vacía | Completar cliente |
| `partes.import.clienteInvalido` | Cliente inválido (fila) | Código inexistente | Cliente activo del catálogo |
| `partes.import.asistenteRequerido` | Asistente obligatorio (fila) | Supervisor sin columna | Completar asistente |
| `partes.import.asistenteInvalido` | Asistente inválido (fila) | Código inexistente | Asistente activo |
| `partes.import.asistenteDistintoSesion` | Asistente distinto de la sesión | Asistente sin permiso | Quitar columna o usar supervisor |
| `partes.import.tipoRequerido` | Tipo obligatorio (fila) | Columna vacía | Completar tipo |
| `partes.import.tipoFueraUniverso` | Tipo fuera del cliente (fila) | Tipo no asignado | Tipo válido para ese cliente |
| `partes.import.fechaInvalida` | Fecha inválida (fila) | Formato incorrecto | Fecha válida |
| `partes.import.duracionInvalida` | Duración inválida (fila) | hh:mm o minutos incorrectos | Múltiplo del tramo |
| `partes.import.booleanoInvalido` | Valor sí/no inválido (fila) | Texto distinto de verdadero/falso | Usar verdadero o falso |
| `partes.import.descripcionRequerida` | Descripción obligatoria (fila) | Observación vacía | Completar descripción |

*(También **P00** y **P03** al grabar filas importadas.)*

---

## P06 — Proceso masivo

| Clave | Qué ve el usuario (resumen) | Causa habitual | Qué hacer |
|-------|-----------------------------|----------------|-----------|
| `partes.masivo.emptySelection` | Sin selección | Ninguna fila marcada | Seleccionar tareas |
| `partes.masivo.accionInvalida` | Acción no válida | Acción incorrecta | Elegir cerrar/reabrir/atributos |
| `partes.masivo.atributoInvalido` | Atributo o valor inválido | Tipo incompatible | Otro valor o menos filas |
| `partes.masivo.itemInvalido` | Ítem de lote inválido | Selección inconsistente | Refrescar y reseleccionar |
| `partes.masivo.noEsTarea` | Registro que no es tarea | Mezcla con paquete de horas | Solo tareas de carga |
| `partes.masivo.forbidden` | Solo supervisor | No es supervisor | Usuario supervisor |
| `partes.masivo.topeExcedido` | Supera tope configurado | Demasiadas filas | Reducir selección |
| `partes.masivo.loteDemasiadoGrande` | Más de ~5000 registros | Lote técnico | Refinar filtros |
| `partes.masivo.conflictoVersion` | Conflicto de versión | Cambio concurrente | Refrescar y rearmar |
| `partes.masivo.idInexistente` | Identificador inexistente | Fila obsoleta | Refrescar listado |
| `partes.tarea.fechasRequeridas` | Faltan fechas de filtro | Filtro incompleto | Completar fechas |

*(También **P00**.)*

---

## P07 — Dashboard e informes

| Clave | Qué ve el usuario (resumen) | Causa habitual | Qué hacer |
|-------|-----------------------------|----------------|-----------|
| `partes.consulta.empty` | Sin datos para el filtro | Periodo/filtros vacíos | Ampliar fechas o quitar filtros |
| `partes.consulta.ejeInvalido` | Eje de agrupación inválido | Eje incorrecto | Elegir eje permitido |
| `partes.consulta.granularidadRequerida` | Falta granularidad | Eje fecha sin día/mes | Elegir día o mes |
| `partes.tarea.fechasRequeridas` | Faltan fechas | Filtro incompleto | Completar fechas (paquete de horas) |

*(Vacío de consulta no es fallo técnico. También **P00**; en mobile **P09**.)*

---

## P08 — Emisiones

| Clave | Qué ve el usuario (resumen) | Causa habitual | Qué hacer |
|-------|-----------------------------|----------------|-----------|
| `emission.capabilityDisabled` | Emisión no habilitada | Parámetro Emission desactivado | Pedir habilitación al administrador |
| `emission.forbidden` | Sin permiso para emitir | Sin menú de consulta | Pedir permiso |
| `emission.design.forbidden` | Sin permiso para diseñar | Sin permiso de diseño | Pedir permiso o usar otro usuario |
| `emission.design.mobileExcluded` | Diseñador no en móvil | App móvil | Usar web en escritorio |
| `emission.design.notConfigured` | Diseñador no configurado | Instalación sin emisiones | Contactar soporte |
| `emission.design.noProcesses` | Sin procesos emisibles | Catálogo vacío | Contactar administrador |
| `emission.design.saveAsFailed` | No se guardó el diseño en catálogo | Error al registrar layout | Reintentar; soporte |
| `emission.design.selectProcess` / `confirmProcess` | (Flujo) Elegir proceso | Paso del diseñador | Confirmar proceso de consulta detallada |

*(Emitir usa los mismos filtros que Consulta detallada. También **P00** y **P07**.)*

---

## P09 — App móvil

| Clave | Qué ve el usuario (resumen) | Causa habitual | Qué hacer |
|-------|-----------------------------|----------------|-----------|
| `mobile.routeExcluded` | Función solo web | Proceso excluido en móvil | Abrir versión web |
| `tenant.invalid` | Empresa no válida en login | Código incorrecto | Corregir empresa |
| `infra.transport` | Falla conexión o prueba health | URL API incorrecta | Revisar URL en configuración (engranaje) |

*(Aplican **P00**, **P01**, **P03** en kardex, **P07** en dashboard/paquete.)*

---

## P10 — Asistente IA (avatar)

| Clave | Qué ve el usuario (resumen) | Causa habitual | Qué hacer |
|-------|-----------------------------|----------------|-----------|
| `chatAssistant.turnError` | No se completó la consulta | Fallo al responder | Reintentar; revisar credencial |
| `chatAssistant.emptyState` | Sin proveedor configurado | Sin credencial en Preferencias | Ir a Preferencias y cargar clave |
| `partes.smartCapture.sinCredencial` | Misma situación en captura | Sin credencial | Preferencias del avatar |

*(El asistente **no graba** tareas; orienta según este manual. También **P00**.)*

---

## P11 — Parámetros y seguridad

| Clave | Qué ve el usuario (resumen) | Causa habitual | Qué hacer |
|-------|-----------------------------|----------------|-----------|
| `roles.delete.hasPermisos` | No se puede eliminar el rol | Rol en uso | Quitar asignaciones antes |

*(Edición de parámetros: mensajes de validación según tipo de dato. También **P00**.)*
