import { downloadBlob } from '@paqsuite/react-core'

export type EmissionJobDeliveryInput = {
  jobId: string
  channel: string
  fileName?: string | null
}

export function defaultEmissionFileName(channel: string, fileName?: string | null): string {
  const trimmed = (fileName ?? '').trim()
  if (trimmed !== '') {
    return trimmed
  }
  if (channel === 'excel') {
    return 'emision.xlsx'
  }
  if (channel === 'csv') {
    return 'emision.csv'
  }
  return 'emision.pdf'
}

export async function fetchEmissionJobBlob(jobId: string): Promise<Blob> {
  const response = await fetch(`/api/v1/emissions/jobs/${encodeURIComponent(jobId)}/download`, {
    method: 'GET',
    headers: { Accept: 'application/octet-stream,application/pdf,*/*' },
  })
  if (!response.ok) {
    throw new Error('emission-download-failed')
  }
  const blob = await response.blob()
  if (blob.size === 0 || blob.type.includes('json')) {
    throw new Error('emission-download-empty')
  }
  return blob
}

export function printPdfBlob(blob: Blob): void {
  if (typeof document === 'undefined' || typeof URL === 'undefined') {
    return
  }
  const url = URL.createObjectURL(blob)
  const iframe = document.createElement('iframe')
  iframe.setAttribute('data-testid', 'emissions.printFrame')
  iframe.setAttribute('title', 'print')
  iframe.style.position = 'fixed'
  iframe.style.right = '0'
  iframe.style.bottom = '0'
  iframe.style.width = '0'
  iframe.style.height = '0'
  iframe.style.border = '0'
  iframe.src = url
  iframe.onload = () => {
    try {
      iframe.contentWindow?.focus()
      iframe.contentWindow?.print()
    } finally {
      window.setTimeout(() => {
        iframe.remove()
        URL.revokeObjectURL(url)
      }, 60_000)
    }
  }
  document.body.appendChild(iframe)
}

export async function deliverEmissionJob(
  input: EmissionJobDeliveryInput,
): Promise<'downloaded' | 'printed' | 'skipped'> {
  if (input.channel === 'mail' || input.jobId.trim() === '') {
    return 'skipped'
  }
  const blob = await fetchEmissionJobBlob(input.jobId)
  if (input.channel === 'print') {
    printPdfBlob(blob)
    return 'printed'
  }
  downloadBlob(blob, defaultEmissionFileName(input.channel, input.fileName))
  return 'downloaded'
}
