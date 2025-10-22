<template>
  <div class="sale-list">
    <n-card :title="title">
      <template #header-extra>
        <n-space>
          <n-button
            v-if="authStore.hasPermission('create_sales')"
            type="primary"
            @click="router.push('/sales/create')"
          >
            <template #icon>
              <n-icon>
                <svg viewBox="0 0 24 24">
                  <path fill="currentColor" d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z" />
                </svg>
              </n-icon>
            </template>
            Registrar Venda
          </n-button>

          <n-button @click="handleRefresh">
            <template #icon>
              <n-icon>
                <svg viewBox="0 0 24 24">
                  <path
                    fill="currentColor"
                    d="M17.65,6.35C16.2,4.9 14.21,4 12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20C15.73,20 18.84,17.45 19.73,14H17.65C16.83,16.33 14.61,18 12,18A6,6 0 0,1 6,12A6,6 0 0,1 12,6C13.66,6 15.14,6.69 16.22,7.78L13,11H20V4L17.65,6.35Z"
                  />
                </svg>
              </n-icon>
            </template>
            Atualizar
          </n-button>
        </n-space>
      </template>

      <n-card embedded class="mb-4">
        <n-statistic-group>
          <n-statistic label="Total de Vendas" :value="salesStore.sales.length" />
          <n-statistic label="Valor Total" :value="formatCurrency(totalAmount)" />
          <n-statistic
            :label="`Comissão Total (${(COMMISSION_RATES.DEFAULT * 100).toFixed(2).replace('.', ',')}%)`"
            :value="formatCurrency(totalCommission)"
          />
        </n-statistic-group>
      </n-card>

      <n-data-table
        :columns="columns"
        :data="salesStore.sales"
        :loading="salesStore.isLoading"
        size="small"
        :scroll-x="700"
        striped
        :pagination="false"
      />

      <PaginationComponent
        :pagination="paginationData"
        :loading="salesStore.isLoading"
        @change="handlePaginationChange"
      />
    </n-card>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onActivated, h } from 'vue'
import { useRouter } from 'vue-router'
import { NButton, NIcon, useMessage } from 'naive-ui'
import { useAuthStore } from '@/stores/auth'
import { useSellersStore } from '@/stores/sellers'
import { useSalesStore } from '@/stores/sales'
import { COMMISSION_RATES } from '@/constants/commission'
import PaginationComponent from '@/components/PaginationComponent.vue'
import { ServiceFactory } from '@/services/ServiceFactory'
import type { Sale } from '@/types'

const router = useRouter()
const message = useMessage()
const authStore = useAuthStore()
const sellersStore = useSellersStore()
const salesStore = useSalesStore()
const title = ref('Vendas')

const paginationData = computed(() => ({
  current_page: salesStore.pagination.page,
  per_page: salesStore.pagination.pageSize,
  total: salesStore.pagination.total,
  last_page: salesStore.pagination.pageCount,
  from:
    salesStore.pagination.total > 0
      ? (salesStore.pagination.page - 1) * salesStore.pagination.pageSize + 1
      : 0,
  to: Math.min(
    salesStore.pagination.page * salesStore.pagination.pageSize,
    salesStore.pagination.total,
  ),
}))

const filters = ref({
  sellerId: null as number | null,
  startDate: null as number | null,
  endDate: null as number | null,
  minAmount: null as number | null,
})

const resendingCommissions = ref(new Set<number>())

const totalAmount = computed(() => {
  return salesStore.totalAmount
})

const totalCommission = computed(() => {
  return salesStore.totalCommission
})

const columns = [
  {
    title: 'ID',
    key: 'id',
    width: 70,
  },
  {
    title: 'Vendedor',
    key: 'seller_id',
    width: 160,
    ellipsis: {
      tooltip: true,
    },
    render: (row: Sale) => {
      const seller = sellersStore.getSellerById(row.seller_id)
      return seller?.name || `Vendedor ${row.seller_id}`
    },
  },
  {
    title: 'Valor',
    key: 'amount',
    width: 110,
    render: (row: Sale) => formatCurrency(row.amount),
  },
  {
    title: 'Comissão',
    key: 'commission_amount',
    width: 110,
    render: (row: Sale) => formatCurrency(row.commission_amount),
  },
  {
    title: 'Data da Venda',
    key: 'sold_at',
    width: 120,
    render: (row: Sale) => formatDate(row.sold_at),
  },
  {
    title: 'Criada em',
    key: 'created_at',
    width: 130,
    render: (row: Sale) => formatDateTime(row.created_at),
  },
  {
    title: 'Ações',
    key: 'actions',
    width: 120,
    render: (row: Sale) => {
      return h(
        NButton,
        {
          size: 'small',
          type: 'info',
          ghost: true,
          loading: resendingCommissions.value.has(row.id),
          disabled: !authStore.hasPermission('resend_commissions'),
          onClick: () => handleResendCommission(row.id),
        },
        {
          default: () => 'Reenviar Email',
          icon: () =>
            h(NIcon, null, {
              default: () =>
                h('svg', { viewBox: '0 0 24 24' }, [
                  h('path', {
                    fill: 'currentColor',
                    d: 'M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4M13,7L12,8L7,13L12,18L13,17L9,13L13,7Z',
                  }),
                ]),
            }),
        },
      )
    },
  },
]

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(value)
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('pt-BR')
}

const formatDateTime = (dateString: string) => {
  return new Date(dateString).toLocaleString('pt-BR')
}

const isRefreshing = ref(false)

const handleRefresh = async () => {
  try {
    isRefreshing.value = true
    await loadSalesData()
    message.success('Dados atualizados com sucesso')
  } catch (err) {
    message.error('Erro ao atualizar dados')
    console.error(err)
  } finally {
    isRefreshing.value = false
  }
}

const handleResendCommission = async (saleId: number) => {
  if (resendingCommissions.value.has(saleId)) return

  try {
    resendingCommissions.value.add(saleId)

    const saleApiService = ServiceFactory.getInstance().getSaleService()
    const result = await saleApiService.resendSaleCommission(saleId)

    message.success(result.message)
  } catch (error) {
    message.error(error instanceof Error ? error.message : 'Erro ao reenviar email de comissão')
  } finally {
    resendingCommissions.value.delete(saleId)
  }
}

const handlePaginationChange = (page: number, pageSize: number) => {
  loadSalesData(page, pageSize)
}

const loadSalesData = async (page = 1, pageSize = 15) => {
  const params = {
    page,
    per_page: pageSize,
    ...filters.value,
  }

  await salesStore.loadSales(params)
}

onMounted(async () => {
  await sellersStore.loadSellers()
  await loadSalesData()
})

onActivated(async () => {
  await loadSalesData()
})
</script>

<style scoped>
.sale-list {
  padding: 24px;
}

.mb-4 {
  margin-bottom: 16px;
}

@media (max-width: 768px) {
  .sale-list {
    padding: 12px;
  }
}
</style>
