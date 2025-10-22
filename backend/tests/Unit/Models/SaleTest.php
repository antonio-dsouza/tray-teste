<?php

namespace Tests\Unit\Models;

use App\Models\Sale;
use App\Models\Seller;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_has_fillable_attributes(): void
    {
        $fillable = ['seller_id', 'amount', 'commission_amount', 'sold_at'];
        $sale = new Sale();

        $this->assertEquals($fillable, $sale->getFillable());
    }

    public function test_sale_has_correct_casts(): void
    {
        $expectedCasts = [
            'amount' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'sold_at' => 'datetime',
        ];

        $sale = new Sale();
        $casts = $sale->getCasts();

        foreach ($expectedCasts as $attribute => $cast) {
            $this->assertEquals($cast, $casts[$attribute]);
        }
    }

    public function test_sale_can_be_created_with_valid_data(): void
    {
        $seller = Seller::factory()->create();
        $saleData = [
            'seller_id' => $seller->id,
            'amount' => 150.50,
            'commission_amount' => 15.05,
            'sold_at' => Carbon::now()
        ];

        $sale = Sale::create($saleData);

        $this->assertEquals($seller->id, $sale->seller_id);
        $this->assertEquals('150.50', $sale->amount);
        $this->assertEquals('15.05', $sale->commission_amount);
        $this->assertInstanceOf(Carbon::class, $sale->sold_at);
    }

    public function test_sale_belongs_to_seller_relationship(): void
    {
        $seller = Seller::factory()->create();
        $sale = Sale::factory()->create(['seller_id' => $seller->id]);

        $this->assertInstanceOf(Seller::class, $sale->seller);
        $this->assertEquals($seller->id, $sale->seller->id);
        $this->assertEquals($seller->name, $sale->seller->name);
    }

    public function test_sale_seller_relationship_returns_belongs_to(): void
    {
        $sale = new Sale();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $sale->seller());
    }

    public function test_amount_is_cast_to_decimal(): void
    {
        $sale = Sale::factory()->create(['amount' => 99.999]);

        $this->assertEquals('100.00', $sale->fresh()->amount);
    }

    public function test_commission_amount_is_cast_to_decimal(): void
    {
        $sale = Sale::factory()->create(['commission_amount' => 10.999]);

        $this->assertEquals('11.00', $sale->fresh()->commission_amount);
    }

    public function test_sold_at_is_cast_to_datetime(): void
    {
        $dateTime = '2024-01-15 10:30:00';
        $sale = Sale::factory()->create(['sold_at' => $dateTime]);

        $this->assertInstanceOf(Carbon::class, $sale->sold_at);
        $this->assertEquals('2024-01-15 10:30:00', $sale->sold_at->format('Y-m-d H:i:s'));
    }

    public function test_sale_factory_creates_valid_sale(): void
    {
        $sale = Sale::factory()->create();

        $this->assertNotNull($sale->seller_id);
        $this->assertNotNull($sale->amount);
        $this->assertNotNull($sale->commission_amount);
        $this->assertNotNull($sale->sold_at);
        $this->assertIsNumeric($sale->amount);
        $this->assertIsNumeric($sale->commission_amount);
        $this->assertInstanceOf(Carbon::class, $sale->sold_at);
    }

    public function test_sale_factory_can_create_with_custom_attributes(): void
    {
        $seller = Seller::factory()->create();
        $customDate = Carbon::create(2024, 1, 1, 12, 0, 0);

        $sale = Sale::factory()->create([
            'seller_id' => $seller->id,
            'amount' => 500.00,
            'commission_amount' => 50.00,
            'sold_at' => $customDate
        ]);

        $this->assertEquals($seller->id, $sale->seller_id);
        $this->assertEquals('500.00', $sale->amount);
        $this->assertEquals('50.00', $sale->commission_amount);
        $this->assertEquals($customDate->format('Y-m-d H:i:s'), $sale->sold_at->format('Y-m-d H:i:s'));
    }

    public function test_sale_timestamps_are_set(): void
    {
        $sale = Sale::factory()->create();

        $this->assertNotNull($sale->created_at);
        $this->assertNotNull($sale->updated_at);
        $this->assertInstanceOf(Carbon::class, $sale->created_at);
        $this->assertInstanceOf(Carbon::class, $sale->updated_at);
    }

    public function test_sale_has_correct_table_name(): void
    {
        $sale = new Sale();

        $this->assertEquals('sales', $sale->getTable());
    }

    public function test_sale_primary_key_is_id(): void
    {
        $sale = new Sale();

        $this->assertEquals('id', $sale->getKeyName());
    }

    public function test_sale_can_be_associated_with_seller_through_factory(): void
    {
        $seller = Seller::factory()->create();
        $sale = Sale::factory()->for($seller)->create();

        $this->assertEquals($seller->id, $sale->seller_id);
        $this->assertTrue($seller->sales->contains($sale));
    }
}
