<?php

namespace Tests\Unit\Services;

use App\Mail\DailyAdminSummaryMail;
use App\Mail\DailySellerCommissionMail;
use App\Mail\SaleCommissionMail;
use App\Models\Sale;
use App\Models\Seller;
use App\Services\Emails\EmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailServiceTest extends TestCase
{
    use RefreshDatabase;

    private EmailService $emailService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->emailService = new EmailService();
        Mail::fake();
    }

    public function test_send_daily_commission_to_seller_queues_email(): void
    {
        $seller = Seller::factory()->make([
            'id' => 1,
            'email' => 'john@seller.com'
        ]);

        $summaryData = [
            'count' => 5,
            'total_amount' => 1000.00,
            'commission' => 85.00,
            'date' => '2024-01-15'
        ];

        $result = $this->emailService->sendDailyCommissionToSeller($seller, $summaryData);

        $this->assertTrue($result);

        Mail::assertQueued(DailySellerCommissionMail::class, function ($mail) use ($seller, $summaryData) {
            return $mail->hasTo($seller->email) &&
                $mail->seller->id === $seller->id &&
                $mail->count === $summaryData['count'] &&
                $mail->totalAmount === $summaryData['total_amount'] &&
                $mail->totalCommission === $summaryData['commission'] &&
                $mail->date === $summaryData['date'];
        });
    }

    public function test_send_daily_commission_to_seller_handles_exception(): void
    {
        Mail::shouldReceive('to')
            ->andThrow(new \Exception('Mail error'));

        $seller = Seller::factory()->make([
            'id' => 1,
            'email' => 'john@seller.com'
        ]);

        $summaryData = [
            'count' => 5,
            'total_amount' => 1000.00,
            'commission' => 85.00,
            'date' => '2024-01-15'
        ];

        $result = $this->emailService->sendDailyCommissionToSeller($seller, $summaryData);

        $this->assertFalse($result);
    }

    public function test_send_daily_summary_to_admin_queues_email(): void
    {
        $adminEmail = 'admin@example.com';
        config(['mail.admin_email' => $adminEmail]);

        $date = '2024-01-15';
        $summaryData = [
            'total_sales' => 15,
            'total_amount' => 3500.00,
            'total_commission' => 297.50
        ];

        $result = $this->emailService->sendDailySummaryToAdmin($date, $summaryData);

        $this->assertTrue($result);

        Mail::assertQueued(DailyAdminSummaryMail::class, function ($mail) use ($adminEmail, $summaryData, $date) {
            return $mail->hasTo($adminEmail) &&
                $mail->totalSales === $summaryData['total_sales'] &&
                $mail->totalAmount === $summaryData['total_amount'] &&
                $mail->date === $date;
        });
    }

    public function test_send_daily_summary_to_admin_with_missing_config_returns_false(): void
    {
        config(['mail.admin_email' => null]);

        $date = '2024-01-15';
        $summaryData = [
            'total_sales' => 15,
            'total_amount' => 3500.00,
            'total_commission' => 297.50
        ];

        $result = $this->emailService->sendDailySummaryToAdmin($date, $summaryData);

        $this->assertFalse($result);
    }

    public function test_send_sale_commission_to_seller_queues_email(): void
    {
        $seller = Seller::factory()->make(['id' => 1, 'email' => 'seller@example.com']);
        $sale = Sale::factory()->make(['id' => 1, 'amount' => 500.00, 'commission_amount' => 42.50]);
        $sale->setRelation('seller', $seller);

        $result = $this->emailService->sendSaleCommissionToSeller($sale);

        $this->assertTrue($result);

        Mail::assertQueued(SaleCommissionMail::class, function ($mail) use ($sale) {
            return $mail->hasTo($sale->seller->email) &&
                $mail->sale->id === $sale->id;
        });
    }

    public function test_send_sale_commission_to_seller_with_no_seller_returns_false(): void
    {
        $sale = Sale::factory()->make([
            'id' => 1,
            'amount' => 500.00,
            'seller_id' => null
        ]);

        $result = $this->emailService->sendSaleCommissionToSeller($sale);

        $this->assertFalse($result);
    }
}
