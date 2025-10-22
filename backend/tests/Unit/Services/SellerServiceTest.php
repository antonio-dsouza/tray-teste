<?php

namespace Tests\Unit\Services;

use App\DTOs\Sellers\CreateSellerData;
use App\Exceptions\Sellers\DuplicateSellerEmailException;
use App\Exceptions\Sellers\SellerNotFoundException;
use App\Jobs\SendDailyAdminSummaryJob;
use App\Jobs\SendDailySellerCommissionJob;
use App\Models\Seller;
use App\Repositories\Contracts\Sellers\SellerRepositoryInterface;
use App\Services\Sellers\SellerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SellerServiceTest extends TestCase
{
    use RefreshDatabase;

    private SellerService $sellerService;
    private $sellerRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sellerRepository = $this->createMock(SellerRepositoryInterface::class);
        $this->sellerService = new SellerService($this->sellerRepository);

        Cache::flush();
    }

    public function test_create_seller_with_valid_data_returns_seller(): void
    {
        $sellerData = CreateSellerData::fromArray([
            'name' => 'John Seller',
            'email' => 'john@seller.com'
        ]);

        $expectedSeller = Seller::factory()->make([
            'name' => 'John Seller',
            'email' => 'john@seller.com'
        ]);

        $this->sellerRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with('john@seller.com')
            ->willReturn(null);

        $this->sellerRepository
            ->expects($this->once())
            ->method('create')
            ->with($sellerData->toArray())
            ->willReturn($expectedSeller);

        $result = $this->sellerService->create($sellerData);

        $this->assertEquals($expectedSeller, $result);
    }

    public function test_create_seller_with_duplicate_email_throws_exception(): void
    {
        $sellerData = CreateSellerData::fromArray([
            'name' => 'John Seller',
            'email' => 'existing@seller.com'
        ]);

        $existingSeller = Seller::factory()->make(['email' => 'existing@seller.com']);

        $this->sellerRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with('existing@seller.com')
            ->willReturn($existingSeller);

        $this->expectException(DuplicateSellerEmailException::class);

        $this->sellerService->create($sellerData);
    }

    public function test_create_seller_works_correctly(): void
    {
        $sellerData = CreateSellerData::fromArray([
            'name' => 'John Seller',
            'email' => 'john@seller.com'
        ]);

        $realService = app(\App\Services\Sellers\SellerService::class);
        $result = $realService->create($sellerData);

        $this->assertInstanceOf(\App\Models\Seller::class, $result);
        $this->assertEquals('John Seller', $result->name);
        $this->assertEquals('john@seller.com', $result->email);
    }
    public function test_find_all_returns_paginated_sellers(): void
    {
        $sellers = collect([
            Seller::factory()->make(),
            Seller::factory()->make(),
        ]);

        $paginatedResult = new LengthAwarePaginator(
            $sellers,
            2,
            20,
            1
        );

        $this->sellerRepository
            ->expects($this->once())
            ->method('findAll')
            ->with(20, 1, ['sales'])
            ->willReturn($paginatedResult);

        $result = $this->sellerService->findAll(20, 1);

        $this->assertEquals($paginatedResult, $result);
    }

    public function test_find_all_uses_cache(): void
    {
        $sellers = collect([Seller::factory()->make()]);
        $paginatedResult = new LengthAwarePaginator($sellers, 1, 20, 1);

        $this->sellerRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($paginatedResult);

        $result1 = $this->sellerService->findAll();

        $result2 = $this->sellerService->findAll();

        $this->assertEquals($result1, $result2);
    }

    public function test_resend_commission_with_valid_seller_dispatches_job(): void
    {
        Queue::fake();

        $seller = Seller::factory()->make(['id' => 1, 'name' => 'John Seller']);
        $date = now()->format('Y-m-d');

        $this->sellerRepository
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($seller);

        $result = $this->sellerService->resendCommission(1, $date);

        Queue::assertPushed(SendDailySellerCommissionJob::class, function ($job) use ($date) {
            return $job->sellerId === 1 && $job->date === $date;
        });

        $this->assertEquals([
            'seller_id' => 1,
            'seller_name' => 'John Seller',
            'date' => $date,
            'message' => 'Email de comissão enfileirado com sucesso'
        ], $result);
    }

    public function test_resend_commission_with_nonexistent_seller_throws_exception(): void
    {
        $this->sellerRepository
            ->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(SellerNotFoundException::class);

        $this->sellerService->resendCommission(999);
    }

    public function test_resend_commission_uses_current_date_when_not_provided(): void
    {
        Queue::fake();

        $seller = Seller::factory()->make(['id' => 1, 'name' => 'John Seller']);
        $currentDate = now()->toDateString();

        $this->sellerRepository
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($seller);

        $result = $this->sellerService->resendCommission(1);

        Queue::assertPushed(SendDailySellerCommissionJob::class, function ($job) use ($currentDate) {
            return $job->sellerId === 1 && $job->date === $currentDate;
        });

        $this->assertEquals($currentDate, $result['date']);
    }

    public function test_run_daily_mails_dispatches_jobs_for_all_sellers(): void
    {
        Queue::fake();

        $sellerIds = [1, 2, 3];
        $date = now()->format('Y-m-d');
        $adminEmail = 'admin@example.com';

        config(['mail.admin_email' => $adminEmail]);

        $this->sellerRepository
            ->expects($this->once())
            ->method('getAllIds')
            ->willReturn($sellerIds);

        $result = $this->sellerService->runDailyMails($date);

        Queue::assertPushed(SendDailySellerCommissionJob::class, 3);

        Queue::assertPushed(SendDailyAdminSummaryJob::class, function ($job) use ($date, $adminEmail) {
            return $job->date === $date && $job->adminEmail === $adminEmail;
        });

        $this->assertEquals([
            'date' => $date,
            'sellers_count' => 3,
            'admin_email' => $adminEmail,
            'message' => 'Emails diários enfileirados com sucesso'
        ], $result);
    }

    public function test_run_daily_mails_uses_current_date_when_not_provided(): void
    {
        Queue::fake();

        $currentDate = now()->toDateString();

        $this->sellerRepository
            ->expects($this->once())
            ->method('getAllIds')
            ->willReturn([]);

        $result = $this->sellerService->runDailyMails();

        $this->assertEquals($currentDate, $result['date']);
    }

    public function test_run_daily_mails_handles_missing_admin_email(): void
    {
        Queue::fake();

        config(['mail.admin_email' => null]);

        $this->sellerRepository
            ->expects($this->once())
            ->method('getAllIds')
            ->willReturn([1, 2]);

        $result = $this->sellerService->runDailyMails();

        Queue::assertPushed(SendDailySellerCommissionJob::class, 2);
        Queue::assertNotPushed(SendDailyAdminSummaryJob::class);

        $this->assertNull($result['admin_email']);
    }
}
