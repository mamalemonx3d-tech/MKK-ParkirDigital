<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tiket_parkir', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')
                ->nullable()
                ->constrained('members')
                ->nullOnDelete();
            $table->string('kode_tiket')->unique();
            $table->string('qr_code')->unique();
            $table->string('jenis')->default('tiket');
            $table->enum('jenis_kendaraan', ['mobil', 'motor'])->nullable();
            $table->string('nomor_polisi', 20)->nullable();
            $table->enum('status', ['masuk', 'keluar'])->default('masuk');
            $table->timestamp('waktu_masuk')->nullable();
            $table->timestamp('waktu_keluar')->nullable();
            $table->decimal('total_bayar', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiket_parkir');
    }
};
