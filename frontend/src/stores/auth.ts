import { defineStore } from "pinia"
import { useAuth } from "../composables/useAuth"

export const useAuthStore = defineStore("auth", () => {
  const auth = useAuth()

  return {
    user: auth.user,
    token: auth.token,
    isAuthenticated: auth.isAuthenticated,
    isLoading: auth.isLoading,
    error: auth.error,
    
    userRole: auth.userRole,
    userPermissions: auth.userPermissions,
    
    login: auth.login,
    logout: auth.logout,
    getCurrentUser: auth.getCurrentUser,
    refreshToken: auth.refreshToken,
    clearError: auth.clearError,
    hasPermission: auth.hasPermission,
    hasRole: auth.hasRole
  }
})