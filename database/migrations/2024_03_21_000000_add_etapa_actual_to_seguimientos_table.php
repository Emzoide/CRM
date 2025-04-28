<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('seguimientos', function (Blueprint $table) {
            $table->string('etapa_actual')->nullable()->after('resultado');
        });
    }

    public function down()
    {
        Schema::table('seguimientos', function (Blueprint $table) {
            $table->dropColumn('etapa_actual');
        });
    }
};
