<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\LedgerAccount;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class LedgerAccountSeeder extends Seeder
{
    public function run(): void
    {
        LedgerAccount::query()->firstOrCreate(
            ['name' => 'Cash in Hand', 'type' => 'cash'],
            ['is_system' => true],
        );

        LedgerAccount::query()->firstOrCreate(
            ['name' => 'Bank Account', 'type' => 'bank'],
            ['is_system' => true],
        );

        LedgerAccount::salesIncomeAccount();
        LedgerAccount::purchaseExpenseAccount();

        // DatabaseSeeder runs without model events, so backfill the party accounts
        // that the Customer/Supplier "created" hooks would otherwise have made.
        Customer::query()->each(fn (Customer $customer) => LedgerAccount::ensureForCustomer($customer));
        Supplier::query()->each(fn (Supplier $supplier) => LedgerAccount::ensureForSupplier($supplier));
    }
}
