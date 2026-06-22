<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

class PrintDocumentAction
{
    public static function make(string $name, string $label, string $routeName): Action
    {
        return Action::make($name)
            ->label($label)
            ->icon(Heroicon::OutlinedPrinter)
            ->url(fn (Model $record): string => route($routeName, $record))
            ->openUrlInNewTab();
    }
}
