<template>
  <n-card
    :title="title"
    :bordered="bordered"
    :size="size"
    :hoverable="hoverable"
    :class="cardClasses"
  >
    <template #header v-if="hasHeaderSlot">
      <n-space align="center" justify="space-between">
        <div>
          <n-icon v-if="icon" :component="icon" :color="iconColor" />
          <n-text :type="titleType">{{ title }}</n-text>
          <n-text v-if="subtitle" depth="3" style="margin-left: 8px">
            {{ subtitle }}
          </n-text>
        </div>
        <div v-if="hasHeaderActions">
          <slot name="header-actions" />
        </div>
      </n-space>
    </template>

    <n-spin :show="loading" :description="loadingText">
      <div v-if="error && !loading" class="error-state">
        <n-result status="error" :title="errorTitle" :description="error">
          <template #footer>
            <n-button @click="handleRetry" v-if="showRetry">
              <template #icon>
                <n-icon :component="RefreshIcon" />
              </template>
              Tentar Novamente
            </n-button>
          </template>
        </n-result>
      </div>

      <div v-else-if="isEmpty && !loading" class="empty-state">
        <n-empty :description="emptyText">
          <template #icon>
            <n-icon :component="emptyIcon" size="48" />
          </template>
          <template #extra v-if="hasEmptyAction">
            <slot name="empty-action" />
          </template>
        </n-empty>
      </div>

      <div v-else-if="!loading">
        <slot />
      </div>
    </n-spin>

    <template #footer v-if="hasFooterSlot">
      <slot name="footer" />
    </template>

    <template #action v-if="hasActionSlot">
      <slot name="actions" />
    </template>
  </n-card>
</template>

<script setup lang="ts">
import { computed, useSlots, type Component, h } from 'vue'
import { NCard, NSpace, NIcon, NText, NSpin, NResult, NButton, NEmpty } from 'naive-ui'

const RefreshIcon = () =>
  h('svg', { viewBox: '0 0 24 24' }, [
    h('path', {
      fill: 'currentColor',
      d: 'M17.65,6.35C16.2,4.9 14.21,4 12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20C15.73,20 18.84,17.45 19.73,14H17.65C16.83,16.33 14.61,18 12,18A6,6 0 0,1 6,12A6,6 0 0,1 12,6C13.66,6 15.14,6.69 16.22,7.78L13,11H20V4L17.65,6.35Z',
    }),
  ])

const InboxIcon = () =>
  h('svg', { viewBox: '0 0 24 24' }, [
    h('path', {
      fill: 'currentColor',
      d: 'M19,3H5A2,2 0 0,0 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5A2,2 0 0,0 19,3M19,15H15A3,3 0 0,1 12,18A3,3 0 0,1 9,15H5V5H19V15Z',
    }),
  ])

interface Props {
  title?: string
  subtitle?: string
  icon?: Component
  iconColor?: string
  titleType?: 'default' | 'primary' | 'info' | 'success' | 'warning' | 'error'
  size?: 'small' | 'medium' | 'large'
  bordered?: boolean
  hoverable?: boolean
  loading?: boolean
  loadingText?: string
  error?: string | null
  errorTitle?: string
  showRetry?: boolean
  isEmpty?: boolean
  emptyText?: string
  emptyIcon?: Component
  variant?: 'default' | 'success' | 'warning' | 'error' | 'info'
}

interface Emits {
  (e: 'retry'): void
}

const props = withDefaults(defineProps<Props>(), {
  titleType: 'default',
  size: 'medium',
  bordered: true,
  hoverable: false,
  loading: false,
  loadingText: 'Carregando...',
  errorTitle: 'Ops! Algo deu errado',
  showRetry: true,
  isEmpty: false,
  emptyText: 'Nenhum item encontrado',
  emptyIcon: InboxIcon,
  variant: 'default',
})

const emit = defineEmits<Emits>()

const slots = useSlots()

const hasHeaderSlot = computed(() => !!slots['header-actions'] || !!props.icon)
const hasHeaderActions = computed(() => !!slots['header-actions'])
const hasFooterSlot = computed(() => !!slots.footer)
const hasActionSlot = computed(() => !!slots.actions)
const hasEmptyAction = computed(() => !!slots['empty-action'])

const cardClasses = computed(() => {
  const classes = ['base-card']

  switch (props.variant) {
    case 'success':
      classes.push('card-success')
      break
    case 'warning':
      classes.push('card-warning')
      break
    case 'error':
      classes.push('card-error')
      break
    case 'info':
      classes.push('card-info')
      break
  }

  return classes
})

const handleRetry = () => {
  emit('retry')
}
</script>

<style scoped>
.base-card {
  transition: all 0.3s ease;
}

.card-success {
  border-left: 4px solid #52c41a;
}

.card-warning {
  border-left: 4px solid #faad14;
}

.card-error {
  border-left: 4px solid #ff4d4f;
}

.card-info {
  border-left: 4px solid #1890ff;
}

.error-state,
.empty-state {
  padding: 20px;
  text-align: center;
}

.n-icon {
  margin-right: 8px;
}
</style>
