<?php

namespace App\Filament\Resources\Eras;

use App\Filament\Resources\Eras\Pages\CreateEra;
use App\Filament\Resources\Eras\Pages\EditEra;
use App\Filament\Resources\Eras\Pages\ListEras;
use App\Filament\Resources\Eras\Schemas\EraForm;
use App\Filament\Resources\Eras\Tables\ErasTable;
use App\Models\Era;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EraResource extends Resource
{
    protected static ?string $model = Era::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return EraForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ErasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEras::route('/'),
            'create' => CreateEra::route('/create'),
            'edit' => EditEra::route('/{record}/edit'),
        ];
    }
}
