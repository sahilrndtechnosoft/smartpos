<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\ContraEntries\Pages\CreateContraEntry;
use App\Filament\Resources\CreditNotes\Pages\CreateCreditNote;
use App\Filament\Resources\DebitNotes\Pages\CreateDebitNote;
use App\Filament\Resources\Receipts\Pages\CreateReceipt;
use App\Filament\Resources\Vouchers\Pages\CreateVoucher;
use App\Models\Customer;
use App\Models\LedgerAccount;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Tests\TestCase;

class LedgerAccountsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@smartpos.local')->firstOrFail();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($admin);
        Filament::auth()->login($admin);
    }

    public function test_customer_and_supplier_creation_auto_creates_ledger_accounts(): void
    {
        $customer = Customer::query()->create([
            'phone' => '9998887771',
            'name' => 'Ledger Test Customer',
            'is_active' => true,
            'preferences' => [],
        ]);

        $supplier = Supplier::query()->create([
            'name' => 'Ledger Test Supplier',
            'email' => 'ledger-test-supplier@example.com',
            'phone' => '9998887771',
            'address' => 'Test Address',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('ledger_accounts', [
            'customer_id' => $customer->id,
            'type' => 'sundry_debtor',
        ]);

        $this->assertDatabaseHas('ledger_accounts', [
            'supplier_id' => $supplier->id,
            'type' => 'sundry_creditor',
        ]);
    }

    public function test_voucher_posts_a_balanced_debit_and_credit_entry(): void
    {
        $cash = LedgerAccount::query()->where('name', 'Cash in Hand')->firstOrFail();
        $expense = LedgerAccount::query()->where('name', 'Purchase Expense')->firstOrFail();

        $test = Livewire::test(CreateVoucher::class)
            ->fillForm([
                'voucher_date' => now()->toDateTimeString(),
                'debit_ledger_account_id' => $expense->id,
                'credit_ledger_account_id' => $cash->id,
                'amount' => 500,
                'narration' => 'Office supplies',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $voucher = $test->instance()->record;

        $this->assertDatabaseHas('ledger_entries', [
            'ledger_account_id' => $expense->id,
            'debit' => 500,
            'credit' => 0,
            'voucher_no' => $voucher->code,
        ]);

        $this->assertDatabaseHas('ledger_entries', [
            'ledger_account_id' => $cash->id,
            'debit' => 0,
            'credit' => 500,
            'voucher_no' => $voucher->code,
        ]);

        $this->assertSame(500.0, $expense->currentBalance());
        $this->assertSame(-500.0, $cash->refresh()->currentBalance());
    }

    public function test_receipt_reduces_customer_outstanding_balance(): void
    {
        $customer = Customer::query()->create([
            'phone' => '9998887772',
            'name' => 'Receipt Test Customer',
            'is_active' => true,
            'preferences' => [],
        ]);

        $customerLedger = LedgerAccount::ensureForCustomer($customer);
        $customerLedger->update(['opening_balance' => 1000]);

        $cash = LedgerAccount::query()->where('name', 'Cash in Hand')->firstOrFail();

        Livewire::test(CreateReceipt::class)
            ->fillForm([
                'customer_id' => $customer->id,
                'ledger_account_id' => $cash->id,
                'receipt_date' => now()->toDateTimeString(),
                'amount' => 400,
                'narration' => 'Part payment',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(600.0, $customerLedger->refresh()->currentBalance());
        $this->assertSame(400.0, $cash->refresh()->currentBalance());
    }

    public function test_debit_note_reduces_supplier_payable(): void
    {
        $supplier = Supplier::query()->create([
            'name' => 'Debit Note Supplier',
            'email' => 'debit-note-supplier@example.com',
            'phone' => '9998887774',
            'address' => 'Test Address',
            'is_active' => true,
        ]);

        $supplierLedger = LedgerAccount::ensureForSupplier($supplier);
        $supplierLedger->update(['opening_balance' => -2000]);

        Livewire::test(CreateDebitNote::class)
            ->fillForm([
                'supplier_id' => $supplier->id,
                'note_date' => now()->toDateTimeString(),
                'amount' => 300,
                'reason' => 'Damaged goods',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(-1700.0, $supplierLedger->refresh()->currentBalance());
    }

    public function test_credit_note_reduces_customer_receivable(): void
    {
        $customer = Customer::query()->create([
            'phone' => '9998887773',
            'name' => 'Credit Note Customer',
            'is_active' => true,
            'preferences' => [],
        ]);

        $customerLedger = LedgerAccount::ensureForCustomer($customer);
        $customerLedger->update(['opening_balance' => 1000]);

        Livewire::test(CreateCreditNote::class)
            ->fillForm([
                'customer_id' => $customer->id,
                'note_date' => now()->toDateTimeString(),
                'amount' => 250,
                'reason' => 'Price adjustment',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(750.0, $customerLedger->refresh()->currentBalance());
    }

    public function test_contra_entry_transfers_between_cash_and_bank(): void
    {
        $cash = LedgerAccount::query()->where('name', 'Cash in Hand')->firstOrFail();
        $bank = LedgerAccount::query()->where('name', 'Bank Account')->firstOrFail();

        Livewire::test(CreateContraEntry::class)
            ->fillForm([
                'from_ledger_account_id' => $cash->id,
                'to_ledger_account_id' => $bank->id,
                'entry_date' => now()->toDateTimeString(),
                'amount' => 1000,
                'narration' => 'Deposited cash',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(-1000.0, $cash->refresh()->currentBalance());
        $this->assertSame(1000.0, $bank->refresh()->currentBalance());
    }
}
