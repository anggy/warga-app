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
    protected static ?string $navigationGroup = 'Keuangan';

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
                    ->prefix('Rp')
                    ->label('Jumlah')
                    ->helperText(function () {
                        $total = \App\Models\Fund::where('is_active', true)->sum('default_amount');
                        $nominal = number_format($total, 0, ',', '.');
                        return "Nominal IPL standar: Rp {$nominal} (Total dari Pos Anggaran Aktif)";
                    }),
                Forms\Components\TextInput::make('period')
                    ->placeholder('YYYY-MM')
                    ->required()
                    ->label('Periode'),
                Forms\Components\DatePicker::make('paid_at')
                    ->required()
                    ->label('Tanggal Bayar'),
                Forms\Components\FileUpload::make('proof_of_transfer')
                    ->label('Bukti Transfer')
                    ->image()
                    ->directory('ipl-proofs')
                    ->visibility('public'),
                Forms\Components\Hidden::make('status')
                    ->default('pending'),
                Forms\Components\Repeater::make('allocations')
                    ->relationship()
                    ->schema([
                        Forms\Components\TextInput::make('fund_name')
                            ->label('Pos Anggaran')
                            ->disabled(),
                        Forms\Components\TextInput::make('amount')
                            ->label('Jumlah')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),
                    ])
                    ->columns(2)
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false)
                    ->visible(fn ($record) => $record && $record->status === 'paid'),
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
                Tables\Columns\ImageColumn::make('proof_of_transfer')
                    ->label('Bukti Transfer')
                    ->visibility('public'),
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
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (IplPayment $record) => $record->status === 'pending')
                    ->modalHeading('Konfirmasi Pembayaran')
                    ->modalDescription('Pastikan alokasi dana sudah sesuai sebelum menyetujui pembayaran.')
                    ->form(function () {
                        $funds = \App\Models\Fund::where('is_active', true)->get();
                        $allocations = $funds->map(fn ($fund) => [
                            'fund_id' => $fund->id,
                            'name' => $fund->name, // Visible label
                            'amount' => $fund->default_amount,
                        ])->toArray();

                        return [
                            Forms\Components\Repeater::make('allocations')
                                ->label('Rincian Alokasi Dana')
                                ->schema([
                                    Forms\Components\Hidden::make('fund_id'),
                                    Forms\Components\TextInput::make('name')
                                        ->label('Pos Anggaran')
                                        ->disabled()
                                        ->dehydrated(false), // Don't submit this
                                    Forms\Components\TextInput::make('amount')
                                        ->label('Jumlah')
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->required(),
                                ])
                                ->addable(false)
                                ->deletable(false)
                                ->reorderable(false)
                                ->default($allocations)
                        ];
                    })
                    ->action(function (IplPayment $record, array $data) {
                        // Create allocations from the submitted data
                        foreach ($data['allocations'] as $allocation) {
                            $fund = \App\Models\Fund::find($allocation['fund_id']);
                            
                            $record->allocations()->create([
                                'fund_id' => $allocation['fund_id'],
                                'fund_name' => $fund ? $fund->name : 'Unknown', // Fallback or lookup
                                'amount' => $allocation['amount'],
                            ]);
                        }

                        $record->update(['status' => 'paid']);

                        \Filament\Notifications\Notification::make()
                            ->title('Pembayaran berhasil disetujui')
                            ->success()
                            ->send();
                    }),
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
