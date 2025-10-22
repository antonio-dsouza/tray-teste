<?php

namespace Tests\Unit\Services;

use App\DTOs\Sales\CreateSaleData;
use App\Exceptions\Sales\InvalidCommissionDataException;
use App\Exceptions\Sellers\SellerNotFoundException;
use App\Models\Sale;
use App\Models\Seller;
use App\Services\Sales\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SaleServiceTest extends TestCase
{
    use RefreshDatabase;

    private SaleService $saleService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->saleService = app(SaleService::class);
        Cache::flush();
    }

    public function test_create_sale_with_valid_data(): void
    {
        $seller = Seller::factory()->create();
        $saleData = CreateSaleData::fromArray([
            'seller_id' => $seller->id,
            'amount' => 100.00,
            'sold_at' => now()->toDateTimeString()
        ]);

        $sale = $this->saleService->create($saleData);

        $this->assertInstanceOf(Sale::class, $sale);
        $this->assertEquals($seller->id, $sale->seller_id);
        $this->assertEquals('100.00', $sale->amount);
        $this->assertNotNull($sale->commission_amount);
        $this->assertEquals(8.5, $sale->commission_amount);
    }

    public function test_create_sale_with_zero_amount_throws_exception(): void
    {
        $seller = Seller::factory()->create();
        $saleData = CreateSaleData::fromArray([
            'seller_id' => $seller->id,
            'amount' => 0,
            'sold_at' => now()->toDateTimeString()
        ]);

        $this->expectException(InvalidCommissionDataException::class);
        $this->expectExceptionMessage('O valor da venda deve ser maior que zero.');

        $this->saleService->create($saleData);
    }

    public function test_create_sale_with_negative_amount_throws_exception(): void
    {
        $seller = Seller::factory()->create();
        $saleData = CreateSaleData::fromArray([
            'seller_id' => $seller->id,
            'amount' => -50.00,
            'sold_at' => now()->toDateTimeString()
        ]);

        $this->expectException(InvalidCommissionDataException::class);

        $this->saleService->create($saleData);
    }

    public function test_create_sale_with_nonexistent_seller_throws_exception(): void
    {
        $saleData = CreateSaleData::fromArray([
            'seller_id' => 999,
            'amount' => 100.00,
            'sold_at' => now()->toDateTimeString()
        ]);

        $this->expectException(SellerNotFoundException::class);

        $this->saleService->create($saleData);
    }

    public function test_find_all_returns_paginated_sales(): void
    {
        Sale::factory()->count(5)->create();

        $result = $this->saleService->findAll(10, 1);

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $result);
        $this->assertEquals(5, $result->total());
    }

    public function test_find_all_by_seller_returns_seller_sales(): void
    {
        $seller1 = Seller::factory()->create();
        $seller2 = Seller::factory()->create();

        Sale::factory()->count(3)->create(['seller_id' => $seller1->id]);
        Sale::factory()->count(2)->create(['seller_id' => $seller2->id]);

        $result = $this->saleService->findAllBySeller($seller1->id);

        $this->assertEquals(3, $result->total());
        foreach ($result->items() as $sale) {
            $this->assertEquals($seller1->id, $sale->seller_id);
        }
    }

    public function test_find_all_by_nonexistent_seller_throws_exception(): void
    {
        $this->expectException(SellerNotFoundException::class);

        $this->saleService->findAllBySeller(999);
    }

    public function test_get_daily_summary_for_seller(): void
    {
        $seller = Seller::factory()->create();
        $date = now()->format('Y-m-d');

        Sale::factory()->create([
            'seller_id' => $seller->id,
            'amount' => 100.00,
            'commission_amount' => 8.50,
            'sold_at' => $date . ' 10:00:00'
        ]);

        Sale::factory()->create([
            'seller_id' => $seller->id,
            'amount' => 200.00,
            'commission_amount' => 17.00,
            'sold_at' => $date . ' 14:00:00'
        ]);

        $result = $this->saleService->getDailySummaryForSeller($seller->id, $date);

        $this->assertEquals($seller->id, $result['seller']->id);
        $this->assertEquals($date, $result['date']);
        $this->assertEquals(2, $result['count']);
        $this->assertEquals(300.00, $result['total_amount']);
        $this->assertEquals(25.50, $result['commission']);
    }

    public function test_get_daily_summary_for_nonexistent_seller_throws_exception(): void
    {
        $this->expectException(SellerNotFoundException::class);

        $this->saleService->getDailySummaryForSeller(999, now()->format('Y-m-d'));
    }

    public function test_create_sale_clears_cache(): void
    {
        $seller = Seller::factory()->create();

        $saleData = CreateSaleData::fromArray([
            'seller_id' => $seller->id,
            'amount' => 100.00,
            'sold_at' => now()->toDateTimeString()
        ]);

        $result = $this->saleService->create($saleData);

        $this->assertInstanceOf(\App\Models\Sale::class, $result);
    }
}
