<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('barberos', function (Blueprint $table) {
            $table->id();
            // Un barbero ES un usuario
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('barberia_id')->constrained('barberias')->onDelete('cascade');
            $table->string('foto_url')->nullable();
            $table->text('biografia')->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barberos');
    }
};
