<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Support\ModuleRegistry;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class UserPermissionsFormSection
{
    public static function make(): Section
    {
        return Section::make('Module permissions')
            ->icon(Heroicon::OutlinedShieldCheck)
            ->description('Choose which modules this user can view, create, edit, or delete.')
            ->visible(fn (string $operation): bool => $operation === 'create' && (Auth::user()?->isSuperAdmin() ?? false))
            ->hidden(fn (Get $get): bool => (bool) $get('is_super_admin'))
            ->schema(
                collect(ModuleRegistry::all())
                    ->map(
                        fn (string $label, string $module): Section => Section::make($label)
                            ->schema([
                                Toggle::make("permissions.{$module}.can_view")
                                    ->label('View')
                                    ->default(false),

                                Toggle::make("permissions.{$module}.can_create")
                                    ->label('Create')
                                    ->default(false),

                                Toggle::make("permissions.{$module}.can_edit")
                                    ->label('Edit')
                                    ->default(false),

                                Toggle::make("permissions.{$module}.can_delete")
                                    ->label('Delete')
                                    ->default(false),
                            ])
                            ->columns(4)
                            ->compact(),
                    )
                    ->values()
                    ->all(),
            );
    }
}
