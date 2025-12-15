<?php

declare(strict_types=1);

namespace App\Filament\Resources\LegalEntityTypes\Pages;

use App\Filament\Resources\LegalEntityTypes\LegalEntityTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable;

class ManageLegalEntityTypes extends ManageRecords
{
    use Translatable;

    protected static string $resource = LegalEntityTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            CreateAction::make(),
        ];
    }
}
