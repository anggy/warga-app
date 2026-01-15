<?php

namespace App\Filament\Resources\FundResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AllocationsRelationManager extends RelationManager
{
    protected static string $relationship = 'allocations';

    protected static ?string $title = 'Pemasukan (In)';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('amount')
            ->columns([
                Tables\Columns\TextColumn::make('iplPayment.paid_at')
                    ->label('Tanggal Bayar')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('iplPayment.payer_name')
                    ->label('Pembayar')
                    ->searchable(),
                Tables\Columns\TextColumn::make('iplPayment.house.block')
                    ->label('Rumah')
                    ->formatStateUsing(fn ($record) => $record->iplPayment->house ? "{$record->iplPayment->house->block} - {$record->iplPayment->house->number}" : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(), // Allocations are created via payment approval only
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //    Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
