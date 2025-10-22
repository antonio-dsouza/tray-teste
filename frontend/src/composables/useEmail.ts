import { ref, type Ref } from 'vue'
import { serviceFactory } from '../services/ServiceFactory'

export interface UseEmailReturn {
  isLoading: Ref<boolean>
  error: Ref<string | null>
  success: Ref<string | null>

  sendDailyCommissionEmail: (sellerId: number, date: string) => Promise<void>
  sendAdminDailyReport: (date: string) => Promise<void>
  resendCommissionEmail: (sellerId: number, date: string) => Promise<void>
  clearMessages: () => void
}

export function useEmail(): UseEmailReturn {
  const isLoading = ref(false)
  const error = ref<string | null>(null)
  const success = ref<string | null>(null)

  const emailService = serviceFactory.getEmailService()

  const clearMessages = () => {
    error.value = null
    success.value = null
  }

  const handleError = (err: unknown, defaultMessage: string) => {
    error.value = err instanceof Error ? err.message : defaultMessage
    success.value = null
    console.error(defaultMessage, err)
  }

  const handleSuccess = (message: string) => {
    success.value = message
    error.value = null
  }

  const sendDailyCommissionEmail = async (sellerId: number, date: string): Promise<void> => {
    try {
      isLoading.value = true
      clearMessages()

      await emailService.sendDailyCommissionEmail(sellerId, date)
      handleSuccess('E-mail de comissão enviado com sucesso!')
    } catch (err) {
      handleError(err, 'Erro ao enviar e-mail de comissão')
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const sendAdminDailyReport = async (date: string): Promise<void> => {
    try {
      isLoading.value = true
      clearMessages()

      await emailService.sendAdminDailyReport(date)
      handleSuccess('Relatório diário enviado para o administrador!')
    } catch (err) {
      handleError(err, 'Erro ao enviar relatório diário')
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const resendCommissionEmail = async (sellerId: number, date: string): Promise<void> => {
    try {
      isLoading.value = true
      clearMessages()

      await emailService.resendCommissionEmail(sellerId, date)
      handleSuccess('E-mail de comissão reenviado com sucesso!')
    } catch (err) {
      handleError(err, 'Erro ao reenviar e-mail de comissão')
      throw err
    } finally {
      isLoading.value = false
    }
  }

  return {
    isLoading,
    error,
    success,

    sendDailyCommissionEmail,
    sendAdminDailyReport,
    resendCommissionEmail,
    clearMessages,
  }
}
