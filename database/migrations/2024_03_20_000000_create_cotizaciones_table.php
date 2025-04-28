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
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('oportunidad_id')->constrained('oportunidades')->onDelete('cascade');
            $table->string('codigo', 30)->unique();
            $table->timestamp('emitida_en');
            $table->timestamp('vence_en')->nullable();
            $table->foreignId('vendedor_id')->constrained('usuarios');
            $table->decimal('total', 10, 2);
            $table->string('estado');
            $table->text('motivo_rechazo')->nullable();
            $table->timestamp('rechazada_en')->nullable();
            $table->foreignId('rechazada_por')->nullable()->constrained('usuarios');
            $table->string('tipo_compra');
            $table->foreignId('banco_id')->nullable()->constrained('bancos');
            $table->string('banco_otro', 100)->nullable();
            $table->boolean('compra_plazos')->default(false);
            $table->string('razon_no_plazos', 200)->nullable();
            $table->boolean('seguro_vehicular')->default(false);
            $table->string('razon_no_seguro', 200)->nullable();
            $table->text('observacion_call_center')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cotizaciones');
    }
};
