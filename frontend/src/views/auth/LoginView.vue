<template>
  <div class="login-container">
    <n-card class="login-card" title="Sistema de Vendas - Login">
      <template #header-extra>
        <n-icon size="24" color="#18a058">
          <svg viewBox="0 0 24 24">
            <path
              fill="currentColor"
              d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M7.07,18.28C7.5,17.38 10.12,16.5 12,16.5C13.88,16.5 16.5,17.38 16.93,18.28C15.57,19.36 13.86,20 12,20C10.14,20 8.43,19.36 7.07,18.28M18.36,16.83C16.93,15.09 13.46,14.5 12,14.5C10.54,14.5 7.07,15.09 5.64,16.83C4.62,15.5 4,13.82 4,12C4,7.59 7.59,4 12,4C16.41,4 20,7.59 20,12C20,13.82 19.38,15.5 18.36,16.83M12,6C10.06,6 8.5,7.56 8.5,9.5C8.5,11.44 10.06,13 12,13C13.94,13 15.5,11.44 15.5,9.5C15.5,7.56 13.94,6 12,6M12,11A1.5,1.5 0 0,1 10.5,9.5A1.5,1.5 0 0,1 12,8A1.5,1.5 0 0,1 13.5,9.5A1.5,1.5 0 0,1 12,11Z"
            />
          </svg>
        </n-icon>
      </template>

      <n-form ref="formRef" :model="formData" :rules="rules" @submit.prevent="handleSubmit">
        <n-form-item path="email" label="Email">
          <n-input
            v-model:value="formData.email"
            type="email"
            placeholder="Digite seu email"
            :loading="loading"
            size="large"
          >
            <template #prefix>
              <n-icon>
                <svg viewBox="0 0 24 24">
                  <path
                    fill="currentColor"
                    d="M12,2C13.1,2 14,2.9 14,4C14,5.1 13.1,6 12,6C10.9,6 10,5.1 10,4C10,2.9 10.9,2 12,2M21,9V7L15,1H5C3.89,1 3,1.89 3,3V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V9M12,13C14.67,13 20,14.33 20,17V19H4V17C4,14.33 9.33,13 12,13Z"
                  />
                </svg>
              </n-icon>
            </template>
          </n-input>
        </n-form-item>

        <n-form-item path="password" label="Senha">
          <n-input
            v-model:value="formData.password"
            type="password"
            placeholder="Digite sua senha"
            :loading="loading"
            size="large"
            show-password-on="mousedown"
          >
            <template #prefix>
              <n-icon>
                <svg viewBox="0 0 24 24">
                  <path
                    fill="currentColor"
                    d="M12,17A2,2 0 0,0 14,15C14,13.89 13.1,13 12,13A2,2 0 0,0 10,15A2,2 0 0,0 12,17M18,8A2,2 0 0,1 20,10V20A2,2 0 0,1 18,22H6A2,2 0 0,1 4,20V10C4,8.89 4.9,8 6,8H7V6A5,5 0 0,1 12,1A5,5 0 0,1 17,6V8H18M12,3A3,3 0 0,0 9,6V8H15V6A3,3 0 0,0 12,3Z"
                  />
                </svg>
              </n-icon>
            </template>
          </n-input>
        </n-form-item>

        <n-form-item>
          <n-button type="primary" size="large" block :loading="loading" attr-type="submit">
            Entrar
          </n-button>
        </n-form-item>
      </n-form>

      <n-divider>Informações de Teste</n-divider>

      <n-alert type="info" title="Dados para Login">
        <strong>Email:</strong> admin@teste-tray.com<br />
        <strong>Senha:</strong> password
      </n-alert>
    </n-card>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useMessage } from 'naive-ui'
import { useAuthStore } from '../../stores/auth'
import type { LoginCredentials } from '../../types/index'

const router = useRouter()
const message = useMessage()
const authStore = useAuthStore()

const formRef = ref()
const loading = ref(false)

const formData = reactive<LoginCredentials>({
  email: '',
  password: '',
})

const rules = {
  email: [
    {
      required: true,
      message: 'Email é obrigatório',
      trigger: ['input', 'blur'],
    },
    {
      type: 'email',
      message: 'Formato de email inválido',
      trigger: ['input', 'blur'],
    },
  ],
  password: [
    {
      required: true,
      message: 'Senha é obrigatória',
      trigger: ['input', 'blur'],
    },
    {
      min: 6,
      message: 'Senha deve ter pelo menos 6 caracteres',
      trigger: ['input', 'blur'],
    },
  ],
}

const handleSubmit = async () => {
  try {
    await formRef.value?.validate()
    loading.value = true

    await authStore.login(formData)

    message.success('Login realizado com sucesso!')

    await nextTick()

    if (!authStore.isAuthenticated) {
      return message.error('Erro na autenticação. Tente novamente.')
    }

    router.push('/')
  } catch (error) {
    const errorMessage = error instanceof Error ? error.message : 'Erro ao fazer login'
    message.error(errorMessage)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #0c1445 0%, #1e3a8a 50%, #1e40af 100%);
  padding: 20px;
}

.login-card {
  width: 100%;
  max-width: 400px;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
  border-radius: 16px;
  backdrop-filter: blur(10px);
}
</style>
