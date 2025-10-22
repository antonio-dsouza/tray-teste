import type {
  ISellerApiService,
  Seller,
  CreateSellerRequest,
  UpdateSellerRequest,
  Sale,
  SalesFilters,
  IApiService,
  PaginatedResponse,
  PaginationMeta,
  PaginationLinks,
} from '../interfaces/IApiService'

export class SellerApiService implements ISellerApiService {
  constructor(private apiService: IApiService) {}

  async getAllSellers(filters?: Record<string, unknown>): Promise<PaginatedResponse<Seller>> {
    const response = await this.apiService.get<{
      success: boolean
      message: string
      data: Seller[]
      meta: PaginationMeta
      links: PaginationLinks
    }>('/sellers', filters)

    return {
      data: response.data,
      meta: response.meta,
      links: response.links,
    }
  }

  async getSellerById(id: number): Promise<Seller> {
    const response = await this.apiService.get<{ data: Seller }>(`/sellers/${id}`)
    return response.data
  }

  async createSeller(data: CreateSellerRequest): Promise<Seller> {
    const response = await this.apiService.post<{ data: Seller }>('/sellers', data)
    return response.data
  }

  async updateSeller(id: number, data: UpdateSellerRequest): Promise<Seller> {
    const response = await this.apiService.put<{ data: Seller }>(`/sellers/${id}`, data)
    return response.data
  }

  async deleteSeller(id: number): Promise<void> {
    await this.apiService.delete<void>(`/sellers/${id}`)
  }

  async getSellerSales(id: number, filters?: SalesFilters): Promise<Sale[]> {
    const response = await this.apiService.get<{ data: Sale[] }>(
      `/sellers/${id}/sales`,
      filters as Record<string, unknown>,
    )
    return response.data
  }
}
