<?php

namespace App\Services\Emails;

use App\Mail\DailyAdminSummaryMail;
use App\Mail\DailySellerCommissionMail;
use App\Mail\SaleCommissionMail;
use App\Models\Sale;
use App\Models\Seller;
use App\Services\Emails\Contracts\EmailServiceInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailService implements EmailServiceInterface
{
    public function sendDailyCommissionToSeller(Seller $seller, array $summaryData): bool
    {
        try {
            Mail::to($seller->email)->queue(
                new DailySellerCommissionMail(
                    $seller,
                    $summaryData['count'],
                    $summaryData['total_amount'],
                    $summaryData['commission'],
                    $summaryData['date']
                )
            );

            Log::info('Daily commission email queued successfully', [
                'seller_id' => $seller->id,
                'seller_email' => $seller->email,
                'date' => $summaryData['date'],
                'commission' => $summaryData['commission']
            ]);

            return true;
        } catch (\Exception $exception) {
            Log::error('Failed to queue daily commission email', [
                'seller_id' => $seller->id,
                'seller_email' => $seller->email,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);

            return false;
        }
    }

    public function sendDailySummaryToAdmin(string $date, array $summaryData): bool
    {
        try {
            $adminEmail = config('mail.admin_email');

            if (!$adminEmail) {
                Log::warning('Admin email not configured, skipping daily summary email', [
                    'date' => $date
                ]);
                return false;
            }

            Mail::to($adminEmail)->queue(
                new DailyAdminSummaryMail(
                    $date,
                    $summaryData['total_sales'],
                    $summaryData['total_amount']
                )
            );

            Log::info('Daily admin summary email queued successfully', [
                'admin_email' => $adminEmail,
                'date' => $date,
                'total_sales' => $summaryData['total_sales'],
                'total_amount' => $summaryData['total_amount']
            ]);

            return true;
        } catch (\Exception $exception) {
            Log::error('Failed to queue daily admin summary email', [
                'date' => $date,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);

            return false;
        }
    }

    public function sendSaleCommissionToSeller(Sale $sale): bool
    {
        try {
            $seller = $sale->seller;

            if (!$seller) {
                Log::warning('Sale does not have a valid seller, skipping commission email', [
                    'sale_id' => $sale->id
                ]);
                return false;
            }

            Mail::to($seller->email)->queue(
                new SaleCommissionMail($sale, $seller)
            );

            Log::info('Sale commission email queued successfully', [
                'sale_id' => $sale->id,
                'seller_id' => $seller->id,
                'seller_email' => $seller->email,
                'amount' => $sale->amount
            ]);

            return true;
        } catch (\Exception $exception) {
            Log::error('Failed to queue sale commission email', [
                'sale_id' => $sale->id,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);

            return false;
        }
    }
}
