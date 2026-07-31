import { getMinutosWeb } from './authSessionStore'

export const defaultIdleMinutes = 60

type IdleOptions = {
  minutosWeb?: number
  onExpire: () => void
}

const activityEvents = ['mousedown', 'keydown', 'touchstart', 'scroll'] as const

let timerId: ReturnType<typeof setTimeout> | undefined
let boundOptions: IdleOptions | undefined
let listenersAttached = false

function resolveMinutes(minutosWeb?: number): number {
  const value = minutosWeb ?? getMinutosWeb()
  return value > 0 ? value : defaultIdleMinutes
}

function scheduleTimer(): void {
  if (!boundOptions) {
    return
  }

  if (timerId !== undefined) {
    clearTimeout(timerId)
  }

  const minutes = resolveMinutes(boundOptions.minutosWeb)
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
  boundOptions = options
  attachListeners()
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
