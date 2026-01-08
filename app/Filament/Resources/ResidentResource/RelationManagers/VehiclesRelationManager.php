<?php

namespace App\Filament\Resources\ResidentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VehiclesRelationManager extends RelationManager
{
    protected static string $relationship = 'vehicles';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('license_plate')
                    ->required()
                    ->maxLength(255)
                    ->label('Plat Nomor'),
                Forms\Components\Select::make('vehicle_type')
                    ->options([
                        'Motor' => 'Motor',
                        'Mobil' => 'Mobil',
                        'Lainnya' => 'Lainnya',
                    ])
                    ->required()
                    ->label('Jenis'),
                Forms\Components\TextInput::make('brand')
                    ->maxLength(255)
                    ->label('Merk'),
                Forms\Components\TextInput::make('color')
                    ->maxLength(255)
                    ->label('Warna'),
                Forms\Components\FileUpload::make('photo')
                    ->image()
                    ->directory('vehicles'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('license_plate')
            ->columns([
                Tables\Columns\TextColumn::make('license_plate')
                    ->label('Plat Nomor'),
                Tables\Columns\TextColumn::make('vehicle_type')
                    ->label('Jenis')
                    ->badge(),
                Tables\Columns\TextColumn::make('brand'),
                Tables\Columns\TextColumn::make('color'),
                Tables\Columns\ImageColumn::make('photo')
                    ->circular(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
