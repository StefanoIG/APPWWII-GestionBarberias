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
        Schema::create('comisiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')->constrained('citas')->onDelete('cascade');
            $table->foreignId('barbero_id')->constrained('barberos')->onDelete('cascade');
            $table->decimal('monto_barbero', 8, 2);
            $table->decimal('monto_barberia', 8, 2);
            $table->decimal('porcentaje', 5, 2); // Ej: 15.00 para 15%
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comisions');
    }
};
