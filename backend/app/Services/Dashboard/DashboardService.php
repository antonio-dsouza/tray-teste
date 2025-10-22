<?php

namespace App\Services\Dashboard;

use App\Repositories\Contracts\Sales\SaleRepositoryInterface;
use App\Repositories\Contracts\Sellers\SellerRepositoryInterface;
use App\Services\Dashboard\Contracts\DashboardServiceInterface;
use Carbon\Carbon;

class DashboardService implements DashboardServiceInterface
{
    public function __construct(
        private SaleRepositoryInterface $saleRepository,
        private SellerRepositoryInterface $sellerRepository
    ) {}

    public function getGeneralStats(): array
    {
        $totalSellers = $this->sellerRepository->count();
        $totalSales = $this->saleRepository->count();
        $totalSalesAmount = $this->saleRepository->sumAmount();
        $totalCommissions = $this->saleRepository->sumCommissions();

        return [
            'total_sellers' => $totalSellers,
            'total_sales' => $totalSales,
            'total_sales_amount' => $totalSalesAmount,
            'formatted_total_sales_amount' => $this->formatCurrency($totalSalesAmount),
            'total_commissions' => $totalCommissions,
            'formatted_total_commissions' => $this->formatCurrency($totalCommissions),
            'average_sale_amount' => $totalSales > 0 ? $totalSalesAmount / $totalSales : 0,
            'formatted_average_sale_amount' => $totalSales > 0
                ? $this->formatCurrency($totalSalesAmount / $totalSales)
                : 'R$ 0,00',
        ];
    }

    public function getTodayStats(): array
    {
        $today = Carbon::today();
        $salesToday = $this->saleRepository->countByDate($today);
        $salesAmountToday = $this->saleRepository->sumAmountByDate($today);

        return [
            'sales_count' => $salesToday,
            'sales_amount' => $salesAmountToday,
            'formatted_sales_amount' => $this->formatCurrency($salesAmountToday),
        ];
    }

    public function getThisMonthStats(): array
    {
        $thisMonth = Carbon::now()->startOfMonth();
        $salesThisMonth = $this->saleRepository->countFromDate($thisMonth);
        $salesAmountThisMonth = $this->saleRepository->sumAmountFromDate($thisMonth);

        return [
            'sales_count' => $salesThisMonth,
            'sales_amount' => $salesAmountThisMonth,
            'formatted_sales_amount' => $this->formatCurrency($salesAmountThisMonth),
        ];
    }

    public function getTopSellers(int $limit = 5): array
    {
        $topSellers = $this->sellerRepository->getTopSellersBySalesAmount($limit);

        return $topSellers->map(function ($seller) {
            $totalAmount = $seller->sales_sum_amount ?? 0;
            $salesCount = $seller->sales_count ?? 0;
            $totalCommission = $seller->total_commission ?? 0;

            return [
                'id' => $seller->id,
                'name' => $seller->name,
                'email' => $seller->email,
                'sales_count' => $salesCount,
                'total_amount' => $totalAmount,
                'formatted_total_amount' => $this->formatCurrency($totalAmount),
                'total_commission' => $totalCommission,
                'formatted_total_commission' => $this->formatCurrency($totalCommission),
            ];
        })->toArray();
    }

    public function getSalesByMonth(int $months = 6): array
    {
        $salesByMonth = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();

            $monthSales = $this->saleRepository->countBetweenDates($monthStart, $monthEnd);
            $monthAmount = $this->saleRepository->sumAmountBetweenDates($monthStart, $monthEnd);

            $salesByMonth[] = [
                'month' => $monthStart->format('Y-m'),
                'month_name' => $monthStart->locale('pt_BR')->format('M Y'),
                'sales_count' => $monthSales,
                'sales_amount' => $monthAmount,
                'formatted_amount' => $this->formatCurrency($monthAmount),
            ];
        }

        return $salesByMonth;
    }

    public function getAllStats(): array
    {
        return [
            'general' => $this->getGeneralStats(),
            'today' => $this->getTodayStats(),
            'this_month' => $this->getThisMonthStats(),
            'top_sellers' => $this->getTopSellers(),
            'sales_by_month' => $this->getSalesByMonth(),
        ];
    }

    private function formatCurrency(float $value): string
    {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }
}
