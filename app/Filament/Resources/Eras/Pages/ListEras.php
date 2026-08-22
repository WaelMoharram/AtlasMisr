<?php

namespace App\Filament\Resources\Eras\Pages;

use App\Filament\Resources\Eras\EraResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEras extends ListRecords
{
    protected static string $resource = EraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
