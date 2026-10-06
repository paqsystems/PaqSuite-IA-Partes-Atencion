import { getMinutosWeb } from './authSessionStore'

export const defaultIdleMinutes = 60

type IdleOptions = {
  minutosWeb?: number
  onExpire: () => void
  now?: () => number
}

const activityEvents = ['pointerdown', 'keydown', 'touchstart'] as const

let timerId: ReturnType<typeof setTimeout> | undefined
let boundOptions: IdleOptions | undefined
let listenersAttached = false

function resolveMinutes(minutosWeb?: number): number {
  const value = minutosWeb ?? getMinutosWeb()
  if (typeof value !== 'number' || value < 0) {
    return defaultIdleMinutes
  }
  return value
}

function scheduleTimer(): void {
  if (!boundOptions) {
    return
  }

  const minutes = resolveMinutes(boundOptions.minutosWeb)
  if (minutes === 0) {
    if (timerId !== undefined) {
      clearTimeout(timerId)
      timerId = undefined
    }
    return
  }

  if (timerId !== undefined) {
    clearTimeout(timerId)
  }

  timerId = setTimeout(() => {
    boundOptions?.onExpire()
  }, minutes * 60_000)
}

function onActivity(): void {
  scheduleTimer()
}

function attachListeners(): void {
  if (listenersAttached || typeof window === 'undefined') {
    return
  }

  activityEvents.forEach((eventName) => {
    window.addEventListener(eventName, onActivity, { passive: true })
  })
  listenersAttached = true
}

function detachListeners(): void {
  if (!listenersAttached || typeof window === 'undefined') {
    return
  }

  activityEvents.forEach((eventName) => {
    window.removeEventListener(eventName, onActivity)
  })
  listenersAttached = false
}

export function startIdleSession(options: IdleOptions): void {
  stopIdleSession()

  const minutes = resolveMinutes(options.minutosWeb)
  if (minutes === 0) {
    return
  }

  boundOptions = options
  attachListeners()
  scheduleTimer()
}

export function resetIdleSession(minutosWeb?: number): void {
  if (!boundOptions) {
    return
  }

  if (minutosWeb !== undefined) {
    boundOptions = { ...boundOptions, minutosWeb }
  }

  const minutes = resolveMinutes(boundOptions.minutosWeb)
  if (minutes === 0) {
    if (timerId !== undefined) {
      clearTimeout(timerId)
      timerId = undefined
    }
    detachListeners()
    boundOptions = undefined
    return
  }

  if (!listenersAttached) {
    attachListeners()
  }
  scheduleTimer()
}

export function stopIdleSession(): void {
  if (timerId !== undefined) {
    clearTimeout(timerId)
    timerId = undefined
  }
  boundOptions = undefined
  detachListeners()
}

export function resolveIdleMinutes(minutosWeb?: number): number {
  return resolveMinutes(minutosWeb)
}
