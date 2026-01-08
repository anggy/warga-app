<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IplPaymentResource\Pages;
use App\Filament\Resources\IplPaymentResource\RelationManagers;
use App\Models\IplPayment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IplPaymentResource extends Resource
{
    protected static ?string $model = IplPayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Pembayaran IPL';
    protected static ?string $modelLabel = 'Pembayaran IPL';
    protected static ?string $pluralModelLabel = 'Pembayaran IPL';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('house_id')
                    ->relationship('house', 'block')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "Blok {$record->block} No. {$record->number}")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Rumah'),
                Forms\Components\TextInput::make('payer_name')
                    ->label('Nama Pembayar'),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->label('Jumlah'),
                Forms\Components\TextInput::make('period')
                    ->placeholder('YYYY-MM')
                    ->required()
                    ->label('Periode'),
                Forms\Components\DatePicker::make('paid_at')
                    ->required()
                    ->label('Tanggal Bayar'),
                Forms\Components\Select::make('status')
                    ->options([
                        'paid' => 'Lunas',
                        'pending' => 'Belum Lunas',
                    ])
                    ->required()
                    ->label('Status'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('house.block')
                    ->label('Rumah')
                    ->formatStateUsing(fn ($record) => "{$record->house->block} - {$record->house->number}")
                    ->sortable(),
                Tables\Columns\TextColumn::make('payer_name')
                    ->label('Pembayar')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('period')
                    ->label('Periode')
                    ->searchable(),
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Tgl Bayar')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                     ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIplPayments::route('/'),
            'create' => Pages\CreateIplPayment::route('/create'),
            'edit' => Pages\EditIplPayment::route('/{record}/edit'),
        ];
    }
}
