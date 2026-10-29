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

function isLayoutXml(value: unknown): value is string {
  if (typeof value !== 'string') {
    return false
  }
  const trimmed = value.trim()
  return trimmed.startsWith('<') && trimmed.length > 5
}

function tryCallSerializer(target: object, methodName: string): string | undefined {
  const method = (target as Record<string, unknown>)[methodName]
  if (typeof method !== 'function') {
    return undefined
  }
  try {
    const xml = unwrapDxValue(method.call(target))
    return isLayoutXml(xml) ? xml : undefined
  } catch {
    return undefined
  }
}

function serializeDxReport(report: unknown): string | undefined {
  const resolved = unwrapDxValue(report)
  if (isLayoutXml(resolved)) {
    return resolved
  }
  if (!resolved || typeof resolved !== 'object') {
    return undefined
  }
  const fromXmlProp = unwrapDxValue((resolved as { XML?: unknown; xml?: unknown }).XML
    ?? (resolved as { xml?: unknown }).xml)
  if (isLayoutXml(fromXmlProp)) {
    return fromXmlProp
  }
  for (const methodName of ['serialize', 'serializeToXml', 'saveToStr', 'SaveToStr']) {
    const xml = tryCallSerializer(resolved, methodName)
    if (xml) {
      return xml
    }
  }
  return undefined
}

function readLayoutFromObject(value: Record<string, unknown>): string | undefined {
  const direct = unwrapDxValue(value.layoutDefinition ?? value.LayoutDefinition)
  if (isLayoutXml(direct)) {
    return direct
  }

  const nestedReport = unwrapDxValue(value.Report ?? value.report)
  const fromNested = serializeDxReport(nestedReport)
  if (fromNested) {
    return fromNested
  }

  const sender = asRecord(unwrapDxValue(value.sender ?? value.Sender ?? value.component))
  if (sender) {
    const fromSenderReport = serializeDxReport(sender.Report ?? sender.report)
    if (fromSenderReport) {
      return fromSenderReport
    }
    const getReport = sender.GetReport ?? sender.getReport
    if (typeof getReport === 'function') {
      try {
        const fromGetter = serializeDxReport(getReport.call(sender))
        if (fromGetter) {
          return fromGetter
        }
      } catch {
        /* ignore */
      }
    }
  }

  return undefined
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
