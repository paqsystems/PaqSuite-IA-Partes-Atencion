import { expect, test } from '@playwright/test'

/**
 * TR-008: abrir Asistente IA desde el avatar → pantalla chat (empty sin LLM).
 * Requiere backend con seed (admin / Paqsystems).
 */
test('avatar abre chat-assistant con empty sin LLM', async ({ page }) => {
  test.setTimeout(60_000)
  await page.goto('/login')
  await page.getByTestId('loginUsuario').locator('input').fill('admin')
  await page.getByTestId('loginPassword').locator('input').fill('Paqsystems')
  await page.getByTestId('loginSubmit').click()

  await expect(page).toHaveURL(/\/partes\/?$/, { timeout: 45_000 })

  await page.getByTestId('userAvatarMenuTrigger').click()
  await page.getByTestId('userAvatarMenuItem-chat').click()

  await expect(page).toHaveURL(/\/chat-assistant\/?$/, { timeout: 15_000 })
  await expect(page.getByTestId('partesChatAssistantHost')).toBeVisible()
  await expect(page.getByTestId('chatAssistant.page')).toBeVisible({ timeout: 15_000 })
})
