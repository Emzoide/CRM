<?php
/**
 * Migración para mover campos de clientes a cotizaciones
 * Este archivo se debe ejecutar desde el navegador
 */

// Inicializar la aplicación Laravel
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Cliente;
use App\Models\Cotizacion;

// Contraseña para ejecutar la migración (cámbiala por una segura)
$passwordCorrecta = 'migrar123';

// Verificar la contraseña
if (!isset($_POST['password']) || $_POST['password'] !== $passwordCorrecta) {
    echo '<form method="post">
    <h1>Migración de datos de clientes a cotizaciones</h1>
    <p>Ingrese la contraseña para ejecutar la migración:</p>
    <input type="password" name="password">
    <input type="submit" value="Ejecutar migración">
    </form>';
    exit;
}

// Iniciar la migración
try {
    // Paso 1: Agregar nuevas columnas a la tabla de cotizaciones
    if (!Schema::hasColumn('cotizaciones', 'email')) {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->string('email', 100)->nullable()->after('observacion_call_center');
            $table->string('phone', 50)->nullable()->after('email');
            $table->string('address', 150)->nullable()->after('phone');
            $table->string('occupation', 100)->nullable()->after('address');
        });
        echo "<p>✅ Nuevas columnas agregadas a la tabla cotizaciones</p>";
    } else {
        echo "<p>⚠️ Las columnas ya existen en la tabla cotizaciones</p>";
    }

    // Paso 2: Migrar datos de clientes a sus cotizaciones más recientes
    $migrados = 0;
    $sinCotizaciones = 0;
    $canalesMigrados = 0;
    
    // Obtenemos todos los clientes directamente de la tabla (sin usar el modelo)
    $clientes = DB::table('clientes')->get();
    
    foreach ($clientes as $cliente) {
        // Buscar la última cotización activa del cliente
        $cotizacion = DB::table('cotizaciones')
            ->join('oportunidades', 'cotizaciones.oportunidad_id', '=', 'oportunidades.id')
            ->where('oportunidades.cliente_id', $cliente->id)
            ->where('cotizaciones.estado', 'active')
            ->orderBy('cotizaciones.emitida_en', 'desc')
            ->select('cotizaciones.id', 'oportunidades.id as oportunidad_id')
            ->first();
        
        if ($cotizacion) {
            // Actualizar la cotización con los datos del cliente
            DB::table('cotizaciones')
                ->where('id', $cotizacion->id)
                ->update([
                    'email' => $cliente->email ?? null,
                    'phone' => $cliente->phone ?? null,
                    'address' => $cliente->address ?? null,
                    'occupation' => $cliente->occupation ?? null,
                ]);
            $migrados++;
            
            // Actualizar el canal en la oportunidad si el cliente tiene un canal asociado
            if (isset($cliente->canal_id) && $cliente->canal_id) {
                DB::table('oportunidades')
                    ->where('id', $cotizacion->oportunidad_id)
                    ->update(['canal_fuente_id' => $cliente->canal_id]);
                $canalesMigrados++;
            }
        } else {
            $sinCotizaciones++;
        }
    }
    
    echo "<p>✅ Se migraron datos de $migrados clientes a sus cotizaciones</p>";
    echo "<p>✅ Se migraron $canalesMigrados canales de contacto a oportunidades</p>";
    echo "<p>⚠️ $sinCotizaciones clientes no tienen cotizaciones activas</p>";
    
    echo "<h2>Migración completada con éxito</h2>";
    echo "<p>Ahora puedes actualizar los modelos y vistas según las instrucciones.</p>";
    
} catch (Exception $e) {
    echo "<h2>Error durante la migración</h2>";
    echo "<p>Mensaje de error: " . $e->getMessage() . "</p>";
}
?>
