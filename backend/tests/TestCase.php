<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearDatabaseTransactions();
    }

    protected function tearDown(): void
    {
        $this->clearDatabaseTransactions();

        parent::tearDown();
    }

    private function clearDatabaseTransactions(): void
    {
        try {
            while (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
        } catch (\Exception $e) {
            try {
                DB::disconnect();
                DB::reconnect();
            } catch (\Exception $reconnectException) {
                //
            }
        }
    }
}
