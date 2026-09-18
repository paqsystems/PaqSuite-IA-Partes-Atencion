export function isExcelBinaryDownloadUrl(url: string): boolean {
  return (
    /\/excel-import\/processes\/[^/?#]+\/template(?:\?|#|$)/.test(url) ||
    /\/excel-import\/batches\/[^/?#]+\/errors\/export(?:\?|#|$)/.test(url)
  )
}

export function isEmissionBinaryDownloadUrl(url: string): boolean {
  return /\/emissions\/jobs\/[^/?#]+\/download(?:\?|#|$)/.test(url)
}

export function isBinaryDownloadUrl(url: string): boolean {
  return isExcelBinaryDownloadUrl(url) || isEmissionBinaryDownloadUrl(url)
}

export function isZipBinary(buffer: ArrayBuffer): boolean {
  if (buffer.byteLength < 2) {
    return false
  }
  const header = new Uint8Array(buffer.slice(0, 2))
  return header[0] === 0x50 && header[1] === 0x4b
}

export function isPdfBinary(buffer: ArrayBuffer): boolean {
  if (buffer.byteLength < 4) {
    return false
  }
  const header = new Uint8Array(buffer.slice(0, 4))
  return (
    header[0] === 0x25 &&
    header[1] === 0x50 &&
    header[2] === 0x44 &&
    header[3] === 0x46
  )
}

export function isValidBinaryArtifact(buffer: ArrayBuffer, url: string): boolean {
  if (isExcelBinaryDownloadUrl(url)) {
    return isZipBinary(buffer)
  }
  if (isEmissionBinaryDownloadUrl(url)) {
    return isZipBinary(buffer) || isPdfBinary(buffer)
  }
  return true
}
