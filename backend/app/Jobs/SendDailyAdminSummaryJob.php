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

class SendDailyAdminSummaryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        public readonly string $date,
        public readonly string $adminEmail
    ) {
        $this->onQueue('emails');
    }

    public function handle(
        ReportServiceInterface $reportService,
        EmailServiceInterface $emailService
    ): void {
        try {
            Log::info('Starting daily admin summary job', [
                'date' => $this->date,
                'admin_email' => $this->adminEmail
            ]);

            $summaryData = $reportService->getDailySalesSummary($this->date);

            $emailSent = $emailService->sendDailySummaryToAdmin($this->date, $summaryData);

            if (!$emailSent) {
                throw new \RuntimeException('Failed to queue daily admin summary email');
            }

            Log::info('Daily admin summary job completed successfully', [
                'date' => $this->date,
                'admin_email' => $this->adminEmail,
                'total_sales' => $summaryData['total_sales'],
                'total_amount' => $summaryData['total_amount']
            ]);
        } catch (\Exception $exception) {
            Log::error('Daily admin summary job failed', [
                'date' => $this->date,
                'admin_email' => $this->adminEmail,
                'error' => $exception->getMessage(),
                'attempt' => $this->attempts(),
                'trace' => $exception->getTraceAsString()
            ]);

            throw $exception;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Daily admin summary job failed permanently', [
            'date' => $this->date,
            'admin_email' => $this->adminEmail,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);
    }

    public function backoff(): array
    {
        return [1, 5, 10];
    }

    public function uniqueId(): string
    {
        return $this->date . '_' . md5($this->adminEmail);
    }
}
