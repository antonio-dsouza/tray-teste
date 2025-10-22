export abstract class BaseErrorHandler {
  public handle(error: unknown): ErrorResult {
    const normalizedError = this.normalizeError(error)
    const userMessage = this.getUserMessage(normalizedError)
    const shouldRetry = this.shouldRetry(normalizedError)

    this.logError(normalizedError)

    return {
      message: userMessage,
      originalError: normalizedError,
      shouldRetry,
      timestamp: new Date().toISOString(),
    }
  }

  protected normalizeError(error: unknown): NormalizedError {
    if (error instanceof Error) {
      const errorWithCode = error as Error & {
        code?: string | number
        status?: number
        response?: { status?: number }
      }
      return {
        name: error.name,
        message: error.message,
        stack: error.stack,
        code: errorWithCode.code,
        status: errorWithCode.status || errorWithCode.response?.status,
      }
    }

    if (typeof error === 'string') {
      return {
        name: 'StringError',
        message: error,
        code: undefined,
        status: undefined,
      }
    }

    return {
      name: 'UnknownError',
      message: 'Erro desconhecido',
      code: undefined,
      status: undefined,
    }
  }

  protected abstract getUserMessage(error: NormalizedError): string
  protected abstract shouldRetry(error: NormalizedError): boolean

  protected logError(error: NormalizedError): void {
    if (import.meta.env.MODE === 'development') {
      console.error('[Error Handler]', error)
    }
  }
}

export class ApiErrorHandler extends BaseErrorHandler {
  protected getUserMessage(error: NormalizedError): string {
    switch (error.status) {
      case 400:
        return 'Dados inválidos. Verifique as informações e tente novamente.'
      case 401:
        return 'Sessão expirada. Faça login novamente.'
      case 403:
        return 'Você não tem permissão para realizar esta ação.'
      case 404:
        return 'Recurso não encontrado.'
      case 422:
        return 'Dados inválidos. Verifique os campos obrigatórios.'
      case 429:
        return 'Muitas tentativas. Aguarde um momento e tente novamente.'
      case 500:
        return 'Erro interno do servidor. Tente novamente mais tarde.'
      case 503:
        return 'Serviço temporariamente indisponível.'
      default:
        if (error.status && error.status >= 400 && error.status < 500) {
          return 'Erro na solicitação. Verifique os dados e tente novamente.'
        }
        if (error.status && error.status >= 500) {
          return 'Erro no servidor. Tente novamente mais tarde.'
        }
        return error.message || 'Erro de comunicação com o servidor.'
    }
  }

  protected shouldRetry(error: NormalizedError): boolean {
    if (!error.status) return true
    if (error.status >= 500) return true
    if (error.status === 429) return true

    return false
  }
}

export class ValidationErrorHandler extends BaseErrorHandler {
  protected getUserMessage(error: NormalizedError): string {
    return error.message || 'Dados inválidos. Verifique os campos obrigatórios.'
  }

  protected shouldRetry(): boolean {
    return false
  }
}

export class BusinessErrorHandler extends BaseErrorHandler {
  protected getUserMessage(error: NormalizedError): string {
    switch (error.code) {
      case 'SELLER_NOT_FOUND':
        return 'Vendedor não encontrado.'
      case 'SALE_NOT_FOUND':
        return 'Venda não encontrada.'
      case 'INSUFFICIENT_PERMISSIONS':
        return 'Permissões insuficientes para esta ação.'
      case 'DUPLICATE_EMAIL':
        return 'Este e-mail já está sendo usado por outro vendedor.'
      default:
        return error.message || 'Erro na operação.'
    }
  }

  protected shouldRetry(): boolean {
    return false
  }
}

export class ErrorHandlerFactory {
  private static normalizeError(error: unknown): NormalizedError {
    if (error instanceof Error) {
      const errorWithCode = error as Error & {
        code?: string | number
        status?: number
        response?: { status?: number }
      }
      return {
        name: error.name,
        message: error.message,
        stack: error.stack,
        code: errorWithCode.code,
        status: errorWithCode.status || errorWithCode.response?.status,
      }
    }

    if (typeof error === 'string') {
      return {
        name: 'StringError',
        message: error,
        code: undefined,
        status: undefined,
      }
    }

    return {
      name: 'UnknownError',
      message: 'Erro desconhecido',
      code: undefined,
      status: undefined,
    }
  }

  static getHandler(error: unknown): BaseErrorHandler {
    const normalizedError = this.normalizeError(error)

    if (normalizedError.status) {
      return new ApiErrorHandler()
    }

    if (
      normalizedError.code &&
      typeof normalizedError.code === 'string' &&
      normalizedError.code.includes('_')
    ) {
      return new BusinessErrorHandler()
    }

    if (normalizedError.name === 'ValidationError') {
      return new ValidationErrorHandler()
    }

    return new ApiErrorHandler()
  }
}

export function useErrorHandler() {
  const handleError = (error: unknown): ErrorResult => {
    const handler = ErrorHandlerFactory.getHandler(error)
    return handler.handle(error)
  }

  return { handleError }
}

export interface NormalizedError {
  name: string
  message: string
  stack?: string
  code?: string | number
  status?: number
}

export interface ErrorResult {
  message: string
  originalError: NormalizedError
  shouldRetry: boolean
  timestamp: string
}
