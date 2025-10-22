import { defineStore } from 'pinia'
import { useSales } from '../composables/useSales'

export const useSalesStore = defineStore('sales', () => {
  const sales = useSales()

  return {
    sales: sales.sales,
    currentSale: sales.currentSale,
    isLoading: sales.isLoading,
    error: sales.error,
    pagination: sales.pagination,

    salesCount: sales.salesCount,
    totalAmount: sales.totalAmount,
    totalCommission: sales.totalCommission,
    averageSaleAmount: sales.averageSaleAmount,

    loadSales: sales.loadSales,
    loadSale: sales.loadSale,
    createSale: sales.createSale,
    updateSale: sales.updateSale,
    deleteSale: sales.deleteSale,
    getSalesByPeriod: sales.getSalesByPeriod,
    getSalesBySeller: sales.getSalesBySeller,
    getSellerStats: sales.getSellerStats,
    clearError: sales.clearError,
    clearCache: sales.clearCache,
  }
})
