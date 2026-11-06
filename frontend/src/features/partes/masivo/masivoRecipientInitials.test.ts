import { describe, expect, it } from 'vitest'
import { masivoRecipientInitials } from './masivoRecipientInitials'

describe('masivoRecipientInitials', () => {
  it('usa dos letras del nombre simple', () => {
    expect(masivoRecipientInitials('Crilen')).toBe('CR')
  })

  it('usa inicial de primer y último término', () => {
    expect(masivoRecipientInitials('Golo Tap')).toBe('GT')
  })

  it('devuelve interrogación si no hay nombre', () => {
    expect(masivoRecipientInitials('   ')).toBe('?')
  })
})
