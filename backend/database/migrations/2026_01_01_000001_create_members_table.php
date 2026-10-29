<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('qr_code')->nullable()->unique();
            $table->string('nama');
            $table->string('perusahaan');
            $table->decimal('harga', 12, 2);
            $table->enum('status', ['lunas', 'belum'])->default('belum');
            $table->date('expired_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
