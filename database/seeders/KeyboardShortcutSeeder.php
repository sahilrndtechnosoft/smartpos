<?php

namespace Database\Seeders;

use App\Filament\Resources\Inventories\InventoryResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Products\ProductResource;
use App\Models\KeyboardShortcut;
use App\Support\KeyboardShortcutActionTypes;
use Illuminate\Database\Seeder;

class KeyboardShortcutSeeder extends Seeder
{
    public function run(): void
    {
        $shortcuts = [
            [
                'name' => 'Open dashboard',
                'combination' => 'alt+d',
                'action_type' => KeyboardShortcutActionTypes::DASHBOARD,
                'action_target' => null,
                'description' => 'Go to the admin dashboard from anywhere.',
                'sr' => 1,
            ],
            [
                'name' => 'Open inventory create',
                'combination' => 'ctrl+shift+i',
                'action_type' => KeyboardShortcutActionTypes::RESOURCE_CREATE,
                'action_target' => InventoryResource::class,
                'description' => 'Create a new inventory receipt.',
                'sr' => 2,
            ],
            [
                'name' => 'Open product create',
                'combination' => 'ctrl+shift+p',
                'action_type' => KeyboardShortcutActionTypes::RESOURCE_CREATE,
                'action_target' => ProductResource::class,
                'description' => 'Create a new product.',
                'sr' => 3,
            ],
            [
                'name' => 'Open sales orders',
                'combination' => 'ctrl+shift+o',
                'action_type' => KeyboardShortcutActionTypes::RESOURCE_INDEX,
                'action_target' => OrderResource::class,
                'description' => 'Open the sales orders list.',
                'sr' => 4,
            ],
            [
                'name' => 'Open keyboard shortcuts',
                'combination' => 'f2',
                'action_type' => KeyboardShortcutActionTypes::SHORTCUTS_HELP,
                'action_target' => null,
                'description' => 'Open keyboard shortcuts in settings.',
                'sr' => 5,
            ],
        ];

        foreach ($shortcuts as $shortcut) {
            KeyboardShortcut::query()->updateOrCreate(
                ['combination' => $shortcut['combination']],
                $shortcut + ['is_active' => true],
            );
        }
    }
}
