import { useMemo } from 'react'
import { useTranslation } from 'react-i18next'

export type PartesEstadoCerradoFiltro = 'todas' | 'abiertas' | 'cerradas'

export function usePartesEstadoCerradoOptions() {
  const { t, i18n } = useTranslation()
  return useMemo(
    () =>
      [
        { id: 'todas' as const, text: t('partes.informe.estado.todas') },
        { id: 'abiertas' as const, text: t('partes.informe.estado.abiertas') },
        { id: 'cerradas' as const, text: t('partes.informe.estado.cerradas') },
      ],
    [t, i18n.language],
  )
}
