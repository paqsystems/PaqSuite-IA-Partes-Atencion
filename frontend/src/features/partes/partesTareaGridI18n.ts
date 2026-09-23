import { useMemo } from 'react'
import { useTranslation } from 'react-i18next'

/** Captions de columnas compartidas entre carga diaria y proceso masivo. */
export function usePartesTareaGridCaptions() {
  const { t, i18n } = useTranslation()
  return useMemo(
    () => ({
      fecha: t('partes.informe.field.fecha'),
      asistente: t('partes.informe.filtro.asistente'),
      cliente: t('partes.informe.filtro.cliente'),
      tipoTarea: t('partes.informe.field.tipoTareaDescripcion'),
      duracion: t('partes.tarea.duracion'),
      duracionDecimal: t('partes.tarea.duracionDecimal'),
      sinCargo: t('partes.informe.field.sinCargo'),
      presencial: t('partes.informe.field.presencial'),
      observacion: t('partes.informe.field.observacion'),
      cerrado: t('partes.informe.field.cerrado'),
      clienteCode: t('partes.informe.field.clienteCode'),
      tipoTareaCode: t('partes.informe.field.tipoTareaCode'),
      minutos: t('partes.common.minutos'),
      hintEditar: t('admin.common.edit'),
      hintEliminar: t('admin.common.delete'),
      hintCerrarReabrir: t('partes.common.hint.cerrarReabrir'),
      nuevaTarea: t('partes.mobile.nuevaTarea'),
    }),
    [t, i18n.language],
  )
}
