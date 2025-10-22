import type { IApiService } from '../interfaces/IApiService'

export interface DashboardStats {
  general: {
    total_sellers: number
    total_sales: number
    total_sales_amount: number
    formatted_total_sales_amount: string
    total_commissions: number
    formatted_total_commissions: string
    average_sale_amount: number
    formatted_average_sale_amount: string
  }
  today: {
    sales_count: number
    sales_amount: number
    formatted_sales_amount: string
  }
  this_month: {
    sales_count: number
    sales_amount: number
    formatted_sales_amount: string
  }
  top_sellers: Array<{
    id: number
    name: string
    email: string
    total_sales_amount: number
    formatted_amount: string
  }>
  sales_by_month: Array<{
    month: string
    month_name: string
    sales_count: number
    sales_amount: number
    formatted_amount: string
  }>
}

export interface IDashboardApiService {
  getStats(): Promise<DashboardStats>
}

export class DashboardApiService implements IDashboardApiService {
  constructor(private apiService: IApiService) {}

  async getStats(): Promise<DashboardStats> {
    const response = await this.apiService.get<{ data: DashboardStats }>('/dashboard/stats')
    return response.data
  }
}
