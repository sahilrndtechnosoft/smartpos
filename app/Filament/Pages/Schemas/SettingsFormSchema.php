<?php

namespace App\Filament\Pages\Schemas;

use App\Filament\Pages\ManageSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SettingsFormSchema
{
    public static function configure(Schema $schema, ManageSettings $page): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Contact Details')
                    ->description('Manage your contact details, social links and more.')
                    ->collapsible()
                    ->schema([
                        Grid::make()
                            ->columns(['default' => 1, 'lg' => 3])
                            ->schema([
                                Section::make('Contact Details')
                                    ->headerActions([
                                        Action::make('saveContactDetails')
                                            ->label('Save Contact Details')
                                            ->action('saveContactDetails')
                                            ->visible(fn (): bool => $page->canEditSettings()),
                                    ])
                                    ->schema([
                                        Grid::make()
                                            ->columns(['default' => 1, 'md' => 2, 'xl' => 4])
                                            ->schema([
                                                TextInput::make('contact_details.shop_name')
                                                    ->label('Shop Name')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->disabled(fn (): bool => ! $page->canEditSettings()),

                                                TextInput::make('contact_details.email')
                                                    ->label('Email')
                                                    ->email()
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->disabled(fn (): bool => ! $page->canEditSettings()),

                                                TextInput::make('contact_details.primary_phone')
                                                    ->label('Primary Phone')
                                                    ->tel()
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->disabled(fn (): bool => ! $page->canEditSettings()),

                                                TextInput::make('contact_details.website_url')
                                                    ->label('Website URL')
                                                    ->url()
                                                    ->maxLength(255)
                                                    ->disabled(fn (): bool => ! $page->canEditSettings()),
                                            ]),

                                        Textarea::make('contact_details.address')
                                            ->label('Address')
                                            ->required()
                                            ->rows(3)
                                            ->columnSpanFull()
                                            ->disabled(fn (): bool => ! $page->canEditSettings()),

                                        TextInput::make('contact_details.google_map_address')
                                            ->label('Google Map Address')
                                            ->placeholder('google map address')
                                            ->maxLength(500)
                                            ->columnSpanFull()
                                            ->disabled(fn (): bool => ! $page->canEditSettings()),

                                        Repeater::make('contact_details.other_phones')
                                            ->label('Other Phone Number')
                                            ->schema([
                                                TextInput::make('name')
                                                    ->required()
                                                    ->maxLength(255),

                                                TextInput::make('phone')
                                                    ->tel()
                                                    ->required()
                                                    ->maxLength(255),
                                            ])
                                            ->columns(2)
                                            ->addActionLabel('add phone number')
                                            ->defaultItems(0)
                                            ->columnSpanFull()
                                            ->disabled(fn (): bool => ! $page->canEditSettings()),

                                        Repeater::make('contact_details.other_emails')
                                            ->label('Other Email Address')
                                            ->schema([
                                                TextInput::make('name')
                                                    ->required()
                                                    ->maxLength(255),

                                                TextInput::make('email')
                                                    ->email()
                                                    ->required()
                                                    ->maxLength(255),
                                            ])
                                            ->columns(2)
                                            ->addActionLabel('add email address')
                                            ->defaultItems(0)
                                            ->columnSpanFull()
                                            ->disabled(fn (): bool => ! $page->canEditSettings()),
                                    ])
                                    ->columnSpan(['default' => 1, 'lg' => 2]),

                                Section::make('Social Links')
                                    ->headerActions([
                                        Action::make('saveSocialLinks')
                                            ->label('Save Social Link')
                                            ->action('saveSocialLinks')
                                            ->visible(fn (): bool => $page->canEditSettings()),
                                    ])
                                    ->schema(self::socialLinkFields($page))
                                    ->columnSpan(['default' => 1, 'lg' => 1]),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Company Details')
                    ->description('Manage your company details')
                    ->collapsible()
                    ->headerActions([
                        Action::make('saveCompanyDetails')
                            ->label('Save Company Details')
                            ->action('saveCompanyDetails')
                            ->visible(fn (): bool => $page->canEditSettings()),
                    ])
                    ->schema([
                        Grid::make()
                            ->columns(['default' => 1, 'md' => 3])
                            ->schema([
                                TextInput::make('company_details.firm_pan_number')
                                    ->label('Firm PAN Number')
                                    ->required()
                                    ->maxLength(255)
                                    ->disabled(fn (): bool => ! $page->canEditSettings()),

                                TextInput::make('company_details.gst_number')
                                    ->label('GST Number')
                                    ->required()
                                    ->maxLength(255)
                                    ->disabled(fn (): bool => ! $page->canEditSettings()),

                                TextInput::make('company_details.fssai_license')
                                    ->label('FSSAI License')
                                    ->required()
                                    ->maxLength(255)
                                    ->disabled(fn (): bool => ! $page->canEditSettings()),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /**
     * @return array<int, TextInput>
     */
    protected static function socialLinkFields(ManageSettings $page): array
    {
        $disabled = fn (): bool => ! $page->canEditSettings();

        return [
            TextInput::make('social_links.whatsapp')
                ->label('Whatsapp')
                ->placeholder('Type your whatsapp link')
                ->maxLength(500)
                ->disabled($disabled),

            TextInput::make('social_links.instagram')
                ->label('Instagram')
                ->placeholder('Type your Instagram link')
                ->maxLength(500)
                ->disabled($disabled),

            TextInput::make('social_links.youtube')
                ->label('Youtube')
                ->placeholder('Type your YouTube link')
                ->maxLength(500)
                ->disabled($disabled),

            TextInput::make('social_links.linkedin')
                ->label('Linkedin')
                ->placeholder('Type your LinkedIn link')
                ->maxLength(500)
                ->disabled($disabled),

            TextInput::make('social_links.facebook')
                ->label('Facebook')
                ->placeholder('Type your Facebook link')
                ->maxLength(500)
                ->disabled($disabled),

            TextInput::make('social_links.twitter')
                ->label('Twitter')
                ->placeholder('Type your Twitter link')
                ->maxLength(500)
                ->disabled($disabled),
        ];
    }
}
