<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Receipt extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'receipt_date',
        'customer_id',
        'ledger_account_id',
        'amount',
        'narration',
    ];

    protected function casts(): array
    {
        return [
            'receipt_date' => 'datetime',
            'amount' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function ledgerAccount(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class);
    }

    public function postLedgerEntries(): void
    {
        LedgerEntry::clearFor(self::class, $this->id);

        LedgerEntry::postPair(
            debitAccount: $this->ledgerAccount,
            creditAccount: LedgerAccount::ensureForCustomer($this->customer),
            amount: (float) $this->amount,
            entryDate: $this->receipt_date,
            voucherNo: $this->code,
            narration: $this->narration,
            sourceType: self::class,
            sourceId: $this->id,
        );
    }
}
