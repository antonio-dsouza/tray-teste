<?php

namespace Tests\Feature\Api\Dashboard;

use App\Models\Sale;
use App\Models\Seller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        Sanctum::actingAs($user);
    }

    public function it_can_get_dashboard_stats()
    {
        $sellers = Seller::factory()->count(3)->create();

        Sale::factory()->count(5)->create([
            'seller_id' => $sellers->first()->id,
            'amount' => 1000,
            'commission_amount' => 85,
            'sold_at' => Carbon::today(),
        ]);

        Sale::factory()->count(3)->create([
            'seller_id' => $sellers->get(1)->id,
            'amount' => 500,
            'commission_amount' => 42.5,
            'sold_at' => Carbon::yesterday(),
        ]);

        Sale::factory()->count(2)->create([
            'seller_id' => $sellers->last()->id,
            'amount' => 750,
            'commission_amount' => 63.75,
            'sold_at' => Carbon::now()->subDays(30),
        ]);

        $response = $this->getJson('/api/dashboard/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'general' => [
                        'total_sellers',
                        'total_sales',
                        'total_sales_amount',
                        'formatted_total_sales_amount',
                        'total_commissions',
                        'formatted_total_commissions',
                        'average_sale_amount',
                        'formatted_average_sale_amount',
                    ],
                    'today' => [
                        'sales_count',
                        'sales_amount',
                        'formatted_sales_amount',
                    ],
                    'this_month' => [
                        'sales_count',
                        'sales_amount',
                        'formatted_sales_amount',
                    ],
                    'top_sellers' => [
                        '*' => [
                            'id',
                            'name',
                            'email',
                            'sales_count',
                            'total_amount',
                            'formatted_total_amount',
                            'total_commission',
                            'formatted_total_commission',
                        ]
                    ],
                    'sales_by_month' => [
                        '*' => [
                            'month',
                            'month_name',
                            'sales_count',
                            'sales_amount',
                            'formatted_amount',
                        ]
                    ]
                ]
            ]);

        $data = $response->json('data');

        $this->assertEquals(3, $data['general']['total_sellers']);
        $this->assertEquals(10, $data['general']['total_sales']);

        $this->assertEquals(5, $data['today']['sales_count']);
        $this->assertEquals(5000, $data['today']['sales_amount']);

        $this->assertGreaterThan(0, count($data['top_sellers']));

        $this->assertCount(6, $data['sales_by_month']);
    }

    public function it_returns_zero_stats_when_no_data_exists()
    {
        $response = $this->getJson('/api/dashboard/stats');

        $response->assertStatus(200);

        $data = $response->json('data');

        $this->assertEquals(0, $data['general']['total_sellers']);
        $this->assertEquals(0, $data['general']['total_sales']);
        $this->assertEquals(0, $data['today']['sales_count']);
        $this->assertEquals(0, $data['this_month']['sales_count']);
        $this->assertEmpty($data['top_sellers']);
    }

    public function it_requires_authentication()
    {
        Sanctum::actingAs(null);

        $response = $this->getJson('/api/dashboard/stats');

        $response->assertStatus(401);
    }

    public function it_formats_currency_correctly()
    {
        $seller = Seller::factory()->create();
        Sale::factory()->create([
            'seller_id' => $seller->id,
            'amount' => 1234.56,
            'commission_amount' => 104.94,
            'sold_at' => Carbon::today(),
        ]);

        $response = $this->getJson('/api/dashboard/stats');

        $response->assertStatus(200);

        $data = $response->json('data');

        $this->assertEquals('R$ 1.234,56', $data['general']['formatted_total_sales_amount']);
        $this->assertEquals('R$ 104,94', $data['general']['formatted_total_commissions']);
        $this->assertEquals('R$ 1.234,56', $data['today']['formatted_sales_amount']);
    }

    public function it_orders_top_sellers_by_sales_amount()
    {
        $seller1 = Seller::factory()->create(['name' => 'Vendedor 1']);
        $seller2 = Seller::factory()->create(['name' => 'Vendedor 2']);
        $seller3 = Seller::factory()->create(['name' => 'Vendedor 3']);

        Sale::factory()->count(3)->create([
            'seller_id' => $seller2->id,
            'amount' => 1000,
            'commission_amount' => 85,
        ]);

        Sale::factory()->count(2)->create([
            'seller_id' => $seller1->id,
            'amount' => 500,
            'commission_amount' => 42.5,
        ]);

        Sale::factory()->create([
            'seller_id' => $seller3->id,
            'amount' => 200,
            'commission_amount' => 17,
        ]);

        $response = $this->getJson('/api/dashboard/stats');

        $response->assertStatus(200);

        $topSellers = $response->json('data.top_sellers');

        $this->assertEquals('Vendedor 2', $topSellers[0]['name']);
        $this->assertEquals(3000, $topSellers[0]['total_amount']);

        $this->assertEquals('Vendedor 1', $topSellers[1]['name']);
        $this->assertEquals(1000, $topSellers[1]['total_amount']);

        $this->assertEquals('Vendedor 3', $topSellers[2]['name']);
        $this->assertEquals(200, $topSellers[2]['total_amount']);
    }
}
