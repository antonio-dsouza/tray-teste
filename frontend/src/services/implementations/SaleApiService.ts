import type {
  ISaleApiService,
  Sale,
  CreateSaleRequest,
  UpdateSaleRequest,
  SalesFilters,
  IApiService,
  PaginatedResponse,
  PaginationMeta,
  PaginationLinks,
} from '../interfaces/IApiService'

export class SaleApiService implements ISaleApiService {
  constructor(private apiService: IApiService) {}

  async getAllSales(filters?: SalesFilters): Promise<PaginatedResponse<Sale>> {
    const response = await this.apiService.get<{
      success: boolean
      message: string
      data: Sale[]
      meta: PaginationMeta
      links: PaginationLinks
    }>('/sales', filters as Record<string, unknown>)

    return {
      data: response.data,
      meta: response.meta,
      links: response.links,
    }
  }

  async getSaleById(id: number): Promise<Sale> {
    const response = await this.apiService.get<{ data: Sale }>(`/sales/${id}`)
    return response.data
  }

  async createSale(data: CreateSaleRequest): Promise<Sale> {
    const response = await this.apiService.post<{ data: Sale }>('/sales', data)
    return response.data
  }

  async updateSale(id: number, data: UpdateSaleRequest): Promise<Sale> {
    const response = await this.apiService.put<{ data: Sale }>(`/sales/${id}`, data)
    return response.data
  }

  async deleteSale(id: number): Promise<void> {
    await this.apiService.delete<void>(`/sales/${id}`)
  }

  async getSalesByPeriod(startDate: string, endDate: string): Promise<PaginatedResponse<Sale>> {
    const filters = { start_date: startDate, end_date: endDate }
    const response = await this.apiService.get<{
      success: boolean
      message: string
      data: Sale[]
      meta: PaginationMeta
      links: PaginationLinks
    }>('/sales', filters)

    return {
      data: response.data,
      meta: response.meta,
      links: response.links,
    }
  }

  async resendSaleCommission(saleId: number): Promise<{ success: boolean; message: string }> {
    const response = await this.apiService.post<{
      success: boolean
      message: string
      data: null
    }>(`/admin/sales/${saleId}/resend-commission`)

    return {
      success: response.success,
      message: response.message,
    }
  }
}
