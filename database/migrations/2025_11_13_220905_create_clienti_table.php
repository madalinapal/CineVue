<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('clienti')) {
            Schema::create('clienti', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
                $table->string('nume');
                $table->string('prenume');
                $table->date('data_nasterii')->nullable();
                $table->string('nr_telefon')->nullable();
                $table->string('email')->unique();
                $table->string('tip')->default('standard');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('clienti');
    }
};
