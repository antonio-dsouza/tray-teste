<?php

namespace App\Providers;

use App\Models\Sale;
use App\Observers\SaleObserver;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\Sales\SaleRepositoryInterface;
use App\Repositories\Contracts\Sellers\SellerRepositoryInterface;
use App\Repositories\Contracts\Users\UserRepositoryInterface;
use App\Repositories\Implementations\Eloquent\BaseRepository;
use App\Repositories\Implementations\Eloquent\Sales\SaleRepository;
use App\Repositories\Implementations\Eloquent\Sellers\SellerRepository;
use App\Repositories\Implementations\Eloquent\Users\UserRepository;
use App\Services\Auth\AuthService;
use App\Services\Auth\Contracts\AuthServiceInterface;
use App\Services\Commissions\Contracts\CommissionCalculatorInterface;
use App\Services\Commissions\DefaultCommissionCalculator;
use App\Services\Dashboard\Contracts\DashboardServiceInterface;
use App\Services\Dashboard\DashboardService;
use App\Services\Emails\Contracts\EmailServiceInterface;
use App\Services\Emails\EmailService;
use App\Services\Reports\Contracts\ReportServiceInterface;
use App\Services\Reports\ReportService;
use App\Services\Sales\Contracts\SaleServiceInterface;
use App\Services\Sales\SaleService;
use App\Services\Sellers\SellerService;
use App\Services\Sellers\Contracts\SellerServiceInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SellerRepositoryInterface::class, SellerRepository::class);
        $this->app->bind(SaleRepositoryInterface::class, SaleRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(BaseRepositoryInterface::class, BaseRepository::class);

        $this->app->bind(SellerServiceInterface::class, SellerService::class);
        $this->app->bind(SaleServiceInterface::class, SaleService::class);
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(CommissionCalculatorInterface::class, DefaultCommissionCalculator::class);
        $this->app->bind(EmailServiceInterface::class, EmailService::class);
        $this->app->bind(ReportServiceInterface::class, ReportService::class);
        $this->app->bind(DashboardServiceInterface::class, DashboardService::class);
    }

    public function boot(): void
    {
        Sale::observe(SaleObserver::class);
    }
}
