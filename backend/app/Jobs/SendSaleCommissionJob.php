<?php

namespace App\Jobs;

use App\Repositories\Contracts\Sales\SaleRepositoryInterface;
use App\Services\Emails\Contracts\EmailServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSaleCommissionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        public readonly int $saleId
    ) {}

    public function handle(
        SaleRepositoryInterface $saleRepository,
        EmailServiceInterface $emailService
    ): void {
        try {
            Log::info('Starting sale commission email job', [
                'sale_id' => $this->saleId
            ]);

            $sale = $saleRepository->findById($this->saleId, ['seller']);

            if (!$sale) {
                Log::warning('Sale not found for commission email', [
                    'sale_id' => $this->saleId
                ]);
                $this->delete();
                return;
            }

            $emailSent = $emailService->sendSaleCommissionToSeller($sale);

            if (!$emailSent) {
                throw new \RuntimeException('Failed to queue sale commission email');
            }

            Log::info('Sale commission email job completed successfully', [
                'sale_id' => $this->saleId,
                'seller_id' => $sale->seller_id
            ]);
        } catch (\Exception $exception) {
            Log::error('Sale commission email job failed', [
                'sale_id' => $this->saleId,
                'error' => $exception->getMessage(),
                'attempt' => $this->attempts(),
                'trace' => $exception->getTraceAsString()
            ]);

            throw $exception;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Sale commission email job failed permanently', [
            'sale_id' => $this->saleId,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);
    }
}
