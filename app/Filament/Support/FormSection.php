<?php

namespace App\Filament\Support;

use BackedEnum;
use Filament\Schemas\Components\Section;

final class FormSection
{
    public static function make(
        string $heading,
        BackedEnum|string|null $icon = null,
        ?string $description = null,
        int $columns = 2,
    ): Section {
        $section = Section::make($heading)
            ->columns($columns)
            ->compact()
            ->collapsible();

        if ($icon !== null) {
            $section->icon($icon);
        }

        if ($description !== null) {
            $section->description($description);
        }

        return $section;
    }
}
