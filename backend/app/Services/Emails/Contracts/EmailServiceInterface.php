<?php

namespace App\Services\Emails\Contracts;

use App\Models\Sale;
use App\Models\Seller;

interface EmailServiceInterface
{
    public function sendDailyCommissionToSeller(Seller $seller, array $summaryData): bool;
    public function sendDailySummaryToAdmin(string $date, array $summaryData): bool;
    public function sendSaleCommissionToSeller(Sale $sale): bool;
}
