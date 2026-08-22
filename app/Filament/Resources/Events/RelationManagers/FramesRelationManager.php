<?php

namespace App\Filament\Resources\Events\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FramesRelationManager extends RelationManager
{
    protected static string $relationship = 'frames';

    public const ICONS = [
        'position' => 'تمركز',
        'troops' => 'قوات برية',
        'naval' => 'قوات بحرية',
        'air' => 'قوات جوية',
        'battle' => 'معركة',
        'flag' => 'علم / سيطرة',
    ];

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('label')
                    ->label('التسمية')
                    ->maxLength(255),
                Select::make('geometry_type')
                    ->label('الشكل')
                    ->options(['point' => 'نقطة', 'polygon' => 'بوليجون'])
                    ->default('point')
                    ->live()
                    ->required(),
                Select::make('icon')
                    ->label('الأيقونة')
                    ->options(self::ICONS)
                    ->native(false),
                Section::make('التوقيت')
                    ->columns(5)
                    ->schema([
                        TextInput::make('occurs_year')
                            ->label('سنة')
                            ->helperText('سالب = قبل الميلاد')
                            ->numeric()
                            ->required(),
                        TextInput::make('occurs_month')
                            ->label('شهر')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(12),
                        TextInput::make('occurs_day')
                            ->label('يوم')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(31),
                        TextInput::make('occurs_hour')
                            ->label('ساعة')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(23),
                        TextInput::make('occurs_minute')
                            ->label('دقيقة')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(59),
                    ]),
                Textarea::make('geometry')
                    ->label(fn ($get) => $get('geometry_type') === 'polygon' ? 'الإحداثيات (GeoJSON Polygon)' : 'الإحداثيات (GeoJSON Point)')
                    ->helperText('مثال لنقطة: {"type":"Point","coordinates":[31.2,30.0]} — مثال لبوليجون: {"type":"Polygon","coordinates":[[[31,30],[32,30],[32,31],[31,30]]]}')
                    ->rows(4)
                    ->required()
                    ->columnSpanFull()
                    ->formatStateUsing(fn (mixed $state): ?string => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT) : $state)
                    ->dehydrateStateUsing(fn (?string $state): ?array => filled($state) ? json_decode($state, true) : null)
                    ->rule('json'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label')
            ->columns([
                TextColumn::make('label')
                    ->label('التسمية')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('geometry_type')
                    ->label('الشكل')
                    ->badge(),
                TextColumn::make('icon')
                    ->label('الأيقونة')
                    ->formatStateUsing(fn (?string $state) => $state ? (self::ICONS[$state] ?? $state) : '—'),
                TextColumn::make('occurs_year')
                    ->label('التوقيت')
                    ->formatStateUsing(function ($record) {
                        $parts = array_filter([$record->occurs_day, $record->occurs_month, $record->occurs_year], fn ($v) => $v !== null);

                        return implode('/', array_reverse($parts));
                    })
                    ->sortable(),
            ])
            ->defaultSort('occurs_year')
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
