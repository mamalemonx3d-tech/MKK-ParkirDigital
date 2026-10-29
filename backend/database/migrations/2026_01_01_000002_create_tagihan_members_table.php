<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tagihan_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')
                ->constrained('members')
                ->cascadeOnDelete();
            $table->decimal('jumlah', 12, 2);
            $table->enum('status', ['belum', 'lunas'])->default('belum');
            $table->date('tanggal_tagihan');
            $table->date('tanggal_bayar')->nullable();
            $table->timestamps();

            $table->unique(['member_id', 'tanggal_tagihan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihan_members');
    }
};
