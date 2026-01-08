<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Filament\Resources\VehicleResource\RelationManagers;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Kendaraan';
    protected static ?string $modelLabel = 'Kendaraan';
    protected static ?string $pluralModelLabel = 'Data Kendaraan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('resident_id')
                    ->relationship('resident', 'full_name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Pemilik (Warga)'),
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
                    ->label('Jenis Kendaraan'),
                Forms\Components\TextInput::make('brand')
                    ->maxLength(255)
                    ->label('Merk/Tipe'),
                Forms\Components\TextInput::make('color')
                    ->maxLength(255)
                    ->label('Warna'),
                Forms\Components\FileUpload::make('photo')
                    ->image()
                    ->directory('vehicles'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('resident.full_name')
                    ->label('Pemilik')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('license_plate')
                    ->label('Plat Nomor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vehicle_type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Mobil' => 'info',
                        'Motor' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('brand')
                    ->label('Merk')
                    ->searchable(),
                Tables\Columns\TextColumn::make('color')
                    ->label('Warna'),
                Tables\Columns\ImageColumn::make('photo')
                    ->circular(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('vehicle_type')
                    ->options([
                        'Motor' => 'Motor',
                        'Mobil' => 'Mobil',
                    ]),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }
}
