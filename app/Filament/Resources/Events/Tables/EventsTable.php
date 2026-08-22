<?php

namespace App\Filament\Resources\Events\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('era.name_ar')
                    ->label('Era')
                    ->searchable(),
                TextColumn::make('title_ar')
                    ->searchable(),
                TextColumn::make('title_en')
                    ->searchable(),
                TextColumn::make('event_year')
                    ->label('Year')
                    ->sortable(),
                TextColumn::make('date_precision')
                    ->badge(),
                TextColumn::make('location_name')
                    ->searchable(),
                TextColumn::make('category')
                    ->searchable(),
                TextColumn::make('source.title')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'reviewed' => 'warning',
                        'published' => 'success',
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('era_id')
                    ->relationship('era', 'name_ar')
                    ->label('Era'),
                SelectFilter::make('status')
                    ->options(['draft' => 'Draft', 'reviewed' => 'Reviewed', 'published' => 'Published']),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
