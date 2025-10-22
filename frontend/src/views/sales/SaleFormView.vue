<template>
  <div class="sale-form">
    <n-card title="Registrar Nova Venda">
      <n-form ref="formRef" :model="formData" :rules="rules" @submit.prevent="handleSubmit">
        <n-grid cols="1 s:2" responsive="screen" :x-gap="16">
          <n-grid-item>
            <n-form-item path="seller_id" label="Vendedor">
              <n-select
                v-model:value="formData.seller_id"
                placeholder="Selecione um vendedor"
                :options="sellerOptions"
                filterable
                :loading="sellersStore.isLoading"
                size="large"
              />
            </n-form-item>
          </n-grid-item>

          <n-grid-item>
            <n-form-item path="amount" label="Valor da Venda">
              <n-input
                v-model:value="displayAmount"
                placeholder="R$ 0,00"
                @input="handleAmountInput"
                @blur="formatAmount"
                size="large"
              />
            </n-form-item>
          </n-grid-item>

          <n-grid-item span="2">
            <n-form-item path="sold_at" label="Data e Hora da Venda">
              <n-date-picker
                v-model:value="formData.sold_at"
                type="datetime"
                placeholder="Selecione a data e hora"
                format="dd/MM/yyyy HH:mm"
                style="width: 100%"
                size="large"
              />
            </n-form-item>
          </n-grid-item>
        </n-grid>

        <n-form-item>
          <n-space>
            <n-button type="primary" size="large" :loading="loading" attr-type="submit">
              Registrar Venda
            </n-button>

            <n-button size="large" @click="router.push('/sales')"> Cancelar </n-button>
          </n-space>
        </n-form-item>
      </n-form>
    </n-card>

    <n-card v-if="selectedSeller || formData.amount" title="Preview da Venda" class="mt-4">
      <n-descriptions :column="1" bordered>
        <n-descriptions-item label="Vendedor">
          {{ selectedSeller?.name || 'Não selecionado' }}
        </n-descriptions-item>
        <n-descriptions-item label="Email do Vendedor">
          {{ selectedSeller?.email || '-' }}
        </n-descriptions-item>
        <n-descriptions-item label="Valor da Venda">
          {{ formData.amount ? formatCurrency(formData.amount) : '-' }}
        </n-descriptions-item>
        <n-descriptions-item label="Comissão">
          {{ formData.amount ? formatCurrency(calculateCommission(formData.amount)) : '-' }}
        </n-descriptions-item>
        <n-descriptions-item label="Data da Venda">
          {{ formData.sold_at ? formatDateTime(formData.sold_at) : '-' }}
        </n-descriptions-item>
      </n-descriptions>

      <n-alert
        v-if="formData.amount && formData.amount >= 10000"
        type="warning"
        title="Venda de Alto Valor"
        class="mt-4"
      >
        Esta é uma venda de alto valor (≥ R$ 10.000,00). Verifique os dados antes de confirmar.
      </n-alert>
    </n-card>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useMessage } from 'naive-ui'
import { useSellersStore } from '@/stores/sellers'
import { useSalesStore } from '@/stores/sales'
import { calculateCommission } from '@/constants/commission'
import type { CreateSaleData } from '@/types'

const router = useRouter()
const message = useMessage()
const sellersStore = useSellersStore()
const salesStore = useSalesStore()

const formRef = ref()
const loading = ref(false)

const formData = reactive({
  seller_id: null as unknown as number,
  amount: null as unknown as number,
  sold_at: Date.now(),
})

const displayAmount = ref('')

const formatCurrencyMask = (value: string) => {
  const digits = value.replace(/\D/g, '')

  if (!digits) return ''

  const number = parseInt(digits) / 100

  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
    minimumFractionDigits: 2,
  }).format(number)
}

const handleAmountInput = (value: string) => {
  if (!value || value === '') {
    displayAmount.value = ''
    formData.amount = 0
    return
  }

  const digits = value.replace(/\D/g, '')

  if (!digits) {
    displayAmount.value = ''
    formData.amount = 0
    return
  }

  displayAmount.value = formatCurrencyMask(digits)

  formData.amount = parseInt(digits) / 100
}

const formatAmount = () => {
  if (formData.amount && formData.amount > 0) {
    displayAmount.value = new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL',
      minimumFractionDigits: 2,
    }).format(formData.amount)
  }
}

const sellerOptions = computed(() => {
  return sellersStore.sellers.map((seller) => ({
    label: `${seller.name} (${seller.email})`,
    value: seller.id,
  }))
})

const selectedSeller = computed(() => {
  return sellersStore.getSellerById(formData.seller_id)
})

const rules = {
  seller_id: [
    {
      required: true,
      message: 'Vendedor é obrigatório',
      trigger: ['change', 'blur'],
      type: 'number',
    },
  ],
  amount: [
    {
      required: true,
      message: 'Valor da venda é obrigatório',
      trigger: ['input', 'blur'],
      type: 'number',
    },
    {
      validator: (_: unknown, value: number) => {
        if (value <= 0) {
          return new Error('Valor deve ser maior que zero')
        }
        return true
      },
      trigger: ['input', 'blur'],
    },
  ],
  sold_at: [
    {
      required: true,
      message: 'Data da venda é obrigatória',
      trigger: ['change', 'blur'],
      type: 'number',
    },
  ],
}

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(value)
}

const formatDateTime = (timestamp: number) => {
  return new Date(timestamp).toLocaleString('pt-BR')
}

const handleSubmit = async () => {
  try {
    await formRef.value?.validate()

    loading.value = true

    const saleData: CreateSaleData = {
      seller_id: formData.seller_id,
      amount: formData.amount,
      sold_at: new Date(formData.sold_at!).toISOString(),
    }

    await salesStore.createSale(saleData)

    message.success('Venda registrada com sucesso!')

    formData.seller_id = null as unknown as number
    formData.amount = null as unknown as number
    formData.sold_at = Date.now()
    displayAmount.value = ''

    router.push('/sales')
  } catch {
    message.error('Erro de validação. Verifique os dados inseridos.')
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await sellersStore.loadSellers()

  displayAmount.value = ''
})
</script>

<style scoped>
.sale-form {
  padding: 24px;
  max-width: 800px;
  margin: 0 auto;
}

.mt-4 {
  margin-top: 16px;
}
</style>
