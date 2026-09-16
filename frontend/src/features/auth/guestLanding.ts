export function searchHasResetToken(search: string): boolean {
  const params = new URLSearchParams(
    search.startsWith('?') ? search.slice(1) : search,
  )
  return (params.get('token') ?? '').trim() !== ''
}

export function guestLandingPathname(search: string): '/reset-password' | '/login' {
  return searchHasResetToken(search) ? '/reset-password' : '/login'
}
