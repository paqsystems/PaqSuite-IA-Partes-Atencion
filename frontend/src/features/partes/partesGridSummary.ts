import { useMemo } from 'react'
import { useTranslation } from 'react-i18next'
import { DEFAULT_GRID_SUMMARY_TYPE_LABELS } from '@paqsuite/react-core'
import { formatMinutosAsHhMm } from './carga/partesTareaDuration'

/** Pie de grilla: suma de columna `duracionHoras` (horas decimales). */
export function usePartesDuracionHorasSummaryItems() {
  const { t, i18n } = useTranslation()
  const sumLabel = DEFAULT_GRID_SUMMARY_TYPE_LABELS.sum
  const hoursSuffix = t('partes.grid.summary.hoursSuffix')
  return useMemo(
    () => [
      {
        column: 'duracionHoras',
        summaryType: 'sum' as const,
        name: 'pq-duracionHoras-sum',
        displayFormat: `${sumLabel}: {0} ${hoursSuffix}`,
        valueFormat: '#0.##',
      },
    ],
    [sumLabel, hoursSuffix, i18n.language],
  )
}

type MinutosSummaryColumn = 'duracionMinutos' | 'totalMinutos'

/** Pie de grilla: suma de minutos con presentación `hh:mm`. */
export function usePartesMinutosColumnSummaryItems(
  column: MinutosSummaryColumn,
  summaryName: string,
) {
  const sumLabel = DEFAULT_GRID_SUMMARY_TYPE_LABELS.sum
  return useMemo(
    () => [
      {
        column,
        summaryType: 'sum' as const,
        name: summaryName,
        displayFormat: `${sumLabel}: {0}`,
        customizeText: (info: { value?: string | number | Date }) =>
          `${sumLabel}: ${formatMinutosAsHhMm(Number(info.value ?? 0))}`,
      },
    ],
    [column, summaryName, sumLabel],
  )
}
