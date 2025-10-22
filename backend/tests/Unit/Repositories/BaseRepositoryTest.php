<?php

namespace Tests\Unit\Repositories;

use App\Models\User;
use App\Repositories\Implementations\Eloquent\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BaseRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private TestRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new TestRepository(new User());
    }

    public function test_find_all_returns_paginated_results(): void
    {
        User::factory()->count(25)->create();

        $result = $this->repository->findAll(10, 1);

        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(1, $result->currentPage());
        $this->assertEquals(25, $result->total());
        $this->assertEquals(3, $result->lastPage());
    }

    public function test_find_all_with_relations(): void
    {
        User::factory()->count(5)->create();

        $result = $this->repository->findAll(10, 1, ['roles']);

        $this->assertEquals(5, $result->count());
        $result->each(function ($user) {
            $this->assertTrue($user->relationLoaded('roles'));
        });
    }

    public function test_find_by_id_returns_model(): void
    {
        $user = User::factory()->create();

        $result = $this->repository->findById($user->id);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user->id, $result->id);
    }

    public function test_find_by_id_with_relations(): void
    {
        $user = User::factory()->create();

        $result = $this->repository->findById($user->id, ['roles']);

        $this->assertInstanceOf(User::class, $result);
        $this->assertTrue($result->relationLoaded('roles'));
    }

    public function test_find_by_id_returns_null_when_not_found(): void
    {
        $result = $this->repository->findById(999);

        $this->assertNull($result);
    }

    public function test_create_creates_new_model(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ];

        $result = $this->repository->create($data);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('Test User', $result->name);
        $this->assertEquals('test@example.com', $result->email);
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);
    }

    public function test_update_updates_existing_model(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name'
        ]);

        $result = $this->repository->update($user->id, [
            'name' => 'Updated Name'
        ]);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('Updated Name', $result->name);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name'
        ]);
    }

    public function test_update_returns_null_when_model_not_found(): void
    {
        $result = $this->repository->update(999, ['name' => 'New Name']);

        $this->assertNull($result);
    }

    public function test_delete_removes_model(): void
    {
        $user = User::factory()->create();

        $result = $this->repository->delete($user->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_delete_returns_false_when_model_not_found(): void
    {
        $result = $this->repository->delete(999);

        $this->assertFalse($result);
    }

    public function test_find_all_orders_by_id_desc(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        $result = $this->repository->findAll(10, 1);

        $this->assertEquals($user3->id, $result->first()->id);
        $this->assertEquals($user1->id, $result->last()->id);
    }

    public function test_find_all_with_different_page_sizes(): void
    {
        User::factory()->count(20)->create();

        $result5 = $this->repository->findAll(5, 1);
        $result10 = $this->repository->findAll(10, 1);

        $this->assertEquals(5, $result5->perPage());
        $this->assertEquals(10, $result10->perPage());
        $this->assertEquals(20, $result5->total());
        $this->assertEquals(20, $result10->total());
    }

    public function test_constructor_sets_model(): void
    {
        $model = new User();
        $repository = new TestRepository($model);

        $this->assertInstanceOf(User::class, $repository->getModel());
    }

    public function test_repository_implements_interface(): void
    {
        $this->assertInstanceOf(
            \App\Repositories\Contracts\BaseRepositoryInterface::class,
            $this->repository
        );
    }
}

class TestRepository extends BaseRepository
{
    public function getModel(): Model
    {
        return $this->model;
    }
}
