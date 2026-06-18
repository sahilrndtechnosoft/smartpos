<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (User $record): string => $record->email),

                TextColumn::make('email_verified_at')
                    ->label('Verification')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => filled($state) ? 'Verified' : 'Unverified')
                    ->color(fn (?string $state): string => filled($state) ? 'success' : 'warning')
                    ->sortable(),

                TextColumn::make('is_super_admin')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Super admin' : 'Staff')
                    ->color(fn (bool $state): string => $state ? 'primary' : 'gray')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Joined')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Last updated')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->striped()
            ->filters([
                TernaryFilter::make('email_verified_at')
                    ->label('Email verification')
                    ->placeholder('All users')
                    ->trueLabel('Verified only')
                    ->falseLabel('Unverified only')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('email_verified_at'),
                        false: fn ($query) => $query->whereNull('email_verified_at'),
                    ),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make()
                        ->hidden(fn (User $record): bool => $record->is(Auth::user())),
                ])
                    ->tooltip('Actions'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function (Collection $records): void {
                            if ($records->contains(fn (User $record): bool => $record->is(Auth::user()))) {
                                Notification::make()
                                    ->title('You cannot delete your own account')
                                    ->danger()
                                    ->send();

                                throw new Halt;
                            }
                        }),
                ]),
            ])
            ->emptyStateHeading('No users yet')
            ->emptyStateDescription('Create a user account to grant access to the admin panel.')
            ->emptyStateIcon(Heroicon::OutlinedUsers);
    }
}
