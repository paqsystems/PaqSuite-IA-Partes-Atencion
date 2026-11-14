import { describe, expect, it } from 'vitest'
import {
  LAYOUT_ACK_URL,
  LAYOUT_APPLY_URL,
  LAYOUT_CONTENT_URL,
  LAYOUT_RESTORE_URL,
} from './layoutPendingReviewApi'

describe('layoutPendingReviewApi helpers', () => {
  it('expone paths same-origin del sidecar (proxy Vite)', () => {
    expect(LAYOUT_APPLY_URL).toBe('/api/layout/apply')
    expect(LAYOUT_RESTORE_URL).toBe('/api/layout/restore')
    expect(LAYOUT_CONTENT_URL).toBe('/api/layout/content')
    expect(LAYOUT_ACK_URL).toBe('/api/layout/ack')
  })
})
