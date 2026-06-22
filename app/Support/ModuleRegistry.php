<?php

namespace App\Support;

class ModuleRegistry
{
    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        return [
            'products' => 'Products',
            'categories' => 'Categories',
            'category_groups' => 'Category groups',
            'brands' => 'Brands',
            'inventories' => 'Inventories',
            'customers' => 'Customers',
            'suppliers' => 'Suppliers',
            'orders' => 'Sales orders',
            'keyboard_shortcuts' => 'Keyboard shortcuts',
            'taxes' => 'Taxes',
            'tax_groups' => 'Tax groups',
            'settings' => 'Settings',
            'users' => 'Users',
            'sessions' => 'Sessions',
        ];
    }

    public static function label(string $module): string
    {
        return self::all()[$module] ?? ucfirst($module);
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::all());
    }
}
