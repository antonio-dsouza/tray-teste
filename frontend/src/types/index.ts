export interface User {
  id: number
  name: string
  email: string
  role: string
  permissions: string[]
}

export interface Seller {
  id: number
  name: string
  email: string
  sales?: Sale[]
  created_at: string
  updated_at: string
}

export interface Sale {
  id: number
  seller_id: number
  amount: number
  commission_amount: number
  sold_at: string
  created_at: string
  updated_at: string
}

export interface LoginCredentials {
  email: string
  password: string
}

export interface LoginResponse {
  success: boolean
  message: string
  data: {
    access_token: string
    token_type: string
    expires_in: number
    user: User
  }
}

export interface ApiResponse<T = unknown> {
  success: boolean
  message: string
  data: T
}

export interface PaginationMeta {
  current_page: number
  per_page: number
  total: number
  last_page: number
  from: number
  to: number
}

export interface PaginatedResponse<T = unknown> {
  success: boolean
  message: string
  data: T[]
  meta: PaginationMeta
  links?: {
    first?: string
    last?: string
    prev?: string
    next?: string
  }
}

export interface PaginationParams {
  page?: number
  per_page?: number
}

export interface CreateSellerData {
  name: string
  email: string
}

export interface CreateSaleData {
  seller_id: number
  amount: number
  sold_at: string
}

export interface ResendCommissionData {
  date?: string
}
