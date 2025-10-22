<template>
  <div class="seller-list">
    <n-card title="Vendedores">
      <template #header-extra>
        <n-space>
          <n-button
            v-if="authStore.hasPermission('manage_sellers')"
            type="success"
            @click="handleSendDailyEmails"
            :loading="sendingEmails"
          >
            <template #icon>
              <n-icon>
                <svg viewBox="0 0 24 24">
                  <path
                    fill="currentColor"
                    d="M20,8L12,13L4,8V6L12,11L20,6M20,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6C22,4.89 21.1,4 20,4Z"
                  />
                </svg>
              </n-icon>
            </template>
            Enviar Emails Diários
          </n-button>

          <n-button
            v-if="authStore.hasPermission('create_sellers')"
            type="primary"
            @click="router.push('/sellers/create')"
          >
            <template #icon>
              <n-icon>
                <svg viewBox="0 0 24 24">
                  <path
                    fill="currentColor"
                    d="M15,14C12.33,14 7,15.33 7,18V20H23V18C23,15.33 17.67,14 15,14M6,10V7H4V10H1V12H4V15H6V12H9V10M15,12A4,4 0 0,0 19,8A4,4 0 0,0 15,4A4,4 0 0,0 11,8A4,4 0 0,0 15,12Z"
                  />
                </svg>
              </n-icon>
            </template>
            Cadastrar Vendedor
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

      <n-data-table
        :columns="columns"
        :data="sellersStore.sellers"
        :loading="sellersStore.isLoading"
        :pagination="false"
        size="large"
      />

      <PaginationComponent
        :pagination="paginationData"
        :loading="sellersStore.isLoading"
        @change="handlePaginationChange"
      />
    </n-card>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onActivated, h, computed } from 'vue'
import { useRouter } from 'vue-router'
import { NButton, useMessage } from 'naive-ui'
import { useAuthStore } from '@/stores/auth'
import { useSellersStore } from '@/stores/sellers'
import { useSalesStore } from '@/stores/sales'
import { serviceFactory } from '@/services/ServiceFactory'
import PaginationComponent from '@/components/PaginationComponent.vue'
import type { Seller, Sale } from '@/types'

const router = useRouter()
const message = useMessage()
const authStore = useAuthStore()
const sellersStore = useSellersStore()
const salesStore = useSalesStore()
const emailService = serviceFactory.getEmailService()

const isRefreshing = ref(false)
const sendingEmails = ref(false)

const paginationData = computed(() => ({
  current_page: sellersStore.pagination.page,
  per_page: sellersStore.pagination.pageSize,
  total: sellersStore.pagination.total,
  last_page: sellersStore.pagination.pageCount,
  from:
    sellersStore.pagination.total > 0
      ? (sellersStore.pagination.page - 1) * sellersStore.pagination.pageSize + 1
      : 0,
  to: Math.min(
    sellersStore.pagination.page * sellersStore.pagination.pageSize,
    sellersStore.pagination.total,
  ),
}))

const columns = [
  {
    title: 'ID',
    key: 'id',
    width: 80,
  },
  {
    title: 'Nome',
    key: 'name',
  },
  {
    title: 'Email',
    key: 'email',
  },
  {
    title: 'Vendas',
    key: 'sales_count',
    render: (row: Seller) => {
      const salesCount = row.sales?.length || 0
      return `${salesCount} vendas`
    },
  },
  {
    title: 'Total Vendido',
    key: 'total_amount',
    render: (row: Seller) => {
      const totalAmount =
        row.sales?.reduce((sum: number, sale: Sale) => sum + Number(sale.amount), 0) || 0
      return formatCurrency(totalAmount)
    },
  },
  {
    title: 'Comissão',
    key: 'commission',
    render: (row: Seller) => {
      const totalCommission =
        row.sales?.reduce((sum: number, sale: Sale) => sum + Number(sale.commission_amount), 0) || 0
      return formatCurrency(totalCommission)
    },
  },
  {
    title: 'Data de Cadastro',
    key: 'created_at',
    render: (row: Seller) => formatDate(row.created_at),
  },
  {
    title: 'Ações',
    key: 'actions',
    width: 200,
    render: (row: Seller) => {
      return h('div', { style: 'display: flex; gap: 8px; justify-content: center;' }, [
        ...(authStore.hasPermission('resend_commissions')
          ? [
              h(
                NButton,
                {
                  size: 'small',
                  type: 'warning',
                  onClick: () => handleResendCommission(row.id),
                },
                { default: () => 'Reenviar Email de Comissões' },
              ),
            ]
          : []),
      ])
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

const handlePaginationChange = (page: number, pageSize: number) => {
  loadSellersData(page, pageSize)
}

const loadSellersData = async (page = 1, pageSize = 15) => {
  const params = {
    page,
    per_page: pageSize,
  }

  await sellersStore.loadSellers(params)
  await salesStore.loadSales()
}

const handleRefresh = async () => {
  if (isRefreshing.value) return

  isRefreshing.value = true

  try {
    await loadSellersData()
    message.success('Dados atualizados com sucesso')
  } catch (err) {
    message.error('Erro ao atualizar dados')
    console.error(err)
  } finally {
    isRefreshing.value = false
  }
}

const handleResendCommission = async (sellerId: number) => {
  try {
    message.loading('Enviando email de comissão...', { duration: 0 })

    await emailService.resendCommissionEmail(sellerId)

    message.destroyAll()
    message.success(`Email de comissão reenviado com sucesso para o vendedor ${sellerId}`)
  } catch (error) {
    message.destroyAll()
    message.error('Erro ao reenviar email de comissão')
    console.error('Error resending commission email:', error)
  }
}

const handleSendDailyEmails = async () => {
  if (sendingEmails.value) return

  try {
    sendingEmails.value = true
    message.loading('Enviando emails diários...', { duration: 0 })

    await emailService.runDailyMails()

    message.destroyAll()
    message.success('Emails diários enviados com sucesso!')
  } catch (error) {
    message.destroyAll()
    message.error('Erro ao enviar emails diários')
    console.error('Error sending daily emails:', error)
  } finally {
    sendingEmails.value = false
  }
}

onMounted(async () => {
  try {
    await loadSellersData()
    message.success('Dados carregados com sucesso')
  } catch (error) {
    message.error('Erro ao carregar dados')
    console.error(error)
  }
})

onActivated(async () => {
  try {
    await loadSellersData()
  } catch (error) {
    console.error('Erro ao recarregar dados:', error)
  }
})
</script>

<style scoped>
.seller-list {
  padding: 24px;
}
</style>
