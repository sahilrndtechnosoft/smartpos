<?php

namespace App\Filament\Resources\Sessions\Tables;

use App\Models\Session;
use App\Models\User;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class SessionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->placeholder('Guest')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (Session $record): string => $record->user?->email ?? 'Unauthenticated session'),

                TextColumn::make('ip_address')
                    ->label('IP address')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('user_agent')
                    ->label('Device / browser')
                    ->limit(45)
                    ->tooltip(fn (Session $record): ?string => $record->user_agent)
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn (Session $record): string => $record->getStatusLabel())
                    ->color(fn (Session $record): string => match ($record->getStatusLabel()) {
                        'Current' => 'primary',
                        'Active' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('last_activity')
                    ->label('Last activity')
                    ->formatStateUsing(fn (Session $record): string => $record->last_activity_at?->diffForHumans() ?? '—')
                    ->description(fn (Session $record): ?string => $record->last_activity_at?->format('d M Y, h:i A'))
                    ->sortable(),

                TextColumn::make('id')
                    ->label('Session ID')
                    ->limit(12)
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('last_activity', 'desc')
            ->striped()
            ->filters([
                TernaryFilter::make('authenticated')
                    ->label('Authentication')
                    ->placeholder('All sessions')
                    ->trueLabel('Authenticated only')
                    ->falseLabel('Guest only')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('user_id'),
                        false: fn ($query) => $query->whereNull('user_id'),
                    ),

                SelectFilter::make('user_id')
                    ->label('User')
                    ->options(fn (): array => User::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->preload(),
            ])
            ->filtersFormColumns(2)
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    DeleteAction::make()
                        ->label('Revoke')
                        ->modalHeading('Revoke session')
                        ->modalDescription('This will immediately sign the user out of this device.')
                        ->successNotificationTitle('Session revoked')
                        ->hidden(fn (Session $record): bool => $record->isCurrent()),
                ])
                    ->tooltip('Actions'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Revoke selected')
                        ->modalHeading('Revoke selected sessions')
                        ->successNotificationTitle('Sessions revoked')
                        ->before(function (Collection $records): void {
                            if ($records->contains(fn (Session $record): bool => $record->isCurrent())) {
                                Notification::make()
                                    ->title('You cannot revoke your current session')
                                    ->danger()
                                    ->send();

                                throw new Halt;
                            }
                        }),
                ]),
            ])
            ->emptyStateHeading('No sessions logged yet')
            ->emptyStateDescription('Sessions appear here once users sign in with the database session driver enabled.')
            ->emptyStateIcon(Heroicon::OutlinedComputerDesktop);
    }
}
