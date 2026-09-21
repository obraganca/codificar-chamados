<script setup lang="ts">
import { reactive, watch } from 'vue'
import FieldError from '@/components/FieldError.vue'
import { PRIORIDADES, PRIORIDADE_LABELS, STATUSES, STATUS_LABELS } from '@/types/models'
import type { Categoria, Responsavel, Tag, Ticket, TicketInput, ValidationError } from '@/types/models'

const props = defineProps<{
  chamado?: Ticket | null
  responsaveis: Responsavel[]
  categorias: Categoria[]
  tags: Tag[]
  enviando: boolean
  errors?: ValidationError['errors']
}>()

const emit = defineEmits<{
  submit: [payload: TicketInput]
}>()

const form = reactive<TicketInput>({
  titulo: '',
  descricao: '',
  prioridade: 'media',
  status: 'aberto',
  responsavel_id: null,
  categoria_id: null,
  tag_ids: [],
  atribuicao_automatica: false,
})

// Em telas de edicao o chamado chega de uma chamada assincrona, depois do primeiro render.
watch(
  () => props.chamado,
  (chamado) => {
    if (!chamado) return

    form.titulo = chamado.titulo
    form.descricao = chamado.descricao
    form.prioridade = chamado.prioridade
    form.status = chamado.status
    form.responsavel_id = chamado.responsavel_id ?? null
    form.categoria_id = chamado.categoria_id ?? null
    form.tag_ids = chamado.tags?.map((t) => t.id) ?? []
    form.atribuicao_automatica = false
  },
  { immediate: true }
)

function aoSubmeter(): void {
  const payload: TicketInput = { ...form, tag_ids: [...(form.tag_ids ?? [])] }

  if (payload.atribuicao_automatica) {
    delete (payload as Partial<TicketInput>).responsavel_id
  }

  emit('submit', payload)
}
</script>

<template>
  <form class="space-y-5" @submit.prevent="aoSubmeter">
    <div>
      <label for="titulo" class="field-label">Título</label>
      <input id="titulo" v-model="form.titulo" type="text" class="field-input" placeholder="Ex.: Computador não liga" />
      <FieldError :mensagens="errors?.titulo" />
    </div>

    <div>
      <label for="descricao" class="field-label">Descrição</label>
      <textarea
        id="descricao"
        v-model="form.descricao"
        rows="4"
        class="field-input"
        placeholder="Descreva o problema com detalhes"
      />
      <FieldError :mensagens="errors?.descricao" />
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
      <div>
        <label for="prioridade" class="field-label">Prioridade</label>
        <select id="prioridade" v-model="form.prioridade" class="field-input">
          <option v-for="p in PRIORIDADES" :key="p" :value="p">{{ PRIORIDADE_LABELS[p] }}</option>
        </select>
        <FieldError :mensagens="errors?.prioridade" />
      </div>

      <div>
        <label for="status" class="field-label">Status</label>
        <select id="status" v-model="form.status" class="field-input">
          <option v-for="s in STATUSES" :key="s" :value="s">{{ STATUS_LABELS[s] }}</option>
        </select>
        <FieldError :mensagens="errors?.status" />
      </div>
    </div>

    <div>
      <label for="categoria" class="field-label">Categoria</label>
      <select id="categoria" v-model="form.categoria_id" class="field-input">
        <option :value="null">Sem categoria</option>
        <option v-for="c in categorias" :key="c.id" :value="c.id">{{ c.nome }}</option>
      </select>
      <FieldError :mensagens="errors?.categoria_id" />
    </div>

    <div v-if="tags.length">
      <span class="field-label">Tags</span>
      <div class="mt-2 flex flex-wrap gap-2">
        <label
          v-for="t in tags"
          :key="t.id"
          class="inline-flex cursor-pointer items-center rounded-full border border-gray-600 px-3 py-1 text-xs text-gray-300 transition hover:bg-gray-700 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-600/20 has-[:checked]:text-indigo-200"
        >
          <input v-model="form.tag_ids" type="checkbox" :value="t.id" class="sr-only" />
          #{{ t.nome }}
        </label>
      </div>
      <FieldError :mensagens="errors?.tag_ids" />
    </div>

    <fieldset class="rounded-md border border-gray-700 p-4">
      <legend class="px-1 text-sm font-medium text-gray-300">Responsável</legend>

      <label class="flex items-center gap-2 text-sm text-gray-400">
        <input
          v-model="form.atribuicao_automatica"
          type="checkbox"
          class="h-4 w-4 rounded border-gray-600 bg-gray-900 accent-indigo-500"
        />
        Atribuir automaticamente (quem tem menos chamados em aberto)
      </label>

      <div v-if="!form.atribuicao_automatica" class="mt-3">
        <select v-model="form.responsavel_id" class="field-input mt-0">
          <option :value="null">Sem responsável</option>
          <option v-for="r in responsaveis" :key="r.id" :value="r.id">
            {{ r.nome }} ({{ r.chamados_abertos_count ?? 0 }} em aberto)
          </option>
        </select>
        <FieldError :mensagens="errors?.responsavel_id" />
      </div>
    </fieldset>

    <div class="flex justify-end gap-3">
      <RouterLink :to="{ name: 'chamados.index' }" class="btn-secondary">Cancelar</RouterLink>
      <button type="submit" :disabled="enviando" class="btn-primary">
        {{ enviando ? 'Salvando...' : 'Salvar' }}
      </button>
    </div>
  </form>
</template>