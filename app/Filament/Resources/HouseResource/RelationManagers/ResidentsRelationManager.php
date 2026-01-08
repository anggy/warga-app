<?php

namespace App\Filament\Resources\HouseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ResidentsRelationManager extends RelationManager
{
    protected static string $relationship = 'residents';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('full_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('nik')
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('phone')
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
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->columns([
                Tables\Columns\TextColumn::make('full_name'),
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
