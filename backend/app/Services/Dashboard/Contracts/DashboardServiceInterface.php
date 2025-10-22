<?php

namespace App\Services\Dashboard\Contracts;

interface DashboardServiceInterface
{
    public function getGeneralStats(): array;
    public function getTodayStats(): array;
    public function getThisMonthStats(): array;
    public function getTopSellers(int $limit = 5): array;
    public function getSalesByMonth(int $months = 6): array;
    public function getAllStats(): array;
}
