import { ref, computed, type Ref } from 'vue'
import { ValidatorFactory, type FieldValidator } from '../utils/validation/validators'
import { useErrorHandler } from '../utils/errors/ErrorHandler'

export interface UseValidationReturn {
  errors: Ref<Record<string, string[]>>
  isValid: Ref<boolean>
  hasError: (field: string) => boolean
  getError: (field: string) => string | null
  validateField: (field: string, value: unknown) => boolean
  validateAll: (data: Record<string, unknown>) => boolean
  clearErrors: () => void
  clearFieldError: (field: string) => void
}

export function useValidation(
  validatorConfig: Record<string, FieldValidator>,
): UseValidationReturn {
  const errors = ref<Record<string, string[]>>({})
  const { handleError } = useErrorHandler()

  const isValid = computed(() => {
    return Object.keys(errors.value).length === 0
  })

  const hasError = (field: string): boolean => {
    return !!(errors.value[field] && errors.value[field].length > 0)
  }

  const getError = (field: string): string | null => {
    const fieldErrors = errors.value[field]
    if (!fieldErrors || fieldErrors.length === 0) {
      return null
    }
    return fieldErrors[0] || null
  }

  const validateField = (field: string, value: unknown): boolean => {
    try {
      const validator = validatorConfig[field]
      if (!validator) {
        console.warn(`Validator não encontrado para o campo: ${field}`)
        return true
      }

      const result = validator.validate(value)

      if (!result.isValid) {
        errors.value[field] = result.errors
        return false
      }

      if (errors.value[field]) {
        delete errors.value[field]
      }
      return true
    } catch (error) {
      const errorResult = handleError(error)
      errors.value[field] = [errorResult.message]
      return false
    }
  }

  const validateAll = (data: Record<string, unknown>): boolean => {
    clearErrors()

    let allValid = true

    for (const field of Object.keys(validatorConfig)) {
      const value = data[field]
      const isFieldValid = validateField(field, value)

      if (!isFieldValid) {
        allValid = false
      }
    }

    return allValid
  }

  const clearErrors = (): void => {
    errors.value = {}
  }

  const clearFieldError = (field: string): void => {
    if (errors.value[field]) {
      delete errors.value[field]
    }
  }

  return {
    errors,
    isValid,
    hasError,
    getError,
    validateField,
    validateAll,
    clearErrors,
    clearFieldError,
  }
}

export function useSellerValidation() {
  return useValidation(ValidatorFactory.seller())
}

export function useSaleValidation() {
  return useValidation(ValidatorFactory.sale())
}

export function useLoginValidation() {
  return useValidation(ValidatorFactory.login())
}
