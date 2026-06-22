<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class PrintDocumentController extends Controller
{
    public function salesOrderInvoice(Order $order): View|Response
    {
        $this->authorizeModule('orders', 'view');

        $order->load(['customer', 'items.product']);

        return view('print.sales-order-invoice', [
            'document' => $order,
            'store' => $this->storeDetails(),
            'title' => 'Sales Order Invoice',
        ]);
    }

    public function inventoryReceipt(Inventory $inventory): View|Response
    {
        $this->authorizeModule('inventories', 'view');

        $inventory->load(['supplier', 'items.product']);

        return view('print.inventory-receipt', [
            'document' => $inventory,
            'store' => $this->storeDetails(),
            'title' => 'Inventory Receipt',
        ]);
    }

    protected function authorizeModule(string $module, string $ability): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        abort_unless($user instanceof User, 403);
        abort_unless($user->isSuperAdmin() || $user->hasModulePermission($module, $ability), 403);
    }

    /**
     * @return array<string, string|null>
     */
    protected function storeDetails(): array
    {
        $settings = Setting::query()
            ->whereIn('name', ['store_name', 'store_phone', 'receipt_footer'])
            ->pluck('payload', 'name');

        return [
            'name' => data_get($settings->get('store_name'), 'value', 'SmartPOS'),
            'phone' => data_get($settings->get('store_phone'), 'value'),
            'footer' => data_get($settings->get('receipt_footer'), 'text'),
            'logo' => file_exists(public_path('logo.jpeg')) ? asset('logo.jpeg') : null,
        ];
    }
}
