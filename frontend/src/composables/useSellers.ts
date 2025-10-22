import { ref, computed, type Ref } from 'vue'
import { repositoryFactory } from '../repositories/RepositoryFactory'
import type {
  Seller,
  CreateSellerRequest,
  UpdateSellerRequest,
  Sale,
  SalesFilters,
} from '../services/interfaces/IApiService'
import type { SellerCommissionReport } from '../repositories/interfaces/IRepository'

export interface UseSellersReturn {
  sellers: Ref<Seller[]>
  currentSeller: Ref<Seller | null>
  isLoading: Ref<boolean>
  error: Ref<string | null>
  pagination: Ref<{
    page: number
    pageSize: number
    pageCount: number
    total: number
  }>

  sellersCount: Ref<number>
  activeSellers: Ref<Seller[]>

  loadSellers: (params?: Record<string, unknown>) => Promise<void>
  loadSeller: (id: number) => Promise<void>
  createSeller: (data: CreateSellerRequest) => Promise<Seller>
  updateSeller: (id: number, data: UpdateSellerRequest) => Promise<Seller>
  deleteSeller: (id: number) => Promise<void>
  getSellerSales: (id: number, filters?: SalesFilters) => Promise<Sale[]>
  getCommissionReport: (
    id: number,
    startDate: string,
    endDate: string,
  ) => Promise<SellerCommissionReport>
  getSellerById: (id: number) => Seller | null
  clearError: () => void
  clearCache: () => void
}

export function useSellers(): UseSellersReturn {
  const sellers = ref<Seller[]>([])
  const currentSeller = ref<Seller | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const sellerRepository = repositoryFactory.getSellerRepository()

  const sellersCount = computed(() => sellers.value.length)
  const activeSellers = computed(() => sellers.value.filter((seller) => seller.email))

  const clearError = () => {
    error.value = null
  }

  const handleError = (err: unknown, defaultMessage: string) => {
    error.value = err instanceof Error ? err.message : defaultMessage
    console.error(defaultMessage, err)
  }

  const pagination = ref({
    page: 1,
    pageSize: 10,
    pageCount: 1,
    total: 0,
  })

  const getSellerById = (id: number): Seller | null => {
    return sellers.value.find((seller) => seller.id === id) || null
  }

  const loadSellers = async (params?: Record<string, unknown>): Promise<void> => {
    try {
      isLoading.value = true
      clearError()

      const paginationParams = {
        page: params?.page || pagination.value.page,
        per_page: params?.per_page || pagination.value.pageSize,
      }

      const allParams = { ...params, ...paginationParams }

      const result = await sellerRepository.getAll(allParams)

      sellers.value = result.items

      if (result.pagination) {
        pagination.value = {
          page: result.pagination.page,
          pageSize: result.pagination.pageSize,
          pageCount: result.pagination.pageCount,
          total: result.pagination.total,
        }
      }
    } catch (err) {
      handleError(err, 'Erro ao carregar vendedores')
    } finally {
      isLoading.value = false
    }
  }

  const loadSeller = async (id: number): Promise<void> => {
    try {
      isLoading.value = true
      clearError()

      currentSeller.value = await sellerRepository.getById(id)
    } catch (err) {
      handleError(err, 'Erro ao carregar vendedor')
    } finally {
      isLoading.value = false
    }
  }

  const createSeller = async (data: CreateSellerRequest): Promise<Seller> => {
    try {
      isLoading.value = true
      clearError()

      const seller = await sellerRepository.create(data)

      sellers.value.push(seller)

      return seller
    } catch (err) {
      handleError(err, 'Erro ao criar vendedor')
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const updateSeller = async (id: number, data: UpdateSellerRequest): Promise<Seller> => {
    try {
      isLoading.value = true
      clearError()

      const seller = await sellerRepository.update(id, data)

      const index = sellers.value.findIndex((s) => s.id === id)
      if (index !== -1) {
        sellers.value[index] = seller
      }

      if (currentSeller.value?.id === id) {
        currentSeller.value = seller
      }

      return seller
    } catch (err) {
      handleError(err, 'Erro ao atualizar vendedor')
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const deleteSeller = async (id: number): Promise<void> => {
    try {
      isLoading.value = true
      clearError()

      await sellerRepository.delete(id)

      sellers.value = sellers.value.filter((s) => s.id !== id)

      if (currentSeller.value?.id === id) {
        currentSeller.value = null
      }
    } catch (err) {
      handleError(err, 'Erro ao excluir vendedor')
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const getSellerSales = async (id: number, filters?: SalesFilters): Promise<Sale[]> => {
    try {
      clearError()
      return await sellerRepository.getSellerSales(id, filters)
    } catch (err) {
      handleError(err, 'Erro ao carregar vendas do vendedor')
      return []
    }
  }

  const getCommissionReport = async (
    id: number,
    startDate: string,
    endDate: string,
  ): Promise<SellerCommissionReport> => {
    try {
      clearError()
      return await sellerRepository.getSellerCommissionReport(id, startDate, endDate)
    } catch (err) {
      handleError(err, 'Erro ao gerar relatório de comissão')
      throw err
    }
  }

  const clearCache = (): void => {
    sellerRepository.clearCache()
  }

  return {
    sellers,
    currentSeller,
    isLoading,
    error,
    pagination,

    sellersCount,
    activeSellers,

    loadSellers,
    loadSeller,
    createSeller,
    updateSeller,
    deleteSeller,
    getSellerSales,
    getCommissionReport,
    getSellerById,
    clearError,
    clearCache,
  }
}
