<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('era_id')
                    ->relationship('era', 'name_ar')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('title_ar')
                    ->required(),
                TextInput::make('title_en'),
                TextInput::make('event_year')
                    ->label('Year')
                    ->helperText('Negative = BCE, e.g. -2560 for 2560 BC')
                    ->required()
                    ->numeric(),
                TextInput::make('event_month')
                    ->label('Month')
                    ->helperText('Optional, only if the exact date is known')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(12),
                TextInput::make('event_day')
                    ->label('Day')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(31),
                Select::make('date_precision')
                    ->options(['exact' => 'Exact', 'approximate' => 'Approximate', 'century' => 'Century'])
                    ->default('approximate')
                    ->required(),
                TextInput::make('latitude')
                    ->required()
                    ->numeric(),
                TextInput::make('longitude')
                    ->required()
                    ->numeric(),
                TextInput::make('location_name'),
                TextInput::make('category'),
                Textarea::make('description')
                    ->columnSpanFull(),
                FileUpload::make('media')
                    ->image()
                    ->multiple()
                    ->directory('events')
                    ->reorderable()
                    ->columnSpanFull(),
                Select::make('source_id')
                    ->relationship('source', 'title')
                    ->searchable()
                    ->preload(),
                Select::make('status')
                    ->options(['draft' => 'Draft', 'reviewed' => 'Reviewed', 'published' => 'Published'])
                    ->default('draft')
                    ->required(),
            ]);
    }
}
