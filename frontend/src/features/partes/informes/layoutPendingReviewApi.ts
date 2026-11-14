/** Endpoints sidecar L-B-10 (proxy Vite `/api/layout`). */

export const LAYOUT_APPLY_URL = '/api/layout/apply'
export const LAYOUT_RESTORE_URL = '/api/layout/restore'
export const LAYOUT_CONTENT_URL = '/api/layout/content'
export const LAYOUT_ACK_URL = '/api/layout/ack'

export type LayoutContentResult =
  | { ok: true; reportUrl: string; layoutXml: string; pendingReview: boolean }
  | { ok: false; error: string }

export type LayoutSimpleResult = { ok: true; reportUrl: string } | { ok: false; error: string }

export async function fetchLayoutContent(
  reportUrl: string,
  signal?: AbortSignal,
): Promise<LayoutContentResult> {
  const qs = new URLSearchParams({ reportUrl })
  const res = await fetch(`${LAYOUT_CONTENT_URL}?${qs.toString()}`, {
    method: 'GET',
    headers: { Accept: 'application/json' },
    signal,
  })
  const json = (await res.json().catch(() => null)) as {
    ok?: boolean
    reportUrl?: string
    layoutXml?: string
    pendingReview?: boolean
    error?: string
  } | null

  if (!res.ok || !json?.ok || typeof json.layoutXml !== 'string') {
    return {
      ok: false,
      error: json?.error ? String(json.error) : 'layout content failed',
    }
  }

  return {
    ok: true,
    reportUrl: json.reportUrl ?? reportUrl,
    layoutXml: json.layoutXml,
    pendingReview: Boolean(json.pendingReview),
  }
}

export async function restoreLayoutBackup(
  reportUrl: string,
  signal?: AbortSignal,
): Promise<LayoutSimpleResult> {
  const res = await fetch(LAYOUT_RESTORE_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    body: JSON.stringify({ reportUrl }),
    signal,
  })
  const json = (await res.json().catch(() => null)) as {
    ok?: boolean
    reportUrl?: string
    error?: string
  } | null

  if (!res.ok || !json?.ok) {
    return {
      ok: false,
      error: json?.error ? String(json.error) : 'layout restore failed',
    }
  }

  return { ok: true, reportUrl: json.reportUrl ?? reportUrl }
}

export async function ackLayoutBackup(
  reportUrl: string,
  signal?: AbortSignal,
): Promise<LayoutSimpleResult> {
  const res = await fetch(LAYOUT_ACK_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    body: JSON.stringify({ reportUrl }),
    signal,
  })
  const json = (await res.json().catch(() => null)) as {
    ok?: boolean
    reportUrl?: string
    error?: string
  } | null

  if (!res.ok || !json?.ok) {
    return {
      ok: false,
      error: json?.error ? String(json.error) : 'layout ack failed',
    }
  }

  return { ok: true, reportUrl: json.reportUrl ?? reportUrl }
}
