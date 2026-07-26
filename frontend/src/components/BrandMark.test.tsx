import { render } from '@testing-library/react'
import { describe, expect, it } from 'vitest'
import BrandMark from './BrandMark'

describe('BrandMark', () => {
  it('renders the brand mark container', () => {
    const { container } = render(<BrandMark />)

    expect(container.firstChild).toBeInTheDocument()
    expect(container.querySelector('svg')).toBeInTheDocument()
  })

  it('applies the light variant styles', () => {
    const { container } = render(<BrandMark light />)
    const mark = container.firstChild as HTMLElement

    expect(mark.className).toContain('bg-white/15')
  })

  it('applies size variants', () => {
    const { container, rerender } = render(<BrandMark size="sm" />)
    expect((container.firstChild as HTMLElement).className).toContain('h-7')

    rerender(<BrandMark size="lg" />)
    expect((container.firstChild as HTMLElement).className).toContain('h-11')
  })
})
