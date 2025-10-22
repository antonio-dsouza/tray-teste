<?php

namespace Tests\Unit\DTOs\Sales;

use App\DTOs\Sales\CreateSaleData;
use Carbon\Carbon;
use Tests\TestCase;

class CreateSaleDataTest extends TestCase
{
    public function test_from_array_creates_create_sale_data_instance(): void
    {
        $data = [
            'seller_id' => 1,
            'amount' => 150.75,
            'sold_at' => '2024-01-15 10:30:00'
        ];

        $saleData = CreateSaleData::fromArray($data);

        $this->assertInstanceOf(CreateSaleData::class, $saleData);
        $this->assertEquals(1, $saleData->seller_id);
        $this->assertEquals(150.75, $saleData->amount);
        $this->assertInstanceOf(Carbon::class, $saleData->sold_at);
        $this->assertEquals('2024-01-15 10:30:00', $saleData->sold_at->format('Y-m-d H:i:s'));
    }

    public function test_from_array_converts_string_seller_id_to_int(): void
    {
        $data = [
            'seller_id' => '5',
            'amount' => 100.00,
            'sold_at' => now()->toDateTimeString()
        ];

        $saleData = CreateSaleData::fromArray($data);

        $this->assertIsInt($saleData->seller_id);
        $this->assertEquals(5, $saleData->seller_id);
    }

    public function test_from_array_converts_string_amount_to_float(): void
    {
        $data = [
            'seller_id' => 1,
            'amount' => '99.95',
            'sold_at' => now()->toDateTimeString()
        ];

        $saleData = CreateSaleData::fromArray($data);

        $this->assertIsFloat($saleData->amount);
        $this->assertEquals(99.95, $saleData->amount);
    }

    public function test_from_array_handles_missing_seller_id(): void
    {
        $data = [
            'amount' => 100.00,
            'sold_at' => now()->toDateTimeString()
        ];

        $saleData = CreateSaleData::fromArray($data);

        $this->assertEquals(0, $saleData->seller_id);
    }

    public function test_from_array_handles_missing_amount(): void
    {
        $data = [
            'seller_id' => 1,
            'sold_at' => now()->toDateTimeString()
        ];

        $saleData = CreateSaleData::fromArray($data);

        $this->assertEquals(0.0, $saleData->amount);
    }

    public function test_from_array_uses_current_time_when_sold_at_missing(): void
    {
        $beforeCreation = now();

        $data = [
            'seller_id' => 1,
            'amount' => 100.00
        ];

        $saleData = CreateSaleData::fromArray($data);

        $afterCreation = now();

        $this->assertTrue($saleData->sold_at->between($beforeCreation, $afterCreation));
    }

    public function test_from_array_parses_various_date_formats(): void
    {
        $testCases = [
            'Y-m-d H:i:s' => '2024-01-15 10:30:00',
            'Y-m-d' => '2024-01-15',
        ];

        foreach ($testCases as $format => $dateString) {
            $data = [
                'seller_id' => 1,
                'amount' => 100.0,
                'sold_at' => $dateString
            ];

            $saleData = CreateSaleData::fromArray($data);

            $this->assertNotNull($saleData->sold_at, "Failed to parse date format: {$format}");
        }
    }

    public function test_to_array_returns_correct_structure(): void
    {
        $soldAt = Carbon::create(2024, 1, 15, 10, 30, 0);
        $saleData = new CreateSaleData(1, 150.75, $soldAt);

        $array = $saleData->toArray();

        $this->assertArrayHasKey('seller_id', $array);
        $this->assertArrayHasKey('amount', $array);
        $this->assertArrayHasKey('sold_at', $array);
        $this->assertEquals(1, $array['seller_id']);
        $this->assertEquals(150.75, $array['amount']);
        $this->assertEquals('2024-01-15 10:30:00', $array['sold_at']);
    }

    public function test_readonly_properties_cannot_be_modified(): void
    {
        $saleData = new CreateSaleData(1, 100.00, now());

        $reflection = new \ReflectionClass($saleData);

        $sellerIdProperty = $reflection->getProperty('seller_id');
        $amountProperty = $reflection->getProperty('amount');
        $soldAtProperty = $reflection->getProperty('sold_at');

        $this->assertTrue($sellerIdProperty->isReadOnly());
        $this->assertTrue($amountProperty->isReadOnly());
        $this->assertTrue($soldAtProperty->isReadOnly());
    }

    public function test_prepare_data_handles_null_values(): void
    {
        $data = [
            'seller_id' => null,
            'amount' => null,
            'sold_at' => null
        ];

        $saleData = CreateSaleData::fromArray($data);

        $this->assertEquals(0, $saleData->seller_id);
        $this->assertEquals(0.0, $saleData->amount);
        $this->assertInstanceOf(Carbon::class, $saleData->sold_at);
    }

    public function test_prepare_data_handles_negative_values(): void
    {
        $data = [
            'seller_id' => -1,
            'amount' => -50.00,
            'sold_at' => now()->toDateTimeString()
        ];

        $saleData = CreateSaleData::fromArray($data);

        $this->assertEquals(-1, $saleData->seller_id);
        $this->assertEquals(-50.00, $saleData->amount);
    }

    public function test_carbon_instance_maintains_timezone(): void
    {
        $carbonInstance = Carbon::create(2024, 1, 15, 10, 30, 0, 'UTC');

        $data = [
            'seller_id' => 1,
            'amount' => 100.00,
            'sold_at' => $carbonInstance
        ];

        $saleData = CreateSaleData::fromArray($data);

        $this->assertEquals('UTC', $saleData->sold_at->getTimezone()->getName());
    }
}
