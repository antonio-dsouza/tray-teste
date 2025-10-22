<?php

namespace Tests\Unit\Models;

use App\Models\Sale;
use App\Models\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_has_fillable_attributes(): void
    {
        $fillable = ['name', 'email'];
        $seller = new Seller();

        $this->assertEquals($fillable, $seller->getFillable());
    }

    public function test_seller_can_be_created_with_valid_data(): void
    {
        $sellerData = [
            'name' => 'John Seller',
            'email' => 'john@seller.com'
        ];

        $seller = Seller::create($sellerData);

        $this->assertEquals('John Seller', $seller->name);
        $this->assertEquals('john@seller.com', $seller->email);
        $this->assertDatabaseHas('sellers', $sellerData);
    }

    public function test_seller_has_many_sales_relationship(): void
    {
        $seller = Seller::factory()->create();
        $sale1 = Sale::factory()->create(['seller_id' => $seller->id]);
        $sale2 = Sale::factory()->create(['seller_id' => $seller->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $seller->sales);
        $this->assertCount(2, $seller->sales);
        $this->assertTrue($seller->sales->contains($sale1));
        $this->assertTrue($seller->sales->contains($sale2));
    }

    public function test_seller_sales_relationship_returns_has_many(): void
    {
        $seller = new Seller();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $seller->sales());
    }

    public function test_seller_factory_creates_valid_seller(): void
    {
        $seller = Seller::factory()->create();

        $this->assertNotNull($seller->name);
        $this->assertNotNull($seller->email);
        $this->assertIsString($seller->name);
        $this->assertIsString($seller->email);
        $this->assertTrue(filter_var($seller->email, FILTER_VALIDATE_EMAIL) !== false);
    }

    public function test_seller_factory_can_create_with_custom_attributes(): void
    {
        $seller = Seller::factory()->create([
            'name' => 'Custom Seller',
            'email' => 'custom@seller.com'
        ]);

        $this->assertEquals('Custom Seller', $seller->name);
        $this->assertEquals('custom@seller.com', $seller->email);
    }

    public function test_seller_can_have_no_sales(): void
    {
        $seller = Seller::factory()->create();

        $this->assertCount(0, $seller->sales);
        $this->assertEmpty($seller->sales);
    }

    public function test_seller_timestamps_are_set(): void
    {
        $seller = Seller::factory()->create();

        $this->assertNotNull($seller->created_at);
        $this->assertNotNull($seller->updated_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $seller->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $seller->updated_at);
    }

    public function test_seller_has_correct_table_name(): void
    {
        $seller = new Seller();

        $this->assertEquals('sellers', $seller->getTable());
    }

    public function test_seller_primary_key_is_id(): void
    {
        $seller = new Seller();

        $this->assertEquals('id', $seller->getKeyName());
    }
}
