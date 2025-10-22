<?php

namespace App\Jobs;

use App\Services\Emails\Contracts\EmailServiceInterface;
use App\Services\Reports\Contracts\ReportServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendDailySellerCommissionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        public readonly int $sellerId,
        public readonly string $date
    ) {}

    public function handle(
        ReportServiceInterface $reportService,
        EmailServiceInterface $emailService
    ): void {
        try {
            Log::info('Starting daily commission email job', [
                'seller_id' => $this->sellerId,
                'date' => $this->date
            ]);

            $summaryData = $reportService->getSellerDailySummary($this->sellerId, $this->date);

            $emailSent = $emailService->sendDailyCommissionToSeller(
                $summaryData['seller'],
                $summaryData
            );

            if (!$emailSent) {
                throw new \RuntimeException('Failed to queue daily commission email');
            }

            Log::info('Daily commission email job completed successfully', [
                'seller_id' => $this->sellerId,
                'date' => $this->date
            ]);
        } catch (\InvalidArgumentException $exception) {
            Log::warning('Seller not found for commission email', [
                'seller_id' => $this->sellerId,
                'date' => $this->date,
                'error' => $exception->getMessage()
            ]);

            $this->delete();
        } catch (\Exception $exception) {
            Log::error('Daily commission email job failed', [
                'seller_id' => $this->sellerId,
                'date' => $this->date,
                'error' => $exception->getMessage(),
                'attempt' => $this->attempts(),
                'trace' => $exception->getTraceAsString()
            ]);

            throw $exception;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Daily commission email job failed permanently', [
            'seller_id' => $this->sellerId,
            'date' => $this->date,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);
    }
}
