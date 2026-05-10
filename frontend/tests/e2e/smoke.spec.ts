import { expect, test } from '@playwright/test'

test('muestra pantalla de login', async ({ page }) => {
  await page.goto('/login')
  await expect(page.getByTestId('login-heading')).toHaveText('Ingresar')
})
