<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DailyAdminSummaryMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $date,
        public int $totalSales,
        public float $totalAmount
    ) {}

    public function build()
    {
        $formattedDate = Carbon::parse($this->date)->format('d/m/Y');

        return $this->subject("Resumo Geral de Vendas - {$formattedDate}")
            ->view('emails.daily_admin_summary');
    }
}
