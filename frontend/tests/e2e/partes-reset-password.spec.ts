import { expect, test } from '@playwright/test'

test('abre la pantalla de nueva contraseña con token', async ({ page }) => {
  await page.goto('/reset-password?token=test-token&locale=es')
  await expect(page.getByTestId('authResetPage')).toBeVisible()
  await expect(page.getByTestId('resetPassword')).toBeVisible()
  await expect(page.getByTestId('resetPasswordConfirmation')).toBeVisible()
})

test('si el token llega al login, redirige a reset-password', async ({ page }) => {
  await page.goto('/login?token=test-token&locale=es')
  await expect(page.getByTestId('authResetPage')).toBeVisible()
  await expect(page).toHaveURL(/\/reset-password/)
})

test('si el token llega a la raíz, redirige a reset-password', async ({ page }) => {
  await page.goto('/?token=test-token&locale=es&cliente=PAQ')
  await expect(page.getByTestId('authResetPage')).toBeVisible()
  await expect(page).toHaveURL(/\/reset-password/)
})
