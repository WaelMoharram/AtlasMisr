<?php

namespace App\Filament\Resources\Eras\Pages;

use App\Filament\Resources\Eras\EraResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEra extends EditRecord
{
    protected static string $resource = EraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
