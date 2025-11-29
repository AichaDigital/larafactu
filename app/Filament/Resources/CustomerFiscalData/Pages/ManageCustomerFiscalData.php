<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerFiscalData\Pages;

use App\Filament\Resources\CustomerFiscalData\CustomerFiscalDataResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageCustomerFiscalData extends ManageRecords
{
    protected static string $resource = CustomerFiscalDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nuevos Datos Fiscales')
                ->icon('heroicon-o-plus')
                ->successNotificationTitle('Datos fiscales creados correctamente')
                ->before(function (Actions\CreateAction $action, array $data) {
                    // Warning si ya existe data activa para este usuario
                    $active = \AichaDigital\Larabill\Models\CustomerFiscalData::where('user_id', $data['user_id'])
                        ->where('is_active', true)
                        ->whereNull('valid_until')
                        ->exists();

                    if ($active && ! $data['valid_until']) {
                        \Filament\Notifications\Notification::make()
                            ->warning()
                            ->title('Atención')
                            ->body('Los datos fiscales activos del cliente se cerrarán automáticamente.')
                            ->send();
                    }
                }),
        ];
    }
}
