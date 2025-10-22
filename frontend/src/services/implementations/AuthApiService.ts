import type {
  IAuthApiService,
  LoginCredentials,
  AuthResponse,
  User,
  IApiService,
} from '../interfaces/IApiService'

export class AuthApiService implements IAuthApiService {
  constructor(private apiService: IApiService) {}

  async login(credentials: LoginCredentials): Promise<AuthResponse> {
    const response = await this.apiService.post<{
      success: boolean
      message: string
      data: {
        access_token: string
        token_type: string
        expires_in: number
        user: User
      }
    }>('/auth/login', credentials)

    const user = response.data.user

    if (!Array.isArray(user.roles)) {
      user.roles = user.roles ? [user.roles] : []
    }

    if (!Array.isArray(user.permissions)) {
      user.permissions = user.permissions ? [user.permissions] : []
    }

    return {
      token: response.data.access_token,
      user: user,
      expires_in: response.data.expires_in,
    }
  }

  async logout(): Promise<void> {
    await this.apiService.post<void>('/auth/logout')
    this.apiService.removeAuthToken()
  }

  async getCurrentUser(): Promise<User> {
    const response = await this.apiService.get<{ data: { user: User } }>('/auth/user')

    const user = response.data.user

    if (!Array.isArray(user.roles)) {
      user.roles = user.roles ? [user.roles] : []
    }

    if (!Array.isArray(user.permissions)) {
      user.permissions = user.permissions ? [user.permissions] : []
    }

    return user
  }

  async refreshToken(): Promise<AuthResponse> {
    const response = await this.apiService.post<{
      data: {
        access_token: string
        token_type: string
        expires_in: number
        user: User
      }
    }>('/auth/refresh')

    const data = response.data
    const user = data.user

    if (!Array.isArray(user.roles)) {
      user.roles = user.roles ? [user.roles] : []
    }

    if (!Array.isArray(user.permissions)) {
      user.permissions = user.permissions ? [user.permissions] : []
    }

    return {
      token: data.access_token,
      user: user,
      expires_in: data.expires_in,
    }
  }
}
