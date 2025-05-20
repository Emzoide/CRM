<?php
/**
 * Script para eliminar campos variables de la tabla clientes
 * SOLO ejecutar después de haber ejecutado migracion-clientes-cotizaciones.php
 */

// Inicializar la aplicación Laravel
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

// Contraseña para ejecutar la migración (cámbiala por una segura)
$passwordCorrecta = 'migrar123';

// Verificar la contraseña
if (!isset($_POST['password']) || $_POST['password'] !== $passwordCorrecta) {
    echo '<form method="post">
    <h1>ELIMINAR campos variables de la tabla clientes</h1>
    <p style="color: red; font-weight: bold;">¡PELIGRO! Solo ejecutar después de haber migrado los datos a cotizaciones</p>
    <p>Ingrese la contraseña para ejecutar la migración:</p>
    <input type="password" name="password">
    <input type="submit" value="Eliminar campos">
    </form>';
    exit;
}

// Confirmar la eliminación
if (!isset($_POST['confirmar'])) {
    echo '<form method="post">
    <h1>CONFIRMAR eliminación de campos</h1>
    <p style="color: red; font-weight: bold;">Esta acción es irreversible. Asegúrate de haber ejecutado primero migracion-clientes-cotizaciones.php</p>
    <input type="hidden" name="password" value="' . htmlspecialchars($passwordCorrecta) . '">
    <input type="hidden" name="confirmar" value="1">
    <p><input type="submit" value="Confirmar eliminación de campos"></p>
    </form>';
    exit;
}

// Iniciar la eliminación
try {
    // Paso 1: Eliminar columnas de la tabla de clientes
    Schema::table('clientes', function (Blueprint $table) {
        // Solo ejecutamos si las columnas existen
        if (Schema::hasColumn('clientes', 'email')) {
            $table->dropColumn('email');
        }
        if (Schema::hasColumn('clientes', 'phone')) {
            $table->dropColumn('phone');
        }
        if (Schema::hasColumn('clientes', 'address')) {
            $table->dropColumn('address');
        }
        if (Schema::hasColumn('clientes', 'occupation')) {
            $table->dropColumn('occupation');
        }
        if (Schema::hasColumn('clientes', 'canal_id')) {
            $table->dropColumn('canal_id');
        }
    });
    
    echo "<p>✅ Columnas variables eliminadas de la tabla clientes</p>";
    echo "<h2>Migración completada con éxito</h2>";
    echo "<p>Se han eliminado los campos variables (email, phone, address, occupation, canal_id) de la tabla clientes.</p>";
    echo "<p>Los datos de contacto ahora se almacenan en la tabla cotizaciones y el canal de contacto en la tabla oportunidades.</p>";
    
} catch (Exception $e) {
    echo "<h2>Error durante la migración</h2>";
    echo "<p>Mensaje de error: " . $e->getMessage() . "</p>";
}
?>
