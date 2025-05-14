<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRoleManagement extends Migration
{
    /**
     * Migración para añadir las tablas de gestión de roles y permisos avanzados
     */
    public function up()
    {
        // 1. Añadir campos JSON a la tabla roles
        if (!Schema::hasColumn('roles', 'tiendas_gestionables')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->json('tiendas_gestionables')->nullable()->after('is_admin');
                $table->json('roles_gestionables')->nullable()->after('tiendas_gestionables');
                $table->json('niveles_acceso')->nullable()->after('roles_gestionables');
            });
        }

        // 2. Crear tabla pivote rol_tienda
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

        // 3. Crear tabla pivote para gestión de roles
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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Eliminar tablas nuevas
        Schema::dropIfExists('rol_gestiona_rol');
        Schema::dropIfExists('rol_tienda');
        
        // Eliminar campos añadidos
        if (Schema::hasColumn('roles', 'tiendas_gestionables')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropColumn(['tiendas_gestionables', 'roles_gestionables', 'niveles_acceso']);
            });
        }
    }
}
