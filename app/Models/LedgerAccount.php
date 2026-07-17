<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LedgerAccount extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'customer_id',
        'supplier_id',
        'opening_balance',
        'is_system',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'is_system' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class);
    }

    /**
     * Positive = a debit balance, negative = a credit balance.
     */
    public function currentBalance(): float
    {
        return round(
            (float) $this->opening_balance
                + (float) $this->entries()->sum('debit')
                - (float) $this->entries()->sum('credit'),
            2,
        );
    }

    /**
     * @return array{amount: float, side: string}
     */
    public function balanceForDisplay(): array
    {
        $balance = $this->currentBalance();

        return [
            'amount' => abs($balance),
            'side' => $balance >= 0 ? 'Dr' : 'Cr',
        ];
    }

    public static function ensureForCustomer(Customer $customer): self
    {
        return self::query()->firstOrCreate(
            ['customer_id' => $customer->id],
            [
                'name' => $customer->name ?: $customer->phone,
                'type' => 'sundry_debtor',
            ],
        );
    }

    public static function ensureForSupplier(Supplier $supplier): self
    {
        return self::query()->firstOrCreate(
            ['supplier_id' => $supplier->id],
            [
                'name' => $supplier->name,
                'type' => 'sundry_creditor',
            ],
        );
    }

    public static function salesIncomeAccount(): self
    {
        return self::query()->firstOrCreate(
            ['name' => 'Sales Income', 'type' => 'income'],
            ['is_system' => true],
        );
    }

    public static function purchaseExpenseAccount(): self
    {
        return self::query()->firstOrCreate(
            ['name' => 'Purchase Expense', 'type' => 'expense'],
            ['is_system' => true],
        );
    }
}
