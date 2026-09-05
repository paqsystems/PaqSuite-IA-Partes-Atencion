import {
  isNativeApp,
  setPreferencesAdapter,
} from '@paqsuite/react-core'

/** Inyecta `@capacitor/preferences` cuando hay runtime native (TR-007 RN-TR-15). */
export async function installCapacitorPreferencesAdapter(): Promise<void> {
  if (!isNativeApp()) {
    return
  }

  try {
    const { Preferences } = await import('@capacitor/preferences')
    setPreferencesAdapter({
      get: async (key) => {
        const result = await Preferences.get({ key })
        return result.value
      },
      set: async (key, value) => {
        await Preferences.set({ key, value })
      },
      remove: async (key) => {
        await Preferences.remove({ key })
      },
    })
  } catch {
    // Fallback GEN (localStorage) si el plugin no está disponible.
  }
}
