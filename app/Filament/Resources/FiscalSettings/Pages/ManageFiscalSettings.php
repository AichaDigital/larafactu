<?php

namespace App\Filament\Resources\FiscalSettings\Pages;

use App\Filament\Resources\FiscalSettings\FiscalSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageFiscalSettings extends ManageRecords
{
    protected static string $resource = FiscalSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
