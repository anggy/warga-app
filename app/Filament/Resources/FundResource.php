<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FundResource\Pages;
use App\Filament\Resources\FundResource\RelationManagers;
use App\Models\Fund;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FundResource extends Resource
{
    protected static ?string $model = Fund::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Pos Anggaran';
    protected static ?string $modelLabel = 'Pos Anggaran';
    protected static ?string $pluralModelLabel = 'Pos Anggaran';
    protected static ?string $navigationGroup = 'Keuangan';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Pos')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('description')
                    ->label('Keterangan')
                    ->maxLength(255),
                Forms\Components\TextInput::make('default_amount')
                    ->label('Nominal Default')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Pos')
                    ->searchable(),
                Tables\Columns\TextColumn::make('default_amount')
                    ->label('Nominal Default')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('allocations_sum_amount')
                    ->label('Total Anggaran')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('expenses_sum_amount')
                    ->label('Pengeluaran')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('balance')
                    ->label('Sisa')
                    ->money('IDR')
                    ->state(fn ($record) => $record->allocations_sum_amount - $record->expenses_sum_amount),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make()
                    ->label('Riwayat')
                    ->icon('heroicon-o-clock'),
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
            RelationManagers\AllocationsRelationManager::class,
            RelationManagers\ExpensesRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withSum('allocations', 'amount')
            ->withSum('expenses', 'amount');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFunds::route('/'),
            'create' => Pages\CreateFund::route('/create'),
            'view' => Pages\ViewFund::route('/{record}'),
            'edit' => Pages\EditFund::route('/{record}/edit'),
        ];
    }
}
