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
    public ?string $botStatus = 'unknown';
    public ?string $qrCode = null;

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
                'bot_port' => '3001',
                'bot_session_id' => 'default',
            ]);
        }
        $this->checkBotStatus();
    }

    public function checkBotStatus()
    {
        $service = new \App\Services\WhatsappService();
        $status = $service->getStatus(); // array ['status' => ..., 'qr' => ...]
        
        $this->botStatus = $status['status'] ?? 'offline';
        if ($this->botStatus === 'offline' && isset($status['error'])) {
             $this->botStatus .= ': ' . $status['error'];
        }
        if (isset($status['qr']) && $status['qr']) {
             // Baileys returns raw QR string. We need to convert it to image source or use a QR library. 
             // Ideally we use a library, but for now let's assume the View handles it or we send raw.
             // Wait, standard img src needs base64. 
             // We can use a simple online API for dev or a JS library.
             // Let's rely on simple qrcode.js in frontend or just use an external API for quick demo?
             // No, let's just pass the raw string and let the view handle it with a library if possible, 
             // OR use a php QR generator. 
             // Actually, Baileys QR is a string. `qrcode-terminal` prints it.
             // Let's use `simplesoftwareio/simple-qrcode` if available, or just a public API for now to verify.
             // Better: use `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=` . urlencode($qr)
             $this->qrCode = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode($status['qr']);
        } else {
             $this->qrCode = null;
        }
    }

    public function startBot()
    {
        $service = new \App\Services\WhatsappService();
        $service->startServer();
        // Wait a bit
        sleep(3);
        $this->checkBotStatus();
        
        \Filament\Notifications\Notification::make()
            ->title('Start command sent')
            ->success()
            ->send();
    }

    public function stopBot()
    {
        $service = new \App\Services\WhatsappService();
        $service->stopServer();
        sleep(2);
        $this->checkBotStatus();

        \Filament\Notifications\Notification::make()
            ->title('Stop command sent')
            ->success()
            ->send();
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
                        \Filament\Forms\Components\View::make('filament.forms.components.system-map-picker'),
                        \Filament\Forms\Components\Grid::make(2)
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('map_latitude')
                                    ->label('Latitude')
                                    ->numeric()
                                    ->required()
                                    ->reactive(),
                                \Filament\Forms\Components\TextInput::make('map_longitude')
                                    ->label('Longitude')
                                    ->numeric()
                                    ->required()
                                    ->reactive(),
                            ]),
                        \Filament\Forms\Components\TextInput::make('map_zoom')
                            ->label('Default Zoom Level')
                            ->numeric()
                            ->default(13)
                            ->minValue(1)
                            ->maxValue(20),
                    ]),
                \Filament\Forms\Components\Section::make('Konfigurasi Bot WA')
                    ->schema([
                         \Filament\Forms\Components\View::make('filament.components.bot-control'),
                         \Filament\Forms\Components\Grid::make(2)
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('bot_port')
                                    ->label('Port Server')
                                    ->default('3001')
                                    ->numeric(),
                                \Filament\Forms\Components\TextInput::make('bot_session_id')
                                    ->label('Session ID')
                                    ->default('default'),
                            ]),
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
