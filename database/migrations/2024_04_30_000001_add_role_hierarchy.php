<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddRoleHierarchy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Agregar columna nivel a la tabla roles
        Schema::table('roles', function ($table) {
            $table->integer('nivel')->default(0)->after('descripcion');
            $table->boolean('es_admin')->default(false)->after('nivel');
        });

        // Agregar columna tienda_id a la tabla usuarios
        Schema::table('usuarios', function ($table) {
            $table->unsignedBigInteger('tienda_id')->nullable()->after('rol_id');
            $table->foreign('tienda_id')->references('id')->on('tiendas')->onDelete('set null');
        });

        // Actualizar roles existentes
        DB::table('roles')->where('nombre', 'Administrador')->update([
            'nivel' => 100,
            'es_admin' => true
        ]);

        // Crear rol de Jefe de Tienda
        DB::table('roles')->insert([
            'nombre' => 'Jefe de Tienda',
            'descripcion' => 'Responsable de gestionar una tienda específica',
            'nivel' => 50,
            'es_admin' => false,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Crear rol de Asesor
        DB::table('roles')->insert([
            'nombre' => 'Asesor',
            'descripcion' => 'Asesor de ventas',
            'nivel' => 10,
            'es_admin' => false,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Agregar permisos específicos para jefes de tienda
        $permisos = [
            [
                'nombre' => 'gestionar_asesores_tienda',
                'descripcion' => 'Permite gestionar asesores de su tienda',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'ver_estadisticas_tienda',
                'descripcion' => 'Permite ver estadísticas de su tienda',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('permisos')->insert($permisos);

        // Asignar permisos al rol de Jefe de Tienda
        $jefeTiendaRol = DB::table('roles')->where('nombre', 'Jefe de Tienda')->first();
        $permisosIds = DB::table('permisos')
            ->whereIn('nombre', ['gestionar_asesores_tienda', 'ver_estadisticas_tienda'])
            ->pluck('id')
            ->toArray();

        $rolPermisos = array_map(function ($permisoId) use ($jefeTiendaRol) {
            return [
                'rol_id' => $jefeTiendaRol->id,
                'permiso_id' => $permisoId,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }, $permisosIds);

        DB::table('rol_permiso')->insert($rolPermisos);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('usuarios', function ($table) {
            $table->dropForeign(['tienda_id']);
            $table->dropColumn('tienda_id');
        });

        Schema::table('roles', function ($table) {
            $table->dropColumn(['nivel', 'es_admin']);
        });

        DB::table('roles')->whereIn('nombre', ['Jefe de Tienda', 'Asesor'])->delete();
        DB::table('permisos')->whereIn('nombre', ['gestionar_asesores_tienda', 'ver_estadisticas_tienda'])->delete();
    }
}
