import { apiRequest } from '@paqsuite/react-core'
import type { LoginSessionResultado } from './authTypes'
import { buildAuthPlatformHeaders } from './platformContext'
import { getAuthToken } from './authSessionStore'

function bearerHeaders(token: string): HeadersInit {
  return { Authorization: `Bearer ${token}` }
}

export async function loginRequest(input: {
  usuario: string
  password: string
  locale?: string
  tenant?: string
}) {
  return apiRequest<LoginSessionResultado>('/api/v1/auth/login', {
    method: 'POST',
    body: JSON.stringify({
      usuario: input.usuario,
      password: input.password,
      locale: input.locale ?? 'es',
    }),
    platform: {
      cliente: buildAuthPlatformHeaders(input.tenant).cliente,
    },
    skipUnauthorizedHandler: true,
  })
}

export async function logoutRequest() {
  const token = getAuthToken()
  if (!token) {
    return null
  }

  return apiRequest<Record<string, never>>('/api/v1/auth/logout', {
    method: 'POST',
    headers: bearerHeaders(token),
    platform: buildAuthPlatformHeaders(),
  })
}

export async function meRequest() {
  const token = getAuthToken()
  if (!token) {
    return null
  }

  return apiRequest<LoginSessionResultado>('/api/v1/auth/me', {
    method: 'GET',
    headers: bearerHeaders(token),
    platform: buildAuthPlatformHeaders(),
  })
}

export async function forgotPasswordRequest(input: { email: string; locale?: string }) {
  return apiRequest<Record<string, never>>('/api/v1/auth/forgot-password', {
    method: 'POST',
    body: JSON.stringify({
      email: input.email,
      locale: input.locale ?? 'es',
    }),
    platform: {
      cliente: buildAuthPlatformHeaders().cliente,
    },
    skipUnauthorizedHandler: true,
  })
}

export async function resetPasswordRequest(input: {
  token: string
  password: string
  passwordConfirmation: string
  locale?: string
}) {
  return apiRequest<Record<string, never>>('/api/v1/auth/reset-password', {
    method: 'POST',
    body: JSON.stringify({
      token: input.token,
      password: input.password,
      passwordConfirmation: input.passwordConfirmation,
      locale: input.locale ?? 'es',
    }),
    platform: {
      cliente: buildAuthPlatformHeaders().cliente,
    },
    skipUnauthorizedHandler: true,
  })
}

export async function changePasswordRequest(input: {
  passwordActual: string
  password: string
  passwordConfirmation: string
}) {
  const token = getAuthToken()
  if (!token) {
    return null
  }

  return apiRequest<Record<string, never>>('/api/v1/auth/change-password', {
    method: 'POST',
    body: JSON.stringify({
      passwordActual: input.passwordActual,
      password: input.password,
      passwordConfirmation: input.passwordConfirmation,
    }),
    headers: bearerHeaders(token),
    platform: buildAuthPlatformHeaders(),
  })
}
