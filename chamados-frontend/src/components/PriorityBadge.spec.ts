import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import PriorityBadge from './PriorityBadge.vue'

describe('PriorityBadge', () => {
  it('mostra o rotulo em portugues e destaca prioridade alta', () => {
    const alta = mount(PriorityBadge, { props: { prioridade: 'alta' } })
    expect(alta.text()).toBe('Alta')
    expect(alta.classes().join(' ')).toContain('red')

    expect(mount(PriorityBadge, { props: { prioridade: 'media' } }).text()).toBe('Média')
    expect(mount(PriorityBadge, { props: { prioridade: 'baixa' } }).text()).toBe('Baixa')
  })
})
