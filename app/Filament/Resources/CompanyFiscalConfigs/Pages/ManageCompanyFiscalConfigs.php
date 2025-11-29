<?php

declare(strict_types=1);

namespace App\Filament\Resources\CompanyFiscalConfigs\Pages;

use App\Filament\Resources\CompanyFiscalConfigs\CompanyFiscalConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageCompanyFiscalConfigs extends ManageRecords
{
    protected static string $resource = CompanyFiscalConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nueva Configuración')
                ->icon('heroicon-o-plus')
                ->successNotificationTitle('Configuración fiscal creada correctamente')
                ->before(function (Actions\CreateAction $action, array $data) {
                    // Warning si ya existe una config activa
                    $active = \AichaDigital\Larabill\Models\CompanyFiscalConfig::where('is_active', true)
                        ->whereNull('valid_until')
                        ->exists();

                    if ($active && ! $data['valid_until']) {
                        \Filament\Notifications\Notification::make()
                            ->warning()
                            ->title('Atención')
                            ->body('La configuración activa anterior se cerrará automáticamente.')
                            ->send();
                    }
                }),
        ];
    }
}
