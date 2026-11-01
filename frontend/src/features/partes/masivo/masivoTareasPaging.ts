export type MasivoGridLoadOptions = {
  skip?: number
  take?: number
}

export function masivoGridLoadOptionsToQuery(loadOptions: MasivoGridLoadOptions): {
  page: number
  pageSize: number
} {
  const pageSize = loadOptions.take != null && loadOptions.take > 0 ? loadOptions.take : 20
  const skip = loadOptions.skip ?? 0
  const page = Math.floor(skip / pageSize) + 1
  return { page, pageSize }
}
