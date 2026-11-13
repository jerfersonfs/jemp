<?php

use App\Models\Invoice;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('invoices:refresh-payment-status', function (): void {
    $count = Invoice::refreshOverdueStatuses();
    $this->info("Notas com situação de vencimento atualizada: {$count}");
})->purpose('Atualiza automaticamente a situação de vencimento das notas não pagas');

Schedule::command('invoices:refresh-payment-status')->dailyAt('00:05')->timezone('America/Sao_Paulo');
