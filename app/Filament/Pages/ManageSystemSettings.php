<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ManageSystemSettings extends Page
{
    use \Filament\Pages\Concerns\InteractsWithFormActions;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Konfigurasi Sistem';
    protected static ?string $title = 'Konfigurasi Sistem';

    protected static string $view = 'filament.pages.manage-system-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = \App\Models\SystemSetting::first();
        if ($settings) {
            $this->form->fill($settings->toArray());
        } else {
            $this->form->fill([
                'app_name' => 'Warga App',
                'map_latitude' => -6.200000,
                'map_longitude' => 106.816666,
                'map_zoom' => 13,
            ]);
        }
    }

    public function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Section::make('Informasi Aplikasi')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('app_name')
                            ->label('Nama Aplikasi')
                            ->required(),
                    ]),
                \Filament\Forms\Components\Section::make('Konfigurasi Peta')
                    ->schema([
                        \Filament\Forms\Components\Grid::make(2)
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('map_latitude')
                                    ->label('Latitude')
                                    ->numeric()
                                    ->required(),
                                \Filament\Forms\Components\TextInput::make('map_longitude')
                                    ->label('Longitude')
                                    ->numeric()
                                    ->required(),
                            ]),
                        \Filament\Forms\Components\TextInput::make('map_zoom')
                            ->label('Default Zoom Level')
                            ->numeric()
                            ->default(13)
                            ->minValue(1)
                            ->maxValue(20),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        
        $settings = \App\Models\SystemSetting::first();
        if ($settings) {
            $settings->update($data);
        } else {
            \App\Models\SystemSetting::create($data);
        }

        \Filament\Notifications\Notification::make()
            ->title('Saved successfully')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Simpan Konfigurasi')
                ->submit('save'),
        ];
    }
}
