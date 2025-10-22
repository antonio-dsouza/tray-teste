<?php

namespace App\Services\Reports\Contracts;

interface ReportServiceInterface
{
    public function getDailySalesSummary(string $date): array;
    public function getSellerDailySummary(int $sellerId, string $date): array;
}
