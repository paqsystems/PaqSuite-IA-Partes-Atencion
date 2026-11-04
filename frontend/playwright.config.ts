import { defineConfig, devices } from '@playwright/test'

const remoteBaseUrl = (process.env.PW_BASE_URL ?? process.env.PLAYWRIGHT_BASE_URL ?? '').trim()
const isRemoteE2e = /^https?:\/\//i.test(remoteBaseUrl) && !/localhost|127\.0\.0\.1/i.test(remoteBaseUrl)

export default defineConfig({
  testDir: './tests/e2e',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: 'list',
  use: {
    baseURL: isRemoteE2e ? remoteBaseUrl : 'http://localhost:3101',
    trace: 'on-first-retry',
  },
  projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],
  ...(isRemoteE2e
    ? {}
    : {
        webServer: {
          command: 'npm run dev:e2e',
          url: 'http://localhost:3101',
          reuseExistingServer: !process.env.CI,
          timeout: 180_000,
          env: {
            ...process.env,
            VITE_API_PROXY_TARGET: process.env.VITE_API_PROXY_TARGET ?? 'http://127.0.0.1:8010',
            VITE_DX_REPORTING_HOST: process.env.VITE_DX_REPORTING_HOST ?? 'http://127.0.0.1:5055',
            VITE_TENANT_DEFAULT_CLIENT:
              process.env.VITE_TENANT_DEFAULT_CLIENT ?? process.env.PW_TENANT ?? 'DEMO',
          },
        },
      }),
})
