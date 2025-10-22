<template>
  <div class="seller-form">
    <n-card title="Cadastrar Novo Vendedor">
      <n-form ref="formRef" :model="formData" :rules="rules" @submit.prevent="handleSubmit">
        <n-grid cols="1 s:2" responsive="screen" :x-gap="16">
          <n-grid-item>
            <n-form-item path="name" label="Nome">
              <n-input v-model:value="formData.name" placeholder="Nome do vendedor" size="large" />
            </n-form-item>
          </n-grid-item>

          <n-grid-item>
            <n-form-item path="email" label="Email">
              <n-input
                v-model:value="formData.email"
                placeholder="Email do vendedor"
                size="large"
              />
            </n-form-item>
          </n-grid-item>
        </n-grid>

        <n-form-item>
          <n-space>
            <n-button type="primary" size="large" :loading="loading" attr-type="submit">
              Cadastrar Vendedor
            </n-button>

            <n-button size="large" @click="router.push('/sellers')"> Cancelar </n-button>
          </n-space>
        </n-form-item>
      </n-form>
    </n-card>

    <n-card v-if="formData.name || formData.email" title="Preview do Vendedor" class="mt-4">
      <n-descriptions :column="1" bordered>
        <n-descriptions-item label="Nome">
          {{ formData.name || 'Não informado' }}
        </n-descriptions-item>
        <n-descriptions-item label="Email">
          {{ formData.email || 'Não informado' }}
        </n-descriptions-item>
      </n-descriptions>
    </n-card>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import type { FormInst } from 'naive-ui'
import { useMessage } from 'naive-ui'
import { useSellersStore } from '@/stores/sellers'
import type { CreateSellerRequest } from '@/services/interfaces/IApiService'

const router = useRouter()
const message = useMessage()
const sellersStore = useSellersStore()

const formRef = ref<FormInst | null>(null)
const loading = ref(false)

const formData = reactive<CreateSellerRequest>({
  name: '',
  email: '',
})

const rules = {
  name: [{ required: true, message: 'Nome é obrigatório', trigger: ['blur', 'input'] }],
  email: [
    { required: true, message: 'Email é obrigatório', trigger: ['blur', 'input'] },
    { type: 'email', message: 'Formato de email inválido', trigger: ['blur', 'input'] },
  ],
}

const handleSubmit = async () => {
  try {
    await formRef.value?.validate()

    loading.value = true

    await sellersStore.createSeller(formData)

    message.success('Vendedor cadastrado com sucesso!')

    formData.name = ''
    formData.email = ''

    router.push('/sellers')
  } catch (error) {
    message.error('Erro ao cadastrar vendedor. Verifique os dados inseridos.')
    console.error(error)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.seller-form {
  padding: 24px;
  max-width: 800px;
  margin: 0 auto;
}

.mt-4 {
  margin-top: 16px;
}
</style>
