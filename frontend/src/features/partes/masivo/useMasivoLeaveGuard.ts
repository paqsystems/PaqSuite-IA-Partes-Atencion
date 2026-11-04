import { useEffect, useRef, useState } from 'react'
import { useNavigate } from 'react-router-dom'

export function useMasivoLeaveGuard(sessionDirty: boolean, informarSentThisSession: boolean) {
  const navigate = useNavigate()
  const allowLeaveRef = useRef(false)
  const pendingPathRef = useRef<string | null>(null)
  const [leaveDialogOpen, setLeaveDialogOpen] = useState(false)

  const shouldGuard = sessionDirty && !informarSentThisSession && !allowLeaveRef.current

  useEffect(() => {
    const handler = (event: BeforeUnloadEvent) => {
      if (shouldGuard) {
        event.preventDefault()
      }
    }
    window.addEventListener('beforeunload', handler)
    return () => window.removeEventListener('beforeunload', handler)
  }, [shouldGuard])

  useEffect(() => {
    if (!shouldGuard) {
      return
    }

    const handleClick = (event: MouseEvent) => {
      const target = event.target as HTMLElement | null
      const anchor = target?.closest('a[href]') as HTMLAnchorElement | null
      if (!anchor) {
        return
      }
      if (anchor.getAttribute('target') === '_blank') {
        return
      }
      const href = anchor.getAttribute('href') ?? ''
      if (href === '' || href.startsWith('#') || href.startsWith('mailto:')) {
        return
      }
      const isInternal =
        href.startsWith('/') ||
        (anchor.origin !== '' && anchor.origin === window.location.origin)
      if (!isInternal) {
        return
      }
      event.preventDefault()
      event.stopPropagation()
      pendingPathRef.current = href.startsWith('/') ? href : `${anchor.pathname}${anchor.search}`
      setLeaveDialogOpen(true)
    }

    document.addEventListener('click', handleClick, true)
    return () => document.removeEventListener('click', handleClick, true)
  }, [shouldGuard])

  function proceedPendingNavigation() {
    const path = pendingPathRef.current
    pendingPathRef.current = null
    if (path) {
      navigate(path)
    }
  }

  function cancelLeave() {
    pendingPathRef.current = null
    setLeaveDialogOpen(false)
  }

  function leaveWithoutInformar() {
    allowLeaveRef.current = true
    setLeaveDialogOpen(false)
    proceedPendingNavigation()
  }

  function markInformarSentAndProceedIfBlocked() {
    allowLeaveRef.current = true
    setLeaveDialogOpen(false)
    proceedPendingNavigation()
  }

  return {
    leaveDialogOpen,
    setLeaveDialogOpen,
    cancelLeave,
    leaveWithoutInformar,
    markInformarSentAndProceedIfBlocked,
  }
}
