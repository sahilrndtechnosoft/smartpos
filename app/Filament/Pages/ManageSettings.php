<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Schemas\SettingsFormSchema;
use App\Filament\Resources\KeyboardShortcuts\Schemas\KeyboardShortcutForm;
use App\Filament\Resources\KeyboardShortcuts\Schemas\KeyboardShortcutInfolist;
use App\Filament\Resources\KeyboardShortcuts\Tables\KeyboardShortcutsTable;
use App\Models\KeyboardShortcut;
use App\Models\User;
use App\Services\SettingStore;
use App\Support\KeyboardShortcutData;
use BackedEnum;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\CanUseDatabaseTransactions;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

/**
 * @property-read Schema $settingsForm
 */
class ManageSettings extends Page implements HasTable
{
    use CanUseDatabaseTransactions;
    use Tables\Concerns\InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Settings';

    protected static ?string $title = 'Settings';

    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'settings';

    /** @var array<string, mixed> */
    public array $data = [];

    public function mount(): void
    {
        $this->fillSettingsForm();
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        return $user->hasModulePermission('settings', 'viewAny')
            || $user->hasModulePermission('keyboard_shortcuts', 'viewAny');
    }

    public function canEditSettings(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && $user->hasModulePermission('settings', 'edit');
    }

    public function canCreateShortcuts(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && $user->hasModulePermission('keyboard_shortcuts', 'create');
    }

    public function canEditShortcuts(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && $user->hasModulePermission('keyboard_shortcuts', 'edit');
    }

    public function canDeleteShortcuts(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && $user->hasModulePermission('keyboard_shortcuts', 'delete');
    }

    public function defaultSettingsForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function settingsForm(Schema $schema): Schema
    {
        return SettingsFormSchema::configure($schema, $this);
    }

    public function infolist(Schema $schema): Schema
    {
        return KeyboardShortcutInfolist::configure($schema);
    }

    public function form(Schema $schema): Schema
    {
        return KeyboardShortcutForm::configure($schema);
    }

    public function getDefaultActionSchemaResolver(Action $action): ?Closure
    {
        return match (true) {
            $action instanceof CreateAction, $action instanceof EditAction => fn (Schema $schema): Schema => $this->form($schema),
            $action instanceof ViewAction => fn (Schema $schema): Schema => $this->infolist($schema),
            default => null,
        };
    }

    public function getModel(): string
    {
        return KeyboardShortcut::class;
    }

    public function table(Table $table): Table
    {
        return KeyboardShortcutsTable::configure($table)
            ->query(
                KeyboardShortcut::query()
                    ->orderBy('sr')
                    ->orderBy('name'),
            )
            ->headerActions([
                CreateAction::make()
                    ->label('New shortcut')
                    ->icon(Heroicon::Plus)
                    ->visible(fn (): bool => $this->canCreateShortcuts())
                    ->using(fn (array $data): Model => KeyboardShortcut::query()->create(
                        KeyboardShortcutData::normalize($data),
                    )),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->visible(fn (): bool => $this->canViewShortcuts()),
                    EditAction::make()
                        ->visible(fn (): bool => $this->canEditShortcuts())
                        ->using(function (KeyboardShortcut $record, array $data): KeyboardShortcut {
                            $record->update(KeyboardShortcutData::normalize($data));

                            return $record;
                        }),
                    DeleteAction::make()
                        ->visible(fn (): bool => $this->canDeleteShortcuts()),
                ])
                    ->tooltip('Actions'),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        $components = [
            Form::make([EmbeddedSchema::make('settingsForm')])
                ->id('settings-form')
                ->columnSpanFull(),
        ];

        if ($this->canViewShortcuts()) {
            $components[] = Section::make('Keyboard shortcuts')
                ->description('Configure global keyboard shortcuts for the admin panel. Shortcuts are ignored while typing in inputs, textareas, selects, or rich text fields.')
                ->schema([
                    EmbeddedTable::make(),
                ])
                ->extraAttributes([
                    'id' => 'keyboard-shortcuts',
                ])
                ->columnSpanFull();
        }

        return $schema->components($components);
    }

    public function saveContactDetails(): void
    {
        abort_unless($this->canEditSettings(), 403);

        $contact = $this->data['contact_details'] ?? [];

        $validated = validator($contact, [
            'shop_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'primary_phone' => ['required', 'string', 'max:255'],
            'website_url' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'google_map_address' => ['nullable', 'string', 'max:500'],
            'other_phones' => ['nullable', 'array'],
            'other_phones.*.name' => ['required', 'string', 'max:255'],
            'other_phones.*.phone' => ['required', 'string', 'max:255'],
            'other_emails' => ['nullable', 'array'],
            'other_emails.*.name' => ['required', 'string', 'max:255'],
            'other_emails.*.email' => ['required', 'email', 'max:255'],
        ])->validate();

        SettingStore::set('contact_details', $validated, 'contact');

        Notification::make()
            ->title('Contact details saved')
            ->success()
            ->send();
    }

    public function saveSocialLinks(): void
    {
        abort_unless($this->canEditSettings(), 403);

        $social = $this->data['social_links'] ?? [];

        $validated = validator($social, [
            'whatsapp' => ['nullable', 'string', 'max:500'],
            'instagram' => ['nullable', 'string', 'max:500'],
            'youtube' => ['nullable', 'string', 'max:500'],
            'linkedin' => ['nullable', 'string', 'max:500'],
            'facebook' => ['nullable', 'string', 'max:500'],
            'twitter' => ['nullable', 'string', 'max:500'],
        ])->validate();

        SettingStore::set('social_links', $validated, 'contact');

        Notification::make()
            ->title('Social links saved')
            ->success()
            ->send();
    }

    public function saveCompanyDetails(): void
    {
        abort_unless($this->canEditSettings(), 403);

        $company = $this->data['company_details'] ?? [];

        $validated = validator($company, [
            'firm_pan_number' => ['required', 'string', 'max:255'],
            'gst_number' => ['required', 'string', 'max:255'],
            'fssai_license' => ['required', 'string', 'max:255'],
        ])->validate();

        SettingStore::set('company_details', $validated, 'company');

        Notification::make()
            ->title('Company details saved')
            ->success()
            ->send();
    }

    protected function fillSettingsForm(): void
    {
        $this->settingsForm->fill([
            'contact_details' => SettingStore::get('contact_details', self::defaultContactDetails()),
            'social_links' => SettingStore::get('social_links', self::defaultSocialLinks()),
            'company_details' => SettingStore::get('company_details', self::defaultCompanyDetails()),
        ]);
    }

    protected function canViewShortcuts(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && $user->hasModulePermission('keyboard_shortcuts', 'viewAny');
    }

    /**
     * @return array<string, mixed>
     */
    protected static function defaultContactDetails(): array
    {
        return [
            'shop_name' => '',
            'email' => '',
            'primary_phone' => '',
            'website_url' => '',
            'address' => '',
            'google_map_address' => '',
            'other_phones' => [],
            'other_emails' => [],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected static function defaultSocialLinks(): array
    {
        return [
            'whatsapp' => '',
            'instagram' => '',
            'youtube' => '',
            'linkedin' => '',
            'facebook' => '',
            'twitter' => '',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected static function defaultCompanyDetails(): array
    {
        return [
            'firm_pan_number' => '',
            'gst_number' => '',
            'fssai_license' => '',
        ];
    }
}
