import config from 'devextreme/core/config'
import type { ReactNode } from 'react'
import { useEffect } from 'react'
import 'devextreme/dist/css/dx.material.blue.light.css'

export function ThemeProvider({ children }: { children: ReactNode }) {
  useEffect(() => {
    config({
      licenseKey: import.meta.env.VITE_DEVEXTREME_LICENSE ?? '',
    })
  }, [])

  return <>{children}</>
}
