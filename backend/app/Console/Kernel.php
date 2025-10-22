<?php

namespace App\Console;

use App\Jobs\SendDailyAdminSummaryJob;
use App\Jobs\SendDailySellerCommissionJob;
use App\Models\Seller;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            $date = now()->toDateString();

            Seller::query()->pluck('id')->each(function (int $id) use ($date) {
                SendDailySellerCommissionJob::dispatch($id, $date);
            });

            $adminEmail = config('mail.admin_email', 'admin@example.com');
            SendDailyAdminSummaryJob::dispatch($date, $adminEmail);
        })->dailyAt('23:59');
    }
}
