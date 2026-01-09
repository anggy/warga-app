<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HouseResource\Pages;
use App\Filament\Resources\HouseResource\RelationManagers;
use App\Models\House;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HouseResource extends Resource
{
    protected static ?string $model = House::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Data Rumah';
    protected static ?string $modelLabel = 'Rumah';
    protected static ?string $pluralModelLabel = 'Data Rumah';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('block')
                    ->label('Blok')
                    ->options([
                        'A' => 'Blok A',
                        'B' => 'Blok B',
                        'C' => 'Blok C',
                        'D' => 'Blok D',
                        'E' => 'Blok E',
                        'F' => 'Blok F',
                        'Jalan Utama' => 'Jalan Utama',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('number')
                    ->label('Nomor Rumah')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Status Bangunan')
                    ->options([
                        'occupied' => 'Terbangun (Occupied)',
                        'vacant_land' => 'Kavling (Vacant Land)',
                    ])
                    ->required()
                    ->default('occupied'),
                Forms\Components\Section::make('Lokasi Peta')
                    ->schema([
                        Forms\Components\View::make('filament.forms.components.map-picker'),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('latitude')
                                    ->numeric()
                                    ->label('Latitude')
                                    ->reactive(),
                                Forms\Components\TextInput::make('longitude')
                                    ->numeric()
                                    ->label('Longitude')
                                    ->reactive(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('block')
                    ->label('Blok')
                    ->searchable(),
                Tables\Columns\TextColumn::make('number')
                    ->label('Nomor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'occupied' => 'success',
                        'vacant_land' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'occupied' => 'Terbangun',
                        'vacant_land' => 'Kavling',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ResidentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHouses::route('/'),
            'create' => Pages\CreateHouse::route('/create'),
            'edit' => Pages\EditHouse::route('/{record}/edit'),
        ];
    }
}
