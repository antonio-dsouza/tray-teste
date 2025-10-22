export interface IApiService {
  get<T>(url: string, params?: Record<string, unknown>): Promise<T>
  post<T>(
    url: string,
    data?: Record<string, unknown> | LoginCredentials | CreateSellerRequest | CreateSaleRequest,
  ): Promise<T>
  put<T>(
    url: string,
    data?: Record<string, unknown> | UpdateSellerRequest | UpdateSaleRequest,
  ): Promise<T>
  delete<T>(url: string): Promise<T>
  setAuthToken(token: string): void
  removeAuthToken(): void
}

export interface IAuthApiService {
  login(credentials: LoginCredentials): Promise<AuthResponse>
  logout(): Promise<void>
  getCurrentUser(): Promise<User>
  refreshToken(): Promise<AuthResponse>
}

export interface ISellerApiService {
  getAllSellers(filters?: Record<string, unknown>): Promise<PaginatedResponse<Seller>>
  getSellerById(id: number): Promise<Seller>
  createSeller(data: CreateSellerRequest): Promise<Seller>
  updateSeller(id: number, data: UpdateSellerRequest): Promise<Seller>
  deleteSeller(id: number): Promise<void>
  getSellerSales(id: number, filters?: SalesFilters): Promise<Sale[]>
}

export interface PaginationMeta {
  current_page: number
  from: number
  last_page: number
  per_page: number
  to: number
  total: number
}

export interface PaginationLinks {
  first: string
  last: string
  prev: string | null
  next: string | null
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: PaginationMeta
  links: PaginationLinks
}

export interface ISaleApiService {
  getAllSales(filters?: SalesFilters): Promise<PaginatedResponse<Sale>>
  getSaleById(id: number): Promise<Sale>
  createSale(data: CreateSaleRequest): Promise<Sale>
  updateSale(id: number, data: UpdateSaleRequest): Promise<Sale>
  deleteSale(id: number): Promise<void>
  getSalesByPeriod(startDate: string, endDate: string): Promise<PaginatedResponse<Sale>>
  resendSaleCommission(saleId: number): Promise<{ success: boolean; message: string }>
}

export interface IEmailApiService {
  sendDailyCommissionEmail(sellerId: number, date: string): Promise<void>
  sendAdminDailyReport(date: string): Promise<void>
  resendCommissionEmail(sellerId: number, date?: string): Promise<void>
  runDailyMails(): Promise<void>
}

export interface LoginCredentials {
  email: string
  password: string
}

export interface AuthResponse {
  token: string
  user: User
  expires_in: number
}

export interface User {
  id: number
  name: string
  email: string
  roles: string[]
  permissions: string[]
}

export interface Seller {
  id: number
  name: string
  email: string
  total_sales?: number
  total_commission?: number
  created_at: string
  updated_at: string
}

export interface Sale {
  id: number
  seller_id: number
  seller?: Seller
  amount: number
  commission_amount: number
  sold_at: string
  created_at: string
  updated_at: string
}

export interface CreateSellerRequest {
  name: string
  email: string
}

export interface UpdateSellerRequest {
  name?: string
  email?: string
}

export interface CreateSaleRequest {
  seller_id: number
  amount: number
  sold_at: string
}

export interface UpdateSaleRequest {
  seller_id?: number
  amount?: number
  sold_at?: string
}

export interface SalesFilters {
  seller_id?: number
  start_date?: string
  end_date?: string
  min_amount?: number
  max_amount?: number
  page?: number
  per_page?: number
}

export interface ApiResponse<T> {
  success: boolean
  message: string
  data: T
}

export interface ApiError {
  message: string
  errors?: Record<string, string[]>
  code?: number
}
