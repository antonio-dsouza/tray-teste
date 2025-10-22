import { ref, computed, type Ref } from 'vue'
import { serviceFactory } from '../services/ServiceFactory'
import type { LoginCredentials, AuthResponse, User } from '../services/interfaces/IApiService'

export interface UseAuthReturn {
  user: Ref<User | null>
  token: Ref<string | null>
  isAuthenticated: Ref<boolean>
  isLoading: Ref<boolean>
  error: Ref<string | null>

  userRole: Ref<string | null>
  userPermissions: Ref<string[]>

  login: (credentials: LoginCredentials) => Promise<void>
  logout: () => Promise<void>
  getCurrentUser: () => Promise<void>
  refreshToken: () => Promise<void>
  clearError: () => void
  hasPermission: (permission: string) => boolean
  hasRole: (role: string) => boolean
}

export function useAuth(): UseAuthReturn {
  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('auth_token'))
  const permissions = ref<string | null>(localStorage.getItem('permissions'))
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const userRole = computed(() => user.value?.roles[0] || null)
  const userPermissions = computed(() => permissions.value?.split(',') || [])
  const authService = serviceFactory.getAuthService()

  const clearError = () => {
    error.value = null
  }

  const login = async (credentials: LoginCredentials): Promise<void> => {
    try {
      isLoading.value = true
      clearError()

      const response: AuthResponse = await authService.login(credentials)

      token.value = response.token
      user.value = response.user

      const permissionsArray = response.user.permissions || []

      localStorage.setItem('auth_token', response.token)
      localStorage.setItem('permissions', permissionsArray.join(','))
      serviceFactory.getApiService().setAuthToken(response.token)
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Erro no login'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const logout = async (): Promise<void> => {
    try {
      isLoading.value = true

      await authService.logout()
    } catch (err) {
      console.error('Erro ao fazer logout:', err)
    } finally {
      user.value = null
      token.value = null
      localStorage.removeItem('auth_token')
      localStorage.removeItem('permissions')
      serviceFactory.getApiService().removeAuthToken()
      isLoading.value = false
    }
  }

  const getCurrentUser = async (): Promise<void> => {
    if (!token.value) return

    try {
      isLoading.value = true
      clearError()

      user.value = await authService.getCurrentUser()

      if (user.value && user.value.permissions) {
        localStorage.setItem('permissions', user.value.permissions.join(','))
      }
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'Erro ao buscar usuário'
      error.value = errorMessage

      if (
        err instanceof Error &&
        (err.message.includes('401') ||
          err.message.includes('expired') ||
          err.message.includes('Token has expired') ||
          err.message.includes('invalid'))
      ) {
        console.info('Token expirado durante getCurrentUser, fazendo logout...')
        await logout()
      }
    } finally {
      isLoading.value = false
    }
  }

  const refreshToken = async (): Promise<void> => {
    if (!token.value) return

    try {
      const response: AuthResponse = await authService.refreshToken()

      token.value = response.token
      user.value = response.user

      serviceFactory.getApiService().setAuthToken(response.token)
    } catch (err) {
      await logout()
      throw err
    }
  }

  const hasPermission = (permission: string): boolean => {
    if (!userPermissions.value || userPermissions.value.length === 0) {
      return false
    }

    if (permission === 'admin' && hasRole('admin')) {
      return true
    }

    return userPermissions.value.includes(permission)
  }

  const hasRole = (role: string): boolean => {
    return user.value?.roles?.includes(role) || false
  }

  if (token.value && !user.value) {
    getCurrentUser()
  }

  return {
    user,
    token,
    isAuthenticated,
    isLoading,
    error,

    userRole,
    userPermissions,

    login,
    logout,
    getCurrentUser,
    refreshToken,
    clearError,
    hasPermission,
    hasRole,
  }
}
