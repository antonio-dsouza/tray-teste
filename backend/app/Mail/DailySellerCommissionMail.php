<?php

namespace App\Mail;

use App\Models\Seller;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DailySellerCommissionMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Seller $seller,
        public int $count,
        public float $totalAmount,
        public float $totalCommission,
        public string $date
    ) {}

    public function build()
    {
        $formattedDate = Carbon::parse($this->date)->format('d/m/Y');

        return $this->subject("Resumo de Vendas - {$formattedDate}")
            ->view('emails.daily_seller_commission');
    }
}
