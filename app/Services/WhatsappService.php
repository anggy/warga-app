<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;

class WhatsappService
{
    protected string $baseUrl;
    protected string $sessionId;

    public function __construct()
    {
        // Priority: ConfigEnv -> DB -> Default
        $this->baseUrl = config('services.whatsapp.url', 'http://127.0.0.1:3001');
        
        $settings = \App\Models\SystemSetting::first();
        $this->sessionId = $settings->bot_session_id ?? 'default';
    }

    public function getStatus()
    {
        try {
            $response = Http::timeout(2)
                ->get("{$this->baseUrl}/api/status/{$this->sessionId}");
            
            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            // Service likely down
            return ['status' => 'offline', 'error' => $e->getMessage()];
        }

        return ['status' => 'error'];
    }

    public function startServer()
    {
        // This is tricky from web context. 
        // For Windows, we can try using 'start' to spawn a background process.
        $botPath = base_path('chatbotwa/server');
        
        // Command to start node in background
        // Windows: start /B node index.js > output.log 2>&1
        $command = "cd \"{$botPath}\" && start /B npm start > bot.log 2>&1";
        
        // Using pclose(popen(...)) is a common trick for background processes in PHP Windows
        pclose(popen("start /B cmd /c \"$command\"", "r"));
        
        return true;
    }

    public function stopServer()
    {
        try {
            // First check if online
            $status = $this->getStatus();
            if ($status['status'] === 'offline' && !isset($status['error'])) {
                 // Already offline or unreachable
                 return true;
            } else if ($status['status'] === 'offline' && isset($status['error']) && str_contains($status['error'], 'detect')) {
                 // Hard to detect if truly off or just connection refused
                 return true; 
            }

            // Call shutdown endpoint
            Http::timeout(2)->post("{$this->baseUrl}/api/shutdown");
            return true;
        } catch (\Exception $e) {
            // Unexpected error
            return false;
        }
    }
    
    public function getQrCode()
    {
        $status = $this->getStatus();
        return $status['qr'] ?? null;
    }
}
