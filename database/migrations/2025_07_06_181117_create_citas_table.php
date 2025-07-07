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
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('barberia_id')->constrained('barberias')->onDelete('cascade');
            $table->foreignId('barbero_id')->constrained('barberos')->onDelete('cascade');
            $table->foreignId('servicio_id')->constrained('servicios')->onDelete('cascade');
            $table->date('fecha');
            $table->time('hora');
            $table->enum('estado', ['pendiente', 'confirmada', 'completada', 'cancelada', 'no_asistio', 'reprogramada'])->default('pendiente');
            $table->enum('metodo_pago', ['en_local', 'transferencia', 'payphone']);
            $table->enum('estado_pago', ['pendiente', 'verificado', 'pagado_en_local', 'rechazado'])->default('pendiente');
            $table->string('codigo_transaccion')->nullable();
            $table->string('comprobante_url')->nullable();
            $table->decimal('total', 8, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
