<?php

namespace Tests\Unit\DTOs;

use App\DTOs\BaseDTO;
use Carbon\Carbon;
use Tests\TestCase;

class TestDTO extends BaseDTO
{
    public function __construct(
        public readonly string $name,
        public readonly int $age,
        public readonly ?Carbon $birthDate = null
    ) {}

    protected static function prepareData(array $data): array
    {
        return [
            'name' => $data['name'] ?? '',
            'age' => (int) ($data['age'] ?? 0),
            'birthDate' => isset($data['birthDate']) ? Carbon::parse($data['birthDate']) : null
        ];
    }
}

class BaseDTOTest extends TestCase
{
    public function test_from_array_creates_dto_instance(): void
    {
        $data = [
            'name' => 'John Doe',
            'age' => 30,
            'birthDate' => '1993-01-01 00:00:00'
        ];

        $dto = TestDTO::fromArray($data);

        $this->assertInstanceOf(TestDTO::class, $dto);
        $this->assertEquals('John Doe', $dto->name);
        $this->assertEquals(30, $dto->age);
        $this->assertInstanceOf(Carbon::class, $dto->birthDate);
    }

    public function test_from_array_handles_missing_properties(): void
    {
        $data = [
            'name' => 'Jane Doe',
            'age' => 25
        ];

        $dto = TestDTO::fromArray($data);

        $this->assertEquals('Jane Doe', $dto->name);
        $this->assertEquals(25, $dto->age);
        $this->assertNull($dto->birthDate);
    }

    public function test_to_array_converts_dto_to_array(): void
    {
        $birthDate = Carbon::create(1993, 1, 1, 0, 0, 0);
        $dto = new TestDTO('John Doe', 30, $birthDate);

        $array = $dto->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('John Doe', $array['name']);
        $this->assertEquals(30, $array['age']);
        $this->assertEquals('1993-01-01 00:00:00', $array['birthDate']);
    }

    public function test_to_array_handles_null_date(): void
    {
        $dto = new TestDTO('Jane Doe', 25, null);

        $array = $dto->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('Jane Doe', $array['name']);
        $this->assertEquals(25, $array['age']);
        $this->assertNull($array['birthDate']);
    }

    public function test_to_array_only_includes_public_properties(): void
    {
        $dto = new TestDTO('John Doe', 30);

        $array = $dto->toArray();

        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('age', $array);
        $this->assertArrayHasKey('birthDate', $array);
        $this->assertCount(3, $array);
    }

    public function test_prepare_data_is_called_before_construction(): void
    {
        $testDtoClass = new class('') extends BaseDTO {
            public function __construct(
                public readonly string $processedName = ''
            ) {}

            protected static function prepareData(array $data): array
            {
                return [
                    'processedName' => strtoupper($data['name'] ?? '')
                ];
            }
        };

        $result = $testDtoClass::fromArray(['name' => 'test']);

        $this->assertEquals('TEST', $result->processedName);
    }
}
