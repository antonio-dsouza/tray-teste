<?php

namespace Tests\Unit\Services;

use App\Models\Seller;
use App\Repositories\Contracts\Sales\SaleRepositoryInterface;
use App\Repositories\Contracts\Sellers\SellerRepositoryInterface;
use App\Services\Commissions\Contracts\CommissionCalculatorInterface;
use App\Services\Reports\ReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ReportServiceTest extends TestCase
{
    use RefreshDatabase;

    private ReportService $reportService;
    private $saleRepository;
    private $sellerRepository;
    private $commissionCalculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleRepository = $this->createMock(SaleRepositoryInterface::class);
        $this->sellerRepository = $this->createMock(SellerRepositoryInterface::class);
        $this->commissionCalculator = $this->createMock(CommissionCalculatorInterface::class);

        $this->reportService = new ReportService(
            $this->saleRepository,
            $this->sellerRepository,
            $this->commissionCalculator
        );
    }

    public function test_get_daily_sales_summary_works_correctly(): void
    {
        $date = '2024-01-15';
        $result = $this->reportService->getDailySalesSummary($date);

        $this->assertArrayHasKey('date', $result);
        $this->assertArrayHasKey('total_sales', $result);
        $this->assertArrayHasKey('total_amount', $result);
        $this->assertArrayHasKey('average_sale', $result);
        $this->assertArrayHasKey('sales', $result);
        $this->assertEquals($date, $result['date']);
    }

    public function test_get_daily_sales_summary_with_no_sales(): void
    {
        $date = '2024-01-15';
        $emptySales = \Illuminate\Database\Eloquent\Collection::make();

        $this->saleRepository
            ->expects($this->once())
            ->method('getSalesByDateRange')
            ->willReturn($emptySales);

        $result = $this->reportService->getDailySalesSummary($date);

        $expectedResult = [
            'date' => $date,
            'total_sales' => 0,
            'total_amount' => 0,
            'average_sale' => 0,
            'sales' => $emptySales
        ];

        $this->assertEquals($expectedResult, $result);
    }

    public function test_get_daily_sales_summary_handles_invalid_date(): void
    {
        $invalidDate = 'invalid-date';

        $this->expectException(\Exception::class);

        $this->reportService->getDailySalesSummary($invalidDate);

        Log::assertLogged('error', function ($message, $context) use ($invalidDate) {
            return $message === 'Failed to calculate daily sales summary' &&
                $context['date'] === $invalidDate &&
                isset($context['error']);
        });
    }

    public function test_get_seller_daily_summary_returns_correct_data(): void
    {
        $sellerId = 1;
        $date = '2024-01-15';

        $expectedStartDate = Carbon::parse($date)->startOfDay();
        $expectedEndDate = Carbon::parse($date)->copy()->endOfDay();

        $sellerData = Seller::factory()->make([
            'id' => $sellerId,
            'name' => 'John Seller',
            'email' => 'john@seller.com'
        ]);
        $salesData = \Illuminate\Database\Eloquent\Collection::make([
            (object)['amount' => '100.00', 'commission_amount' => '8.50'],
            (object)['amount' => '250.00', 'commission_amount' => '21.25'],
            (object)['amount' => '75.00', 'commission_amount' => '6.38']
        ]);

        $this->sellerRepository
            ->expects($this->once())
            ->method('findById')
            ->with($sellerId)
            ->willReturn($sellerData);

        $this->saleRepository
            ->expects($this->once())
            ->method('getSellerSalesByDateRange')
            ->with(
                $sellerId,
                $this->equalTo($expectedStartDate),
                $this->equalTo($expectedEndDate)
            )
            ->willReturn($salesData);

        $this->commissionCalculator
            ->expects($this->once())
            ->method('totalCommission')
            ->with($salesData)
            ->willReturn(106.25);

        $result = $this->reportService->getSellerDailySummary($sellerId, $date);

        $expectedResult = [
            'seller' => $sellerData,
            'date' => $date,
            'count' => 3,
            'total_amount' => 425.00,
            'commission' => 106.25,
            'sales' => $salesData
        ];

        $this->assertEquals($expectedResult, $result);
    }

    public function test_get_seller_daily_summary_with_nonexistent_seller(): void
    {
        $sellerId = 999;
        $date = '2024-01-15';

        $this->sellerRepository
            ->expects($this->once())
            ->method('findById')
            ->with($sellerId)
            ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Seller with ID 999 not found');

        $this->reportService->getSellerDailySummary($sellerId, $date);
    }

    public function test_get_seller_daily_summary_handles_exception(): void
    {
        $sellerId = 1;
        $date = 'invalid-date';

        $this->expectException(\Exception::class);

        $this->reportService->getSellerDailySummary($sellerId, $date);

        Log::assertLogged('error', function ($message, $context) use ($sellerId, $date) {
            return $message === 'Failed to calculate seller daily summary' &&
                $context['seller_id'] === $sellerId &&
                $context['date'] === $date &&
                isset($context['error']);
        });
    }

    public function test_repository_exceptions_are_logged_and_rethrown(): void
    {
        $date = '2024-01-15';
        $exception = new \Exception('Database connection failed');

        $this->saleRepository
            ->expects($this->once())
            ->method('getSalesByDateRange')
            ->willThrowException($exception);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database connection failed');

        $this->reportService->getDailySalesSummary($date);

        Log::assertLogged('error', function ($message, $context) use ($date) {
            return $message === 'Failed to calculate daily sales summary' &&
                $context['date'] === $date &&
                $context['error'] === 'Database connection failed';
        });
    }
}
