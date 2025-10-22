<?php

namespace App\Services\Sellers;

use App\DTOs\Sellers\CreateSellerData;
use App\Exceptions\Sellers\DuplicateSellerEmailException;
use App\Exceptions\Sellers\SellerNotFoundException;
use App\Jobs\SendDailyAdminSummaryJob;
use App\Jobs\SendDailySellerCommissionJob;
use App\Models\Seller;
use App\Repositories\Contracts\Sellers\SellerRepositoryInterface;
use App\Services\Sellers\Contracts\SellerServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class SellerService implements SellerServiceInterface
{
    public function __construct(
        private readonly SellerRepositoryInterface $sellerRepository
    ) {}

    public function create(CreateSellerData $data): Seller
    {
        $seller = $this->sellerRepository->findByEmail($data->email);

        if ($seller) {
            throw new DuplicateSellerEmailException($data->email);
        }

        $seller = $this->sellerRepository->create($data->toArray());

        Cache::tags(['sellers'])->flush();

        return $seller;
    }

    public function findAll(int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        return Cache::tags(['sellers'])->remember(
            'sellers:all:page:' . $page . ':perPage:' . $perPage,
            now()->addMinutes(10),
            fn() => $this->sellerRepository->findAll($perPage, $page, ['sales'])
        );
    }

    public function resendCommission(int $sellerId, ?string $date = null): array
    {
        $seller = $this->sellerRepository->findById($sellerId);

        if (!$seller) {
            throw new SellerNotFoundException($sellerId);
        }

        $date = $date ?? now()->toDateString();

        SendDailySellerCommissionJob::dispatch($sellerId, $date);

        return [
            'seller_id' => $sellerId,
            'seller_name' => $seller->name,
            'date' => $date,
            'message' => 'Email de comissão enfileirado com sucesso'
        ];
    }

    public function runDailyMails(?string $date = null): array
    {
        $date = $date ?? now()->toDateString();

        $sellerIds = $this->sellerRepository->getAllIds();

        foreach ($sellerIds as $sellerId) {
            SendDailySellerCommissionJob::dispatch($sellerId, $date);
        }

        $adminEmail = config('mail.admin_email');
        if ($adminEmail) {
            SendDailyAdminSummaryJob::dispatch($date, $adminEmail);
        }

        return [
            'date' => $date,
            'sellers_count' => count($sellerIds),
            'admin_email' => $adminEmail,
            'message' => 'Emails diários enfileirados com sucesso'
        ];
    }
}
