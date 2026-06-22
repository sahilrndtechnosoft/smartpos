<?php

namespace App\Support;

class KeyboardShortcutActionTypes
{
    public const DASHBOARD = 'dashboard';

    public const RESOURCE_INDEX = 'resource_index';

    public const RESOURCE_CREATE = 'resource_create';

    public const RESOURCE_VIEW = 'resource_view';

    public const RESOURCE_EDIT = 'resource_edit';

    public const RESOURCE_DELETE = 'resource_delete';

    public const CUSTOM_URL = 'custom_url';

    public const SHORTCUTS_HELP = 'shortcuts_help';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::DASHBOARD => 'Open dashboard',
            self::RESOURCE_INDEX => 'Open module list',
            self::RESOURCE_CREATE => 'Open module create page',
            self::RESOURCE_VIEW => 'Open module view page',
            self::RESOURCE_EDIT => 'Open module edit page',
            self::RESOURCE_DELETE => 'Delete module record',
            self::CUSTOM_URL => 'Open custom URL',
            self::SHORTCUTS_HELP => 'Open settings (keyboard shortcuts)',
        ];
    }

    /**
     * @return list<string>
     */
    public static function requiresResourceTarget(): array
    {
        return [
            self::RESOURCE_INDEX,
            self::RESOURCE_CREATE,
            self::RESOURCE_VIEW,
            self::RESOURCE_EDIT,
            self::RESOURCE_DELETE,
        ];
    }

    /**
     * @return list<string>
     */
    public static function requiresRecordTarget(): array
    {
        return [
            self::RESOURCE_VIEW,
            self::RESOURCE_EDIT,
            self::RESOURCE_DELETE,
        ];
    }

    /**
     * @return list<string>
     */
    public static function requiresUrlTarget(): array
    {
        return [
            self::CUSTOM_URL,
        ];
    }

    /**
     * @return list<string>
     */
    public static function clearsActionTarget(): array
    {
        return [
            self::DASHBOARD,
            self::SHORTCUTS_HELP,
        ];
    }

    public static function permissionAbility(string $actionType): string
    {
        return match ($actionType) {
            self::RESOURCE_CREATE => 'create',
            self::RESOURCE_EDIT => 'edit',
            self::RESOURCE_VIEW => 'view',
            self::RESOURCE_DELETE => 'delete',
            default => 'viewAny',
        };
    }

    public static function resourcePageName(string $actionType): ?string
    {
        return match ($actionType) {
            self::RESOURCE_INDEX => 'index',
            self::RESOURCE_CREATE => 'create',
            self::RESOURCE_VIEW => 'view',
            self::RESOURCE_EDIT => 'edit',
            default => null,
        };
    }
}
