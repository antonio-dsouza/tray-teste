import type {
  Seller,
  Sale,
  CreateSellerRequest,
  UpdateSellerRequest,
  CreateSaleRequest,
  UpdateSaleRequest,
  SalesFilters,
} from '../../services/interfaces/IApiService'

export interface PaginatedData<T> {
  items: T[]
  pagination: {
    page: number
    pageSize: number
    pageCount: number
    total: number
  }
}

export interface IBaseRepository<T, CreateRequest, UpdateRequest> {
  getAll(filters?: Record<string, unknown>): Promise<PaginatedData<T>>
  getById(id: number): Promise<T>
  create(data: CreateRequest): Promise<T>
  update(id: number, data: UpdateRequest): Promise<T>
  delete(id: number): Promise<void>
  clearCache(): void
}

export interface ISellerRepository
  extends IBaseRepository<Seller, CreateSellerRequest, UpdateSellerRequest> {
  getSellerSales(id: number, filters?: SalesFilters): Promise<Sale[]>
  getSellerCommissionReport(
    id: number,
    startDate: string,
    endDate: string,
  ): Promise<SellerCommissionReport>
}

export interface ISaleRepository
  extends IBaseRepository<Sale, CreateSaleRequest, UpdateSaleRequest> {
  getSalesByPeriod(startDate: string, endDate: string): Promise<PaginatedData<Sale>>
  getSalesBySeller(sellerId: number, filters?: SalesFilters): Promise<Sale[]>
}

export interface SellerCommissionReport {
  seller: Seller
  period: {
    start_date: string
    end_date: string
  }
  total_sales: number
  total_amount: number
  total_commission: number
  sales: Sale[]
}
