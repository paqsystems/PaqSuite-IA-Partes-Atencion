import { expect, test } from '@playwright/test'

test('muestra pantalla de login', async ({ page }) => {
  await page.goto('/login')
  await expect(page.getByTestId('authLoginPage')).toBeVisible()
  await expect(page.getByRole('heading', { name: 'Iniciar sesión' })).toBeVisible()
})
