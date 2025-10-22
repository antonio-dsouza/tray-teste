<?php

namespace Tests\Unit\DTOs\Sellers;

use App\DTOs\Sellers\CreateSellerData;
use Tests\TestCase;

class CreateSellerDataTest extends TestCase
{
    public function test_from_array_creates_create_seller_data_instance(): void
    {
        $data = [
            'name' => 'John Seller',
            'email' => 'john@seller.com'
        ];

        $sellerData = CreateSellerData::fromArray($data);

        $this->assertInstanceOf(CreateSellerData::class, $sellerData);
        $this->assertEquals('John Seller', $sellerData->name);
        $this->assertEquals('john@seller.com', $sellerData->email);
    }

    public function test_from_array_trims_name_whitespace(): void
    {
        $data = [
            'name' => '  John Seller  ',
            'email' => 'john@seller.com'
        ];

        $sellerData = CreateSellerData::fromArray($data);

        $this->assertEquals('John Seller', $sellerData->name);
    }

    public function test_from_array_trims_and_lowercases_email(): void
    {
        $data = [
            'name' => 'John Seller',
            'email' => '  JOHN@SELLER.COM  '
        ];

        $sellerData = CreateSellerData::fromArray($data);

        $this->assertEquals('john@seller.com', $sellerData->email);
    }

    public function test_from_array_handles_missing_name(): void
    {
        $data = [
            'email' => 'john@seller.com'
        ];

        $sellerData = CreateSellerData::fromArray($data);

        $this->assertEquals('', $sellerData->name);
        $this->assertEquals('john@seller.com', $sellerData->email);
    }

    public function test_from_array_handles_missing_email(): void
    {
        $data = [
            'name' => 'John Seller'
        ];

        $sellerData = CreateSellerData::fromArray($data);

        $this->assertEquals('John Seller', $sellerData->name);
        $this->assertEquals('', $sellerData->email);
    }

    public function test_from_array_handles_empty_data(): void
    {
        $sellerData = CreateSellerData::fromArray([]);

        $this->assertEquals('', $sellerData->name);
        $this->assertEquals('', $sellerData->email);
    }

    public function test_to_array_returns_correct_structure(): void
    {
        $sellerData = new CreateSellerData('John Seller', 'john@seller.com');

        $array = $sellerData->toArray();

        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('email', $array);
        $this->assertEquals('John Seller', $array['name']);
        $this->assertEquals('john@seller.com', $array['email']);
    }

    public function test_readonly_properties_cannot_be_modified(): void
    {
        $sellerData = new CreateSellerData('John Seller', 'john@seller.com');

        $this->assertEquals('John Seller', $sellerData->name);
        $this->assertEquals('john@seller.com', $sellerData->email);

        $reflection = new \ReflectionClass($sellerData);
        $nameProperty = $reflection->getProperty('name');
        $emailProperty = $reflection->getProperty('email');

        $this->assertTrue($nameProperty->isReadOnly());
        $this->assertTrue($emailProperty->isReadOnly());
    }

    public function test_prepare_data_processes_null_values(): void
    {
        $data = [
            'name' => null,
            'email' => null
        ];

        $sellerData = CreateSellerData::fromArray($data);

        $this->assertEquals('', $sellerData->name);
        $this->assertEquals('', $sellerData->email);
    }

    public function test_prepare_data_processes_numeric_values(): void
    {
        $data = [
            'name' => 12345,
            'email' => 67890
        ];

        $sellerData = CreateSellerData::fromArray($data);

        $this->assertEquals('12345', $sellerData->name);
        $this->assertEquals('67890', $sellerData->email);
    }

    public function test_prepare_data_handles_special_characters_in_name(): void
    {
        $data = [
            'name' => '  José da Silva Ç  ',
            'email' => 'jose@example.com'
        ];

        $sellerData = CreateSellerData::fromArray($data);

        $this->assertEquals('José da Silva Ç', $sellerData->name);
    }

    public function test_prepare_data_handles_mixed_case_email(): void
    {
        $data = [
            'name' => 'John',
            'email' => 'JoHn.DoE@ExAmPlE.CoM'
        ];

        $sellerData = CreateSellerData::fromArray($data);

        $this->assertEquals('john.doe@example.com', $sellerData->email);
    }

    public function test_prepare_data_preserves_email_format(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john+test@example.co.uk'
        ];

        $sellerData = CreateSellerData::fromArray($data);

        $this->assertEquals('john+test@example.co.uk', $sellerData->email);
    }

    public function test_from_array_with_boolean_values(): void
    {
        $data = [
            'name' => true,
            'email' => false
        ];

        $sellerData = CreateSellerData::fromArray($data);

        $this->assertEquals('1', $sellerData->name);
        $this->assertEquals('', $sellerData->email);
    }
}
