<?php

namespace App\Services;

use App\Filament\Pages\ManageSettings;
use App\Models\KeyboardShortcut;
use App\Models\User;
use App\Support\KeyboardShortcutActionTypes;
use App\Support\KeyboardShortcutTargets;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;

class KeyboardShortcutResolver
{
    public function resolveUrl(KeyboardShortcut $shortcut, ?User $user = null): ?string
    {
        $payload = $this->resolvePayload($shortcut, $user);

        return $payload['url'] ?? null;
    }

    /**
     * @return array{name: string, combination: string, combination_display: string, url: string|null, behavior: string, run_url: string|null, confirm_message: string|null}|null
     */
    public function resolvePayload(KeyboardShortcut $shortcut, ?User $user = null): ?array
    {
        $user ??= auth()->user();

        if (! $user instanceof User) {
            return null;
        }

        if (! $shortcut->is_active) {
            return null;
        }

        if (! $this->userCanRunShortcut($shortcut, $user)) {
            return null;
        }

        $behavior = $shortcut->action_type === KeyboardShortcutActionTypes::RESOURCE_DELETE
            ? 'delete'
            : 'navigate';

        $url = match ($shortcut->action_type) {
            KeyboardShortcutActionTypes::DASHBOARD => Filament::getUrl(),
            KeyboardShortcutActionTypes::SHORTCUTS_HELP => ManageSettings::getUrl() . '#keyboard-shortcuts',
            KeyboardShortcutActionTypes::RESOURCE_INDEX => $this->resourceUrl($shortcut->action_target, 'index'),
            KeyboardShortcutActionTypes::RESOURCE_CREATE => $this->resourceUrl($shortcut->action_target, 'create'),
            KeyboardShortcutActionTypes::RESOURCE_VIEW => $this->resourceUrl(
                $shortcut->action_target,
                'view',
                $shortcut->action_record_id,
            ),
            KeyboardShortcutActionTypes::RESOURCE_EDIT => $this->resourceUrl(
                $shortcut->action_target,
                'edit',
                $shortcut->action_record_id,
            ),
            KeyboardShortcutActionTypes::CUSTOM_URL => filled($shortcut->action_target)
                ? url($shortcut->action_target)
                : null,
            KeyboardShortcutActionTypes::RESOURCE_DELETE => null,
            default => null,
        };

        if ($behavior === 'navigate' && blank($url)) {
            return null;
        }

        return [
            'name' => $shortcut->name,
            'combination' => $shortcut->combination,
            'combination_display' => \App\Support\KeyboardShortcutCombination::display($shortcut->combination),
            'url' => $url,
            'behavior' => $behavior,
            'run_url' => $behavior === 'delete'
                ? route('keyboard-shortcuts.run', $shortcut)
                : null,
            'confirm_message' => $behavior === 'delete'
                ? "Delete the record mapped to \"{$shortcut->name}\"?"
                : null,
        ];
    }

    public function userCanRunShortcut(KeyboardShortcut $shortcut, User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if (in_array($shortcut->action_type, [
            KeyboardShortcutActionTypes::DASHBOARD,
            KeyboardShortcutActionTypes::SHORTCUTS_HELP,
            KeyboardShortcutActionTypes::CUSTOM_URL,
        ], true)) {
            return true;
        }

        $module = KeyboardShortcutTargets::moduleForResource($shortcut->action_target);

        if (blank($module)) {
            return false;
        }

        return $user->hasModulePermission(
            $module,
            KeyboardShortcutActionTypes::permissionAbility($shortcut->action_type),
        );
    }

    public function resolveTargetRecord(KeyboardShortcut $shortcut): ?Model
    {
        if ($shortcut->action_type !== KeyboardShortcutActionTypes::RESOURCE_DELETE) {
            return null;
        }

        if (blank($shortcut->action_target) || blank($shortcut->action_record_id)) {
            return null;
        }

        if (! class_exists($shortcut->action_target) || ! is_subclass_of($shortcut->action_target, Resource::class)) {
            return null;
        }

        /** @var class-string<Model> $modelClass */
        $modelClass = $shortcut->action_target::getModel();

        return $modelClass::query()->find($shortcut->action_record_id);
    }

    protected function resourceUrl(?string $resourceClass, string $page, ?string $recordId = null): ?string
    {
        if (blank($resourceClass) || ! class_exists($resourceClass)) {
            return null;
        }

        if (! is_subclass_of($resourceClass, Resource::class)) {
            return null;
        }

        if (in_array($page, ['view', 'edit'], true) && blank($recordId)) {
            return null;
        }

        if (in_array($page, ['view', 'edit'], true)) {
            return $resourceClass::getUrl($page, ['record' => $recordId]);
        }

        return $resourceClass::getUrl($page);
    }

    /**
     * @return list<array{name: string, combination: string, combination_display: string, url: string|null, behavior: string, run_url: string|null, confirm_message: string|null}>
     */
    public function activeShortcutsForUser(?User $user = null): array
    {
        $user ??= auth()->user();

        if (! $user instanceof User) {
            return [];
        }

        return KeyboardShortcut::query()
            ->where('is_active', true)
            ->orderBy('sr')
            ->orderBy('name')
            ->get()
            ->map(fn (KeyboardShortcut $shortcut): ?array => $this->resolvePayload($shortcut, $user))
            ->filter()
            ->values()
            ->all();
    }
}
