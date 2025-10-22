<?php

namespace Tests\Unit\Repositories;

use App\Models\Sale;
use App\Models\Seller;
use App\Repositories\Implementations\Eloquent\Sales\SaleRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private SaleRepository $repository;
    private Seller $seller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new SaleRepository(new Sale());
        $this->seller = Seller::factory()->create();
    }

    public function test_can_find_sales_by_seller(): void
    {
        $sales = Sale::factory()->count(3)->create(['seller_id' => $this->seller->id]);

        $anotherSeller = Seller::factory()->create();
        Sale::factory()->count(2)->create(['seller_id' => $anotherSeller->id]);

        $result = $this->repository->findBySeller($this->seller->id);

        $this->assertCount(3, $result);
        $result->each(function (Sale $sale) {
            $this->assertEquals($this->seller->id, $sale->seller_id);
        });
    }

    public function test_can_find_sales_by_seller_with_date_filter(): void
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        Sale::factory()->count(2)->create([
            'seller_id' => $this->seller->id,
            'created_at' => $today
        ]);

        Sale::factory()->count(1)->create([
            'seller_id' => $this->seller->id,
            'created_at' => $yesterday
        ]);

        $todaySales = $this->repository->findBySeller($this->seller->id, $today->toDateString());
        $yesterdaySales = $this->repository->findBySeller($this->seller->id, $yesterday->toDateString());

        $this->assertCount(2, $todaySales);
        $this->assertCount(1, $yesterdaySales);
    }

    public function test_find_by_seller_returns_empty_collection_when_no_sales(): void
    {
        $result = $this->repository->findBySeller($this->seller->id);

        $this->assertCount(0, $result);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $result);
    }

    public function test_can_find_all_sales_by_seller_with_pagination(): void
    {
        Sale::factory()->count(25)->create(['seller_id' => $this->seller->id]);

        $result = $this->repository->findAllBySeller($this->seller->id, null, 10, 1);

        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(1, $result->currentPage());
        $this->assertEquals(25, $result->total());
        $this->assertEquals(3, $result->lastPage());
    }

    public function test_can_find_all_sales_by_seller_with_date_and_pagination(): void
    {
        $today = Carbon::today();

        Sale::factory()->count(15)->create([
            'seller_id' => $this->seller->id,
            'created_at' => $today
        ]);

        Sale::factory()->count(5)->create([
            'seller_id' => $this->seller->id,
            'created_at' => Carbon::yesterday()
        ]);

        $result = $this->repository->findAllBySeller($this->seller->id, $today->toDateString(), 10, 1);

        $this->assertEquals(15, $result->total());
        $this->assertEquals(10, $result->perPage());
    }

    public function test_can_find_all_sales_by_seller_with_relations(): void
    {
        Sale::factory()->count(3)->create(['seller_id' => $this->seller->id]);

        $result = $this->repository->findAllBySeller($this->seller->id, null, 10, 1, ['seller']);

        $result->each(function (Sale $sale) {
            $this->assertTrue($sale->relationLoaded('seller'));
            $this->assertNotNull($sale->seller);
        });
    }

    public function test_can_get_sales_by_date_range(): void
    {
        $startDate = Carbon::now()->startOfWeek();
        $endDate = Carbon::now()->endOfWeek();

        $salesInRange = Sale::factory()->count(3)->create([
            'seller_id' => $this->seller->id,
            'sold_at' => Carbon::now()->addDays(1)
        ]);

        Sale::factory()->count(2)->create([
            'seller_id' => $this->seller->id,
            'sold_at' => Carbon::now()->addWeeks(2)
        ]);

        $result = $this->repository->getSalesByDateRange($startDate->toDateTime(), $endDate->toDateTime());

        $this->assertCount(3, $result);
        $result->each(function (Sale $sale) {
            $this->assertTrue($sale->relationLoaded('seller'));
        });
    }

    public function test_can_get_seller_sales_by_date_range(): void
    {
        $anotherSeller = Seller::factory()->create();
        $startDate = Carbon::now()->startOfWeek();
        $endDate = Carbon::now()->endOfWeek();

        Sale::factory()->count(2)->create([
            'seller_id' => $this->seller->id,
            'sold_at' => Carbon::now()->addDays(1)
        ]);

        Sale::factory()->count(3)->create([
            'seller_id' => $anotherSeller->id,
            'sold_at' => Carbon::now()->addDays(1)
        ]);

        Sale::factory()->count(1)->create([
            'seller_id' => $this->seller->id,
            'sold_at' => Carbon::now()->addWeeks(2)
        ]);

        $result = $this->repository->getSellerSalesByDateRange(
            $this->seller->id,
            $startDate->toDateTime(),
            $endDate->toDateTime()
        );

        $this->assertCount(2, $result);
        $result->each(function (Sale $sale) {
            $this->assertEquals($this->seller->id, $sale->seller_id);
            $this->assertTrue($sale->relationLoaded('seller'));
        });
    }

    public function test_date_range_methods_handle_empty_results(): void
    {
        $startDate = Carbon::now()->startOfWeek();
        $endDate = Carbon::now()->endOfWeek();

        $allSales = $this->repository->getSalesByDateRange($startDate->toDateTime(), $endDate->toDateTime());
        $sellerSales = $this->repository->getSellerSalesByDateRange(
            $this->seller->id,
            $startDate->toDateTime(),
            $endDate->toDateTime()
        );

        $this->assertCount(0, $allSales);
        $this->assertCount(0, $sellerSales);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $allSales);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $sellerSales);
    }

    public function test_repository_extends_base_repository(): void
    {
        $this->assertInstanceOf(
            \App\Repositories\Implementations\Eloquent\BaseRepository::class,
            $this->repository
        );
    }

    public function test_repository_implements_sale_repository_interface(): void
    {
        $this->assertInstanceOf(
            \App\Repositories\Contracts\Sales\SaleRepositoryInterface::class,
            $this->repository
        );
    }

    public function test_can_handle_invalid_date_format_gracefully(): void
    {
        $this->expectException(\Exception::class);
        $this->repository->findBySeller($this->seller->id, 'invalid-date');
    }

    public function test_pagination_with_different_page_sizes(): void
    {
        Sale::factory()->count(50)->create(['seller_id' => $this->seller->id]);

        $result5 = $this->repository->findAllBySeller($this->seller->id, null, 5, 1);
        $result15 = $this->repository->findAllBySeller($this->seller->id, null, 15, 1);
        $result25 = $this->repository->findAllBySeller($this->seller->id, null, 25, 1);

        $this->assertEquals(5, $result5->perPage());
        $this->assertEquals(15, $result15->perPage());
        $this->assertEquals(25, $result25->perPage());

        $this->assertEquals(50, $result5->total());
        $this->assertEquals(50, $result15->total());
        $this->assertEquals(50, $result25->total());
    }
}
