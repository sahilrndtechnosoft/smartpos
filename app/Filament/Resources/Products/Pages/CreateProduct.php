<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Concerns\HasFormActionsAtTopAndBottom;
use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CreateProduct extends CreateRecord
{
    use HasFormActionsAtTopAndBottom;

    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $userId = Auth::id();

        if ($userId) {
            $data['created_by'] = $userId;
            $data['updated_by'] = $userId;
        }

        $data['slug'] = Str::slug($data['name'] ?? '');

        return $data;
    }
}
