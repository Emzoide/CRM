<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoleManagementTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Crear tabla pivot para relación roles-tiendas gestionables
        if (!Schema::hasTable('rol_tienda')) {
            Schema::create('rol_tienda', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('rol_id');
                $table->unsignedBigInteger('tienda_id');
                $table->timestamps();

                $table->foreign('rol_id')->references('id')->on('roles')->onDelete('cascade');
                $table->foreign('tienda_id')->references('id')->on('tiendas')->onDelete('cascade');
                
                $table->unique(['rol_id', 'tienda_id']);
            });
        }

        // Crear tabla pivot para relación roles-roles gestionables
        if (!Schema::hasTable('rol_gestiona_rol')) {
            Schema::create('rol_gestiona_rol', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('rol_gestor_id');
                $table->unsignedBigInteger('rol_gestionado_id');
                $table->timestamps();

                $table->foreign('rol_gestor_id')->references('id')->on('roles')->onDelete('cascade');
                $table->foreign('rol_gestionado_id')->references('id')->on('roles')->onDelete('cascade');
                
                $table->unique(['rol_gestor_id', 'rol_gestionado_id']);
            });
        }
        
        // Añadir campos JSON para almacenar meta-información
        Schema::table('roles', function (Blueprint $table) {
            // Primero verificamos si la columna es_admin existe
            if (!Schema::hasColumn('roles', 'es_admin')) {
                // Si no existe, añadimos es_admin primero
                $table->boolean('es_admin')->default(false);
            }
            
            // Luego añadimos los demás campos
            $table->json('tiendas_gestionables')->nullable();
            $table->json('roles_gestionables')->nullable();
            $table->json('niveles_acceso')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rol_tienda');
        Schema::dropIfExists('rol_gestiona_rol');
        
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn([
                'tiendas_gestionables',
                'roles_gestionables',
                'niveles_acceso'
            ]);
        });
    }
}
