<?php

namespace App\Filament\Resources\Concerns;

use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;

trait HasFormActionsAtTopAndBottom
{
    public function content(Schema $schema): Schema
    {
        if (
            $this instanceof EditRecord
            && $this->hasCombinedRelationManagerTabsWithContent()
        ) {
            return parent::content($schema);
        }

        $components = [
            $this->getFormContentComponent(),
        ];

        if ($this instanceof EditRecord) {
            $components[] = $this->getRelationManagersContentComponent();
        }

        $components[] = $this->getBottomFormActionsContentComponent();

        return $schema->components($components);
    }

    public function getFormContentComponent(): Component
    {
        if (! $this->hasFormWrapper()) {
            return Group::make([
                EmbeddedSchema::make('form'),
                $this->getTopFormActionsContentComponent(),
            ]);
        }

        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler($this->getSubmitFormLivewireMethodName())
            ->header([
                $this->getTopFormActionsContentComponent(),
            ]);
    }

    protected function getTopFormActionsContentComponent(): Component
    {
        return Actions::make($this->getFormActions())
            ->alignment(Alignment::End)
            ->fullWidth($this->hasFullWidthFormActions())
            ->key('form-actions-header');
    }

    protected function getBottomFormActionsContentComponent(): Component
    {
        return Actions::make($this->getFormActions())
            ->alignment(Alignment::End)
            ->fullWidth($this->hasFullWidthFormActions())
            ->sticky(false)
            ->key('form-actions-footer');
    }
}
