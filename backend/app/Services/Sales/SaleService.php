<?php

namespace App\Services\Sales;

use App\Constants\CommissionRates;
use App\DTOs\Sales\CreateSaleData;
use App\Exceptions\Sales\InvalidCommissionDataException;
use App\Exceptions\Sales\SaleNotFoundException;
use App\Exceptions\Sellers\SellerNotFoundException;
use App\Jobs\SendSaleCommissionJob;
use App\Models\Sale;
use App\Repositories\Contracts\Sales\SaleRepositoryInterface;
use App\Repositories\Contracts\Sellers\SellerRepositoryInterface;
use App\Services\Commissions\Contracts\CommissionCalculatorInterface;
use App\Services\Sales\Contracts\SaleServiceInterface;
use App\Services\Sellers\Contracts\SellerServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class SaleService implements SaleServiceInterface
{
    public function __construct(
        private readonly SaleRepositoryInterface $saleRepository,
        private readonly SellerRepositoryInterface $sellerRepository,
        private readonly SellerServiceInterface $sellerService,
        private readonly CommissionCalculatorInterface $commissionCalculator
    ) {}

    public function create(CreateSaleData $data): Sale
    {
        if ($data->amount <= 0) {
            throw new InvalidCommissionDataException('O valor da venda deve ser maior que zero.');
        }

        $seller = $this->sellerRepository->findById($data->seller_id);

        if (!$seller) {
            throw new SellerNotFoundException($data->seller_id);
        }

        $commissionAmount = $this->commissionCalculator->calculateCommission($data->amount);

        $saleData = array_merge($data->toArray(), [
            'commission_amount' => $commissionAmount
        ]);

        $sale = $this->saleRepository->create($saleData);

        Cache::tags(['sales'])->flush();
        Cache::tags(['seller:' . $sale->seller_id])->flush();

        return $sale;
    }

    public function findAll(int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        $cacheKey = "sales:all:page:{$page}:perPage:{$perPage}";

        return Cache::tags(['sales'])->remember(
            $cacheKey,
            now()->addMinutes(5),
            fn() => $this->saleRepository->findAll($perPage, $page)
        );
    }

    public function findAllBySeller(int $sellerId, ?string $date = null, int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        $seller = $this->sellerRepository->findById($sellerId);

        if (!$seller) {
            throw new SellerNotFoundException($sellerId);
        }

        $cacheKey = 'sales:seller:' . $sellerId . ($date ? ':date:' . $date : ':all') . ":page:{$page}";

        return Cache::tags(['seller:' . $sellerId])->remember(
            $cacheKey,
            now()->addMinutes(5),
            fn() => $this->saleRepository->findAllBySeller($sellerId, $date, $perPage, $page, ['seller'])
        );
    }

    public function getDailySummaryForSeller(int $sellerId, string $date): array
    {
        $seller = $this->sellerRepository->findById($sellerId);

        if (!$seller) {
            throw new SellerNotFoundException($sellerId);
        }

        $sales = $this->saleRepository->findBySeller($sellerId, $date);

        $count = $sales->count();
        $totalAmount = (float) $sales->sum('amount');
        $commission = $this->commissionCalculator->totalCommission($sales);

        return [
            'seller' => $seller,
            'date' => $date,
            'count' => $count,
            'total_amount' => $totalAmount,
            'commission' => $commission,
        ];
    }

    public function resendSaleCommission(int $saleId): void
    {
        $sale = $this->saleRepository->findById($saleId, ['seller']);

        if (!$sale) {
            throw new SaleNotFoundException($saleId);
        }

        SendSaleCommissionJob::dispatch($saleId);
    }
}
