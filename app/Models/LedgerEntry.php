<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LedgerEntry extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'ledger_account_id',
        'entry_date',
        'debit',
        'credit',
        'narration',
        'voucher_no',
        'source_type',
        'source_id',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'datetime',
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
        ];
    }

    public function ledgerAccount(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class);
    }

    /**
     * Records a balanced pair of entries (one debit, one credit) for a transaction.
     */
    public static function postPair(
        LedgerAccount $debitAccount,
        LedgerAccount $creditAccount,
        float $amount,
        \DateTimeInterface $entryDate,
        string $voucherNo,
        ?string $narration = null,
        ?string $sourceType = null,
        ?string $sourceId = null,
    ): void {
        $attributes = [
            'entry_date' => $entryDate,
            'narration' => $narration,
            'voucher_no' => $voucherNo,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
        ];

        $debitAccount->entries()->create([
            ...$attributes,
            'debit' => $amount,
            'credit' => 0,
        ]);

        $creditAccount->entries()->create([
            ...$attributes,
            'debit' => 0,
            'credit' => $amount,
        ]);
    }

    /**
     * Removes any previously posted entries for a given source record, so an edit
     * can safely re-post from scratch.
     */
    public static function clearFor(string $sourceType, string $sourceId): void
    {
        self::query()
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->delete();
    }
}
