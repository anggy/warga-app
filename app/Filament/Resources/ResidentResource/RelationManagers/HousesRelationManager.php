<?php

namespace App\Filament\Resources\ResidentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HousesRelationManager extends RelationManager
{
    protected static string $relationship = 'houses';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('block')
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
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->label('Status Bangunan')
                    ->options([
                        'occupied' => 'Terbangun (Occupied)',
                        'vacant_land' => 'Kavling (Vacant Land)',
                    ])
                    ->required()
                    ->default('occupied'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('block')
            ->columns([
                Tables\Columns\TextColumn::make('block'),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'owner' => 'success',
                        'occupant' => 'info',
                        default => 'gray',
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\Select::make('role')
                            ->options([
                                'owner' => 'Owner',
                                'occupant' => 'Occupant',
                            ])
                            ->required(),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
