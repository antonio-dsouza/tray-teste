import type { ISaleRepository, PaginatedData } from '../interfaces/IRepository'
import type {
  Sale,
  CreateSaleRequest,
  UpdateSaleRequest,
  SalesFilters,
  ISaleApiService,
  ISellerApiService,
} from '../../services/interfaces/IApiService'
import type { ICacheStrategy } from './CacheStrategy'
import { MemoryCacheStrategy } from './CacheStrategy'

export class SaleRepository implements ISaleRepository {
  private cache: ICacheStrategy

  constructor(
    private saleApiService: ISaleApiService,
    private sellerApiService: ISellerApiService,
    cacheStrategy?: ICacheStrategy,
  ) {
    this.cache = cacheStrategy || new MemoryCacheStrategy()
  }

  async getAll(filters?: Record<string, unknown>): Promise<PaginatedData<Sale>> {
    const cacheKey = `sales_all_${JSON.stringify(filters || {})}`

    const cached = this.cache.get<PaginatedData<Sale>>(cacheKey)
    if (cached) return cached

    const response = await this.saleApiService.getAllSales(filters as SalesFilters)

    const paginatedData: PaginatedData<Sale> = {
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

  async getById(id: number): Promise<Sale> {
    const cacheKey = `sale_${id}`

    const cached = this.cache.get<Sale>(cacheKey)
    if (cached) return cached

    const sale = await this.saleApiService.getSaleById(id)
    this.cache.set(cacheKey, sale)

    return sale
  }

  async create(data: CreateSaleRequest): Promise<Sale> {
    const sale = await this.saleApiService.createSale(data)

    this.cache.delete('sales_all_{}')
    if (data.seller_id) {
      const keys = this.cache.keys()
      keys.forEach((key) => {
        if (key.startsWith('sales_seller_') || key.startsWith('sales_period_')) {
          this.cache.delete(key)
        }
      })
    }

    return sale
  }

  async update(id: number, data: UpdateSaleRequest): Promise<Sale> {
    const sale = await this.saleApiService.updateSale(id, data)

    this.cache.delete(`sale_${id}`)

    const keys = this.cache.keys()
    keys.forEach((key) => {
      if (
        key.startsWith('sales_all_') ||
        key.startsWith('sales_seller_') ||
        key.startsWith('sales_period_')
      ) {
        this.cache.delete(key)
      }
    })

    return sale
  }

  async delete(id: number): Promise<void> {
    await this.saleApiService.deleteSale(id)

    this.cache.delete(`sale_${id}`)

    const keys = this.cache.keys()
    keys.forEach((key) => {
      if (
        key.startsWith('sales_all_') ||
        key.startsWith('sales_seller_') ||
        key.startsWith('sales_period_')
      ) {
        this.cache.delete(key)
      }
    })
  }

  async getSalesByPeriod(startDate: string, endDate: string): Promise<PaginatedData<Sale>> {
    const cacheKey = `sales_period_${startDate}_${endDate}`

    const cached = this.cache.get<PaginatedData<Sale>>(cacheKey)
    if (cached) return cached

    const response = await this.saleApiService.getSalesByPeriod(startDate, endDate)

    const paginatedData: PaginatedData<Sale> = {
      items: response.data,
      pagination: {
        page: response.meta.current_page,
        pageSize: response.meta.per_page,
        pageCount: response.meta.last_page,
        total: response.meta.total,
      },
    }

    this.cache.set(cacheKey, paginatedData, 5 * 60 * 1000)

    return paginatedData
  }

  async getSalesBySeller(sellerId: number, filters?: SalesFilters): Promise<Sale[]> {
    const cacheKey = `sales_seller_${sellerId}_${JSON.stringify(filters || {})}`

    const cached = this.cache.get<Sale[]>(cacheKey)
    if (cached) return cached

    const filtersWithSeller = { ...filters, seller_id: sellerId }
    const response = await this.saleApiService.getAllSales(filtersWithSeller)
    this.cache.set(cacheKey, response.data, 2 * 60 * 1000)

    return response.data
  }

  clearCache(): void {
    this.cache.clear()
  }
}
