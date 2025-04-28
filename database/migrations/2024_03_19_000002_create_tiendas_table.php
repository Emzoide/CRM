<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tiendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')->constrained()->onDelete('cascade');
            $table->string('nombre');
            $table->text('direccion');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tiendas');
    }
};
