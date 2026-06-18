<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account details')
                    ->icon(Heroicon::OutlinedUserCircle)
                    ->description('Basic login credentials and profile information.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(table: User::class, ignoreRecord: true)
                            ->columnSpanFull(),

                        Toggle::make('is_verified')
                            ->label('Email verified')
                            ->default(false)
                            ->helperText('Mark whether this user has a verified email address.'),

                        Toggle::make('is_super_admin')
                            ->label('Super admin')
                            ->default(false)
                            ->helperText('Super admins have full access to all modules and can manage permissions.')
                            ->visible(fn (): bool => Auth::user()?->isSuperAdmin() ?? false)
                            ->disabled(fn (string $operation, ?User $record): bool => $operation !== 'create' && $record?->is(Auth::user())),

                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->rule(Password::default())
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->same('passwordConfirmation')
                            ->hidden(fn (string $operation): bool => $operation === 'view')
                            ->columnSpanFull(),

                        TextInput::make('passwordConfirmation')
                            ->label('Confirm password')
                            ->password()
                            ->revealable()
                            ->required(fn (Get $get, string $operation): bool => $operation === 'create' || filled($get('password')))
                            ->dehydrated(false)
                            ->hidden(fn (string $operation): bool => $operation === 'view')
                            ->columnSpanFull(),
                    ]),

                UserPermissionsFormSection::make(),
            ]);
    }
}
