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
        Schema::create('versiones_vehiculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modelo_id')->constrained('modelos')->onDelete('cascade');
            $table->string('nombre', 100);
            $table->decimal('precio_base', 10, 2);
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('versiones_vehiculos');
    }
};
