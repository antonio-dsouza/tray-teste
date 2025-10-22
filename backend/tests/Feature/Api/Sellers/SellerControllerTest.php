<?php

namespace Tests\Feature\Sellers;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class SellerControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::create(['name' => 'view_sellers']);
        Permission::create(['name' => 'create_sellers']);
        Permission::create(['name' => 'resend_commissions']);
        Permission::create(['name' => 'run_daily_mails']);

        $this->user = User::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo(['view_sellers', 'create_sellers', 'resend_commissions', 'run_daily_mails']);
        $this->user->assignRole($role);

        $this->token = JWTAuth::fromUser($this->user);
    }

    public function test_index_returns_paginated_sellers(): void
    {
        Seller::factory()->count(5)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/sellers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
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
                'message' => 'Vendedores recuperados com sucesso'
            ]);

        $this->assertEquals(5, $response->json('meta.total'));
    }

    public function test_index_respects_pagination_parameters(): void
    {
        Seller::factory()->count(15)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/sellers?page=2&per_page=5');

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
            ->getJson('/api/sellers');

        $response->assertStatus(403);
    }

    public function test_index_without_authentication_returns_unauthorized(): void
    {
        $response = $this->getJson('/api/sellers');

        $response->assertStatus(401);
    }

    public function test_store_creates_new_seller(): void
    {
        $sellerData = [
            'name' => 'John Seller',
            'email' => 'john@seller.com'
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/sellers', $sellerData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Vendedor criado com sucesso',
                'data' => [
                    'name' => 'John Seller',
                    'email' => 'john@seller.com'
                ]
            ]);

        $this->assertDatabaseHas('sellers', $sellerData);
    }

    public function test_store_with_missing_name_returns_validation_error(): void
    {
        $sellerData = [
            'email' => 'john@seller.com'
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/sellers', $sellerData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_with_missing_email_returns_validation_error(): void
    {
        $sellerData = [
            'name' => 'John Seller'
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/sellers', $sellerData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_store_with_invalid_email_returns_validation_error(): void
    {
        $sellerData = [
            'name' => 'John Seller',
            'email' => 'invalid-email'
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/sellers', $sellerData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_store_with_duplicate_email_returns_validation_error(): void
    {
        Seller::factory()->create(['email' => 'existing@seller.com']);

        $sellerData = [
            'name' => 'John Seller',
            'email' => 'existing@seller.com'
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/sellers', $sellerData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_store_without_permission_returns_forbidden(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'viewer']);
        $role->givePermissionTo(['view_sellers']);
        $user->assignRole($role);
        $token = JWTAuth::fromUser($user);

        $sellerData = [
            'name' => 'John Seller',
            'email' => 'john@seller.com'
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/sellers', $sellerData);

        $response->assertStatus(403);
    }

    public function test_store_without_authentication_returns_unauthorized(): void
    {
        $sellerData = [
            'name' => 'John Seller',
            'email' => 'john@seller.com'
        ];

        $response = $this->postJson('/api/sellers', $sellerData);

        $response->assertStatus(401);
    }

    public function test_resend_seller_commission_returns_success(): void
    {
        $seller = Seller::factory()->create();
        $date = now()->format('Y-m-d');

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/admin/sellers/{$seller->id}/resend-commission", [
                'date' => $date
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data'
            ]);
    }

    public function test_resend_seller_commission_with_nonexistent_seller_returns_error(): void
    {
        $date = now()->format('Y-m-d');

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/admin/sellers/999/resend-commission', [
                'date' => $date
            ]);

        $response->assertStatus(404);
    }

    public function test_resend_seller_commission_without_permission_returns_forbidden(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'viewer']);
        $role->givePermissionTo(['view_sellers']);
        $user->assignRole($role);
        $token = JWTAuth::fromUser($user);

        $seller = Seller::factory()->create();
        $date = now()->format('Y-m-d');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/admin/sellers/{$seller->id}/resend-commission", [
                'date' => $date
            ]);

        $response->assertStatus(403);
    }

    public function test_run_daily_mails_returns_success(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/admin/run-daily-mails');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data'
            ]);
    }

    public function test_run_daily_mails_without_permission_returns_forbidden(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'viewer']);
        $role->givePermissionTo(['view_sellers']);
        $user->assignRole($role);
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/admin/run-daily-mails');

        $response->assertStatus(403);
    }

    public function test_run_daily_mails_without_authentication_returns_unauthorized(): void
    {
        $response = $this->postJson('/api/admin/run-daily-mails');

        $response->assertStatus(401);
    }
}
