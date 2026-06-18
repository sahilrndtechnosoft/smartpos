<?php

namespace App\Filament\Resources\Sessions\Schemas;

use App\Models\Session;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SessionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Session overview')
                    ->icon(Heroicon::OutlinedComputerDesktop)
                    ->description('Connection details for this browser session.')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('id')
                            ->label('Session ID')
                            ->copyable()
                            ->columnSpanFull(),

                        TextEntry::make('user.name')
                            ->label('User')
                            ->placeholder('Guest'),

                        TextEntry::make('user.email')
                            ->label('Email')
                            ->placeholder('—'),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->getStateUsing(fn (Session $record): string => $record->getStatusLabel())
                            ->color(fn (Session $record): string => match ($record->getStatusLabel()) {
                                'Current' => 'primary',
                                'Active' => 'success',
                                default => 'gray',
                            }),

                        TextEntry::make('ip_address')
                            ->label('IP address')
                            ->placeholder('—'),

                        TextEntry::make('last_activity')
                            ->label('Last activity')
                            ->formatStateUsing(fn (Session $record): string => $record->last_activity_at?->format('d M Y, h:i A') ?? '—')
                            ->helperText(fn (Session $record): ?string => $record->last_activity_at?->diffForHumans()),
                    ]),

                Section::make('Device information')
                    ->icon(Heroicon::OutlinedDevicePhoneMobile)
                    ->schema([
                        TextEntry::make('user_agent')
                            ->label('User agent')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
