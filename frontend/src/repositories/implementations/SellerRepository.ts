import type {
  ISellerRepository,
  SellerCommissionReport,
  PaginatedData,
} from '../interfaces/IRepository'
import type {
  Seller,
  Sale,
  CreateSellerRequest,
  UpdateSellerRequest,
  SalesFilters,
  ISellerApiService,
  ISaleApiService,
} from '../../services/interfaces/IApiService'
import type { ICacheStrategy } from './CacheStrategy'
import { MemoryCacheStrategy } from './CacheStrategy'

export class SellerRepository implements ISellerRepository {
  private cache: ICacheStrategy

  constructor(
    private sellerApiService: ISellerApiService,
    private saleApiService: ISaleApiService,
    cacheStrategy?: ICacheStrategy,
  ) {
    this.cache = cacheStrategy || new MemoryCacheStrategy()
  }

  async getAll(filters?: Record<string, unknown>): Promise<PaginatedData<Seller>> {
    const cacheKey = `sellers_all_${JSON.stringify(filters || {})}`

    const cached = this.cache.get<PaginatedData<Seller>>(cacheKey)
    if (cached) return cached

    const response = await this.sellerApiService.getAllSellers(filters)

    const paginatedData: PaginatedData<Seller> = {
      items: response.data,
      pagination: {
        page: response.meta.current_page,
        pageSize: response.meta.per_page,
        pageCount: response.meta.last_page,
        total: response.meta.total,
      },
    }

    this.cache.set(cacheKey, paginatedData)

    return paginatedData
  }

  async getById(id: number): Promise<Seller> {
    const cacheKey = `seller_${id}`

    const cached = this.cache.get<Seller>(cacheKey)
    if (cached) return cached

    const seller = await this.sellerApiService.getSellerById(id)
    this.cache.set(cacheKey, seller)

    return seller
  }

  async create(data: CreateSellerRequest): Promise<Seller> {
    const seller = await this.sellerApiService.createSeller(data)

    const keys = this.cache.keys()
    keys.forEach((key) => {
      if (key.startsWith('sellers_all_')) {
        this.cache.delete(key)
      }
    })

    return seller
  }

  async update(id: number, data: UpdateSellerRequest): Promise<Seller> {
    const seller = await this.sellerApiService.updateSeller(id, data)

    this.cache.delete(`seller_${id}`)

    const keys = this.cache.keys()
    keys.forEach((key) => {
      if (key.startsWith('sellers_all_')) {
        this.cache.delete(key)
      }
    })

    return seller
  }

  async delete(id: number): Promise<void> {
    await this.sellerApiService.deleteSeller(id)

    this.cache.delete(`seller_${id}`)

    const keys = this.cache.keys()
    keys.forEach((key) => {
      if (key.startsWith('sellers_all_') || key.startsWith(`seller_${id}_`)) {
        this.cache.delete(key)
      }
    })
  }

  async getSellerSales(id: number, filters?: SalesFilters): Promise<Sale[]> {
    const cacheKey = `seller_${id}_sales_${JSON.stringify(filters || {})}`

    const cached = this.cache.get<Sale[]>(cacheKey)
    if (cached) return cached

    const sales = await this.sellerApiService.getSellerSales(id, filters)
    this.cache.set(cacheKey, sales, 2 * 60 * 1000)

    return sales
  }

  async getSellerCommissionReport(
    id: number,
    startDate: string,
    endDate: string,
  ): Promise<SellerCommissionReport> {
    const cacheKey = `seller_${id}_commission_${startDate}_${endDate}`

    const cached = this.cache.get<SellerCommissionReport>(cacheKey)
    if (cached) return cached

    const [seller, sales] = await Promise.all([
      this.getById(id),
      this.getSellerSales(id, { start_date: startDate, end_date: endDate }),
    ])

    const totalAmount = sales.reduce((sum, sale) => sum + sale.amount, 0)
    const totalCommission = sales.reduce((sum, sale) => sum + sale.commission_amount, 0)

    const report: SellerCommissionReport = {
      seller,
      period: { start_date: startDate, end_date: endDate },
      total_sales: sales.length,
      total_amount: totalAmount,
      total_commission: totalCommission,
      sales,
    }

    this.cache.set(cacheKey, report, 10 * 60 * 1000)

    return report
  }

  clearCache(): void {
    this.cache.clear()
  }
}
