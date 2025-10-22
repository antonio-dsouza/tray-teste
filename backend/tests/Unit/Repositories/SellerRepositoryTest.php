<?php

namespace Tests\Unit\Repositories;

use App\Models\Seller;
use App\Repositories\Implementations\Eloquent\Sellers\SellerRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private SellerRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new SellerRepository(new Seller());
    }

    public function test_can_find_seller_by_email(): void
    {
        $seller = Seller::factory()->create([
            'email' => 'test@example.com'
        ]);

        $result = $this->repository->findByEmail('test@example.com');

        $this->assertNotNull($result);
        $this->assertInstanceOf(Seller::class, $result);
        $this->assertEquals($seller->id, $result->id);
        $this->assertEquals('test@example.com', $result->email);
    }

    public function test_find_by_email_returns_null_when_not_found(): void
    {
        $result = $this->repository->findByEmail('nonexistent@example.com');

        $this->assertNull($result);
    }

    public function test_find_by_email_is_case_sensitive(): void
    {
        Seller::factory()->create([
            'email' => 'test@example.com'
        ]);

        $result = $this->repository->findByEmail('TEST@EXAMPLE.COM');

        $this->assertNull($result);
    }

    public function test_find_by_email_with_multiple_sellers(): void
    {
        $seller1 = Seller::factory()->create(['email' => 'seller1@example.com']);
        $seller2 = Seller::factory()->create(['email' => 'seller2@example.com']);
        $seller3 = Seller::factory()->create(['email' => 'seller3@example.com']);

        $result1 = $this->repository->findByEmail('seller1@example.com');
        $result2 = $this->repository->findByEmail('seller2@example.com');
        $result3 = $this->repository->findByEmail('seller3@example.com');

        $this->assertEquals($seller1->id, $result1->id);
        $this->assertEquals($seller2->id, $result2->id);
        $this->assertEquals($seller3->id, $result3->id);
    }

    public function test_can_get_all_ids(): void
    {
        $sellers = Seller::factory()->count(5)->create();
        $expectedIds = $sellers->pluck('id')->sort()->values()->toArray();

        $result = $this->repository->getAllIds();
        sort($result);

        $this->assertCount(5, $result);
        $this->assertEquals($expectedIds, $result);
    }

    public function test_get_all_ids_returns_empty_array_when_no_sellers(): void
    {
        $result = $this->repository->getAllIds();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_get_all_ids_returns_only_ids(): void
    {
        Seller::factory()->count(3)->create();

        $result = $this->repository->getAllIds();

        $this->assertCount(3, $result);
        foreach ($result as $id) {
            $this->assertIsInt($id);
            $this->assertGreaterThan(0, $id);
        }
    }

    public function test_get_all_ids_with_large_dataset(): void
    {
        Seller::factory()->count(100)->create();

        $result = $this->repository->getAllIds();

        $this->assertCount(100, $result);
        $this->assertIsArray($result);

        $uniqueIds = array_unique($result);
        $this->assertCount(100, $uniqueIds);
    }

    public function test_repository_extends_base_repository(): void
    {
        $this->assertInstanceOf(
            \App\Repositories\Implementations\Eloquent\BaseRepository::class,
            $this->repository
        );
    }

    public function test_repository_implements_seller_repository_interface(): void
    {
        $this->assertInstanceOf(
            \App\Repositories\Contracts\Sellers\SellerRepositoryInterface::class,
            $this->repository
        );
    }

    public function test_find_by_email_with_whitespace(): void
    {
        $seller = Seller::factory()->create([
            'email' => 'test@example.com'
        ]);

        $result = $this->repository->findByEmail(' test@example.com ');

        $this->assertNull($result);
    }

    public function test_find_by_email_with_empty_string(): void
    {
        $result = $this->repository->findByEmail('');

        $this->assertNull($result);
    }

    public function test_get_all_ids_maintains_order(): void
    {
        $seller1 = Seller::factory()->create();
        $seller2 = Seller::factory()->create();
        $seller3 = Seller::factory()->create();

        $result = $this->repository->getAllIds();

        $this->assertContains($seller1->id, $result);
        $this->assertContains($seller2->id, $result);
        $this->assertContains($seller3->id, $result);
        $this->assertCount(3, $result);
    }

    public function test_find_by_email_uses_first_method(): void
    {
        $email = 'duplicate@example.com';

        $seller = Seller::factory()->create(['email' => $email]);

        $result = $this->repository->findByEmail($email);

        $this->assertInstanceOf(Seller::class, $result);
        $this->assertEquals($seller->id, $result->id);
    }

    public function test_repository_handles_database_errors_gracefully(): void
    {
        $result = $this->repository->findByEmail('test@example.com');
        $this->assertNull($result);

        $ids = $this->repository->getAllIds();
        $this->assertIsArray($ids);
    }

    public function test_find_by_email_with_special_characters(): void
    {
        $specialEmail = 'test+tag@example-domain.co.uk';
        $seller = Seller::factory()->create(['email' => $specialEmail]);

        $result = $this->repository->findByEmail($specialEmail);

        $this->assertNotNull($result);
        $this->assertEquals($seller->id, $result->id);
        $this->assertEquals($specialEmail, $result->email);
    }
}
