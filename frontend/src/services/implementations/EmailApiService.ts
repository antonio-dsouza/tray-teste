import type { IEmailApiService, IApiService } from '../interfaces/IApiService'

export class EmailApiService implements IEmailApiService {
  constructor(private apiService: IApiService) {}

  async sendDailyCommissionEmail(sellerId: number, date: string): Promise<void> {
    await this.apiService.post<void>(`/admin/sellers/${sellerId}/resend-commission`, {
      date,
    })
  }

  async sendAdminDailyReport(date: string): Promise<void> {
    await this.apiService.post<void>('/admin/run-daily-mails', {
      date,
    })
  }

  async resendCommissionEmail(sellerId: number, date?: string): Promise<void> {
    const data = date ? { date } : {}
    await this.apiService.post<void>(`/admin/sellers/${sellerId}/resend-commission`, data)
  }

  async runDailyMails(): Promise<void> {
    await this.apiService.post<void>('/admin/run-daily-mails')
  }
}
