<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditNote extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'note_date',
        'customer_id',
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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * A credit note reduces what the customer owes us, and reduces the sales
     * income recognized against them.
     */
    public function postLedgerEntries(): void
    {
        LedgerEntry::clearFor(self::class, $this->id);

        LedgerEntry::postPair(
            debitAccount: LedgerAccount::salesIncomeAccount(),
            creditAccount: LedgerAccount::ensureForCustomer($this->customer),
            amount: (float) $this->amount,
            entryDate: $this->note_date,
            voucherNo: $this->code,
            narration: $this->reason,
            sourceType: self::class,
            sourceId: $this->id,
        );
    }
}
