<template>
  <div class="dashboard">
    <n-grid cols="1 s:2 m:3 l:4" responsive="screen" :x-gap="16" :y-gap="16">
      <n-grid-item v-if="authStore.hasPermission('view_sellers')">
        <n-card class="stat-card">
          <template #header>
            <div class="stat-header">
              <n-icon size="32" color="#18a058">
                <svg viewBox="0 0 24 24">
                  <path
                    fill="currentColor"
                    d="M16,4C18.11,4 19.8,5.69 19.8,7.8C19.8,9.91 18.11,11.6 16,11.6C13.89,11.6 12.2,9.91 12.2,7.8C12.2,5.69 13.89,4 16,4M16,13.4C18.67,13.4 24,14.73 24,17.4V20H8V17.4C8,14.73 13.33,13.4 16,13.4M8.8,7.8C8.8,9.91 7.11,11.6 5,11.6C2.89,11.6 1.2,9.91 1.2,7.8C1.2,5.69 2.89,4 5,4C7.11,4 8.8,5.69 8.8,7.8M5,13.4C7.67,13.4 13,14.73 13,17.4V20H0V17.4C0,14.73 5.33,13.4 5,13.4Z"
                  />
                </svg>
              </n-icon>
              <span>Vendedores</span>
            </div>
          </template>
          <div class="stat-content">
            <div class="stat-number">{{ dashboardStats?.general.total_sellers || 0 }}</div>
            <div class="stat-label">Total de Vendedores</div>
          </div>
          <template #action>
            <n-button size="small" type="primary" ghost @click="router.push('/sellers')">
              Ver Todos
            </n-button>
          </template>
        </n-card>
      </n-grid-item>

      <n-grid-item v-if="authStore.hasPermission('view_sales')">
        <n-card class="stat-card">
          <template #header>
            <div class="stat-header">
              <n-icon size="32" color="#2080f0">
                <svg viewBox="0 0 24 24">
                  <path
                    fill="currentColor"
                    d="M7,15H9C9,16.08 10.37,17 12,17C13.63,17 15,16.08 15,15C15,13.9 13.96,13.5 11.76,12.97C9.64,12.44 7,11.78 7,9C7,7.21 8.47,5.69 10.5,5.18V3H13.5V5.18C15.53,5.69 17,7.21 17,9H15C15,7.92 13.63,7 12,7C10.37,7 9,7.92 9,9C9,10.1 10.04,10.5 12.24,11.03C14.36,11.56 17,12.22 17,15C17,16.79 15.53,18.31 13.5,18.82V21H10.5V18.82C8.47,18.31 7,16.79 7,15Z"
                  />
                </svg>
              </n-icon>
              <span>Vendas</span>
            </div>
          </template>
          <div class="stat-content">
            <div class="stat-number">{{ dashboardStats?.general.total_sales || 0 }}</div>
            <div class="stat-label">Total de Vendas</div>
          </div>
          <template #action>
            <n-button size="small" type="primary" ghost @click="router.push('/sales')">
              Ver Todas
            </n-button>
          </template>
        </n-card>
      </n-grid-item>

      <n-grid-item v-if="authStore.hasPermission('view_sales')">
        <n-card class="stat-card">
          <template #header>
            <div class="stat-header">
              <n-icon size="32" color="#f0a020">
                <svg viewBox="0 0 24 24">
                  <path
                    fill="currentColor"
                    d="M11.8,10.9C9.53,10.31 8.8,9.7 8.8,8.75C8.8,7.66 9.81,6.9 11.5,6.9C13.28,6.9 13.94,7.75 14,9H16.21C16.14,7.28 15.09,5.7 13,5.19V3H10V5.16C8.06,5.58 6.5,6.84 6.5,8.77C6.5,11.08 8.41,12.23 11.2,12.9C13.7,13.5 14.2,14.38 14.2,15.31C14.2,16 13.71,17.1 11.5,17.1C9.44,17.1 8.63,16.18 8.5,15H6.32C6.44,17.19 8.08,18.42 10,18.83V21H13V18.85C14.95,18.5 16.5,17.35 16.5,15.3C16.5,12.46 14.07,11.5 11.8,10.9Z"
                  />
                </svg>
              </n-icon>
              <span>Faturamento</span>
            </div>
          </template>
          <div class="stat-content">
            <div class="stat-number">
              {{ dashboardStats?.general.formatted_total_sales_amount || 'R$ 0,00' }}
            </div>
            <div class="stat-label">Total Vendido</div>
          </div>
        </n-card>
      </n-grid-item>

      <n-grid-item v-if="authStore.hasPermission('view_sales')">
        <n-card class="stat-card">
          <template #header>
            <div class="stat-header">
              <n-icon size="32" color="#d03050">
                <svg viewBox="0 0 24 24">
                  <path
                    fill="currentColor"
                    d="M5,6H23V18H5V6M14,9A3,3 0 0,1 17,12A3,3 0 0,1 14,12A3,3 0 0,1 14,9M9,8A2,2 0 0,1 11,10V14A2,2 0 0,1 9,16H8A2,2 0 0,1 6,14V10A2,2 0 0,1 8,8H9M1,10H3V14H1V10Z"
                  />
                </svg>
              </n-icon>
              <span>Comissões</span>
            </div>
          </template>
          <div class="stat-content">
            <div class="stat-number">
              {{ dashboardStats?.general.formatted_total_commissions || 'R$ 0,00' }}
            </div>
            <div class="stat-label">Comissão Total (8,5%)</div>
          </div>
        </n-card>
      </n-grid-item>
    </n-grid>

    <n-card
      v-if="authStore.hasPermission('view_sales') && dashboardStats?.top_sellers?.length"
      class="mt-6"
      title="Top 5 Vendedores"
    >
      <n-data-table
        :columns="topSellersColumns"
        :data="dashboardStats.top_sellers"
        :loading="loading"
        size="small"
        :pagination="false"
      />
    </n-card>

    <n-grid
      v-if="authStore.hasPermission('view_sales') && dashboardStats?.today"
      :cols="2"
      :x-gap="24"
      class="mt-6"
    >
      <n-grid-item>
        <n-card title="Vendas de Hoje">
          <n-space vertical>
            <div><strong>Total de Vendas:</strong> {{ dashboardStats.today.sales_count || 0 }}</div>
            <div>
              <strong>Valor Total:</strong>
              {{ dashboardStats.today.formatted_sales_amount || 'R$ 0,00' }}
            </div>
          </n-space>
        </n-card>
      </n-grid-item>

      <n-grid-item>
        <n-card title="Vendas do Mês">
          <n-space vertical>
            <div>
              <strong>Total de Vendas:</strong> {{ dashboardStats.this_month?.sales_count || 0 }}
            </div>
            <div>
              <strong>Valor Total:</strong>
              {{ dashboardStats.this_month?.formatted_sales_amount || 'R$ 0,00' }}
            </div>
          </n-space>
        </n-card>
      </n-grid-item>
    </n-grid>

    <div v-if="loading" class="text-center mt-6">
      <n-spin size="large" />
      <div class="mt-4">Carregando estatísticas...</div>
    </div>

    <n-alert v-if="error" type="error" class="mt-6" :title="error" />
  </div>
</template>

<script setup lang="ts">
import { onMounted, onActivated, ref } from 'vue'
import { useRouter } from 'vue-router'
import {
  NCard,
  NGrid,
  NGridItem,
  NIcon,
  NButton,
  NSpace,
  NAlert,
  NDataTable,
  type DataTableColumns,
  NSpin,
} from 'naive-ui'
import { useAuthStore } from '@/stores/auth'
import { serviceFactory } from '@/services/ServiceFactory'
import type { DashboardStats } from '@/services/implementations/DashboardApiService'

const router = useRouter()
const authStore = useAuthStore()
const dashboardService = serviceFactory.getDashboardService()

const dashboardStats = ref<DashboardStats | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)

const loadDashboardStats = async () => {
  try {
    loading.value = true
    error.value = null
    dashboardStats.value = await dashboardService.getStats()
  } catch (err) {
    console.error('Erro ao carregar estatísticas do dashboard:', err)
    error.value = 'Erro ao carregar dados do dashboard'
  } finally {
    loading.value = false
  }
}

const topSellersColumns: DataTableColumns<NonNullable<DashboardStats['top_sellers']>[0]> = [
  {
    title: 'Vendedor',
    key: 'name',
    width: 200,
  },
  {
    title: 'Total de Vendas',
    key: 'sales_count',
    width: 120,
    align: 'center',
  },
  {
    title: 'Valor Total',
    key: 'formatted_total_amount',
    width: 140,
    align: 'right',
  },
  {
    title: 'Comissão',
    key: 'formatted_total_commission',
    width: 140,
    align: 'right',
  },
]

onMounted(() => {
  loadDashboardStats()
})

onActivated(() => {
  loadDashboardStats()
})
</script>

<style scoped>
.dashboard {
  padding: 24px;
}

.stat-card {
  height: 100%;
}

.stat-header {
  display: flex;
  align-items: center;
  gap: 12px;
  font-weight: 600;
}

.stat-content {
  text-align: center;
  padding: 16px 0;
}

.stat-number {
  font-size: 28px;
  font-weight: 700;
  color: #333;
  line-height: 1;
}

.stat-label {
  margin-top: 8px;
  color: #666;
  font-size: 14px;
}

.mt-6 {
  margin-top: 24px;
}
</style>
