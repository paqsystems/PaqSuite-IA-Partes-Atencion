import axios from 'axios'
import { getStoredToken } from '../auth/authStorage'

export const httpClient = axios.create({
  baseURL: '/api',
  headers: { Accept: 'application/json' },
})

httpClient.interceptors.request.use((config) => {
  const token = getStoredToken()
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})
