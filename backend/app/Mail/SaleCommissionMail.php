<?php

namespace App\Mail;

use App\Models\Sale;
use App\Models\Seller;
use App\Services\Commissions\Contracts\CommissionCalculatorInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SaleCommissionMail extends Mailable
{
    use Queueable, SerializesModels;

    public readonly Sale $sale;
    public readonly Seller $seller;
    public readonly float $commission;

    public function __construct(Sale $sale, Seller $seller)
    {
        $this->sale = $sale;
        $this->seller = $seller;

        $calculator = app(CommissionCalculatorInterface::class);
        $this->commission = $calculator->calculateCommission($sale->amount);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nova Venda Realizada - Comissão Disponível',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.sale-commission',
            with: [
                'seller' => $this->seller,
                'sale' => $this->sale,
                'commission' => $this->commission,
                'formattedAmount' => 'R$ ' . number_format($this->sale->amount, 2, ',', '.'),
                'formattedCommission' => 'R$ ' . number_format($this->commission, 2, ',', '.'),
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
