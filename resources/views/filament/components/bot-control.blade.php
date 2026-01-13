<div class="space-y-4">
    <div class="flex items-center justify-between p-4 bg-gray-100 rounded-lg dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
        <div>
            <h3 class="text-lg font-medium">Status Server Chatbot</h3>
            <p class="text-sm text-gray-500">
                @if($this->botStatus === 'connected')
                    <span class="text-green-600 font-bold">TERHUBUNG</span>
                @elseif($this->botStatus === 'disconnected')
                    <span class="text-orange-500 font-bold">TERPUTUS</span>
                @else
                    <span class="text-gray-500 font-bold">{{ strtoupper($this->botStatus ?? 'OFFLINE') }}</span>
                    @if(isset($this->botStatus) && $this->botStatus !== 'connected' && $this->botStatus !== 'disconnected')
                        <p class="text-xs text-red-500 mt-1">Debug: {{ $this->botStatus }}</p>
                    @endif
                @endif
            </p>
        </div>
        <div class="space-x-2">
            <x-filament::button wire:click="checkBotStatus" color="gray">
                Cek Status
            </x-filament::button>
            <x-filament::button wire:click="startBot" color="success">
                Start Server
            </x-filament::button>
            <x-filament::button wire:click="stopBot" color="danger">
                Stop Server
            </x-filament::button>
        </div>
    </div>

    @if($this->qrCode)
        <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800 flex flex-col items-center">
            <h4 class="mb-2 font-medium">Scan QR Code ini dengan WhatsApp Anda</h4>
            <div class="bg-white p-2">
                <img src="{{ $this->qrCode }}" alt="QR Code" class="w-64 h-64" />
            </div>
        </div>
    @endif
</div>
