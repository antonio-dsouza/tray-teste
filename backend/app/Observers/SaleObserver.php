<?php

namespace App\Observers;

use App\Jobs\SendSaleCommissionJob;
use App\Models\Sale;
use Illuminate\Support\Facades\Log;

class SaleObserver
{
    public function created(Sale $sale): void
    {
        try {
            Log::info('Sale created, dispatching commission email job', [
                'sale_id' => $sale->id,
                'seller_id' => $sale->seller_id,
                'amount' => $sale->amount
            ]);

            SendSaleCommissionJob::dispatch($sale->id);
        } catch (\Exception $e) {
            Log::error('Failed to dispatch sale commission job', [
                'sale_id' => $sale->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
