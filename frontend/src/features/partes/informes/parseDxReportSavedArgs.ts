export type ParsedDxReportSaved = {
  url: string
  layoutDefinition?: string
}

function unwrapDxValue(value: unknown): unknown {
  if (typeof value === 'function') {
    try {
      return unwrapDxValue(value())
    } catch {
      return undefined
    }
  }
  if (value && typeof value === 'object') {
    const peek = (value as { peek?: unknown }).peek
    if (typeof peek === 'function') {
      try {
        return unwrapDxValue(peek.call(value))
      } catch {
        return undefined
      }
    }
  }
  return value
}

function readUrlFromObject(value: Record<string, unknown>): string {
  const candidates = [value.Url, value.url, value.NewUrl, value.newUrl, value.reportUrl]
  for (const candidate of candidates) {
    const unwrapped = unwrapDxValue(candidate)
    if (typeof unwrapped === 'string' && unwrapped.trim() !== '') {
      return unwrapped.trim()
    }
  }
  return ''
}

function readLayoutFromObject(value: Record<string, unknown>): string | undefined {
  const report = unwrapDxValue(value.Report ?? value.report)
  if (!report || typeof report !== 'object') {
    return undefined
  }
  const serialize = (report as { serialize?: () => string }).serialize
  if (typeof serialize !== 'function') {
    return undefined
  }
  try {
    const xml = serialize()
    return typeof xml === 'string' && xml.trim() !== '' ? xml : undefined
  } catch {
    return undefined
  }
}

function asRecord(value: unknown): Record<string, unknown> | null {
  if (!value || typeof value !== 'object') {
    return null
  }
  return value as Record<string, unknown>
}

/**
 * DevExpress ReportSaved llega de varias formas:
 * - objeto plano `{ Url, Report }`
 * - `(sender, args)` como lista
 * - wrapper React 26.1 `{ sender, args: { Url, Report } }` (devexpress-reporting-react)
 */
export function parseDxReportSavedArgs(args: unknown): ParsedDxReportSaved {
  const queue: unknown[] = Array.isArray(args) ? [...args] : [args]
  const seen = new Set<unknown>()
  let url = ''
  let layoutDefinition: string | undefined

  for (let index = 0; index < queue.length; index += 1) {
    const item = queue[index]
    if (item == null || seen.has(item)) {
      continue
    }
    if (typeof item === 'object') {
      seen.add(item)
    }
    const record = asRecord(item)
    if (!record) {
      continue
    }
    if (!url) {
      url = readUrlFromObject(record)
    }
    if (!layoutDefinition) {
      layoutDefinition = readLayoutFromObject(record)
    }
    const nested = record.args ?? record.Args
    if (nested != null && nested !== item) {
      queue.push(nested)
    }
  }

  return { url, layoutDefinition }
}
