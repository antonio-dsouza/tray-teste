<?php

namespace App\Services\Reports;

use App\Repositories\Contracts\Sales\SaleRepositoryInterface;
use App\Repositories\Contracts\Sellers\SellerRepositoryInterface;
use App\Services\Commissions\Contracts\CommissionCalculatorInterface;
use App\Services\Reports\Contracts\ReportServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ReportService implements ReportServiceInterface
{
    public function __construct(
        private readonly SaleRepositoryInterface $saleRepository,
        private readonly SellerRepositoryInterface $sellerRepository,
        private readonly CommissionCalculatorInterface $commissionCalculator
    ) {}

    public function getDailySalesSummary(string $date): array
    {
        try {
            $carbonDate = Carbon::parse($date);

            $sales = $this->saleRepository->getSalesByDateRange(
                $carbonDate->startOfDay(),
                $carbonDate->copy()->endOfDay()
            );

            $totalSales = $sales->count();
            $totalAmount = $sales->sum('amount');

            Log::info('Daily sales summary calculated', [
                'date' => $date,
                'total_sales' => $totalSales,
                'total_amount' => $totalAmount
            ]);

            return [
                'date' => $date,
                'total_sales' => $totalSales,
                'total_amount' => $totalAmount,
                'average_sale' => $totalSales > 0 ? $totalAmount / $totalSales : 0,
                'sales' => $sales
            ];
        } catch (\Exception $exception) {
            Log::error('Failed to calculate daily sales summary', [
                'date' => $date,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);

            throw $exception;
        }
    }

    public function getSellerDailySummary(int $sellerId, string $date): array
    {
        try {
            $seller = $this->sellerRepository->findById($sellerId);

            if (!$seller) {
                throw new \InvalidArgumentException("Seller with ID {$sellerId} not found");
            }

            $carbonDate = Carbon::parse($date);

            $sales = $this->saleRepository->getSellerSalesByDateRange(
                $sellerId,
                $carbonDate->startOfDay(),
                $carbonDate->copy()->endOfDay()
            );

            $totalAmount = $sales->sum('amount');
            $totalCommission = $this->commissionCalculator->totalCommission($sales);

            Log::info('Seller daily summary calculated', [
                'seller_id' => $sellerId,
                'date' => $date,
                'count' => $sales->count(),
                'total_amount' => $totalAmount,
                'commission' => $totalCommission
            ]);

            return [
                'seller' => $seller,
                'date' => $date,
                'count' => $sales->count(),
                'total_amount' => $totalAmount,
                'commission' => $totalCommission,
                'sales' => $sales
            ];
        } catch (\Exception $exception) {
            Log::error('Failed to calculate seller daily summary', [
                'seller_id' => $sellerId,
                'date' => $date,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);

            throw $exception;
        }
    }
}
