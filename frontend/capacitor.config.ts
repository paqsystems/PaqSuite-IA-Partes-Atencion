import type { CapacitorConfig } from '@capacitor/cli'

const config: CapacitorConfig = {
  appId: 'com.paqsystems.partesatencion',
  appName: 'Partes de Atención',
  webDir: 'dist',
  server: {
    androidScheme: 'https',
    iosScheme: 'https',
    cleartext: true,
  },
  android: {
    // WebView es https://localhost (BrowserRouter). Health/API de lab es http://10.0.2.2.
    allowMixedContent: true,
  },
}

export default config
