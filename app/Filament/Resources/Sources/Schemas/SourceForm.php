<?php

namespace App\Filament\Resources\Sources\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SourceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('title_en')
                    ->label('Title (English)'),
                TextInput::make('author'),
                TextInput::make('reference'),
                Textarea::make('reliability_note')
                    ->columnSpanFull(),
                Textarea::make('reliability_note_en')
                    ->label('Reliability note (English)')
                    ->columnSpanFull(),
            ]);
    }
}
