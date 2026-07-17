<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\InventoryStatusChartWidget;
use App\Filament\Widgets\InventoryStockStatsWidget;
use App\Filament\Widgets\LowStockProductsTable;
use App\Filament\Widgets\NearExpiryTable;
use App\Filament\Widgets\OrdersCountChartWidget;
use App\Filament\Widgets\OverviewStatsWidget;
use App\Filament\Widgets\PaymentModeChartWidget;
use App\Filament\Widgets\PendingBillsStatsWidget;
use App\Filament\Widgets\PendingCustomerPaymentsTable;
use App\Filament\Widgets\PendingSupplierBillsTable;
use App\Filament\Widgets\RecentSalesOrdersTable;
use App\Filament\Widgets\SalesStatsWidget;
use App\Filament\Widgets\SalesTrendChartWidget;
use App\Filament\Widgets\TopProductsChartWidget;
use App\Models\Supplier;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $title = 'Dashboard';

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dashboard filters')
                    ->description('Filter stats, charts, and tables across the dashboard.')
                    ->icon(Heroicon::OutlinedFunnel)
                    ->schema([
                        DatePicker::make('date_from')
                            ->label('From date')
                            ->native(false)
                            ->default(now()->startOfMonth()),

                        DatePicker::make('date_to')
                            ->label('To date')
                            ->native(false)
                            ->default(now()),

                        Select::make('supplier_id')
                            ->label('Supplier')
                            ->options(fn (): array => Supplier::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->native(false)
                            ->placeholder('All suppliers'),

                        Select::make('inventory_status')
                            ->label('Supplier bill status')
                            ->options([
                                'all' => 'All statuses',
                                'pending' => 'Pending',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('pending')
                            ->native(false),

                        Select::make('payment_status')
                            ->label('Customer payment status')
                            ->options([
                                'all' => 'All statuses',
                                'pending' => 'Pending',
                                'completed' => 'Completed',
                                'failed' => 'Failed',
                            ])
                            ->default('pending')
                            ->native(false),

                        Select::make('payment_mode')
                            ->label('Sales payment mode')
                            ->options([
                                'cod' => 'Cash',
                                'online' => 'Online',
                                'card' => 'Card',
                                'upi' => 'UPI',
                                'wallet' => 'Wallet',
                                'credit' => 'Credit',
                                'multi' => 'Multiple',
                            ])
                            ->native(false)
                            ->placeholder('All modes'),

                        Select::make('stock_alert')
                            ->label('Stock alert')
                            ->options([
                                '' => 'All products',
                                'low_stock' => 'Low stock only',
                                'out_of_stock' => 'Out of stock only',
                                'expiring' => 'Expiring soon only',
                            ])
                            ->native(false),

                        Select::make('trend_days')
                            ->label('Chart period')
                            ->options([
                                '7' => 'Last 7 days',
                                '14' => 'Last 14 days',
                                '30' => 'Last 30 days',
                            ])
                            ->default('7')
                            ->native(false),
                    ])
                    ->columns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 4,
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /**
     * @return array<class-string>
     */
    public function getWidgets(): array
    {
        return [
            OverviewStatsWidget::class,
            SalesStatsWidget::class,
            InventoryStockStatsWidget::class,
            PendingBillsStatsWidget::class,
            SalesTrendChartWidget::class,
            OrdersCountChartWidget::class,
            PaymentModeChartWidget::class,
            InventoryStatusChartWidget::class,
            TopProductsChartWidget::class,
            LowStockProductsTable::class,
            NearExpiryTable::class,
            PendingSupplierBillsTable::class,
            PendingCustomerPaymentsTable::class,
            RecentSalesOrdersTable::class,
        ];
    }

    public function getColumns(): int | array
    {
        return [
            'default' => 1,
            'xl' => 2,
        ];
    }
}
