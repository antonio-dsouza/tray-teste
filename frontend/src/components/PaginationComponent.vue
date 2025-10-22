<template>
  <div class="pagination-wrapper">
    <n-pagination
      v-model:page="currentPage"
      v-model:page-size="currentPageSize"
      :item-count="totalItems"
      :page-count="totalPages"
      :page-sizes="pageSizeOptions"
      :show-size-picker="showSizePicker"
      :show-quick-jumper="showQuickJumper"
      :disabled="loading"
      @update:page="handlePageChange"
      @update:page-size="handlePageSizeChange"
    >
      <template #prefix>
        <span class="pagination-info">
          Mostrando {{ fromItem }} - {{ toItem }} de {{ totalItems }} registros
        </span>
      </template>
      <template #goto> Ir para </template>
      <template #size-picker> {{ currentPageSize }} / página </template>
    </n-pagination>
  </div>
</template>

<script setup lang="ts">
import { computed, watch } from 'vue'

export interface PaginationData {
  current_page: number
  per_page: number
  total: number
  last_page: number
  from: number
  to: number
}

interface Props {
  pagination: PaginationData
  loading?: boolean
  showSizePicker?: boolean
  showQuickJumper?: boolean
  pageSizeOptions?: number[]
}

interface Emits {
  (e: 'update:pagination', value: Partial<PaginationData>): void
  (e: 'change', page: number, pageSize: number): void
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  showSizePicker: true,
  showQuickJumper: true,
  pageSizeOptions: () => [10, 15, 30, 50, 100],
})

const emit = defineEmits<Emits>()

const currentPage = computed({
  get: () => props.pagination.current_page,
  set: (value: number) => {
    emit('update:pagination', { current_page: value })
  },
})

const currentPageSize = computed({
  get: () => props.pagination.per_page,
  set: (value: number) => {
    emit('update:pagination', { per_page: value })
  },
})

const totalItems = computed(() => props.pagination.total)
const totalPages = computed(() => props.pagination.last_page)
const fromItem = computed(() => props.pagination.from || 0)
const toItem = computed(() => props.pagination.to || 0)

const handlePageChange = (page: number) => {
  currentPage.value = page
  emit('change', page, currentPageSize.value)
}

const handlePageSizeChange = (pageSize: number) => {
  currentPageSize.value = pageSize
  currentPage.value = 1
  emit('change', 1, pageSize)
}

watch(
  () => props.pagination,
  (newPagination) => {
    if (newPagination.current_page !== currentPage.value) {
      currentPage.value = newPagination.current_page
    }
    if (newPagination.per_page !== currentPageSize.value) {
      currentPageSize.value = newPagination.per_page
    }
  },
  { deep: true },
)
</script>

<style scoped>
.pagination-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  margin: 24px 0;
  gap: 16px;
}

.pagination-info {
  font-size: 14px;
  color: #666;
  margin-right: 16px;
}

@media (max-width: 768px) {
  .pagination-wrapper {
    flex-direction: column;
    gap: 12px;
  }

  .pagination-info {
    margin-right: 0;
    margin-bottom: 8px;
  }
}
</style>
