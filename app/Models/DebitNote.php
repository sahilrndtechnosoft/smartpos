<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DebitNote extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'note_date',
        'supplier_id',
        'amount',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'note_date' => 'datetime',
            'amount' => 'decimal:2',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * A debit note reduces what we owe the supplier, and reduces the purchase
     * expense recognized against them.
     */
    public function postLedgerEntries(): void
    {
        LedgerEntry::clearFor(self::class, $this->id);

        LedgerEntry::postPair(
            debitAccount: LedgerAccount::ensureForSupplier($this->supplier),
            creditAccount: LedgerAccount::purchaseExpenseAccount(),
            amount: (float) $this->amount,
            entryDate: $this->note_date,
            voucherNo: $this->code,
            narration: $this->reason,
            sourceType: self::class,
            sourceId: $this->id,
        );
    }
}
