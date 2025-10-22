import apiService from './implementations/ApiService'
import { AuthApiService } from './implementations/AuthApiService'
import { SellerApiService } from './implementations/SellerApiService'
import { SaleApiService } from './implementations/SaleApiService'
import { EmailApiService } from './implementations/EmailApiService'
import { DashboardApiService } from './implementations/DashboardApiService'

import type {
  IAuthApiService,
  ISellerApiService,
  ISaleApiService,
  IEmailApiService,
  IApiService,
} from './interfaces/IApiService'
import type { IDashboardApiService } from './implementations/DashboardApiService'

export class ServiceFactory {
  private static instance: ServiceFactory
  private services: Map<string, unknown> = new Map()

  private constructor() {}

  public static getInstance(): ServiceFactory {
    if (!ServiceFactory.instance) {
      ServiceFactory.instance = new ServiceFactory()
    }
    return ServiceFactory.instance
  }

  public getApiService(): IApiService {
    return apiService
  }

  public getAuthService(): IAuthApiService {
    if (!this.services.has('auth')) {
      this.services.set('auth', new AuthApiService(this.getApiService()))
    }
    return this.services.get('auth') as IAuthApiService
  }

  public getSellerService(): ISellerApiService {
    if (!this.services.has('seller')) {
      this.services.set('seller', new SellerApiService(this.getApiService()))
    }
    return this.services.get('seller') as ISellerApiService
  }

  public getSaleService(): ISaleApiService {
    if (!this.services.has('sale')) {
      this.services.set('sale', new SaleApiService(this.getApiService()))
    }
    return this.services.get('sale') as ISaleApiService
  }

  public getEmailService(): IEmailApiService {
    if (!this.services.has('email')) {
      this.services.set('email', new EmailApiService(this.getApiService()))
    }
    return this.services.get('email') as IEmailApiService
  }

  public getDashboardService(): IDashboardApiService {
    if (!this.services.has('dashboard')) {
      this.services.set('dashboard', new DashboardApiService(this.getApiService()))
    }
    return this.services.get('dashboard') as IDashboardApiService
  }
}

export const serviceFactory = ServiceFactory.getInstance()
