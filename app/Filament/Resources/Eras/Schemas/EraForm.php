<?php

namespace App\Filament\Resources\Eras\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class EraForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name_ar')
                    ->required(),
                TextInput::make('name_en'),
                TextInput::make('start_year')
                    ->required()
                    ->numeric(),
                TextInput::make('end_year')
                    ->required()
                    ->numeric(),
                Textarea::make('description')
                    ->columnSpanFull(),
                Textarea::make('boundary_geojson')
                    ->label('Boundary (GeoJSON)')
                    ->helperText('Polygon/MultiPolygon geometry as raw GeoJSON')
                    ->rows(6)
                    ->columnSpanFull()
                    ->formatStateUsing(fn (mixed $state): ?string => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT) : $state)
                    ->dehydrateStateUsing(fn (?string $state): ?array => filled($state) ? json_decode($state, true) : null)
                    ->rule('json'),
                Select::make('source_id')
                    ->relationship('source', 'title'),
                Select::make('status')
                    ->options(['draft' => 'Draft', 'reviewed' => 'Reviewed', 'published' => 'Published'])
                    ->default('draft')
                    ->required(),
            ]);
    }
}
