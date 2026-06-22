<?php

namespace App\Support;

use App\Filament\Resources\Brands\BrandResource;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\CategoryGroups\CategoryGroupResource;
use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Inventories\InventoryResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Suppliers\SupplierResource;
use App\Filament\Resources\TaxGroups\TaxGroupResource;
use App\Filament\Resources\Taxes\TaxResource;
use App\Filament\Resources\Users\UserResource;

class KeyboardShortcutTargets
{
    /**
     * @return array<class-string, array{label: string, module: string|null}>
     */
    public static function resources(): array
    {
        return [
            ProductResource::class => ['label' => 'Products', 'module' => 'products'],
            CategoryResource::class => ['label' => 'Categories', 'module' => 'categories'],
            CategoryGroupResource::class => ['label' => 'Category groups', 'module' => 'category_groups'],
            BrandResource::class => ['label' => 'Brands', 'module' => 'brands'],
            InventoryResource::class => ['label' => 'Inventories', 'module' => 'inventories'],
            OrderResource::class => ['label' => 'Sales orders', 'module' => 'orders'],
            CustomerResource::class => ['label' => 'Customers', 'module' => 'customers'],
            SupplierResource::class => ['label' => 'Suppliers', 'module' => 'suppliers'],
            TaxResource::class => ['label' => 'Taxes', 'module' => 'taxes'],
            TaxGroupResource::class => ['label' => 'Tax groups', 'module' => 'tax_groups'],
            UserResource::class => ['label' => 'Users', 'module' => 'users'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function resourceOptions(): array
    {
        return collect(self::resources())
            ->mapWithKeys(fn (array $meta, string $class): array => [$class => $meta['label']])
            ->all();
    }

    public static function moduleForResource(?string $resourceClass): ?string
    {
        if (blank($resourceClass)) {
            return null;
        }

        return self::resources()[$resourceClass]['module'] ?? null;
    }
}
