<?php

namespace App\Filament\Resources\IplPaymentResource\Pages;

use App\Filament\Resources\IplPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIplPayments extends ListRecords
{
    protected static string $resource = IplPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
