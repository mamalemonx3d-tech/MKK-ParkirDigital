<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('parkir:clear', function () {
    \App\Models\TiketParkir::truncate();
    $this->info('Semua data tiket parkir berhasil dihapus.');
})->purpose('Hapus semua rekaman tiket parkir');
