import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import StatusBadge from './StatusBadge.vue'

describe('StatusBadge', () => {
  it('mostra o rotulo em portugues para cada status', () => {
    expect(mount(StatusBadge, { props: { status: 'aberto' } }).text()).toBe('Aberto')
    expect(mount(StatusBadge, { props: { status: 'em_andamento' } }).text()).toBe('Em andamento')
    expect(mount(StatusBadge, { props: { status: 'resolvido' } }).text()).toBe('Resolvido')
    expect(mount(StatusBadge, { props: { status: 'fechado' } }).text()).toBe('Fechado')
  })
})
