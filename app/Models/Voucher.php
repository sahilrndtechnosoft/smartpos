<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'voucher_date',
        'debit_ledger_account_id',
        'credit_ledger_account_id',
        'amount',
        'narration',
    ];

    protected function casts(): array
    {
        return [
            'voucher_date' => 'datetime',
            'amount' => 'decimal:2',
        ];
    }

    public function debitLedgerAccount(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'debit_ledger_account_id');
    }

    public function creditLedgerAccount(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'credit_ledger_account_id');
    }

    public function postLedgerEntries(): void
    {
        LedgerEntry::clearFor(self::class, $this->id);

        LedgerEntry::postPair(
            debitAccount: $this->debitLedgerAccount,
            creditAccount: $this->creditLedgerAccount,
            amount: (float) $this->amount,
            entryDate: $this->voucher_date,
            voucherNo: $this->code,
            narration: $this->narration,
            sourceType: self::class,
            sourceId: $this->id,
        );
    }
}
