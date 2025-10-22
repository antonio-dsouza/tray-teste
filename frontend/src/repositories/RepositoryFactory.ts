import { SellerRepository } from './implementations/SellerRepository'
import { SaleRepository } from './implementations/SaleRepository'
import { MemoryCacheStrategy, LocalStorageCacheStrategy } from './implementations/CacheStrategy'
import { serviceFactory } from '../services/ServiceFactory'
import type { ICacheStrategy } from './implementations/CacheStrategy'

export class RepositoryFactory {
  private static instance: RepositoryFactory
  private repositories: Map<string, unknown> = new Map()
  private cacheStrategy: ICacheStrategy

  private constructor() {
    this.cacheStrategy = this.isLocalStorageAvailable()
      ? new LocalStorageCacheStrategy()
      : new MemoryCacheStrategy()
  }

  public static getInstance(): RepositoryFactory {
    if (RepositoryFactory.instance) {
      return RepositoryFactory.instance
    }
    RepositoryFactory.instance = new RepositoryFactory()
    return RepositoryFactory.instance
  }

  private isLocalStorageAvailable(): boolean {
    try {
      const test = '__localStorage_test__'
      localStorage.setItem(test, test)
      localStorage.removeItem(test)
      return true
    } catch {
      return false
    }
  }

  public getSellerRepository(): SellerRepository {
    if (this.repositories.has('seller')) {
      return this.repositories.get('seller') as SellerRepository
    }
    const repository = new SellerRepository(
      serviceFactory.getSellerService(),
      serviceFactory.getSaleService(),
      this.cacheStrategy,
    )
    this.repositories.set('seller', repository)
    return repository
  }

  public getSaleRepository(): SaleRepository {
    if (this.repositories.has('sale')) {
      return this.repositories.get('sale') as SaleRepository
    }
    const repository = new SaleRepository(
      serviceFactory.getSaleService(),
      serviceFactory.getSellerService(),
      this.cacheStrategy,
    )
    this.repositories.set('sale', repository)
    return repository
  }

  public clearAllCaches(): void {
    this.cacheStrategy.clear()
  }

  public setCacheStrategy(strategy: ICacheStrategy): void {
    this.cacheStrategy = strategy
    this.repositories.clear()
  }
}

export const repositoryFactory = RepositoryFactory.getInstance()
