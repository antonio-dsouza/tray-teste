<template>
  <div class="base-table-container">
    <div class="table-header" v-if="title || hasHeaderActions">
      <div class="table-title">
        <n-icon v-if="icon" :component="icon" :color="iconColor" />
        <n-text v-if="title" :type="titleType">{{ title }}</n-text>
        <n-text v-if="subtitle" depth="3" style="margin-left: 8px">
          {{ subtitle }}
        </n-text>
      </div>
      <div class="table-actions" v-if="hasHeaderActions">
        <slot name="header-actions" />
      </div>
    </div>

    <div class="table-filters" v-if="hasFilters">
      <slot name="filters" />
    </div>

    <div class="table-stats" v-if="showStats && stats">
      <n-space>
        <n-statistic
          v-for="stat in stats"
          :key="stat.key"
          :label="stat.label"
          :value="stat.value"
          :precision="stat.precision"
          size="small"
        >
          <template #prefix v-if="stat.prefix">{{ stat.prefix }}</template>
          <template #suffix v-if="stat.suffix">{{ stat.suffix }}</template>
        </n-statistic>
      </n-space>
    </div>

    <n-data-table
      ref="tableRef"
      :columns="columns"
      :data="data"
      :loading="loading"
      :pagination="paginationConfig"
      :bordered="bordered"
      :size="size"
      :striped="striped"
      :scroll-x="scrollX"
      :row-key="rowKeyFunc"
      :row-class-name="getRowClassName"
      :summary="summary"
      :max-height="maxHeight"
      @update:page="handlePageChange"
      @update:page-size="handlePageSizeChange"
      @update:sorter="handleSorterChange"
      @update:filters="handleFiltersChange"
    >
      <template v-for="(_, name) in $slots" v-slot:[name]="slotData" :key="name">
        <slot :name="name" v-bind="slotData" />
      </template>

      <template #empty>
        <div class="table-empty">
          <n-empty :description="emptyText">
            <template #icon>
              <n-icon :component="emptyIcon" size="48" />
            </template>
            <template #extra v-if="hasEmptyAction">
              <slot name="empty-action" />
            </template>
          </n-empty>
        </div>
      </template>
    </n-data-table>

    <div class="table-footer" v-if="hasFooter">
      <slot name="footer" />
    </div>

    <n-spin :show="actionLoading" description="Processando...">
      <div style="height: 100px; opacity: 0"></div>
    </n-spin>
  </div>
</template>

<script setup lang="ts">
import { computed, h, ref, useSlots, type Component } from 'vue'
import {
  NDataTable,
  NSpace,
  NIcon,
  NText,
  NStatistic,
  NEmpty,
  NSpin,
  type DataTableColumns,
  type DataTableRowKey,
} from 'naive-ui'

const InboxIcon = () =>
  h('svg', { viewBox: '0 0 24 24' }, [
    h('path', {
      fill: 'currentColor',
      d: 'M19,3H5A2,2 0 0,0 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5A2,2 0 0,0 19,3M19,15H15A3,3 0 0,1 12,18A3,3 0 0,1 9,15H5V5H19V15Z',
    }),
  ])

interface TableStat {
  key: string
  label: string
  value: number
  precision?: number
  prefix?: string
  suffix?: string
}

interface Props {
  columns: DataTableColumns
  data: Array<Record<string, unknown>>
  rowKey?: string | ((row: Record<string, unknown>) => string | number)

  title?: string
  subtitle?: string
  icon?: Component
  iconColor?: string
  titleType?: 'default' | 'primary' | 'info' | 'success' | 'warning' | 'error'
  size?: 'small' | 'medium' | 'large'
  bordered?: boolean
  striped?: boolean
  maxHeight?: number | string
  scrollX?: number

  loading?: boolean
  actionLoading?: boolean
  emptyText?: string
  emptyIcon?: Component

  pagination?: boolean | object
  pageSize?: number

  showStats?: boolean
  stats?: TableStat[]

  summary?: (pageData: Record<string, unknown>[]) => SummaryRowData | SummaryRowData[]
  rowClassName?: string | ((row: Record<string, unknown>, index: number) => string)
}

interface SummaryRowData {
  [key: string]: {
    value?: string | number | null
    colSpan?: number
    rowSpan?: number
  }
}

interface Emits {
  (e: 'page-change', page: number): void
  (e: 'page-size-change', pageSize: number): void
  (e: 'sorter-change', sorter: Record<string, unknown>): void
  (e: 'filters-change', filters: Record<string, unknown>): void
}

const props = withDefaults(defineProps<Props>(), {
  titleType: 'default',
  size: 'medium',
  bordered: true,
  striped: false,
  loading: false,
  actionLoading: false,
  emptyText: 'Nenhum item encontrado',
  emptyIcon: InboxIcon,
  pagination: true,
  pageSize: 20,
  showStats: false,
})

const emit = defineEmits<Emits>()
const slots = useSlots()

const tableRef = ref()

const hasHeaderActions = computed(() => !!slots['header-actions'])
const hasFilters = computed(() => !!slots.filters)
const hasFooter = computed(() => !!slots.footer)
const hasEmptyAction = computed(() => !!slots['empty-action'])

const rowKeyFunc = computed(() => {
  if (!props.rowKey) return undefined

  if (typeof props.rowKey === 'string') {
    return (row: Record<string, unknown>) => row[props.rowKey as string] as string | number
  }

  return props.rowKey
})

const paginationConfig = computed(() => {
  if (!props.pagination) return false

  if (typeof props.pagination === 'object') {
    return props.pagination
  }

  return {
    pageSize: props.pageSize,
    showSizePicker: true,
    pageSizes: [10, 20, 50, 100],
    showQuickJumper: true,
    prefix: ({ itemCount }: { itemCount: number }) => `Total: ${itemCount} itens`,
  }
})

const getRowClassName = (row: Record<string, unknown>, index: number): string => {
  if (typeof props.rowClassName === 'function') {
    return props.rowClassName(row, index)
  }
  return props.rowClassName || ''
}

const handlePageChange = (page: number) => {
  emit('page-change', page)
}

const handlePageSizeChange = (pageSize: number) => {
  emit('page-size-change', pageSize)
}

const handleSorterChange = (sorter: Record<string, unknown>) => {
  emit('sorter-change', sorter)
}

const handleFiltersChange = (filters: Record<string, unknown>) => {
  emit('filters-change', filters)
}

const scrollTo = (options: {
  index?: number
  key?: DataTableRowKey
  top?: number
  left?: number
}) => {
  tableRef.value?.scrollTo(options)
}

defineExpose({
  scrollTo,
  tableRef,
})
</script>

<style scoped>
.base-table-container {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.table-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 16px;
}

.table-title {
  display: flex;
  align-items: center;
  gap: 8px;
}

.table-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

.table-filters {
  padding: 16px;
  background-color: var(--card-color);
  border-radius: 6px;
  border: 1px solid var(--border-color);
}

.table-stats {
  padding: 12px 16px;
  background-color: var(--info-color-suppl);
  border-radius: 6px;
  border: 1px solid var(--info-color);
}

.table-empty {
  padding: 40px 20px;
}

.table-footer {
  padding: 16px;
  background-color: var(--card-color);
  border-radius: 6px;
  border: 1px solid var(--border-color);
}
</style>
