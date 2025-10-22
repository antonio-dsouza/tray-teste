import { ref, computed, type Ref } from 'vue'
import { repositoryFactory } from '../repositories/RepositoryFactory'
import type {
  Sale,
  CreateSaleRequest,
  UpdateSaleRequest,
  SalesFilters,
} from '../services/interfaces/IApiService'

export interface UseSalesReturn {
  sales: Ref<Sale[]>
  currentSale: Ref<Sale | null>
  isLoading: Ref<boolean>
  error: Ref<string | null>
  pagination: Ref<{
    page: number
    pageSize: number
    pageCount: number
    total: number
  }>

  salesCount: Ref<number>
  totalAmount: Ref<number>
  totalCommission: Ref<number>
  averageSaleAmount: Ref<number>

  loadSales: (filters?: SalesFilters) => Promise<void>
  loadSale: (id: number) => Promise<void>
  createSale: (data: CreateSaleRequest) => Promise<Sale>
  updateSale: (id: number, data: UpdateSaleRequest) => Promise<Sale>
  deleteSale: (id: number) => Promise<void>
  getSalesByPeriod: (startDate: string, endDate: string) => Promise<Sale[]>
  getSalesBySeller: (sellerId: number, filters?: SalesFilters) => Promise<Sale[]>
  getSellerStats: (sellerId: number) => {
    salesCount: number
    totalAmount: number
    commission: number
  }
  clearError: () => void
  clearCache: () => void
}

export function useSales(): UseSalesReturn {
  const sales = ref<Sale[]>([])
  const currentSale = ref<Sale | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const saleRepository = repositoryFactory.getSaleRepository()

  const salesCount = computed(() => sales.value.length)

  const totalAmount = computed(() => sales.value.reduce((sum, sale) => sum + sale.amount, 0))

  const totalCommission = computed(() =>
    sales.value.reduce((sum, sale) => sum + sale.commission_amount, 0),
  )

  const averageSaleAmount = computed(() =>
    salesCount.value > 0 ? totalAmount.value / salesCount.value : 0,
  )

  const clearError = () => {
    error.value = null
  }

  const handleError = (err: unknown, defaultMessage: string) => {
    error.value = err instanceof Error ? err.message : defaultMessage
    console.error(defaultMessage, err)
  }

  const pagination = ref({
    page: 1,
    pageSize: 15,
    pageCount: 1,
    total: 0,
  })

  const loadSales = async (filters?: SalesFilters): Promise<void> => {
    try {
      isLoading.value = true
      clearError()

      const paginationParams = {
        page: filters?.page || pagination.value.page,
        per_page: filters?.per_page || pagination.value.pageSize,
      }

      const allFilters = { ...filters, ...paginationParams }

      const result = await saleRepository.getAll(allFilters as Record<string, unknown>)

      sales.value = result.items

      if (result.pagination) {
        pagination.value = {
          page: result.pagination.page,
          pageSize: result.pagination.pageSize,
          pageCount: result.pagination.pageCount,
          total: result.pagination.total,
        }
      }
    } catch (err) {
      handleError(err, 'Erro ao carregar vendas')
    } finally {
      isLoading.value = false
    }
  }

  const loadSale = async (id: number): Promise<void> => {
    try {
      isLoading.value = true
      clearError()

      currentSale.value = await saleRepository.getById(id)
    } catch (err) {
      handleError(err, 'Erro ao carregar venda')
    } finally {
      isLoading.value = false
    }
  }

  const createSale = async (data: CreateSaleRequest): Promise<Sale> => {
    try {
      isLoading.value = true
      clearError()

      const sale = await saleRepository.create(data)

      sales.value.unshift(sale)

      return sale
    } catch (err) {
      handleError(err, 'Erro ao criar venda')
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const updateSale = async (id: number, data: UpdateSaleRequest): Promise<Sale> => {
    try {
      isLoading.value = true
      clearError()

      const sale = await saleRepository.update(id, data)

      const index = sales.value.findIndex((s) => s.id === id)
      if (index !== -1) {
        sales.value[index] = sale
      }

      if (currentSale.value?.id === id) {
        currentSale.value = sale
      }

      return sale
    } catch (err) {
      handleError(err, 'Erro ao atualizar venda')
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const deleteSale = async (id: number): Promise<void> => {
    try {
      isLoading.value = true
      clearError()

      await saleRepository.delete(id)

      sales.value = sales.value.filter((s) => s.id !== id)

      if (currentSale.value?.id === id) {
        currentSale.value = null
      }
    } catch (err) {
      handleError(err, 'Erro ao excluir venda')
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const getSalesByPeriod = async (startDate: string, endDate: string): Promise<Sale[]> => {
    try {
      clearError()
      const result = await saleRepository.getSalesByPeriod(startDate, endDate)
      return result.items
    } catch (err) {
      handleError(err, 'Erro ao buscar vendas por período')
      return []
    }
  }

  const getSalesBySeller = async (sellerId: number, filters?: SalesFilters): Promise<Sale[]> => {
    try {
      clearError()
      return await saleRepository.getSalesBySeller(sellerId, filters)
    } catch (err) {
      handleError(err, 'Erro ao buscar vendas do vendedor')
      return []
    }
  }

  const clearCache = (): void => {
    saleRepository.clearCache()
  }

  const getSellerStats = (sellerId: number) => {
    const sellerSales = sales.value.filter((sale) => sale.seller_id === sellerId)

    const sellerSalesCount = sellerSales.length
    const sellerTotalAmount = sellerSales.reduce((sum, sale) => sum + sale.amount, 0)
    const sellerCommission = sellerSales.reduce((sum, sale) => sum + sale.commission_amount, 0)

    return {
      salesCount: sellerSalesCount,
      totalAmount: sellerTotalAmount,
      commission: sellerCommission,
    }
  }

  return {
    sales,
    currentSale,
    isLoading,
    error,
    pagination,

    salesCount,
    totalAmount,
    totalCommission,
    averageSaleAmount,

    loadSales,
    loadSale,
    createSale,
    updateSale,
    deleteSale,
    getSalesByPeriod,
    getSalesBySeller,
    getSellerStats,
    clearError,
    clearCache,
  }
}
