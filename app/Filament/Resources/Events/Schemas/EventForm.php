<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
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
                Section::make('Starts at')
                    ->columns(5)
                    ->schema([
                        TextInput::make('event_year')
                            ->label('Year')
                            ->helperText('Negative = BCE, e.g. -2560')
                            ->required()
                            ->numeric(),
                        TextInput::make('event_month')
                            ->label('Month')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(12),
                        TextInput::make('event_day')
                            ->label('Day')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(31),
                        TextInput::make('event_hour')
                            ->label('Hour')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(23),
                        TextInput::make('event_minute')
                            ->label('Minute')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(59),
                    ]),
                Select::make('date_precision')
                    ->options(['exact' => 'Exact', 'approximate' => 'Approximate', 'century' => 'Century'])
                    ->default('approximate')
                    ->required(),
                Section::make('Ends at')
                    ->description('Optional. If left empty, the event is considered active for the rest of its own start precision (e.g. a year-only event stays active for that whole year) and then disappears from the timeline.')
                    ->columns(5)
                    ->collapsible()
                    ->schema([
                        TextInput::make('ends_year')
                            ->label('Year')
                            ->numeric(),
                        TextInput::make('ends_month')
                            ->label('Month')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(12),
                        TextInput::make('ends_day')
                            ->label('Day')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(31),
                        TextInput::make('ends_hour')
                            ->label('Hour')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(23),
                        TextInput::make('ends_minute')
                            ->label('Minute')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(59),
                    ]),
                View::make('filament.forms.components.map-point-picker')
                    ->columnSpanFull(),
                Section::make('الإحداثيات بالأرقام')
                    ->description('بتتحدّث تلقائيًا من الخريطة فوق — عدّلها هنا بس لو محتاج دقة أعلى.')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('latitude')
                            ->required()
                            ->numeric()
                            ->extraInputAttributes(['data-map-field' => 'latitude']),
                        TextInput::make('longitude')
                            ->required()
                            ->numeric()
                            ->extraInputAttributes(['data-map-field' => 'longitude']),
                    ]),
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
