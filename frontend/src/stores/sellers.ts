import { defineStore } from 'pinia'
import { useSellers } from '../composables/useSellers'

export const useSellersStore = defineStore('sellers', () => {
  const sellers = useSellers()

  const getSellerById = (id: number) => {
    return sellers.sellers.value.find((seller) => seller.id === id) || null
  }

  return {
    sellers: sellers.sellers,
    currentSeller: sellers.currentSeller,
    isLoading: sellers.isLoading,
    error: sellers.error,
    pagination: sellers.pagination,

    sellersCount: sellers.sellersCount,
    activeSellers: sellers.activeSellers,

    loadSellers: sellers.loadSellers,
    loadSeller: sellers.loadSeller,
    createSeller: sellers.createSeller,
    updateSeller: sellers.updateSeller,
    deleteSeller: sellers.deleteSeller,
    getSellerSales: sellers.getSellerSales,
    getCommissionReport: sellers.getCommissionReport,
    getSellerById,
    clearError: sellers.clearError,
    clearCache: sellers.clearCache,
  }
})
