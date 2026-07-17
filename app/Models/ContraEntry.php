<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContraEntry extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'contra_entries';

    protected $fillable = [
        'code',
        'entry_date',
        'from_ledger_account_id',
        'to_ledger_account_id',
        'amount',
        'narration',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'datetime',
            'amount' => 'decimal:2',
        ];
    }

    public function fromLedgerAccount(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'from_ledger_account_id');
    }

    public function toLedgerAccount(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'to_ledger_account_id');
    }

    public function postLedgerEntries(): void
    {
        LedgerEntry::clearFor(self::class, $this->id);

        LedgerEntry::postPair(
            debitAccount: $this->toLedgerAccount,
            creditAccount: $this->fromLedgerAccount,
            amount: (float) $this->amount,
            entryDate: $this->entry_date,
            voucherNo: $this->code,
            narration: $this->narration,
            sourceType: self::class,
            sourceId: $this->id,
        );
    }
}
