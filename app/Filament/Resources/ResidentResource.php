<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResidentResource\Pages;
use App\Filament\Resources\ResidentResource\RelationManagers;
use App\Models\Resident;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ResidentResource extends Resource
{
    protected static ?string $model = Resident::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Data Warga';
    protected static ?string $modelLabel = 'Warga';
    protected static ?string $pluralModelLabel = 'Data Warga';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('full_name')
                    ->label('Nama Lengkap')
                    ->required(),
                Forms\Components\TextInput::make('nik')
                    ->label('NIK')
                    ->required(),
                Forms\Components\TextInput::make('phone')
                    ->label('No. Telepon')
                    ->tel(),
                Forms\Components\Select::make('status')
                    ->label('Status Tempat Tinggal')
                    ->options([
                        'permanent' => 'Tetap',
                        'contract' => 'Kontrak',
                        'periodic' => 'Periodik',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('family_card_number')
                    ->label('Nomor KK')
                    ->required(),
                Forms\Components\Toggle::make('is_head_of_family')
                    ->label('Kepala Keluarga?')
                    ->required(),
                Forms\Components\Select::make('family_relation')
                    ->label('Hubungan Keluarga')
                    ->options([
                        'husband' => 'Suami',
                        'wife' => 'Istri',
                        'child' => 'Anak',
                        'other' => 'Lainnya',
                    ])
                    ->required(),
                Forms\Components\Section::make('Data Pribadi')
                    ->schema([
                        Forms\Components\TextInput::make('place_of_birth')
                            ->label('Tempat Lahir'),
                        Forms\Components\DatePicker::make('date_of_birth')
                            ->label('Tanggal Lahir'),
                        Forms\Components\TextInput::make('occupation')
                            ->label('Pekerjaan'),
                        Forms\Components\Select::make('marital_status')
                            ->label('Status Perkawinan')
                            ->options([
                                'single' => 'Belum Kawin',
                                'married' => 'Kawin',
                                'divorced' => 'Cerai Hidup',
                                'widowed' => 'Cerai Mati',
                            ]),
                        Forms\Components\Select::make('religion')
                            ->label('Agama')
                            ->options([
                                'Islam' => 'Islam',
                                'Kristen' => 'Kristen',
                                'Katolik' => 'Katolik',
                                'Hindu' => 'Hindu',
                                'Buddha' => 'Buddha',
                                'Khonghucu' => 'Khonghucu',
                                'Lainnya' => 'Lainnya',
                            ]),
                    ])->columns(2),
                Forms\Components\Section::make('Dokumen')
                    ->schema([
                        Forms\Components\FileUpload::make('kk_file')
                            ->label('Upload KK')
                            ->directory('resident-documents')
                            ->image()
                            ->openable(),
                        Forms\Components\FileUpload::make('ktp_file')
                            ->label('Upload KTP')
                            ->directory('resident-documents')
                            ->image()
                            ->openable(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nama Lengkap')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nik')
                    ->label('NIK')
                    ->searchable(),
                Tables\Columns\TextColumn::make('houses.block')
                    ->label('Blok Rumah')
                    ->formatStateUsing(fn ($record) => $record->houses->map(fn($house) => "{$house->block} - {$house->number}")->join(', '))
                    ->badge()
                    ->separator(','),
                Tables\Columns\TextColumn::make('phone')
                    ->label('No. Telepon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'permanent' => 'success',
                        'contract' => 'warning',
                        'periodic' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'permanent' => 'Tetap',
                        'contract' => 'Kontrak',
                        'periodic' => 'Periodik',
                        default => $state,
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('family_card_number')
                    ->label('No. KK')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_head_of_family')
                    ->label('Kepala Keluarga')
                    ->boolean(),
                Tables\Columns\TextColumn::make('family_relation')
                    ->label('Hubungan')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'husband' => 'Suami',
                        'wife' => 'Istri',
                        'child' => 'Anak',
                        'other' => 'Lainnya',
                        'head' => 'Kepala Keluarga', // Keep for backward compatibility if needed
                        default => $state,
                    })
                    ->searchable(),
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
            RelationManagers\HousesRelationManager::class,
            RelationManagers\VehiclesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListResidents::route('/'),
            'create' => Pages\CreateResident::route('/create'),
            'edit' => Pages\EditResident::route('/{record}/edit'),
        ];
    }
}
