<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Widgets\Concerns\InteractsWithDashboardFilters;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentSalesOrdersTable extends BaseWidget
{
    use InteractsWithDashboardFilters;

    protected static ?int $sort = 14;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Sales orders')
            ->description('Orders matching the dashboard date range and payment mode filters.')
            ->query(fn (): Builder => $this->statsService()->recentOrdersQuery($this->dashboardFilters()))
            ->columns([
                TextColumn::make('code')
                    ->label('Order #')
                    ->searchable()
                    ->weight('medium'),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('ordered_at')
                    ->label('Ordered at')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('payment_mode')
                    ->label('Payment mode')
                    ->badge(),

                TextColumn::make('grand_total')
                    ->label('Grand total')
                    ->money('INR')
                    ->alignEnd(),
            ])
            ->filters([
                SelectFilter::make('payment_mode')
                    ->options([
                        'cod' => 'Cash',
                        'online' => 'Online',
                        'card' => 'Card',
                        'upi' => 'UPI',
                        'wallet' => 'Wallet',
                        'credit' => 'Credit',
                        'multi' => 'Multiple',
                    ]),
            ])
            ->recordUrl(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record]))
            ->headerActions([
                Action::make('viewAll')
                    ->label('View all orders')
                    ->url(OrderResource::getUrl('index')),
            ])
            ->striped()
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10);
    }
}
