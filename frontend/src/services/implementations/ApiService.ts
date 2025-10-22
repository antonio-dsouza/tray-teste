import axios, { type AxiosInstance, type AxiosResponse } from 'axios'
import type { IApiService, ApiError } from '../interfaces/IApiService'

class ApiService implements IApiService {
  private api: AxiosInstance

  constructor() {
    this.api = axios.create({
      baseURL: import.meta.env.VITE_API_LOCAL_URL,
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
    })

    this.setupInterceptors()
  }

  private setupInterceptors(): void {
    this.api.interceptors.request.use(
      (config) => {
        const token = localStorage.getItem('auth_token')
        if (token) {
          config.headers.Authorization = `Bearer ${token}`
        }
        return config
      },
      (error) => {
        return Promise.reject(error)
      },
    )

    this.api.interceptors.response.use(
      (response: AxiosResponse) => {
        return response
      },
      (error) => {
        const apiError: ApiError = {
          message: error.response?.data?.message || error.message || 'Erro desconhecido',
          errors: error.response?.data?.errors,
          code: error.response?.status,
        }

        if (error.response?.status === 401) {
          const errorMessage = error.response?.data?.message || ''

          if (
            errorMessage.includes('expired') ||
            errorMessage.includes('invalid') ||
            errorMessage.includes('Token has expired') ||
            errorMessage.includes('Token de autorização inválido ou expirado')
          ) {
            console.info('Token expirado, redirecionando para login...')
            this.removeAuthToken()

            if (!window.location.pathname.includes('/login')) {
              window.location.href = '/login'
            }
          } else {
            this.removeAuthToken()
            window.location.href = '/login'
          }
        }

        return Promise.reject(apiError)
      },
    )
  }

  async get<T>(url: string, params?: Record<string, unknown>): Promise<T> {
    const response = await this.api.get(url, { params })
    return response.data
  }

  async post<T>(url: string, data?: Record<string, unknown> | object): Promise<T> {
    const response = await this.api.post(url, data)
    return response.data
  }

  async put<T>(url: string, data?: Record<string, unknown> | object): Promise<T> {
    const response = await this.api.put(url, data)
    return response.data
  }

  async delete<T>(url: string): Promise<T> {
    const response = await this.api.delete(url)
    return response.data
  }

  setAuthToken(token: string): void {
    localStorage.setItem('auth_token', token)
    this.api.defaults.headers.common['Authorization'] = `Bearer ${token}`
  }

  removeAuthToken(): void {
    localStorage.removeItem('auth_token')
    delete this.api.defaults.headers.common['Authorization']
  }
}

const apiService = new ApiService()
export default apiService
