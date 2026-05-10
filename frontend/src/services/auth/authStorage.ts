const tokenKey = 'paqsuite_auth_token'

export function getStoredToken(): string | null {
  return localStorage.getItem(tokenKey)
}

export function setStoredToken(token: string | null): void {
  if (token) {
    localStorage.setItem(tokenKey, token)
  } else {
    localStorage.removeItem(tokenKey)
  }
}
