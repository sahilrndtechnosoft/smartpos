<?php

namespace App\Filament\Resources\Sessions;

use App\Filament\Resources\Concerns\AuthorizesModuleAccess;
use App\Filament\Resources\Sessions\Pages\ListSessions;
use App\Filament\Resources\Sessions\Pages\ViewSession;
use App\Filament\Resources\Sessions\Schemas\SessionInfolist;
use App\Filament\Resources\Sessions\Tables\SessionsTable;
use App\Models\Session;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class SessionResource extends Resource
{
    use AuthorizesModuleAccess;

    protected static ?string $model = Session::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedComputerDesktop;

    protected static ?string $navigationLabel = 'Sessions';

    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'session';

    protected static ?string $pluralModelLabel = 'sessions';

    protected static ?string $recordTitleAttribute = 'id';

    public static function infolist(Schema $schema): Schema
    {
        return SessionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SessionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSessions::route('/'),
            'view' => ViewSession::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Session::query()
            ->where('last_activity', '>=', now()->subMinutes((int) config('session.lifetime', 120))->timestamp)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Active sessions';
    }

    protected static function getModuleKey(): string
    {
        return 'sessions';
    }
}
