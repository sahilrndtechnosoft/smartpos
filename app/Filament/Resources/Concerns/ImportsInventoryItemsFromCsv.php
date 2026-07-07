<?php

namespace App\Filament\Resources\Concerns;

use App\Services\InventoryItemsCsvParser;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

trait ImportsInventoryItemsFromCsv
{
    protected function getImportInventoryItemsAction(): Action
    {
        return Action::make('importItems')
            ->label('Import items')
            ->icon(Heroicon::ArrowUpTray)
            ->modalHeading('Import inventory items')
            ->modalDescription('Upload a CSV with columns: sku, qty, purchase_rate, rate_a, mrp, expiry_date.')
            ->modalSubmitActionLabel('Import')
            ->form([
                FileUpload::make('csv_file')
                    ->label('CSV file')
                    ->acceptedFileTypes([
                        'text/csv',
                        'text/plain',
                        'application/vnd.ms-excel',
                    ])
                    ->required()
                    ->maxSize(5120),
            ])
            ->action(function (array $data): void {
                $uploadedFile = $data['csv_file'];

                if (is_array($uploadedFile)) {
                    $uploadedFile = $uploadedFile[0] ?? null;
                }

                if (! $uploadedFile instanceof TemporaryUploadedFile) {
                    Notification::make()
                        ->title('Import failed')
                        ->body('Please upload a valid CSV file.')
                        ->danger()
                        ->send();

                    return;
                }

                try {
                    $parsedItems = app(InventoryItemsCsvParser::class)->parse($uploadedFile->getRealPath());
                    $existingItems = $this->data['items'] ?? [];

                    $this->data['items'] = array_values(array_merge($existingItems, $parsedItems));

                    Notification::make()
                        ->title('Items imported')
                        ->body(count($parsedItems).' line item(s) added to this inventory.')
                        ->success()
                        ->send();

                    $this->dispatch('pos-barcode-focus');
                } catch (\Throwable $exception) {
                    Notification::make()
                        ->title('Import failed')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
