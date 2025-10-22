<?php

namespace Tests\Unit\Services;

use App\Repositories\Contracts\Sales\SaleRepositoryInterface;
use App\Repositories\Contracts\Sellers\SellerRepositoryInterface;
use App\Services\Dashboard\DashboardService;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\TestCase;
use Mockery;

class DashboardServiceTest extends TestCase
{
    protected $saleRepository;
    protected $sellerRepository;
    protected $dashboardService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleRepository = Mockery::mock(SaleRepositoryInterface::class);
        $this->sellerRepository = Mockery::mock(SellerRepositoryInterface::class);
        $this->dashboardService = new DashboardService(
            $this->saleRepository,
            $this->sellerRepository
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_can_get_general_stats()
    {
        $this->sellerRepository->shouldReceive('count')->once()->andReturn(10);
        $this->saleRepository->shouldReceive('count')->once()->andReturn(50);
        $this->saleRepository->shouldReceive('sumAmount')->once()->andReturn(10000.0);
        $this->saleRepository->shouldReceive('sumCommissions')->once()->andReturn(850.0);

        $result = $this->dashboardService->getGeneralStats();

        $this->assertEquals(10, $result['total_sellers']);
        $this->assertEquals(50, $result['total_sales']);
        $this->assertEquals(10000.0, $result['total_sales_amount']);
        $this->assertEquals('R$ 10.000,00', $result['formatted_total_sales_amount']);
        $this->assertEquals(850.0, $result['total_commissions']);
        $this->assertEquals('R$ 850,00', $result['formatted_total_commissions']);
        $this->assertEquals(200.0, $result['average_sale_amount']);
        $this->assertEquals('R$ 200,00', $result['formatted_average_sale_amount']);
    }

    public function test_it_can_get_today_stats()
    {
        $this->saleRepository->shouldReceive('countByDate')->once()->andReturn(5);
        $this->saleRepository->shouldReceive('sumAmountByDate')->once()->andReturn(1000.0);

        $result = $this->dashboardService->getTodayStats();

        $this->assertEquals(5, $result['sales_count']);
        $this->assertEquals(1000.0, $result['sales_amount']);
        $this->assertEquals('R$ 1.000,00', $result['formatted_sales_amount']);
    }

    public function test_it_can_get_this_month_stats()
    {
        $this->saleRepository->shouldReceive('countFromDate')->once()->andReturn(25);
        $this->saleRepository->shouldReceive('sumAmountFromDate')->once()->andReturn(5000.0);

        $result = $this->dashboardService->getThisMonthStats();

        $this->assertEquals(25, $result['sales_count']);
        $this->assertEquals(5000.0, $result['sales_amount']);
        $this->assertEquals('R$ 5.000,00', $result['formatted_sales_amount']);
    }

    public function test_it_can_get_top_sellers()
    {
        $sellers = new Collection([
            (object) [
                'id' => 1,
                'name' => 'João Silva',
                'email' => 'joao@example.com',
                'sales_sum_amount' => 5000.0,
                'sales_count' => 10,
                'total_commission' => 425.0,
            ],
            (object) [
                'id' => 2,
                'name' => 'Maria Santos',
                'email' => 'maria@example.com',
                'sales_sum_amount' => 3000.0,
                'sales_count' => 8,
                'total_commission' => 255.0,
            ],
        ]);

        $this->sellerRepository->shouldReceive('getTopSellersBySalesAmount')->with(5)->once()->andReturn($sellers);

        $result = $this->dashboardService->getTopSellers();

        $this->assertCount(2, $result);
        $this->assertEquals('João Silva', $result[0]['name']);
        $this->assertEquals('R$ 5.000,00', $result[0]['formatted_total_amount']);
        $this->assertEquals('R$ 425,00', $result[0]['formatted_total_commission']);
        $this->assertEquals('Maria Santos', $result[1]['name']);
        $this->assertEquals('R$ 3.000,00', $result[1]['formatted_total_amount']);
    }

    public function test_it_can_get_sales_by_month()
    {
        $this->saleRepository->shouldReceive('countBetweenDates')
            ->twice()
            ->andReturn(10, 15);

        $this->saleRepository->shouldReceive('sumAmountBetweenDates')
            ->twice()
            ->andReturn(2000.0, 3000.0);

        $result = $this->dashboardService->getSalesByMonth(2);

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('month', $result[0]);
        $this->assertArrayHasKey('month_name', $result[0]);
        $this->assertArrayHasKey('sales_count', $result[0]);
        $this->assertArrayHasKey('sales_amount', $result[0]);
        $this->assertArrayHasKey('formatted_amount', $result[0]);
    }

    public function test_it_handles_zero_sales_in_general_stats()
    {
        $this->sellerRepository->shouldReceive('count')->once()->andReturn(5);
        $this->saleRepository->shouldReceive('count')->once()->andReturn(0);
        $this->saleRepository->shouldReceive('sumAmount')->once()->andReturn(0.0);
        $this->saleRepository->shouldReceive('sumCommissions')->once()->andReturn(0.0);

        $result = $this->dashboardService->getGeneralStats();

        $this->assertEquals(0, $result['total_sales']);
        $this->assertEquals(0.0, $result['average_sale_amount']);
        $this->assertEquals('R$ 0,00', $result['formatted_average_sale_amount']);
    }
}
