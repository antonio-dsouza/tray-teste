<?php

namespace Tests\Feature\Sales;

use App\Models\Sale;
use App\Models\Seller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class SaleControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::create(['name' => 'view_sales']);
        Permission::create(['name' => 'create_sales']);

        $this->user = User::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo(['view_sales', 'create_sales']);
        $this->user->assignRole($role);

        $this->token = JWTAuth::fromUser($this->user);
    }

    public function test_index_returns_paginated_sales(): void
    {
        Sale::factory()->count(5)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/sales');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'seller_id',
                        'amount',
                        'commission_amount',
                        'sold_at',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'meta' => [
                    'current_page',
                    'per_page',
                    'total'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Vendas recuperadas com sucesso'
            ]);

        $this->assertEquals(5, $response->json('meta.total'));
    }

    public function test_index_respects_pagination_parameters(): void
    {
        Sale::factory()->count(15)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/sales?page=2&per_page=5');

        $response->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'current_page' => 2,
                    'per_page' => 5
                ]
            ]);

        $this->assertCount(5, $response->json('data'));
    }

    public function test_index_without_permission_returns_forbidden(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/sales');

        $response->assertStatus(403);
    }

    public function test_index_without_authentication_returns_unauthorized(): void
    {
        $response = $this->getJson('/api/sales');

        $response->assertStatus(401);
    }

    public function test_store_creates_new_sale(): void
    {
        $seller = Seller::factory()->create();
        $saleData = [
            'seller_id' => $seller->id,
            'amount' => 100.00,
            'sold_at' => now()->toDateTimeString()
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/sales', $saleData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Venda criada com sucesso'
            ]);

        $this->assertDatabaseHas('sales', [
            'seller_id' => $seller->id,
            'amount' => 100.00,
            'commission_amount' => 8.5
        ]);
    }

    public function test_store_with_missing_seller_id_returns_validation_error(): void
    {
        $saleData = [
            'amount' => 100.00,
            'sold_at' => now()->toDateTimeString()
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/sales', $saleData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['seller_id']);
    }

    public function test_store_with_missing_amount_returns_validation_error(): void
    {
        $seller = Seller::factory()->create();
        $saleData = [
            'seller_id' => $seller->id,
            'sold_at' => now()->toDateTimeString()
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/sales', $saleData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    public function test_store_with_missing_sold_at_returns_validation_error(): void
    {
        $seller = Seller::factory()->create();
        $saleData = [
            'seller_id' => $seller->id,
            'amount' => 100.00
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/sales', $saleData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sold_at']);
    }

    public function test_store_with_invalid_seller_id_returns_validation_error(): void
    {
        $saleData = [
            'seller_id' => 999,
            'amount' => 100.00,
            'sold_at' => now()->toDateTimeString()
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/sales', $saleData);

        $response->assertStatus(404);
    }

    public function test_store_with_zero_amount_returns_error(): void
    {
        $seller = Seller::factory()->create();
        $saleData = [
            'seller_id' => $seller->id,
            'amount' => 0,
            'sold_at' => now()->toDateTimeString()
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/sales', $saleData);

        $response->assertStatus(422);
    }

    public function test_store_with_negative_amount_returns_error(): void
    {
        $seller = Seller::factory()->create();
        $saleData = [
            'seller_id' => $seller->id,
            'amount' => -50.00,
            'sold_at' => now()->toDateTimeString()
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/sales', $saleData);

        $response->assertStatus(422);
    }

    public function test_store_without_permission_returns_forbidden(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'viewer']);
        $role->givePermissionTo(['view_sales']);
        $user->assignRole($role);
        $token = JWTAuth::fromUser($user);

        $seller = Seller::factory()->create();
        $saleData = [
            'seller_id' => $seller->id,
            'amount' => 100.00,
            'sold_at' => now()->toDateTimeString()
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/sales', $saleData);

        $response->assertStatus(403);
    }

    public function test_store_without_authentication_returns_unauthorized(): void
    {
        $seller = Seller::factory()->create();
        $saleData = [
            'seller_id' => $seller->id,
            'amount' => 100.00,
            'sold_at' => now()->toDateTimeString()
        ];

        $response = $this->postJson('/api/sales', $saleData);

        $response->assertStatus(401);
    }

    public function test_by_seller_returns_seller_sales(): void
    {
        $seller1 = Seller::factory()->create();
        $seller2 = Seller::factory()->create();

        Sale::factory()->count(3)->create(['seller_id' => $seller1->id]);
        Sale::factory()->count(2)->create(['seller_id' => $seller2->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/sellers/{$seller1->id}/sales");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Vendas do vendedor recuperadas com sucesso'
            ]);

        $this->assertEquals(3, $response->json('meta.total'));

        foreach ($response->json('data') as $sale) {
            $this->assertEquals($seller1->id, $sale['seller_id']);
        }
    }

    public function test_by_seller_with_date_filter_returns_filtered_sales(): void
    {
        $seller = Seller::factory()->create();
        $targetDate = '2024-01-15';

        Sale::factory()->count(2)->create([
            'seller_id' => $seller->id,
            'sold_at' => $targetDate . ' 10:00:00'
        ]);

        Sale::factory()->create([
            'seller_id' => $seller->id,
            'sold_at' => '2024-01-16 10:00:00'
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/sellers/{$seller->id}/sales?date={$targetDate}");

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(0, $response->json('meta.total'));
    }

    public function test_by_seller_with_nonexistent_seller_returns_not_found(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/sellers/999/sales');

        $response->assertStatus(404);
    }

    public function test_by_seller_respects_pagination_parameters(): void
    {
        $seller = Seller::factory()->create();
        Sale::factory()->count(15)->create(['seller_id' => $seller->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/sellers/{$seller->id}/sales?page=2&per_page=5");

        $response->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'current_page' => 2,
                    'per_page' => 5
                ]
            ]);

        $this->assertCount(5, $response->json('data'));
    }

    public function test_by_seller_without_permission_returns_forbidden(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);
        $seller = Seller::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/sellers/{$seller->id}/sales");

        $response->assertStatus(403);
    }

    public function test_store_calculates_correct_commission(): void
    {
        $seller = Seller::factory()->create();
        $amount = 1000.00;

        $saleData = [
            'seller_id' => $seller->id,
            'amount' => $amount,
            'sold_at' => now()->toDateTimeString()
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/sales', $saleData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('sales', [
            'seller_id' => $seller->id,
            'amount' => $amount,
            'commission_amount' => $amount * 0.085
        ]);
    }

    public function test_store_with_decimal_amount_works_correctly(): void
    {
        $seller = Seller::factory()->create();
        $amount = 123.45;

        $saleData = [
            'seller_id' => $seller->id,
            'amount' => $amount,
            'sold_at' => now()->toDateTimeString()
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/sales', $saleData);

        $response->assertStatus(201);

        $sale = Sale::where('seller_id', $seller->id)->first();
        $this->assertEquals('123.45', $sale->amount);
        $this->assertEquals(round($amount * 0.085, 2), (float) $sale->commission_amount);
    }
}
